<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Workflow\Entity\WorkflowInstanceTable;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Main\Config\Option;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Runtime\Starter\Context;

Loc::loadMessages(__FILE__);

class Runtime
{
	use Bizproc\Debugger\Mixins\WriterDebugTrack;

	protected $target;
	protected static $startedTemplates = [];

	public function setTarget(BaseTarget $target)
	{
		$this->target = $target;
		return $this;
	}

	/**
	 * @return BaseTarget
	 * @throws InvalidOperationException
	 */
	public function getTarget()
	{
		if ($this->target === null)
		{
			throw new InvalidOperationException('Target must be set by setTarget method.');
		}

		return $this->target;
	}

	protected function getWorkflowInstanceIds()
	{
		$documentType = $this->getTarget()->getDocumentType();
		$documentId = $this->getTarget()->getDocumentId();
		$ids = WorkflowInstanceTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=MODULE_ID' => $documentType[0],
				'=ENTITY' => $documentType[1],
				'=DOCUMENT_ID' => $documentId,
				'@STARTED_EVENT_TYPE' => [\CBPDocumentEventType::Automation, \CBPDocumentEventType::Debug],
				'=TEMPLATE.DOCUMENT_TYPE' => $documentType[2],
			],
		])->fetchAll();

		return array_column($ids, 'ID');
	}

	public function getCurrentWorkflowId(): ?string
	{
		$documentType = $this->getTarget()->getDocumentType();

		$template = new Template(
			$documentType,
			$this->getTarget()->getDocumentStatus()
		);

		if ($template->getId() > 0)
		{
			$filter = [
				'=DOCUMENT_ID' => $this->getTarget()->getDocumentId(),
				'=TEMPLATE.ID' => $template->getId(),
			];

			if ($this->isDebug())
			{
				$session = Bizproc\Debugger\Session\Manager::getActiveSession();
				$filter['@ID'] = $session->getWorkflowContexts()->getWorkflowIdList();
			}

			$row = WorkflowStateTable::getList([
				'select' => ['ID'],
				'filter' => $filter,
				'order' => ['STARTED' => 'DESC'],
				'limit' => 1,
			])->fetch();

			return $row ? $row['ID'] : null;
		}

		return null;
	}

	protected function runTemplates($documentStatus, string $preGeneratedWorkflowId = null)
	{
		$isDebug = $this->isDebug();
		$template = new Template($this->getTarget()->getDocumentType(), $documentStatus);
		$workflowId = null;

		if ($template->getId() > 0)
		{
			$errors = [];
			$trigger = $this->getTarget()->getAppliedTrigger();
			$this->getTarget()->setAppliedTrigger([]);

			if (
				!$template->isExternalModified()
				&& !$isDebug
				&& !$trigger
				&& !$template->getRobots()
			)
			{
				return false;
			}

			$documentType = $this->getTarget()->getDocumentType();
			$documentId = $this->getTarget()->getDocumentId();
			$documentComplexId = [$documentType[0], $documentType[1], $documentId];
			$useForcedTracking = $this->canUseForcedTracking() && !$template->isExternalModified();

			$startParameters = [
				\CBPDocument::PARAM_TAGRET_USER => null, //Started by System
				\CBPDocument::PARAM_USE_FORCED_TRACKING => $isDebug || $useForcedTracking,
				\CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT => true,
				\CBPDocument::PARAM_DOCUMENT_TYPE => $documentType,
				\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE =>
					$isDebug ? \CBPDocumentEventType::Debug : \CBPDocumentEventType::Automation,
				\CBPDocument::PARAM_PRE_GENERATED_WORKFLOW_ID => $preGeneratedWorkflowId ?? null,
			];

			if (isset($trigger['RETURN']) && is_array($trigger['RETURN']))
			{
				$startParameters += $trigger['RETURN'];
			}

			foreach ($template->getParameters() as $parameterId => $parameter)
			{
				if (!isset($startParameters[$parameterId]) && isset($parameter['Default']))
				{
					$startParameters[$parameterId] = $parameter['Default'];
				}
			}

			$this->setStarted($documentType[2], $documentId, $documentStatus);

			$args = [$template->getId(), $documentComplexId, $startParameters, $errors];

			if ($isDebug && $preGeneratedWorkflowId)
			{
				$session = Bizproc\Debugger\Session\Manager::getActiveSession();
				$session->addWorkflowContext($preGeneratedWorkflowId, $template);
			}

			$workflowId = $isDebug
				? \CBPDocument::startDebugWorkflow(...$args)
				: \CBPDocument::startWorkflow(...$args)
			;

			if (!$errors && $workflowId)
			{
				if ($trigger)
				{
					$this->writeTriggerTracking($workflowId, $trigger);
					$this->writeTriggerAnalytics($documentComplexId, $trigger);
				}
			}
		}

		return $workflowId;
	}

	protected function writeTriggerTracking($workflowId, $trigger)
	{
		$trackingService = \CBPRuntime::getRuntime(true)->getTrackingService();

		$trackingService->write(
			$workflowId,
			\CBPTrackingType::Trigger,
			'APPLIED_TRIGGER',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			'',
			$trigger['ID']
		);
	}

	protected function writeTriggerAnalytics(array $documentId, array $trigger)
	{
		$analyticsService = \CBPRuntime::getRuntime(true)->getAnalyticsService();
		if ($analyticsService->isEnabled())
		{
			$analyticsService->write($documentId, 'trigger_run', $trigger['CODE']);
		}
	}

	protected function stopTemplates()
	{
		$errors = [];
		$instanceIds = $this->getWorkflowInstanceIds();
		$documentType = $this->getTarget()->getDocumentType();
		$documentId = [$documentType[0], $documentType[1], $this->getTarget()->getDocumentId()];
		foreach ($instanceIds as $instanceId)
		{
			\CBPDocument::terminateWorkflow(
				$instanceId,
				$documentId,
				$errors,
				Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_TERMINATED')
			);
		}
	}

	/**
	 * Document creation handler.
	 *
	 * @return void
	 * @throws InvalidOperationException
	 */
	public function onDocumentAdd(?Context $context = null)
	{
		$preGeneratedWorkflowId = \CBPRuntime::generateWorkflowId();
		$isManualAdd = $context && $context->isManualOperation();

		if (!$isManualAdd && $this->isDebug(true))
		{
			$debugSession = Bizproc\Debugger\Session\Manager::getActiveSession();

			if ($debugSession->isBeforeDebuggerStartState())
			{
				$debugSession->addDocument($this->getTarget()->getDocumentId());

				return;
			}

			$debugSession->addWorkflowContext($preGeneratedWorkflowId, []);

			$status = $this->getTarget()->getDocumentStatus();
			$this->writeSessionLegendTrack($preGeneratedWorkflowId);
			$this->writeStatusTracking($preGeneratedWorkflowId, $status);
			$this->writeCategoryTracking($preGeneratedWorkflowId);
		}

		$this->runDocumentStatus($preGeneratedWorkflowId);
	}

	/**
	 * Document status changed handler.
	 *
	 * @return void
	 * @throws InvalidOperationException
	 */
	public function onDocumentStatusChanged()
	{
		$preGeneratedWorkflowId = \CBPRuntime::generateWorkflowId();
		if ($this->isDebug())
		{
			$debugSession = Bizproc\Debugger\Session\Manager::getActiveSession();

			if ($debugSession->isBeforeDebuggerStartState())
			{
				return;
			}

			$debugSession->addWorkflowContext($preGeneratedWorkflowId, []);

			$status = $this->getTarget()->getDocumentStatus();
			$documentType = $this->getTarget()->getDocumentType()[2];
			$documentId = $this->getTarget()->getDocumentId();
			if (!$this->isStarted($documentType, $documentId, $status))
			{
				$this->onDocumentStatusChangedDebug($preGeneratedWorkflowId, $status);
			}
		}

		$this->runDocumentStatus($preGeneratedWorkflowId);
	}

	public function runDocumentStatus(string $preGeneratedWorkflowId = null): ?string
	{
		$status = $this->getTarget()->getDocumentStatus();
		$documentType = $this->getTarget()->getDocumentType()[2];
		$documentId = $this->getTarget()->getDocumentId();

		if ($status && !$this->isStarted($documentType, $documentId, $status))
		{
			$this->stopTemplates();

			return $this->runTemplates($status, $preGeneratedWorkflowId);
		}

		return null;
	}

	protected function isDebug(bool $isOnDocumentAdd = false): bool
	{
		$debugSession = Bizproc\Debugger\Session\Manager::getActiveSession();
		if (!$debugSession)
		{
			return false;
		}

		$documentType = $this->getTarget()->getDocumentType();
		if (!$debugSession->isStartedInDocumentType($documentType))
		{
			return false;
		}

		$documentId = $this->getTarget()->getComplexDocumentId();
		if (!$isOnDocumentAdd || $debugSession->isExperimentalMode() || $debugSession->isFixed())
		{
			return $debugSession->isSessionDocument($documentId);
		}

		$documentCategoryId = $this->getTarget()->getDocumentCategory();

		return $documentCategoryId === $debugSession->getDocumentCategoryId();
	}

	protected function onDocumentStatusChangedDebug(?string $workflowId, string $status)
	{
		if ($workflowId)
		{
			$trigger = $this->getTarget()->getAppliedTrigger();
			if ($trigger)
			{
				$trigger['APPLIED_RULE_LOG'] = $this->getTarget()->getAppliedTriggerConditionResults();
				$this->writeAppliedTriggerTrack($workflowId, $trigger);
			}

			$this->writeStatusTracking($workflowId, $status);
		}

		Bizproc\Debugger\Listener::getInstance()->onDocumentStatusChanged($status);
	}

	/**
	 * Document moving handler.
	 *
	 * @return void
	 */
	public function onDocumentMove()
	{
		$this->stopTemplates();
	}

	public function onFieldsChanged(array $changes)
	{
		if ($this->isDebug() && $changes)
		{
			$debugSession = Bizproc\Debugger\Session\Manager::getActiveSession();
			if ($debugSession->isBeforeDebuggerStartState())
			{
				return;
			}

			$target = $this->getTarget();

			if ($target->getDocumentCategoryCode() && in_array($target->getDocumentCategoryCode(), $changes))
			{
				$session = Bizproc\Debugger\Session\Manager::getActiveSession();
				$sessionWorkflows = $session->getWorkflowContexts()->getWorkflowIdList();
				if (!empty($sessionWorkflows))
				{
					$lastWorkflowId = $sessionWorkflows[array_key_last($sessionWorkflows)];
					$this->writeCategoryTracking($lastWorkflowId);
				}
			}

			Bizproc\Debugger\Listener::getInstance()->onDocumentUpdated($changes);
		}
	}

	private function setStarted($documentType, $documentId, $status)
	{
		$key = $documentType . '_' . $documentId;
		static::$startedTemplates[$key] = (string)$status;
		return $this;
	}

	private function isStarted($documentType, $documentId, $status)
	{
		$key = $documentType . '_' . $documentId;
		return (
			isset(static::$startedTemplates[$key])
			&& (string)$status === static::$startedTemplates[$key]
		);
	}

	private function writeStatusTracking($workflowId, string $status): ?int
	{
		$statuses = $this->getTarget()->getDocumentStatusList();
		$status = $statuses[$status] ?? [];

		return $this->writeDocumentStatusTrack($workflowId, $status);
	}

	private function writeCategoryTracking($workflowId): ?int
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$categories = $documentService->getDocumentCategories($this->target->getDocumentType());

		$categoryName = $categories[$this->target->getDocumentCategory()]['name'];

		return $this->writeDocumentCategoryTrack($workflowId, $categoryName);
	}

	private function canUseForcedTracking(): bool
	{
		static $use;

		if (!isset($use))
		{
			$use = Option::get('bizproc', 'automation_no_forced_tracking', 'N') === 'N';
		}

		return $use;
	}
}
