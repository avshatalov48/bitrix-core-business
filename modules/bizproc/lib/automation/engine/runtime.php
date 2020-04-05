<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc\WorkflowInstanceTable;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Main\InvalidOperationException;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Runtime
{
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
		$ids = WorkflowInstanceTable::getList(array(
			'select' => array('ID'),
			'filter' => array(
				'=STATE.MODULE_ID'             => $documentType[0],
				'=STATE.ENTITY'                => $documentType[1],
				'=STATE.DOCUMENT_ID'           => $documentId,
				'=STATE.TEMPLATE.AUTO_EXECUTE' => \CBPDocumentEventType::Automation
			)
		))->fetchAll();

		return array_column($ids, 'ID');
	}

	protected function runTemplates($documentStatus)
	{
		$template = new Template($this->getTarget()->getDocumentType(), $documentStatus);

		if ($template->getId() > 0)
		{
			$errors = array();
			$trigger = $this->getTarget()->getAppliedTrigger();

			if (!$template->isExternalModified() && !$trigger && !$template->getRobots())
			{
				return false;
			}

			$documentType = $this->getTarget()->getDocumentType();
			$documentId = $this->getTarget()->getDocumentId();

			$workflowId = \CBPDocument::StartWorkflow(
				$template->getId(),
				[$documentType[0], $documentType[1], $documentId],
				array(
					\CBPDocument::PARAM_USE_FORCED_TRACKING => !$template->isExternalModified(),
					\CBPDocument::PARAM_IGNORE_SIMULTANEOUS_PROCESSES_LIMIT => true
				),
				$errors
			);

			if (!$errors && $trigger && $workflowId)
			{
				$this->writeTriggerTracking($workflowId, $trigger);
			}

			$this->setStarted($documentId, $documentStatus);
		}
		return true;
	}

	protected function writeTriggerTracking($workflowId, $trigger)
	{
		$runtime = \CBPRuntime::GetRuntime();
		$runtime->StartRuntime();

		/** @var \CBPTrackingService $trackingService */
		$trackingService = $runtime->GetService('TrackingService');

		$trackingService->Write(
			$workflowId,
			\CBPTrackingType::Trigger,
			'APPLIED_TRIGGER',
			\CBPActivityExecutionStatus::Closed,
			\CBPActivityExecutionResult::Succeeded,
			'',
			$trigger['ID']
		);
	}

	protected function stopTemplates()
	{
		$errors = [];
		$instanceIds = $this->getWorkflowInstanceIds();
		$documentType = $this->getTarget()->getDocumentType();
		$documentId = [$documentType[0], $documentType[1], $this->getTarget()->getDocumentId()];
		foreach ($instanceIds as $instanceId)
		{
			\CBPDocument::TerminateWorkflow(
				$instanceId,
				$documentId,
				$errors,
				Loc::getMessage('BIZPROC_AUTOMATION_TEMPLATE_TERMINATED')
			);
		}
	}

	public function onDocumentAdd()
	{
		$status = $this->getTarget()->getDocumentStatus();
		if ($status && !$this->isStarted($this->getTarget()->getDocumentId(), $status))
		{
			$this->runTemplates($status);
		}
	}

	public function onDocumentStatusChanged()
	{
		$status = $this->getTarget()->getDocumentStatus();
		if ($status && !$this->isStarted($this->getTarget()->getDocumentId(), $status))
		{
			$this->stopTemplates();
			$this->runTemplates($status);
		}
	}

	private function setStarted($documentId, $status)
	{
		static::$startedTemplates[$documentId] = (string) $status;
		return $this;
	}

	private function isStarted($documentId, $status)
	{
		return (
			isset(static::$startedTemplates[$documentId])
			&& (string) $status === static::$startedTemplates[$documentId]
		);
	}
}