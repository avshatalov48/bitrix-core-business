<?php
namespace Bitrix\Bizproc\Automation;

use Bitrix\Bizproc\WorkflowInstanceTable;
use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable;

class Tracker
{
	const STATUS_WAITING = 0;
	const STATUS_RUNNING = 1;
	const STATUS_COMPLETED = 2;
	const STATUS_AUTOCOMPLETED = 3;

	/** @var BaseTarget */
	protected $target;

	public function __construct(BaseTarget $target)
	{
		$this->target = $target;
	}

	public function getLog(array $statuses)
	{
		return $this->getBizprocTrackingEntries($statuses);
	}

	private function getBizprocTrackingEntries($statuses)
	{
		$entries = [];

		$states = $this->getStatusesStates($statuses);

		if ($states)
		{
			$trackIterator = \CBPTrackingService::GetList(
				['ID' => 'ASC'],
				['@WORKFLOW_ID' => array_keys($states)]
			);

			$workflowStatuses = [];

			while ($row = $trackIterator->fetch())
			{
				if (!array_key_exists($row['WORKFLOW_ID'], $workflowStatuses))
				{
					$hasInstance = $row['WORKFLOW_ID'] && WorkflowInstanceTable::exists($row['WORKFLOW_ID']);
					$workflowStatus = $hasInstance ? \CBPWorkflowStatus::Running : \CBPWorkflowStatus::Completed;

					$workflowStatuses[$row['WORKFLOW_ID']] = $workflowStatus;
				}

				$status = $states[$row['WORKFLOW_ID']];
				$row['WORKFLOW_STATUS'] = $workflowStatuses[$row['WORKFLOW_ID']];
				$entries[$status][] = $row;
			}
		}

		return $entries;
	}

	private function getStatusesStates($statuses)
	{
		$states = array();
		$templateIds = $this->getBizprocTemplateIds($statuses);

		if (!$templateIds)
			return $states;

		$stateIterator = WorkflowStateTable::getList(array(
			'select' => array('ID', 'WORKFLOW_TEMPLATE_ID'),
			'filter' => array(
				'=DOCUMENT_ID' => $this->target->getDocumentId(),
				'@WORKFLOW_TEMPLATE_ID' => array_keys($templateIds)
			),
			'order' => array('STARTED' => 'DESC')
		));

		while ($row = $stateIterator->fetch())
		{
			$status = $templateIds[$row['WORKFLOW_TEMPLATE_ID']];
			if (!in_array($status, $states))
				$states[$row['ID']] = $status;
		}

		return $states;
	}

	private function getBizprocTemplateIds($statuses)
	{
		$documentType = $this->target->getDocumentType();
		$ids = array();

		$iterator = WorkflowTemplateTable::getList(array(
			'select' => array('ID', 'DOCUMENT_STATUS'),
			'filter' => array(
				'=MODULE_ID' => $documentType[0],
				'=ENTITY' => $documentType[1],
				'=DOCUMENT_TYPE' => $documentType[2],
				//'=AUTO_EXECUTE' => \CBPDocumentEventType::Automation,
				'@DOCUMENT_STATUS' => $statuses
			)
		));

		while ($row = $iterator->fetch())
		{
			$ids[$row['ID']] = $row['DOCUMENT_STATUS'];
		}

		return $ids;
	}
}