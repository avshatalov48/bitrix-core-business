<?php

namespace Bitrix\Bizproc\Debugger\Session;

use Bitrix\Bizproc\Automation\Engine\Template;
use Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionDocumentTable;
use Bitrix\Bizproc\Debugger\Session\Entity\DebuggerSessionWorkflowContextTable;
use Bitrix\Bizproc\Service\Entity\EO_Tracking;
use Bitrix\Bizproc\Service\Entity\TrackingTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

class Session extends Entity\EO_DebuggerSession
{
	public const ERROR_DOCUMENT_ID_ALREADY_FIXED = 'DOCUMENT ID IS ALREADY FIXED';
	public const ERROR_UNKNOWN_DOCUMENT_ID = 'FIXING UNKNOWN DOCUMENT ID';

	public function canUserFinish(int $userId): bool
	{
		// or admin ?
		return $this->isStartedByUser($userId);
	}

	public function canUserDebug(int $userId): bool
	{
		// or admin ?
		return $this->isStartedByUser($userId);
	}

	public function finish()
	{
		if ($this->isBeforeDebuggerStartState())
		{
			$this->fillDocuments();
			$documents = clone($this->getDocuments());
			if ($documents)
			{
				foreach ($documents as $document)
				{
					$this->removeFromDocuments($document);
				}
			}
		}

		$this
			->setFinishedDate(new \Bitrix\Main\Type\DateTime())
			->setActive(false)
		;

		//$this->killWorkflows();
		$this->terminateWorkflows();

		$result = $this->save();
		if ($result->isSuccess() && $this->isFixed())
		{
			$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

			$documentService->onDebugSessionDocumentStatusChanged(
				$this->getFixedDocument()->getParameterDocumentId(),
				$this->getStartedBy(),
				DocumentStatus::FINISHED
			);
		}

		return $result;
	}

	public function deleteAll(): Result
	{
		$result = new Result();

		$this->deleteWorkflows();
		$result->addErrors($this->deleteDocuments()->getErrors());
		$result->addErrors($this->delete()->getErrors());

		return $result;
	}

	public function deleteWorkflows()
	{
		$this->killWorkflows();
	}

	public function deleteDocuments(): Result
	{
		$result = new Result();

		foreach ($this->getDocuments() as $document)
		{
			$deletionResult = $document->delete();

			if (!$deletionResult->isSuccess())
			{
				$result->addErrors($deletionResult->getErrors());
			}
		}

		return $result;
	}

	private function killWorkflows()
	{
		$workflowContexts = $this->getWorkflowContexts();

		if ($workflowContexts)
		{
			foreach ($workflowContexts as $context)
			{
				\CBPDocument::killWorkflow($context->getWorkflowId());

				$shouldSaveTemplateShards = (bool)DebuggerSessionWorkflowContextTable::getList([
					'filter' => [
						'!=ID' => $context->getId(),
						'TEMPLATE_SHARDS_ID' => $context->getTemplateShardsId(),
					],
					'limit' => 1,
				])->fetchObject();

				if (!$shouldSaveTemplateShards)
				{
					$shards = $context->fillTemplateShards();
					if ($shards)
					{
						$shards->delete();
					}
				}

				$context->delete();
			}
		}
	}

	private function terminateWorkflows(): Result
	{
		$workflowContexts = $this->getWorkflowContexts();
		$result = new Result();

		if ($workflowContexts)
		{
			foreach ($workflowContexts as $context)
			{
				\CBPDocument::TerminateWorkflow($context->getWorkflowId(), null, $errors);

				foreach ($errors as $rawError)
				{
					$error = new Error(
						$rawError['message'],
						$rawError['code'],
						['workflowId' => $context->getWorkflowId()]
					);
					$result->addError($error);
				}
			}
		}

		return $result;
	}

	public function addDocument(string $documentId)
	{
		if ($this->isFixed())
		{
			$result = new \Bitrix\Main\Result();
			$error = static::getErrorByCode(self::ERROR_DOCUMENT_ID_ALREADY_FIXED);
			$result->addError($error);

			return $result;
		}

		$document = DebuggerSessionDocumentTable::createObject();
		$document
			->setSessionId($this->getId())
			->setDocumentId($documentId)
		;

		if ($this->getMode() === Mode::EXPERIMENTAL)
		{
			$document->setDateExpire(null);
		}

		$this->addToDocuments($document);

		$result = $this->save();
		if ($result->isSuccess() && $this->getMode() === Mode::INTERCEPTION)
		{
			$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
			$parameterDocumentId = $this->getParameterDocumentType();
			$parameterDocumentId[2] = $documentId;

			$documentService->onDebugSessionDocumentStatusChanged(
				$parameterDocumentId,
				$this->getStartedBy(),
				DocumentStatus::INTERCEPTED
			);
		}

		return $result;
	}

	public function removeFromDocuments(\Bitrix\Bizproc\Debugger\Session\Document $debuggerSessionDocument): void
	{
		$documentId = $debuggerSessionDocument->getDocumentId();
		$parameterDocumentId = $this->getParameterDocumentType();
		$parameterDocumentId[2] = $documentId;

		parent::removeFromDocuments($debuggerSessionDocument);
		$debuggerSessionDocument->delete();

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

		$documentService->onDebugSessionDocumentStatusChanged(
			$parameterDocumentId,
			$this->getStartedBy(),
			DocumentStatus::REMOVED
		);

		/** @var \Bitrix\Bizproc\Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($this->getParameterDocumentType());
		$target->setDocumentId($documentId);

		$target->getRuntime()->runDocumentStatus();
	}

	/**
	 * @param string $workflowId
	 * @param Template | array $template
	 * @return \Bitrix\Main\ORM\Data\Result
	 */
	public function addWorkflowContext(string $workflowId, $template): \Bitrix\Main\ORM\Data\Result
	{
		if ($this->hasWorkflow($workflowId))
		{
			$contextRow = DebuggerSessionWorkflowContextTable::getRow([
				'filter' => ['WORKFLOW_ID' => $workflowId],
			]);

			$context = DebuggerSessionWorkflowContextTable::wakeUpObject($contextRow);
		}
		else
		{
			$context = DebuggerSessionWorkflowContextTable::createObject();
			$context
				->setWorkflowId($workflowId)
				->setSessionId($this->getId())
			;
		}

		$context->addTemplateShards($template);
		$result = $context->save();

		if ($result->isSuccess())
		{
			$this->addToWorkflowContexts($context);
		}

		return $result;
	}

	public function hasWorkflow(string $workflowId): bool
	{
		$ids = $this->getWorkflowContexts()->getWorkflowIdList();

		return in_array($workflowId, $ids);
	}

	public function fixateDocument(string $documentId)
	{
		if (!$this->canAddDocument())
		{
			$result = new \Bitrix\Main\Result();
			$error = static::getErrorByCode(self::ERROR_DOCUMENT_ID_ALREADY_FIXED);
			$result->addError($error);

			return $result;
		}

		if ($this->getMode() === Mode::EXPERIMENTAL)
		{
			$addDocumentResult = $this->addDocument($documentId);
			if ($addDocumentResult->isSuccess())
			{
				$this->setFixed(true);

				$result = $this->save();
				if ($result->isSuccess())
				{
					$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

					$documentService->onDebugSessionDocumentStatusChanged(
						$this->getFixedDocument()->getParameterDocumentId(),
						$this->getStartedBy(),
						DocumentStatus::IN_DEBUG
					);
				}

				return $result;
			}

			return $addDocumentResult;
		}

		$documents = clone($this->getDocuments());
		$documentIds = $documents ? $documents->getDocumentIdList() : [];

		if (!in_array($documentId, $documentIds, true))
		{
			$result = new \Bitrix\Main\Result();
			$error = static::getErrorByCode(self::ERROR_UNKNOWN_DOCUMENT_ID);
			$result->addError($error);
			$result->setData([
				'session' => $this,
				'documentId' => $documentId,
			]);

			return $result;
		}

		foreach ($documents as $document)
		{
			if ($document->getDocumentId() === $documentId)
			{
				$this->setFixed(true);

				continue;
			}

			$this->removeFromDocuments($document);
		}

		$result = $this->save();
		if ($result->isSuccess())
		{
			$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

			$documentService->onDebugSessionDocumentStatusChanged(
				$this->getFixedDocument()->getParameterDocumentId(),
				$this->getStartedBy(),
				DocumentStatus::IN_DEBUG
			);
		}

		return $result;
	}

	public function isActive(): bool
	{
		return $this->getActive();
	}

	public function isFixed(): bool
	{
		return $this->getFixed();
	}

	public function getParameterDocumentType(): array
	{
		return [
			$this->getModuleId(),
			$this->getEntity(),
			$this->getDocumentType(),
		];
	}

	public function getFixedDocument(): ?\Bitrix\Bizproc\Debugger\Session\Document
	{
		if ($this->isFixed())
		{
			$document = $this->getDocuments()->getAll()[0];
			if ($document)
			{
				$document->setSession($this);
			}

			return $document;
		}

		return null;
	}

	public function isStartedByUser(int $userId): bool
	{
		return $userId === $this->getStartedBy();
	}

	public function isStartedInDocumentType(array $parameterDocumentType): bool
	{
		[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);

		return (
			$moduleId === $this->getModuleId()
			&& $entity === $this->getEntity()
			&& $documentType === $this->getDocumentType()
		);
	}

	public function isFixedDocument(array $parameterDocumentId): bool
	{
		$documents = $this->getDocuments();
		if (!$documents)
		{
			return false;
		}

		foreach ($documents as $document)
		{
			$document->setSession($this);
			if ($parameterDocumentId === $document->getParameterDocumentId())
			{
				return true;
			}
		}

		return false;
	}

	public function isSessionDocument(array $parameterDocumentId): bool
	{
		return $this->isFixedDocument($parameterDocumentId);
	}

	public function getShortDescription(): ?string
	{
		return \Bitrix\Main\Localization\Loc::getMessage(
			'BIZPROC_DEBUGGER_SESSION_SESSION_SHORT_DESCRIPTION',
			[
				'#USER#' => $this->getStartedByPrintable(),
				'#ENTITY#' => $this->getDocumentTypeCaption(),
			]
		);
	}

	public function getDescription(): ?string
	{
		return \Bitrix\Main\Localization\Loc::getMessage(
			'BIZPROC_DEBUGGER_SESSION_SESSION_DESCRIPTION',
			[
				'#USER#' => $this->getStartedByPrintable(),
				'#ENTITY#' => $this->getDocumentTypeCaption(),
				'#DATE#' => $this->getStartedDate()->toUserTime(),
			]
		);
	}

	/**
	 * Returns true if the session mode is Experimental and false otherwise.
	 * @return bool
	 */
	public function isExperimentalMode(): bool
	{
		return ($this->getMode() === Mode::EXPERIMENTAL);
	}

	/**
	 * Returns true if the session mode is Interception and false otherwise.
	 * @return bool
	 */
	public function isInterceptionMode(): bool
	{
		return ($this->getMode() === Mode::INTERCEPTION);
	}

	/**
	 * Returns true if can add document to session documents and false otherwise
	 * @return bool
	 */
	public function canAddDocument(): bool
	{
		return !$this->isFixed();
	}

	/**
	 * @return bool
	 */
	public function isBeforeDebuggerStartState(): bool
	{
		return $this->isInterceptionMode() && $this->canAddDocument();
	}

	private function getStartedByPrintable(): string
	{
		$format = \CSite::GetNameFormat(false);
		$user = \CUser::GetList(
			'id',
			'asc',
			['ID' => $this->getStartedBy()],
			[
				'FIELDS' => [
					'TITLE',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'NAME_SHORT',
					'LAST_NAME_SHORT',
					'SECOND_NAME_SHORT',
					'EMAIL',
					'ID'
				],
			]
		)->Fetch();

		return $user ? \CUser::FormatName($format, $user, true, false) : '';
	}

	private function getDocumentTypeCaption()
	{
		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService('DocumentService');

		return $documentService->getDocumentTypeCaption($this->getParameterDocumentType());
	}

	public function toArray(): array
	{
		$documents = [];

		$this->fillDocuments();
		$sessionDocuments = $this->getDocuments() ?? [];
		foreach ($sessionDocuments as $document)
		{
			$document->setSession($this);
			$documents[] = $document->toArray();
		}

		return [
			'Id' => $this->getId(),
			'Mode' => $this->getMode(),
			'StartedBy' => $this->getStartedBy(),
			'Active' => $this->getActive(),
			'Fixed' => $this->getFixed(),
			'Documents' => $documents,
			'ShortDescription' => $this->getShortDescription(),
			'CategoryId' => $this->getDocumentCategoryId(),
		];
	}

	/**
	 * @return EO_Tracking[]
	 */
	public function getLogs(): array
	{
		$workflowIds = [];
		foreach ($this->getWorkflowContexts() as $context)
		{
			$workflowIds[] = $context->getWorkflowId();
		}

		return TrackingTable::getList([
			'filter' => [
				'@WORKFLOW_ID' => $workflowIds,
			],
			'order' => ['ID'],
		])->fetchCollection()->getAll();
	}

	public function getRobots(): array
	{
		$workflowRobots = [];
		$this->getWorkflowContexts()->fillTemplateShards();
		foreach ($this->getWorkflowContexts() as $context)
		{
			$workflowId = $context->getWorkflowId();
			$templateShards = $context->getTemplateShards();

			$workflowRobots[$workflowId] = $templateShards ? $templateShards->getRobotData() : [];
		}

		return $workflowRobots;
	}

	private static function getErrorByCode(string $code): ?\Bitrix\Main\Error
	{
		if ($code === static::ERROR_DOCUMENT_ID_ALREADY_FIXED)
		{
			return new \Bitrix\Main\Error(
				Loc::getMessage('BIZPROC_DEBUGGER_SESSION_ERROR_DOCUMENT_ID_ALREADY_FIXED'),
				self::ERROR_DOCUMENT_ID_ALREADY_FIXED
			);
		}

		if ($code === static::ERROR_UNKNOWN_DOCUMENT_ID)
		{
			return new \Bitrix\Main\Error(
				Loc::getMessage('BIZPROC_DEBUGGER_SESSION_ERROR_UNKNOWN_DOCUMENT_ID_1'),
				self::ERROR_UNKNOWN_DOCUMENT_ID
			);
		}

		return null;
	}
}
