<?php
namespace Bitrix\Bizproc\Automation;

use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\WorkflowStateTable;

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
		$log = array();

		$currentStatus = $this->target->getDocumentStatus();

		$needleKey = array_search($currentStatus, $statuses);

		if ($needleKey === false)
			return $log;

		$logStatuses = array_slice($statuses, 0, $needleKey + 1);
		$statusEntries = $this->getBizprocTrackingEntries($logStatuses);

		foreach ($statusEntries as $status => $entries)
		{
			$log[$status] = $this->convertBizprocTrackingToLog($entries, $status == $currentStatus);
		}

		return $log;
	}

	private function convertBizprocTrackingToLog($entries, $isCurrentStatus)
	{
		$trigger = null;
		$robotEntries = array();
		$robots = array();

		foreach ($entries as $entry)
		{
			if ($entry['TYPE'] == \CBPTrackingType::Trigger)
			{
				$trigger = array(
					'ID' => $entry['ACTION_NOTE'],
					'STATUS' => static::STATUS_COMPLETED,
					'MODIFIED' => $entry['MODIFIED']
				);
			}
			else
			{
				$robotEntries[$entry['ACTION_NAME']][] = $entry;
			}
		}

		foreach ($robotEntries as $robotId => $robotEntry)
		{
			$status = static::STATUS_WAITING;
			$modified = null;
			$isExecute = $isClosed = $isAutocompleted = false;
			$executeTime = $closedTime = $autocompletedTime = null;
			$errors = array();
			foreach ($robotEntry as $entry)
			{
				if ($entry['TYPE'] == \CBPTrackingType::ExecuteActivity)
				{
					$isExecute = true;
					$executeTime = $entry['MODIFIED'];
				}
				elseif ($entry['TYPE'] == \CBPTrackingType::CloseActivity)
				{
					$isClosed = true;
					$closedTime = $entry['MODIFIED'];
				}
				elseif ($entry['TYPE'] == \CBPTrackingType::Error)
				{
					$errors[] = $entry['ACTION_NOTE'];
				}
			}

			if ($isAutocompleted)
			{
				$status = static::STATUS_AUTOCOMPLETED;
				$modified = $autocompletedTime;
			}
			elseif ($isClosed)
			{
				$status = static::STATUS_COMPLETED;
				$modified = $closedTime;
			}
			elseif ($isExecute)
			{
				$status = $isCurrentStatus? static::STATUS_RUNNING : static::STATUS_COMPLETED;
				$modified = $executeTime;
			}

			$robots[$robotId] = array(
				'ID' => $robotId,
				'STATUS' => $status,
				'MODIFIED' => $modified,
				'ERRORS' => $errors
			);
		}

		return array(
			'trigger' => $trigger,
			'robots' => $robots
		);
	}

	private function getBizprocTrackingEntries($statuses)
	{
		$entries = array();

		$states = $this->getStatusesStates($statuses);

		if ($states)
		{
			$trackIterator = \CBPTrackingService::GetList(array('ID' => 'DESC'), array(
				'@WORKFLOW_ID' => array_keys($states)
			));

			while ($row = $trackIterator->fetch())
			{
				$status = $states[$row['WORKFLOW_ID']];
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
				'=AUTO_EXECUTE' => \CBPDocumentEventType::Automation,
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