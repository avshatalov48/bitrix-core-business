<?php

namespace Bitrix\Bizproc\Debugger\Services;

use Bitrix\Bizproc\Debugger\Listener;
use Bitrix\Bizproc\Debugger\Mixins\WriterDebugTrack;
use Bitrix\Bizproc\Debugger\Session\Manager;
use Bitrix\Main\Text\Emoji;
use Bitrix\Main\Type\DateTime;

class TrackingService extends \CBPTrackingService
{
	use WriterDebugTrack;

	public function canWrite($type, $workflowId)
	{
		$session = Manager::getActiveSession();
		if (!$session)
		{
			return false;
		}

		return $session->hasWorkflow($workflowId);
	}

	public function write(
		$workflowId,
		$type,
		$actionName,
		$executionStatus,
		$executionResult,
		$actionTitle = "",
		$actionNote = "",
		$modifiedBy = 0
	): ?int
	{
		if (in_array((int)$type, self::DEBUG_TRACK_TYPES, true))
		{
			if (!is_array($actionNote))
			{
				$actionNote = $this->preparePropertyForWritingToTrack($actionNote);
			}

			array_walk_recursive($actionNote, static function (&$value) {
				if (is_string($value))
				{
					$value = Emoji::encode($value);
				}
			});

			$actionNote = \Bitrix\Main\Web\Json::encode($actionNote);
		}

		$id = parent::write(
			$workflowId,
			$type,
			$actionName,
			$executionStatus,
			$executionStatus,
			$actionTitle,
			$actionNote,
			$modifiedBy
		);

		$trackingResult = new \CBPTrackingServiceResult();
		$trackingResult->InitFromArray([
			$id => [
				'ID' => $id,
				'WORKFLOW_ID' => $workflowId,
				'TYPE' => $type,
				'ACTION_NAME' => $actionName,
				'ACTION_TITLE' => $actionTitle,
				'ACTION_NOTE' => $actionNote,
				'MODIFIED' => (string)(new DateTime()),
			]
		]);

		$result = $trackingResult->fetch();
		Listener::getInstance()->onTrackWrite($result);

		return $id;
	}
}
