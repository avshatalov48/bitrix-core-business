<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use CAgent;

class InitLocalDataToGoogle extends Stepper
{
	protected static $moduleId = "calendar";

	public static function className()
	{
		return get_called_class();
	}

	public function execute(array &$result)
	{
		if (!Loader::includeModule("calendar") && !Loader::includeModule("dav"))
		{
			return self::FINISH_EXECUTION;
		}

		$pushEnabled = \CCalendar::IsBitrix24() || \COption::GetOptionString('calendar', 'sync_by_push', false);
		if (!$pushEnabled)
		{
			return self::FINISH_EXECUTION;
		}

		$lastId = Option::get('calendar', 'initLocalDataToGoogleLastId', 0);

		$connections = $this->getNotSyncLocalConnection((int)$lastId);
		if ($connections)
		{
			CAgent::RemoveAgent("CCalendarSync::doSync();", "calendar");
			foreach ($connections as $connection)
			{
				$connection = $this->stopChannels($connection);

				$lastId = $connection['ID'];
				\CCalendarSync::dataSync($connection);
			}

			Option::set('calendar', 'initLocalDataToGoogleLastId', (string)$lastId);
			CAgent::AddAgent("CCalendarSync::doSync();", "calendar", "N", 120);

			return self::CONTINUE_EXECUTION;
		}

		return self::FINISH_EXECUTION;
	}

	/**
	 * @param int $lastId
	 * @return array
	 */
	private function getNotSyncLocalConnection(int $lastId): array
	{
		global $DB;
		$connections = [];

		$strSql = "SELECT DISTINCT c.*, GROUP_CONCAT(s.ID) as SECTION_LIST"
			. " FROM b_dav_connections c"
			. " INNER JOIN b_calendar_section s ON s.OWNER_ID = c.ENTITY_ID"
			. " WHERE (s.EXTERNAL_TYPE = 'local' OR s.EXTERNAL_TYPE = 'google') AND c.ACCOUNT_TYPE = 'google_api_oauth' AND c.ID > " . $lastId
			. " GROUP BY c.ID"
			. " ORDER BY ID ASC"
			. " LIMIT 3;"
		;
		$connectionsDb = $DB->Query($strSql);

		while ($connection = $connectionsDb->Fetch())
		{
			$connections[] = $connection;
		}

		return $connections;
	}

	/**
	 * @param $connection
	 * @return mixed
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \CDavArgumentNullException
	 */
	private function stopChannels($connection)
	{
		GoogleApiPush::stopChannel(GoogleApiPush::getPush(GoogleApiPush::TYPE_CONNECTION, (int)$connection['ID']));
		if (isset($connection['SECTION_LIST']) && is_string($connection['SECTION_LIST']))
		{
			foreach (explode(',', $connection['SECTION_LIST']) as $sectionId)
			{
				GoogleApiPush::stopChannel(GoogleApiPush::getPush(GoogleApiPush::TYPE_SECTION, $sectionId));
			}
		}

		return $connection;
	}

}