<?php

namespace Bitrix\Calendar\Sync;

use Bitrix\Main\Type;
use \Bitrix\Calendar\PushTable;

final class GoogleApiPush
{
	const RENEW_LIMIT = 3;
	const CREATE_LIMIT = 2;
	const PROCESS_LIMIT = 4;
	const CLEAR_LIMIT = 6;
	/**
	 * Checks connection and ability to create push channel
	 * Recommended agent interval = 4h
	 */
	public static function renewWatchChannels()
	{
		global $DB;
		$result = $DB->Query("SELECT * FROM b_calendar_push WHERE " .
			\CDatabaseMysql::DateFormatToDB(FORMAT_DATETIME, 'EXPIRES') .
			" <= '" .
			\Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime('+1 day')) .
			"' ORDER BY EXPIRES ASC LIMIT " . self::RENEW_LIMIT);

		$pushRows = array();
		$connectionIds = array();
		$sectionIds = array();

		while ($row = $result->fetch())
		{
			$pushRows[] = $row;
			if ($row['ENTITY_TYPE'] == 'CONNECTION')
			{
				$connectionIds[] = $row['ENTITY_ID'];
			}
			if ($row['ENTITY_TYPE'] == 'SECTION')
			{
				$sectionIds[] = $row['ENTITY_ID'];
			}
		}

		if (!empty($pushRows))
		{
			global $DB;
			$sections = array();
			$connections = array();
			if (!empty($sectionIds))
			{
				$sectionResult = $DB->Query("SELECT * FROM b_calendar_section WHERE ID IN (" . implode(',', $sectionIds) . ")");
				while($row = $sectionResult->fetch())
				{
					$sections[$row['ID']] = $row;
				}
			}

			if (!empty($connectionIds))
			{
				$connectionResult = $DB->Query("SELECT * FROM b_dav_connections WHERE ID IN (" . implode(',', $connectionIds) . ")");
				while($row = $connectionResult->fetch())
				{
					$connections[$row['ID']] = $row;
				}
			}

			foreach ($pushRows as $row)
			{
				if ($row['ENTITY_TYPE'] == 'CONNECTION' && !empty($connections[$row['ENTITY_ID']]))
				{
					$connectionData = $connections[$row['ENTITY_ID']];
					$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID']);
					$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
				}
				elseif ($row['ENTITY_TYPE'] == 'SECTION' && !empty($sections[$row['ENTITY_ID']]))
				{
					$section = $sections[$row['ENTITY_ID']];
					$googleApiConnection = new GoogleApiSync($section['OWNER_ID']);
					$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
				}
				else
				{
					continue;
				}

				$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				PushTable::delete(array('ENTITY_TYPE' => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']));

				if ($channelInfo)
				{
					PushTable::update(
						array('ENTITY_TYPE' => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']),
						array('NOT_PROCESSED' => 'N', 'FIRST_PUSH_DATE' => null)
					);
				}
			}
		}

		if (count($pushRows) < 4)
		{
			$result = PushTable::getList(array(
				'order'  => array('EXPIRES' => 'ASC'),
				'limit'	 => 1
			));
			$row = $result->fetch();
			if ($row)
			{
				$nextAgentDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime($row['EXPIRES']) - (60*60*20))->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME));
			}
			else
			{
				$nextAgentDate = \Bitrix\Main\Type\DateTime::createFromTimestamp(strtotime('now') + (60*60*20))->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME));
			}

			\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");
			\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar", "N", 900,"", "Y", $nextAgentDate);
			return false;
		}

		return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();";
	}

	public static function checkSectionsPush($localSections, $userId)
	{
		$googleApiConnection = new GoogleApiSync($userId);
		//Create new channels and refresh old push channels for sections of current connection
		$sectionIds = array();
		foreach ($localSections as $section)
		{
			//Skip virtual calendars, because they are not pushable.
			if (preg_match('/(holiday.calendar.google.com)/', $section['GAPI_CALENDAR_ID']) ||
				preg_match('/(group.v.calendar.google.com)/', $section['GAPI_CALENDAR_ID']) ||
				preg_match('/(@virtual)/', $section['GAPI_CALENDAR_ID']))
				continue;
			$sectionIds[] = $section['ID'];
		}

		$sectionsIn = implode(',', $sectionIds);
		$pushChannels = PushTable::getList(array(
			'filter' => array('=ENTITY_TYPE' => 'SECTION', '@ IN (' . $sectionsIn . ')'),
		));
		$inactiveSections = array_flip($sectionIds);

		while($row = $pushChannels->fetch())
		{
			$diff = strtotime($row['EXPIRES']) - strtotime('now');
			if ($diff > GoogleApiSync::ONE_DAY)
			{
				unset($inactiveSections[$row['ENTITY_ID']]);
				continue;
			}
			$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
			$localCalendarIndex = array_search($row['ENTITY_ID'], array_column($localSections, 'ID'));
			if ($localCalendarIndex !== false)
			{
				$channelInfo = $googleApiConnection->startWatchCalendarList($localSections[$localCalendarIndex]['GAPI_CALENDAR_ID']);

				if ($channelInfo)
				{
					PushTable::update(
						array(
							'ENTITY_TYPE' => $row['ENTITY_TYPE'],
							'ENTITY_ID' => $row['ENTITY_ID']
						),
						array(
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						)
					);
					continue;
				}
			}

			//If we can't create channel or section deleted - we should remove it from sync channels.
			//It will be recreated at daily agent, if possible.
			PushTable::delete(array('ENTITY_TYPE' => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']));
			unset($inactiveSections[$row['ENTITY_ID']]);
		}

		foreach ($localSections as $section)
		{
			if (isset($inactiveSections[$section['ID']]))
			{
				$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);

				if ($channelInfo)
				{
					PushTable::add(array(
						'ENTITY_TYPE' => 'SECTION',
						'ENTITY_ID' => $section['ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					));
				}
			}
		}
	}

	/**
	 * Creates and renew watch channels for connections
	 * Recommended interval = 1 hour, for push enable - recommended interval 3 minutes
	 *
	 * @param int $start
	 * @return string
	 */
	public static function createWatchChannels($start = 0)
	{
		$pushOptionEnabled = \COption::GetOptionString('calendar', 'sync_by_push', false);
		if (!$pushOptionEnabled && !\CCalendar::IsBitrix24())
		{
			return null;
		}

		$lastId = $start;
		\Bitrix\Main\Loader::includeModule('dav');
		$davConnections = \CDavConnection::GetList(
			array("ID" => "ASC"),
			array(
				'ACCOUNT_TYPE' => 'google_api_oauth',
				'>ID' => $start
			),
			false,
			array('nTopCount' => self::CREATE_LIMIT)
		);

		$connections = array();
		$pushConnectionIds = array();

		while($row = $davConnections->fetch())
		{
			$lastId = $row['ID'];
			$connections[] = $row;
			$pushConnectionIds[] = $row['ID'];
		}

		if (!empty($connections))
		{
			$result = PushTable::getList(array(
				'filter' => array(
					'=ENTITY_TYPE' => 'CONNECTION',
					'=ENTITY_ID' => '@ IN (' . implode(',', $pushConnectionIds) . ')'
				),
			));

			$pushChannels = array();
			while($row = $result->fetch())
			{
				$pushChannels[$row['ENTITY_ID']] = $row;
			}

			foreach($connections as $davConnection)
			{
				$googleApiConnection = new GoogleApiSync($davConnection['ENTITY_ID']);
				if (empty($pushChannels[$davConnection['ID']]))
				{
					$channelInfo = $googleApiConnection->startWatchCalendarList($connections['NAME']);
					if ($channelInfo)
					{
						PushTable::delete(array(
							"ENTITY_TYPE" => 'CONNECTION',
							'ENTITY_ID' => $davConnection['ID']
						));

						PushTable::add(array(
							'ENTITY_TYPE' => 'CONNECTION',
							'ENTITY_ID' => $davConnection['ID'],
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						));
					}
				}

				unset($googleApiConnection);
			}
		}
		if ($lastId == $start)
		{
			\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(" . $start . ");", "calendar");
			\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar");
			\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar", "N", 3600, "", "Y", Type\DateTime::createFromTimestamp(strtotime('+1 hour'))->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME)));
			return null;
		}
		else
		{
			return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(" . $lastId . ");";
		}
	}

	public static function stopChannel($row = array(), $ownerId = 0)
	{
		if ($row)
		{
			if ($row['ENTITY_TYPE'] == 'CONNECTION')
			{
				if ($ownerId == 0)
				{
					$connectionData = \CDavConnection::GetById($row['ENTITY_ID']);
					$ownerId = $connectionData['ENTITY_ID'];
				}

				if ($ownerId > 0)
				{
					$googleApiConnection = new GoogleApiSync($ownerId);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				}
			}
			elseif ($row['ENTITY_TYPE'] == 'SECTION')
			{
				if ($ownerId == 0)
				{
					$section = \CCalendarSect::GetById($row['ENTITY_ID']);
					$ownerId = $section['OWNER_ID'];
				}

				if ($ownerId > 0)
				{
					$googleApiConnection = new GoogleApiSync($ownerId);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				}
			}
			PushTable::delete(array("ENTITY_TYPE" => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']));
		}
	}

	/**
	 * Stop all push channels agent
	 * Recommended interval - 60 sec. Response from google not required
	 *
	 * @return null|string
	 */
	public static function clearPushChannels()
	{
		\Bitrix\Main\Loader::includeModule('dav');
		$result = PushTable::getList(array(
			'limit'	 => self::CLEAR_LIMIT
		));
		$hasRows = false;
		while ($row = $result->fetch())
		{
			$hasRows = true;
			self::stopChannel($row);
		}
		if ($hasRows)
			return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();";
		else
			return null;
	}

	/**
	 * @param $channelId
	 * @return bool
	 */
	public static function receivePushSignal($channelId, $resourceId)
	{
		$result = PushTable::getList(array(
			'filter' => array(
				'=NOT_PROCESSED' => 'N',
				'=CHANNEL_ID' => $channelId,
				'=RESOURCE_ID' => $resourceId
			),
		));

		if ($row = $result->fetch())
		{
			PushTable::update(
				array(
					'ENTITY_TYPE' => $row['ENTITY_TYPE'],
					'ENTITY_ID' => $row['ENTITY_ID']
				),
				array(
					'NOT_PROCESSED' => 'Y',
					'FIRST_PUSH_DATE' => Type\DateTime::createFromTimestamp(strtotime('now'))
				)
			);
			return true;
		}
		return false;
	}

	/**
	 * Synchronize sections and connections on push signal receive
	 * Recommended agent interval = 3 minutes
	 *
	 * @return string
	 */
	public static function processPush()
	{
		\Bitrix\Main\Loader::includeModule('dav');
		$result = PushTable::getList(array(
			'filter' => array('=NOT_PROCESSED' => 'Y'),
			'order' => array('FIRST_PUSH_DATE' => 'ASC'),
			'limit' => self::PROCESS_LIMIT
		));
		$pushRows = array();

		while ($row = $result->fetch())
		{
			$pushRows[] = $row;
			if ($row['ENTITY_TYPE'] == 'CONNECTION')
			{
				$connectionIds[] = $row['ENTITY_ID'];
			}
			if ($row['ENTITY_TYPE'] == 'SECTION')
			{
				$sectionIds[] = $row['ENTITY_ID'];
			}
		}

		if (!empty($pushRows))
		{
			global $DB;
			$sections = array();
			$connections = array();
			if (!empty($sectionIds))
			{
				$sectionResult = $DB->Query("SELECT * FROM b_calendar_section WHERE ID IN (" . implode(',', $sectionIds) . ")");
				while ($row = $sectionResult->fetch())
				{
					$sections[$row['ID']] = $row;
				}
			}

			if (!empty($connectionIds))
			{
				$connectionResult = $DB->Query("SELECT * FROM b_dav_connections WHERE ID IN (" . implode(',', $connectionIds) . ")");
				while ($row = $connectionResult->fetch())
				{
					$connections[$row['ID']] = $row;
				}
			}

			foreach($pushRows as $row)
			{
				$resynced = false;
				$eventsSyncToken = false;
				if ($row['ENTITY_TYPE'] == 'CONNECTION')
				{
					if (!empty($connections[$row['ENTITY_ID']]))
					{
						$resynced = \CCalendarSync::syncConnection($connections[$row['ENTITY_ID']]);
					}
				}
				elseif ($row['ENTITY_TYPE'] == 'SECTION')
				{
					if (!empty($sections[$row['ENTITY_ID']]))
					{
						$eventsSyncToken = \CCalendarSync::syncCalendarEvents($sections[$row['ENTITY_ID']]);
						if ($eventsSyncToken)
						{
							\CCalendarSect::Edit(array(
								'arFields' => array(
									'ID' => $sections[$row['ENTITY_ID']]['ID'],
									'SYNC_TOKEN' => $eventsSyncToken
								)
							));
						}
					}
				}

				if (($resynced && $row['ENTITY_TYPE'] == 'CONNECTION') ||
					($eventsSyncToken && $row['ENTITY_TYPE'] == 'SECTION'))
				{
					PushTable::Update(
						array(
							'ENTITY_TYPE' => $row['ENTITY_TYPE'],
							'ENTITY_ID' => $row['ENTITY_ID']
						),
						array(
							'NOT_PROCESSED' => 'N',
							'FIRST_PUSH_DATE' => null
						)
					);
				}
				else
				{
					//PushTable::delete(array("ENTITY_TYPE" => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']));
				}
			}
			\CCalendar::ClearCache();
		}

		return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();";
	}
}