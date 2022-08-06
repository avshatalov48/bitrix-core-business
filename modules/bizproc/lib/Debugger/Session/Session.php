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
		$this
			->setFinishedDate(new \Bitrix\Main\Type\DateTime())
			->setActive(false)
		;

		//$this->killWorkflows();
		$this->terminateWorkflows();

		return $this->save();
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

		return $this->save();
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

		return $context->save();
	}

	public function hasWorkflow(string $workflowId): bool
	{
		$ids = $this->getWorkflowContexts()->getWorkflowIdList();

		return in_array($workflowId, $ids);
	}

	public function fixateDocument(string $documentId)
	{
		if ($this->isFixed())
		{
			$result = new \Bitrix\Main\Result();
			$error = static::getErrorByCode(self::ERROR_DOCUMENT_ID_ALREADY_FIXED);
			$result->addError($error);

			return $result;
		}

		$documents = $this->getDocuments() ?? [];
		foreach ($documents as $document)
		{
			if ($document->getDocumentId() === $documentId)
			{
				$this->setFixed(true);

				continue;
			}

			$document->delete();
		}

		if (!$this->isFixed() && $this->getMode() === Mode::EXPERIMENTAL)
		{
			$this->addDocument($documentId);
			$this->setFixed(true);
		}

		if ($this->isFixed())
		{
			return $this->save();
		}

		$result = new \Bitrix\Main\Result();
		$error = static::getErrorByCode(self::ERROR_UNKNOWN_DOCUMENT_ID);
		$result->addError($error);
		$result->setData([
			'session' => $this,
			'documentId' => $documentId,
		]);

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
		if ($userId === $this->getStartedBy())
		{
			return true;
		}

		return false;
	}

	public function isStartedInDocumentType(array $parameterDocumentType): bool
	{
		[$moduleId, $entity, $documentType] = \CBPHelper::ParseDocumentId($parameterDocumentType);
		if (
			$moduleId === $this->getModuleId()
			&& $entity === $this->getEntity()
			&& $documentType === $this->getDocumentType()
		)
		{
			return true;
		}

		return false;
	}

	public function isFixedDocument(array $parameterDocumentId): bool
	{
		$documents = $this->getDocuments() ?? [];
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
		elseif ($code === static::ERROR_UNKNOWN_DOCUMENT_ID)
		{
			return new \Bitrix\Main\Error(
				Loc::getMessage('BIZPROC_DEBUGGER_SESSION_ERROR_UNKNOWN_DOCUMENT_ID'),
				self::ERROR_UNKNOWN_DOCUMENT_ID
			);
		}

		return null;
	}
}
