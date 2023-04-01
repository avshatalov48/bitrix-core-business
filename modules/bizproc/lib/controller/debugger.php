<?php

namespace Bitrix\Bizproc\Controller;

use Bitrix\Bizproc\Automation;
use Bitrix\Bizproc\Automation\Engine\Template;
use Bitrix\Bizproc\Debugger\Listener;
use Bitrix\Bizproc\Debugger\Session\Session;
use Bitrix\Bizproc\Debugger\Workflow\DebugWorkflow;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Bizproc\Debugger\Session\DebuggerState;
use Bitrix\Main\Engine\ActionFilter\ContentType;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Bizproc\Debugger\Session\Manager;

class Debugger extends Base
{
	public function configureActions()
	{
		return [
			'finishDebugSession' => [
				'+prefilters' => [
					new ContentType([ContentType::JSON])
				],
			],
		];
	}

	public function fillAutomationViewAction(string $sessionId): ?array
	{
		$session = $this->getSession($sessionId);

		if (!$session)
		{
			return null;
		}

		$isBeforeDebuggerStartState = $session->isBeforeDebuggerStartState();

		if ($isBeforeDebuggerStartState)
		{
			$documentId = null;
			$documentType = $session->getParameterDocumentType();
		}
		else
		{
			[$documentId, $documentType] = $this->getActiveDocument($session);

			if (!$documentId)
			{
				return null;
			}
		}

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		/** @var Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($documentType);

		$target->setDocumentId($documentId);
		[$status, $statusList] = $this->getStatus($target, $session);

		$template = new Template($documentType, $status);
		$documentFields = $isBeforeDebuggerStartState ? [] : $this->getDocumentFields($documentType);

		$triggers = $target->getTriggers(array_keys($statusList));
		$target->prepareTriggersToShow($triggers);

		$workflowId = $target->getRuntime()->getCurrentWorkflowId();
		$hasInstance = $workflowId && WorkflowInstanceTable::exists($workflowId);

		$workflowStatus = $hasInstance ? \CBPWorkflowStatus::Suspended : \CBPWorkflowStatus::Completed;
		$workflowEvents = [];
		$debuggerState = Manager::getDebuggerState()->getId();

		if ($hasInstance)
		{
			/** @var DebugWorkflow $workflow */
			$workflow = \CBPRuntime::GetRuntime(true)->getWorkflow($workflowId, true);
			foreach ($workflow->getDebugEventIds() as $eventName)
			{
				foreach ($template->getRobots() as $robot)
				{
					if ($robot->getDelayName() === $eventName)
					{
						$workflowEvents[] = [
							'name' => $eventName,
							'sourceId' => $robot->getName(),
						];
						break;
					}
				}
			}
		}

		$documentCategoryId =
			$isBeforeDebuggerStartState
				? $session->getDocumentCategoryId()
				: $target->getDocumentCategory()
		;

		$documentValues =
			$isBeforeDebuggerStartState
				? []
				: $this->getDocumentValues($documentType, $target->getComplexDocumentId(), $documentFields)
		;

		return [
			'triggers' => $triggers,
			'template' => Automation\Component\Base::getTemplateViewData($template->toArray(), $documentType),
			'documentId' => $documentId,
			'documentStatus' => $status,
			'statusList' => array_values($statusList),
			'documentCategoryId' => $documentCategoryId,
			'documentFields' => array_values($documentFields),
			'documentValues' => $documentValues,
			'workflowId' => $workflowId,
			'workflowStatus' => $workflowStatus,
			'workflowEvents' => $workflowEvents,
			'debuggerState' => $debuggerState,
			'track' => $this->getTrack($workflowId, $workflowStatus),
			'globalVariables' => array_values(Automation\Helper::getGlobalVariables($documentType)),
			'globalConstants' => array_values(Automation\Helper::getGlobalConstants($documentType)),
		];
	}

	private function getDocumentFields(array $documentType): array
	{
		$fields = Automation\Helper::getDocumentFields($documentType);

		foreach ($fields as $id => $field)
		{
			if (
				FieldType::isBaseType($field['Type'])
				&& strpos($field['Id'], '.') === false
				&& strpos($field['Id'], '_PRINTABLE') === false
				&& strpos($field['Id'], '_IDS') === false
			)
			{
				$fields[$id]['Watchable'] = true;
			}
		}

		return $fields;
	}

	private function getDocumentValues(array $documentType, array $documentId, array $fields): array
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$lazyList = $documentService->GetDocument($documentId);

		$values = [];

		foreach ($fields as $fieldId => $property)
		{
			if (empty($property['Watchable']))
			{
				continue;
			}

			$fieldType = $documentService->getFieldTypeObject($documentType, $property);
			$fieldType->setDocumentId($documentId);

			$values[$fieldId] = $fieldType->formatValue($lazyList[$fieldId]);
		}

		return $values;
	}

	public function setDocumentStatusAction($statusId): ?array
	{
		$session = $this->getSession();

		if (!$session)
		{
			return null;
		}

		[$documentId, $documentType] = $this->getActiveDocument($session);

		if (!$documentId)
		{
			return null;
		}

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		/** @var Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($documentType);

		$target->setDocumentId($documentId);

		if ($target->getDocumentStatus() === $statusId)
		{
			return null;
		}

		$target->setDocumentStatus($statusId);
		$target->getRuntime()->onDocumentStatusChanged();

		$template = new Template($documentType, $statusId);

		return [
			'newStatus' => $statusId,
			'template' => Automation\Component\Base::getTemplateViewData($template->toArray(), $documentType),
		];
	}

	public function resumeAutomationTemplateAction(string $sessionId)
	{
		$session = $this->getSession($sessionId);

		if (!$session)
		{
			return null;
		}

		[$documentId, $documentType] = $this->getActiveDocument($session);

		if (!$documentId)
		{
			return null;
		}

		$currentState = Manager::getDebuggerState();

		if ($currentState->is(DebuggerState::RUN))
		{
			$newState = DebuggerState::pause();
		}
		else
		{
			$newState = DebuggerState::run();
		}

		Manager::setDebuggerState($newState);

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$automationTarget = $documentService->createAutomationTarget($documentType);
		$automationTarget->setDocumentId($documentId);

		$workflowId = $automationTarget->getRuntime()->getCurrentWorkflowId();
		$hasInstance = $workflowId && WorkflowInstanceTable::exists($workflowId);

		if ($hasInstance && $newState->is(DebuggerState::RUN))
		{
			/** @var DebugWorkflow $workflow */
			$workflow = \CBPRuntime::GetRuntime(true)->getWorkflow($workflowId);
			$workflow->resume();
		}

		return [
			'workflowId' => $workflowId,
			'debuggerState' => $newState->getId(),
		];
	}

	public function emulateExternalEventAction(string $workflowId, string $eventId): ?bool
	{
		$session = $this->getSession();

		if (!$session)
		{
			return null;
		}

		if (!$session->hasWorkflow($workflowId) || !WorkflowInstanceTable::exists($workflowId))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_WORKFLOW')));

			return null;
		}

		$runtime = \CBPRuntime::GetRuntime(true);
		$workflow = $runtime->getWorkflow($workflowId);

		if (!($workflow instanceof DebugWorkflow))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_WORKFLOW')));

			return null;
		}

		$workflow->sendDebugEvent($eventId);

		return true;
	}

	/** ends debug session */
	public function finishDebugSessionAction(string $sessionId, bool $deleteDocument = false): ?array
	{
		$session = $this->getSession($sessionId);

		if (!$session)
		{
			return null;
		}

		if (!$session->canUserFinish($this->getCurrentUser()->getId()))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_CAN_FINISH_ERROR')));

			return null;
		}

		$toDeleteDocument = $session->isExperimentalMode() && $deleteDocument ? $session->getFixedDocument() : null;

		$result = Manager::finishSession($session);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrors());
		}
		else
		{
			Listener::getInstance()->onSessionFinished($sessionId);

			if ($toDeleteDocument)
			{
				$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
				$documentService->deleteDocument($toDeleteDocument->getParameterDocumentId());
			}
		}

		return null;
	}

	public function loadAllLogAction(string $sessionId): ?array
	{
		$userId = (int)$this->getCurrentUser()->getId();

		$session = \Bitrix\Bizproc\Debugger\Session\Manager::getSessionById($sessionId);
		if (!$session)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_SESSION')));

			return null;
		}

		if (!$session->canUserDebug($userId))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_CAN_DEBUG_ERROR')));

			return null;
		}

		$logs = [];

		$trackingResult = new \CBPTrackingServiceResult();
		$trackingResult->InitFromArray($session->getLogs());

		while ($log = $trackingResult->fetch())
		{
			/** @var $log \Bitrix\Bizproc\Service\Entity\EO_Tracking*/
			$values = $log->collectValues();
			$values['MODIFIED'] = (string)($values['MODIFIED']);
			$logs[] = $values;
		}

		return [
			'logs' => $logs,
			'workflowRobots' => $session->getRobots(),
		];
	}

	private function getTrack(?string $workflowId, int $workflowStatus): array
	{
		$rows = [];

		if ($workflowId)
		{
			$trackResult = \CBPTrackingService::GetList(['ID' => 'ASC'], ['WORKFLOW_ID' => $workflowId]);

			while ($row = $trackResult->fetch())
			{
				$row['WORKFLOW_STATUS'] = $workflowStatus;
				$rows[] = $row;
			}
		}

		return $rows;
	}

	private function getSession(string $id = null): ?Session
	{
		$userId = $this->getCurrentUser()->getId();

		$session = $id ? Manager::getSessionById($id) : Manager::getActiveSession();
		if (!$session)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_SESSION')));

			return null;
		}

		if (!$session->canUserDebug($userId))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_CAN_DEBUG_ERROR')));

			return null;
		}

		return $session;
	}

	private function getActiveDocument(Session $session): array
	{
		$document = $session->getFixedDocument();

		if (!$document || !$this->isDocumentExists($document->getParameterDocumentId()))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_DOCUMENT'), 404));

			return [null, null];
		}

		return [$document->getDocumentId(), $session->getParameterDocumentType()];
	}

	private function getStatus(Automation\Target\BaseTarget $target, Session $session): array
	{
		$isBeforeDebuggerStartState = $session->isBeforeDebuggerStartState();

		if ($isBeforeDebuggerStartState)
		{
			$statusList = $target->getDocumentStatusList($session->getDocumentCategoryId());
			$status = array_key_first($statusList);

			return [$status, $statusList];
		}

		return [$target->getDocumentStatus(), $target->getDocumentStatusList($target->getDocumentCategory())];
	}

	private function isDocumentExists(array $documentId): bool
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

		try
		{
			$documentService->getDocumentType($documentId);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
	}

	/** starts a debug session */
	public function startSessionAction(string $documentSigned, int $mode): ?array
	{
		[$documentType, $documentCategoryId] = \CBPDocument::unSignParameters($documentSigned);
		$userId = (int)$this->getCurrentUser()->getId();

		if (!Manager::canUserDebugAutomation($userId, $documentType))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_CAN_DEBUG_ERROR')));

			return null;
		}

		$result = Manager::startSession($documentType, $mode, $userId, $documentCategoryId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrorCollection()->toArray());

			return null;
		}

		/** @var Session $session */
		$session = $result->getObject();
		if ($session->getMode() === \Bitrix\Bizproc\Debugger\Session\Mode::EXPERIMENTAL)
		{
			$documentId = $this->createExperimentalDocument($documentType, $documentCategoryId);
			if (!$documentId)
			{
				Manager::finishSession($session);
				$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_DOCUMENT')));

				return null;
			}

			return $this->fixateSessionDocumentAction($documentId);
		}

		return [
			'documentSigned' => \CBPDocument::signParameters([$documentType]),
			'session' => $session->toArray(),
		];
	}

	private function createExperimentalDocument(array $documentType, string $documentCategoryId): ?string
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();

		$fields = [
			'TITLE' => \Bitrix\Main\Localization\Loc::getMessage(
				'BIZPROC_CONTROLLER_DEBUGGER_DOCUMENT_TITLE',
				['#ENTITY#' => $documentService->getDocumentTypeName($documentType)]
			),
			'CATEGORY_ID' => (int)$documentCategoryId
		];

		return $documentService->createTestDocument(
			$documentType,
			$fields,
			(int)(\Bitrix\Main\Engine\CurrentUser::get()->getId())
		);
	}

	public function fixateSessionDocumentAction(string $documentId): ?array
	{
		$session = $this->getSession();
		if (!$session)
		{
			return null;
		}

		$result = $session->fixateDocument($documentId);
		if (!$result->isSuccess())
		{
			$this->addErrors($result->getErrorCollection()->toArray());

			return null;
		}

		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		/** @var Automation\Target\BaseTarget $target */
		$target = $documentService->createAutomationTarget($session->getParameterDocumentType());
		$target->setDocumentId($documentId);
		$target->getRuntime()->onDocumentAdd();

		/** @var Session $updatedSession */
		$updatedSession = $result->getObject();

		return [
			'documentSigned' => $updatedSession->getFixedDocument()->getSignedDocument(),
			'session' => $updatedSession->toArray(),
		];
	}

	public function removeSessionDocumentAction(array $documentIds = []): ?array
	{
		$session = $this->getSession();

		if (!$session)
		{
			return null;
		}

		$documents = clone($session->getDocuments());

		foreach ($documents as $document)
		{
			if (in_array($document->getDocumentId(), $documentIds, true))
			{
				$session->removeFromDocuments($document);
			}
		}

		return [
			'session' => $session,
		];
	}

	public function loadRobotsByWorkflowIdAction(string $sessionId, string $workflowId): ?array
	{
		$userId = (int)$this->getCurrentUser()->getId();

		$session = \Bitrix\Bizproc\Debugger\Session\Manager::getSessionById($sessionId);
		if (!$session)
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_SESSION')));

			return null;
		}

		if (!$session->canUserDebug($userId))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_CAN_DEBUG_ERROR')));

			return null;
		}

		if (!$session->hasWorkflow($workflowId))
		{
			$this->addError(new Error(Loc::getMessage('BIZPROC_CONTROLLER_DEBUGGER_NO_WORKFLOW')));

			return null;
		}

		$robots = [];

		foreach ($session->getWorkflowContexts() as $context)
		{
			if ($context->getWorkflowId() !== $workflowId)
			{
				continue;
			}

			$templateShards = $context->fillTemplateShards();
			$robots = $templateShards ? $templateShards->getRobotData() : [];
			break;
		}

		return [
			'workflowRobots' => $robots
		];
	}
}
