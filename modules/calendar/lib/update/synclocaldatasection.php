<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Sync\GoogleApiBatch;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;

class SyncLocalDataSection extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		if (!Loader::includeModule("calendar"))
		{
			return self::FINISH_EXECUTION;
		}

		$status = $this->loadCurrentStatus();

		if (empty($status))
		{
			return self::FINISH_EXECUTION;
		}

		$googleApiBatch = new GoogleApiBatch($status['sectionIds'][0]);
		$res = $googleApiBatch->syncStepLocalEvents();

		if ($res === true)
		{
			array_shift($status['sectionIds']);
		}

		if (!empty($status['sectionIds']))
		{
			Option::set('calendar', 'syncDataSections', implode(';', $status['sectionIds']));
		}
		else
		{
			Option::set('calendar', 'syncDataSections', 'default');
		}

		$result = [
			'count' => count($status['sectionIds']),
			'steps' => 0,
		];

		return $result['count'] > 0 ? self::CONTINUE_EXECUTION : self::FINISH_EXECUTION;
	}

	private function loadCurrentStatus()
	{
		$status = [];
		$sections = Option::get('calendar', 'syncDataSections', 'default');
		$sectionIds = explode(';', $sections);

		if (is_array($sectionIds))
		{
			$status = array(
				'count' => count($sectionIds),
				'steps' => 1,
				'sectionIds' => $sectionIds,
			);
		}

		return $status;
	}
}