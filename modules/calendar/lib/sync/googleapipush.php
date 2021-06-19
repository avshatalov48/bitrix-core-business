<?php

namespace Bitrix\Calendar\Sync;

use Bitrix\Main\Type;
use \Bitrix\Calendar\PushTable;
use \Bitrix\Main\Loader;
use Bitrix\Calendar\Internals;

final class GoogleApiPush
{
	const RENEW_LIMIT = 3;
	const CREATE_LIMIT = 2;
	const PROCESS_LIMIT = 4;
	const CLEAR_LIMIT = 6;
	const CHECK_LIMIT = 10;
	const CHECK_INTERVAL_CHANNEL = 14400;//60*60*4
	const RENEW_INTERVAL_CHANNEL = 14400;//60*60*4

	/**
	 * Checks connection and ability to create push channel
	 * Recommended agent interval = 4h
	 */
	public static function renewWatchChannels()
	{
		global $DB;
		$result =  $DB->query("SELECT * FROM b_calendar_push WHERE " .
			$DB->dateFormatToDB(FORMAT_DATETIME, 'EXPIRES') .
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
				$sectionResult =  $DB->query("SELECT * FROM b_calendar_section WHERE ID IN (" . implode(',', $sectionIds) . ")");
				while($row = $sectionResult->fetch())
				{
					$sections[$row['ID']] = $row;
				}
			}

			if (!empty($connectionIds))
			{
				$connectionResult =  $DB->query("SELECT * FROM b_dav_connections WHERE ID IN (" . implode(',', $connectionIds) . ")");
				while($row = $connectionResult->fetch())
				{
					$connections[$row['ID']] = $row;
				}
			}

			foreach ($pushRows as $row)
			{
				$channelInfo = false;
				if ($row['ENTITY_TYPE'] == 'CONNECTION' && !empty($connections[$row['ENTITY_ID']]))
				{
					$connectionData = $connections[$row['ENTITY_ID']];
					$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
					if (!self::isConnectionError($connectionData['LAST_RESULT'])
						&& $googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']))
					{
						self::deletePushChannel($row);
						$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
					}
					else
					{
						$error = $googleApiConnection->getTransportConnectionError();
						if (is_string($error) && self::isWrongChannel($error))
						{
							self::deletePushChannel($row);
							$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
						}
					}
				}
				elseif ($row['ENTITY_TYPE'] == 'SECTION' && !empty($sections[$row['ENTITY_ID']]))
				{
					$section = $sections[$row['ENTITY_ID']];
					$googleApiConnection = new GoogleApiSync($section['OWNER_ID'], $section['CAL_DAV_CON']);
					if (isset($connections[$section['CAL_DAV_CON']])
						&& !self::isConnectionError($connections[$section['CAL_DAV_CON']]['LAST_RESULT'])
						&& $googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']))
					{
						self::deletePushChannel($row);
						$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
					}
					else
					{
						$error = $googleApiConnection->getTransportConnectionError();
						if (is_string($error) && self::isWrongChannel($error))
						{
							self::deletePushChannel($row);
							$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
						}
					}
				}

				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => $row['ENTITY_TYPE'],
						'ENTITY_ID' => $row['ENTITY_ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					]);
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

			\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");
			\CAgent::addAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar", "N", self::RENEW_INTERVAL_CHANNEL,"", "Y", $nextAgentDate);
			return false;
		}

		return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();";
	}

	public static function checkSectionsPush($localSections, $userId, $connectionId)
	{
		$googleApiConnection = new GoogleApiSync($userId, $connectionId);
		//Create new channels and refresh old push channels for sections of current connection
		$sectionIds = [];
		foreach ($localSections as $section)
		{
			//Skip virtual calendars, because they are not pushable.
			if (self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE']))
			{
				continue;
			}

			$sectionIds[] = intval($section['ID']);
		}

		$pushChannels = PushTable::getList([
			'filter' => [
				'=ENTITY_TYPE' => 'SECTION',
				'=ENTITY_ID' => $sectionIds,
			]
		]);
		$inactiveSections = array_flip($sectionIds);

		while($row = $pushChannels->fetch())
		{
			$now = strtotime('now');
			$tsExpires = strtotime($row['EXPIRES']);

			if ($now > $tsExpires)
			{
				self::deletePushChannel($row);
				continue;
			}
			elseif (($tsExpires - $now) > GoogleApiSync::ONE_DAY)
			{
				unset($inactiveSections[$row['ENTITY_ID']]);
				continue;
			}

			if (!$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']))
			{
				$error = $googleApiConnection->getTransportConnectionError();
				if (is_string($error) && self::isWrongChannel($error))
				{
					self::deletePushChannel($row);
				}
				else
				{
					return false;
				}
			}

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
			self::deletePushChannel($row);
			unset($inactiveSections[$row['ENTITY_ID']]);
		}

		foreach ($localSections as $section)
		{
			if (isset($inactiveSections[$section['ID']]))
			{
				$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					self::deletePushChannel(["ENTITY_TYPE" => 'SECTION', 'ENTITY_ID' => $section['ID']]);
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => 'SECTION',
						'ENTITY_ID' => $section['ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					]);
				}
				else
				{
					$error = $googleApiConnection->getTransportConnectionError();
					if (is_string($error))
					{
						$googleApiConnection->updateLastResultConnection($error);
					}
				}
			}
		}

		return true;
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
		$pushOptionEnabled = \COption::getOptionString('calendar', 'sync_by_push', false);
		if (!$pushOptionEnabled && !\CCalendar::isBitrix24())
		{
			return null;
		}

		$lastId = $start;
		if(Loader::includeModule('dav'))
		{
			$davConnections = \CDavConnection::getList(
				["ID" => "ASC"],
				[
					'ACCOUNT_TYPE' => 'google_api_oauth',
					'>ID' => $start
				],
				false,
				['nTopCount' => self::CREATE_LIMIT]
			);

			$connections = array();
			$pushConnectionIds = array();

			while($row = $davConnections->fetch())
			{
				//connectivity check
				if (!self::isConnectionError($row['LAST_RESULT']))
				{
					$lastId = $row['ID'];
					$connections[] = $row;
					$pushConnectionIds[] = $row['ID'];
				}
			}

			if(!empty($connections))
			{
				$result = PushTable::getList(
					[
						'filter' => [
							'=ENTITY_TYPE' => 'CONNECTION',
							'=ENTITY_ID' => $pushConnectionIds,
						],
					]
				);

				$pushChannels = array();
				while($row = $result->fetch())
				{
					$pushChannels[$row['ENTITY_ID']] = $row;
				}

				foreach($connections as $davConnection)
				{
					$googleApiConnection = new GoogleApiSync($davConnection['ENTITY_ID'], $davConnection['ID']);
					if(empty($pushChannels[$davConnection['ID']]))
					{
						$channelInfo = $googleApiConnection->startWatchCalendarList($connections['NAME']);
						if($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
						{
							self::deletePushChannel(["ENTITY_TYPE" => 'CONNECTION', 'ENTITY_ID' => $davConnection['ID']]);
							$googleApiConnection->updateSuccessLastResultConnection();
							PushTable::add([
								'ENTITY_TYPE' => 'CONNECTION',
								'ENTITY_ID' => $davConnection['ID'],
								'CHANNEL_ID' => $channelInfo['id'],
								'RESOURCE_ID' => $channelInfo['resourceId'],
								'EXPIRES' => $channelInfo['expiration'], 'NOT_PROCESSED' => 'N'
							]);
						}
					}

					unset($googleApiConnection);
				}
			}

			if($lastId == $start)
			{
				\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(".$start.");", "calendar");
				\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar");
				\CAgent::addAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar", "N", 3600, "", "Y", Type\DateTime::createFromTimestamp(strtotime('+1 hour'))->format(Type\Date::convertFormatToPhp(FORMAT_DATETIME)));
				return null;
			}
			else
			{
				return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(".$lastId.");";
			}
		}
		else
		{
			return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(".$lastId.");";
		}
	}

	public static function stopChannel($row = array(), $ownerId = 0)
	{
		if ($row)
		{
			if ($row['ENTITY_TYPE'] == 'CONNECTION')
			{
				if (Loader::includeModule('dav'))
				{
					if ($ownerId == 0)
					{
						$connectionData = \CDavConnection::getById($row['ENTITY_ID']);
						$ownerId = $connectionData['ENTITY_ID'];
					}
					else
					{
						$connectionData = \CDavConnection::getById($row['ENTITY_ID']);
					}
				}

				if ($ownerId > 0 && isset($connectionData) && !self::isConnectionError($connectionData['LAST_RESULT']))
				{
					$googleApiConnection = new GoogleApiSync($ownerId, $row['ENTITY_ID']);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				}
			}
			elseif ($row['ENTITY_TYPE'] == 'SECTION')
			{
				if ($ownerId == 0)
				{
					$section = \CCalendarSect::getById($row['ENTITY_ID']);
					$ownerId = $section['OWNER_ID'];
				}
				else
				{
					$section = \CCalendarSect::getById($row['ENTITY_ID']);
				}

				//TODO: modify the saving of the result
				if (Loader::includeModule('dav'))
				{
					$connectionData = \CDavConnection::getById($section['CAL_DAV_CON']);
				}

				if ($ownerId > 0 && isset($connectionData) && !self::isConnectionError($connectionData['LAST_RESULT']))
				{
					$googleApiConnection = new GoogleApiSync($ownerId, $section['CAL_DAV_CON']);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				}
			}

			self::deletePushChannel($row);
		}
	}

	/**
	 * @param string $lastResult
	 * @return bool
	 */
	public static function isConnectionError(string $lastResult = null): bool
	{
		if (!empty($lastResult) && preg_match("/^\[(4\d\d)\][a-z0-9 _]*/i", $lastResult))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param string $error
	 * @return bool
	 */
	public static function isWrongChannel(string $error = null): bool
	{
		if (!empty($error) && preg_match("^\[404\] Channel \'[a-z0-9 _]*\' not found for project \'[a-z0-9 _]*\'", $error))
		{
			return true;
		}

		return false;
	}

	/**
	 * Stop all push channels agent
	 * Recommended interval - 60 sec. Response from google not required
	 *
	 * @return null|string
	 */
	public static function clearPushChannels()
	{
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
	 * @param $resourceId
	 * @return bool
	 */
	public static function receivePushSignal($channelId, $resourceId)
	{
		$result = PushTable::getList(array(
			'filter' => array(
				'=CHANNEL_ID' => $channelId,
				'=RESOURCE_ID' => $resourceId
			),
		));

		if ($row = $result->fetch())
		{
			if ($row['NOT_PROCESSED'] == 'N')
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
		}
		elseif ($channelOwner = GoogleApiSync::getChannelOwner($channelId))
		{
			// stop channel if we can't find it in the push table
			$googleApiConnection = new GoogleApiSync($channelOwner);
			$googleApiConnection->stopChannel($channelId, $resourceId);
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
		if (Loader::includeModule('dav'))
		{
			$result = PushTable::getList([
				'filter' => ['=NOT_PROCESSED' => 'Y'],
				'order' => ['FIRST_PUSH_DATE' => 'ASC'],
				'limit' => self::PROCESS_LIMIT
			]);
			$pushRows = [];

			while($row = $result->fetch())
			{
				$pushRows[] = $row;
				if($row['ENTITY_TYPE'] == 'CONNECTION')
				{
					$connectionIds[] = $row['ENTITY_ID'];
				}
				if($row['ENTITY_TYPE'] == 'SECTION')
				{
					$sectionIds[] = $row['ENTITY_ID'];
				}
			}

			if(!empty($pushRows))
			{
				global $DB;
				$sections = [];
				$connections = [];

				if (!empty($sectionIds))
				{
					$sectionResult = $DB->query("SELECT * FROM b_calendar_section WHERE ID IN (".implode(',', $sectionIds).")");
					while($row = $sectionResult->fetch())
					{
						$sections[$row['ID']] = $row;
					}
				}

				if(!empty($connectionIds))
				{
					$connectionResult = $DB->query("SELECT * FROM b_dav_connections WHERE ID IN (".implode(',', $connectionIds).")");
					while($row = $connectionResult->fetch())
					{
						$connections[$row['ID']] = $row;
					}
				}

				foreach($pushRows as $row)
				{
					$resynced = false;
					$eventsSyncToken = false;
					if($row['ENTITY_TYPE'] == 'CONNECTION')
					{
						if(!empty($connections[$row['ENTITY_ID']]))
						{
							$resynced = \CCalendarSync::syncConnection($connections[$row['ENTITY_ID']]);
						}
					}
					elseif($row['ENTITY_TYPE'] == 'SECTION')
					{
						if(!empty($sections[$row['ENTITY_ID']]))
						{
							$eventsSyncToken = \CCalendarSync::syncCalendarEvents($sections[$row['ENTITY_ID']]);
							if(!empty($eventsSyncToken))
							{
								\CCalendarSect::edit(array('arFields' => array('ID' => $sections[$row['ENTITY_ID']]['ID'], 'SYNC_TOKEN' => $eventsSyncToken)));
							}
						}
					}

					if(($resynced && $row['ENTITY_TYPE'] == 'CONNECTION')
						|| ($eventsSyncToken && $row['ENTITY_TYPE'] == 'SECTION'))
					{
						PushTable::update(
							[
								'ENTITY_TYPE' => $row['ENTITY_TYPE'],
								'ENTITY_ID' => $row['ENTITY_ID']
							],
							[
								'NOT_PROCESSED' => 'N',
								'FIRST_PUSH_DATE' => null
							]
						);
					}
					else
					{
						//PushTable::delete(array("ENTITY_TYPE" => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']));
					}
				}
				\CCalendar::clearCache();
			}
		}

		return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();";
	}

	public static function checkPushChannel(int $lastIdConnection = 0)
	{
		$connections = [];

		if(Loader::includeModule('dav'))
		{
			$davConnectionsDb = \CDavConnection::getList(
				["ID" => "ASC"],
				[
					'ACCOUNT_TYPE' => 'google_api_oauth',
					'>ID' => $lastIdConnection,
				],
				false,
				['nTopCount' => self::CHECK_LIMIT]
			);

			while ($davConnection = $davConnectionsDb->fetch())
			{
				$connections[$davConnection['ID']] = $davConnection;
				$connectionIds[] = $davConnection['ID'];
			}

			if (!empty($connectionIds))
			{
				self::checkPushConnectionChannel($connectionIds, $connections);
				self::checkPushSectionChannel($connectionIds, $connections);
				return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::checkPushChannel(". end($connectionIds) .");";
			}
		}

		return "\\Bitrix\\Calendar\\Sync\\GoogleApiPush::checkPushChannel();";
	}

	/**
	 * @param array $connectionIds
	 * @param array $connections
	 */
	private static function checkPushConnectionChannel(array $connectionIds, array $connections): void
	{
		$existedConnectionChannels = [];
		$pushConnectionChannelsDb = PushTable::getList(
			[
				'filter' => [
					'=ENTITY_TYPE' => 'CONNECTION',
					'=ENTITY_ID' => $connectionIds
				]
			]
		);

		while ($row = $pushConnectionChannelsDb->fetch())
		{
			$channelInfo = null;
			if (!empty($connections[$row['ENTITY_ID']]))
			{
				$connectionData = $connections[$row['ENTITY_ID']];
				if (!self::isConnectionError($connectionData['LAST_RESULT']))
				{
					$existedConnectionChannels[] = $row['ENTITY_ID'];
					continue;
				}

				$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
				if ($googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']))
				{
					self::deletePushChannel($row);
					$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
				}
				else
				{
					if (is_string($googleApiConnection->getTransportConnectionError()))
					{
						self::deletePushChannel($row);
					}
				}

				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					$existedConnectionChannels[] = $row['ENTITY_ID'];
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => $row['ENTITY_TYPE'],
						'ENTITY_ID' => $row['ENTITY_ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					]);
				}
			}
		}

		//create new channel for connections
		$missedChannelConnections = array_diff($connectionIds, $existedConnectionChannels);
		if (!empty($missedChannelConnections))
		{
			foreach ($missedChannelConnections as $missedConnection)
			{
				$channelInfo = null;
				$connectionData = $connections[$missedConnection];
				$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
				$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => 'CONNECTION',
						'ENTITY_ID' => $connectionData['ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					]);
				}
				else
				{
					$error = $googleApiConnection->getTransportConnectionError();
					if (is_string($error))
					{
						$googleApiConnection->updateLastResultConnection($error);
					}
				}
			}
		}
	}

	/**
	 * @param array $connectionIds
	 * @param array $connections
	 */
	private static function checkPushSectionChannel(array $connectionIds, array $connections): void
	{
		$existedSectionChannels = [];

		$sectionsDb = Internals\SectionTable::getList(
			array(
				'filter' => [
					'CAL_DAV_CON' => $connectionIds,
				],
				'order' => [
					'ID' => 'ASC',
				],
			)
		);

		while ($section = $sectionsDb->fetch())
		{
			$sections[$section['ID']] = $section;
			$sectionIds[] = $section['ID'];
		}

		if (!empty($sectionIds))
		{
			$pushSectionChannelsDb = PushTable::getList(
				[
					'filter' => [
						'=ENTITY_TYPE' => 'SECTION',
						'=ENTITY_ID' => $sectionIds
					]
				]
			);

			while ($row = $pushSectionChannelsDb->fetch())
			{
				$channelInfo = null;
				if (!empty($sections[$row['ENTITY_ID']]))
				{
					$section = $sections[$row['ENTITY_ID']];
					if (!self::isConnectionError($connections[$section['CAL_DAV_CON']]['LAST_RESULT']))
					{
						$existedSectionChannels[] = $row['ENTITY_ID'];
						continue;
					}

					$googleApiConnection = new GoogleApiSync($section['OWNER_ID'], $section['CAL_DAV_CON']);
					if ($googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']))
					{
						self::deletePushChannel($row);
						$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
					}
					else
					{
						if (is_string($googleApiConnection->getTransportConnectionError()))
						{
							self::deletePushChannel($row);
						}
					}

					if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
					{
						$existedSectionChannels[] = $row['ENTITY_ID'];
						$googleApiConnection->updateSuccessLastResultConnection();
						PushTable::add([
							'ENTITY_TYPE' => $row['ENTITY_TYPE'],
							'ENTITY_ID' => $row['ENTITY_ID'],
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						]);
					}
				}
			}

			//create new channel for sections
			$missedChannelSections = array_diff($sectionIds, $existedSectionChannels);
			if (!empty($missedChannelSections))
			{
				foreach ($missedChannelSections as $missedSection)
				{
					$channelInfo = null;
					$connectionData = $connections[$sections[$missedSection]['CAL_DAV_CON']];
					$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
					$channelInfo = $googleApiConnection->startWatchEventsChannel($sections[$missedSection]['GAPI_CALENDAR_ID']);
					if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
					{
						$googleApiConnection->updateSuccessLastResultConnection();
						PushTable::add([
							'ENTITY_TYPE' => 'SECTION',
							'ENTITY_ID' => $missedSection,
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						]);
					}
					else
					{
						$error = $googleApiConnection->getTransportConnectionError();
						if (is_string($error))
						{
							$googleApiConnection->updateLastResultConnection($error);
						}
					}
				}
			}
		}
	}

	private static function deletePushChannel(array $row)
	{
		PushTable::delete(['ENTITY_TYPE' => $row['ENTITY_TYPE'], 'ENTITY_ID' => $row['ENTITY_ID']]);
	}

	/**
	 * @param string|null $gApiCalendarId
	 * @param string|null $externalType
	 * @return bool
	 */
	private static function isVirtualCalendar(?string $gApiCalendarId, ?string $externalType): bool
	{
		if (preg_match('/(holiday.calendar.google.com)/', $gApiCalendarId)
			|| preg_match('/(group.v.calendar.google.com)/', $gApiCalendarId)
			|| preg_match('/(group.calendar.google.com)/', $gApiCalendarId)
			|| preg_match('/(@virtual)/', $gApiCalendarId)
			|| preg_match('/(_readonly)/', $externalType)
			|| preg_match('/(_freebusy)/', $externalType)
		)
		{
			return true;
		}

		return false;
	}
}