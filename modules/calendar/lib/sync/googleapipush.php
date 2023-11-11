<?php

namespace Bitrix\Calendar\Sync;

use Bitrix\Calendar\Sync\Google\Dictionary;
use Bitrix\Calendar\Sync\Google\QueueManager;
use Bitrix\Main\Type;
use Bitrix\Calendar\PushTable;
use Bitrix\Main\Loader;
use Bitrix\Calendar\Internals;

/**
 * @deprecated Old API
 */
final class GoogleApiPush
{
	private const RENEW_LIMIT = 5;
	private const CREATE_LIMIT = 2;
	private const CLEAR_LIMIT = 6;
	private const CHECK_LIMIT = 10;
	public const TYPE_SECTION = 'SECTION';
	public const TYPE_CONNECTION = 'CONNECTION';


	/**
	 * Checks connection and ability to create push channel
	 * Recommended agent interval = 4h
	 */
	public static function renewWatchChannels()
	{
		$pushRows = [];
		$connectionIds = [];
		$sectionIds = [];

		$pushesDb = PushTable::getList([
			'filter' => [
				'<=EXPIRES' => (new Type\DateTime())->add('+1 day'),
			],
			'order' => [
				'EXPIRES' => 'ASC',
			],
			'limit' => self::RENEW_LIMIT,
		]);

		while ($row = $pushesDb->fetch())
		{
			$pushRows[] = $row;
			if ($row['ENTITY_TYPE'] === self::TYPE_CONNECTION)
			{
				$connectionIds[] = (int)$row['ENTITY_ID'];
			}

			if ($row['ENTITY_TYPE'] === self::TYPE_SECTION)
			{
				$sectionIds[] = (int)$row['ENTITY_ID'];
			}
		}

		if (!empty($pushRows))
		{
			global $DB;
			$sections = [];
			$connections = [];

			if (!empty($sectionIds))
			{
				$sectionResult = $DB->query("SELECT * FROM b_calendar_section WHERE ID IN (" . implode(',', $sectionIds) . ")");
				while($row = $sectionResult->fetch())
				{
					$sections[$row['ID']] = $row;
					if (!empty($row['CAL_DAV_CON']) && !in_array((int)$row['CAL_DAV_CON'], $connectionIds, true))
					{
						$connectionIds[] = (int)$row['CAL_DAV_CON'];
					}
				}
			}

			if (!empty($connectionIds))
			{
				$connectionResult = $DB->query("SELECT * FROM b_dav_connections WHERE ID IN (" . implode(',', $connectionIds) . ")");
				while($row = $connectionResult->fetch())
				{
					$connections[$row['ID']] = $row;
				}
			}

			foreach ($pushRows as $row)
			{
				$channelInfo = false;
				if ($row['ENTITY_TYPE'] === self::TYPE_CONNECTION && !empty($connections[$row['ENTITY_ID']]))
				{
					$connectionData = $connections[$row['ENTITY_ID']];
					if (is_string($connectionData['LAST_RESULT']) && self::isAuthError($connectionData['LAST_RESULT']))
					{
						continue;
					}
					$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
					self::deletePushChannel($row);
					$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
				}
				elseif ($row['ENTITY_TYPE'] === self::TYPE_SECTION && !empty($sections[$row['ENTITY_ID']]))
				{
					$section = $sections[$row['ENTITY_ID']];

					if (
						(!empty($connectionData)
							&& is_string($connectionData['LAST_RESULT'])
							&& self::isAuthError($connectionData['LAST_RESULT'])
						)
						|| self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE'])
					)
					{
						continue;
					}

					$connectionData = $connections[$section['CAL_DAV_CON']];
					$googleApiConnection = new GoogleApiSync($section['OWNER_ID'], $section['CAL_DAV_CON']);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
					self::deletePushChannel($row);
					$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
				}
				else
				{
					self::deletePushChannel($row);
				}

				if (
					$channelInfo
					&& isset($channelInfo['id'], $channelInfo['resourceId'])
					&& isset($googleApiConnection)
				)
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
			\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");

			return false;
		}

		return false;
	}

	public static function checkSectionsPush($localSections, $userId, $connectionId)
	{
		$googleApiConnection = new GoogleApiSync($userId, $connectionId);
		//Create new channels and refresh old push channels for sections of current connection
		$sectionIds = self::getNotVirtualSectionIds($localSections);

		$pushChannels = PushTable::getList([
			'filter' => [
				'=ENTITY_TYPE' => self::TYPE_SECTION,
				'=ENTITY_ID' => $sectionIds,
			]
		]);
		$inactiveSections = array_flip($sectionIds);

		while($row = $pushChannels->fetch())
		{
			$now = time();
			$tsExpires = strtotime($row['EXPIRES']);

			if ($now > $tsExpires)
			{
				self::deletePushChannel($row);
				continue;
			}

			if (($tsExpires - $now) > GoogleApiSync::ONE_DAY
				|| self::isAuthError(self::getLastResultBySectionId((int)$row['ENTITY_ID']))
			)
			{
				unset($inactiveSections[$row['ENTITY_ID']]);
				continue;
			}

			$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
			self::deletePushChannel($row);
			unset($inactiveSections[$row['ENTITY_ID']]);

			$localCalendarIndex = array_search($row['ENTITY_ID'], array_column($localSections, 'ID'));
			if ($localCalendarIndex !== false)
			{
				$channelInfo = $googleApiConnection->startWatchCalendarList($localSections[$localCalendarIndex]['GAPI_CALENDAR_ID']);

				if ($channelInfo)
				{
					PushTable::update(
						[
							'ENTITY_TYPE' => $row['ENTITY_TYPE'],
							'ENTITY_ID' => $row['ENTITY_ID']
						],
						[
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						]
					);
				}
			}
		}

		if (is_array($localSections) && is_array($inactiveSections) && $googleApiConnection instanceof GoogleApiSync)
		{
			self::startChannelForInActiveSections($localSections, $inactiveSections, $googleApiConnection);
		}

		return false;
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
		if(!Loader::includeModule('dav'))
		{
			return false;
		}

		$davConnections = \CDavConnection::getList(
			["ID" => "ASC"],
			[
				'ACCOUNT_TYPE' => Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
				'>ID' => $start
			],
			false,
			['nTopCount' => self::CREATE_LIMIT]
		);

		$connections = [];
		$pushConnectionIds = [];
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
						'=ENTITY_TYPE' => self::TYPE_CONNECTION,
						'=ENTITY_ID' => $pushConnectionIds,
					],
				]
			);

			$pushChannels = [];
			while($row = $result->fetch())
			{
				$pushChannels[$row['ENTITY_ID']] = $row;
			}

			foreach($connections as $davConnection)
			{
				if(isset($pushChannels[$davConnection['ID']]))
				{
					continue;
				}

				$googleApiConnection = new GoogleApiSync($davConnection['ENTITY_ID'], $davConnection['ID']);
				$channelInfo = $googleApiConnection->startWatchCalendarList($connections['NAME']);
				if($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					self::deletePushChannel(["ENTITY_TYPE" => self::TYPE_CONNECTION, 'ENTITY_ID' => $davConnection['ID']]);
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => self::TYPE_CONNECTION,
						'ENTITY_ID' => $davConnection['ID'],
						'CHANNEL_ID' => $channelInfo['id'],
						'RESOURCE_ID' => $channelInfo['resourceId'],
						'EXPIRES' => $channelInfo['expiration'],
						'NOT_PROCESSED' => 'N'
					]);
				}
			}
		}

		if($lastId == $start)
		{
			\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(".$start.");", "calendar");
			\CAgent::removeAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar");
			return null;
		}

		return false;
	}

	/**
	 * @param array|null $row
	 * @param int $ownerId
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \CDavArgumentNullException
	 */
	public static function stopChannel(array $row = null, $ownerId = 0): void
	{
		if ($row)
		{
			if ($row['ENTITY_TYPE'] === self::TYPE_SECTION)
			{
				if (Loader::includeModule('dav'))
				{
					$connectionData = \CDavConnection::getById($row['ENTITY_ID']);
					if ($ownerId === 0)
					{
						$ownerId = $connectionData['ENTITY_ID'];
					}
				}

				if ($ownerId > 0 && isset($connectionData) && !self::isConnectionError($connectionData['LAST_RESULT']))
				{
					$googleApiConnection = new GoogleApiSync($ownerId, $row['ENTITY_ID']);
					$googleApiConnection->stopChannel($row['CHANNEL_ID'], $row['RESOURCE_ID']);
				}
			}

			if ($row['ENTITY_TYPE'] === self::TYPE_SECTION)
			{
				$section = \CCalendarSect::getById($row['ENTITY_ID']);
				if ($ownerId === 0)
				{
					$ownerId = $section['OWNER_ID'];
				}

				//TODO: modify the saving of the result
				if (Loader::includeModule('dav') && !empty($section['CAL_DAV_CON']))
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
		return !empty($lastResult) && preg_match("/^\[(4\d\d)\][a-z0-9 _]*/i", $lastResult);
	}

	public static function isAuthError(string $lastResult = null): bool
	{
		return !empty($lastResult) && preg_match("/^\[(401)\][a-z0-9 _]*/i", $lastResult);
	}

	public static function isSyncTokenExpiresError(string $lastResult = null): bool
	{
		return !empty($lastResult) && preg_match("/^\[(410)\][a-z0-9 _]*/i", $lastResult);
	}

	/**
	 * @param string $error
	 * @return bool
	 */
	public static function isWrongChannel(string $error = null): bool
	{
		return !empty($error)
			&& preg_match(
				"/^\[404\] Channel \'[a-z0-9 _]*\' not found for project \'[a-z0-9 _]*\'/i",
				$error
			);
	}
	
	/**
	 * Stop all push channels agent
	 * Recommended interval - 60 sec. Response from google not required
	 *
	 * @return null|string
	 */
	public static function clearPushChannels()
	{
		return null;
	}
	
	/**
	 * @param $channelId
	 * @param $resourceId
	 * @return void
	 */
	public static function receivePushSignal($channelId, $resourceId)
	{
		return;
	}
	

	/**
	 * Handles incoming push for entity - runs synchronization for connection or for section
	 * @param string $entityType
	 * @param int $entityId
	 * @return void
	 */
	public static function processIncomingPush(string $entityType, int $entityId)
	{
		global $DB;
		if ($entityType === self::TYPE_SECTION)
		{
			$r = $DB->query(
				"SELECT s.*, c.LAST_RESULT as LAST_RESULT 
									FROM b_calendar_section s
									LEFT JOIN b_dav_connections c 
										ON s.CAL_DAV_CON = c.ID
									WHERE s.ID=" . $entityId
			);
			if ($section = $r->fetch())
			{
				if (self::isAuthError($section['LAST_RESULT']))
				{
					return;
				}

				$tokens = [];
				if (!empty($tokens))
				{
					if (empty($tokens['nextSyncToken']))
					{
						QueueManager::setIntervalForAgent(QueueManager::PERMANENT_UPDATE_TIME, QueueManager::PERMANENT_UPDATE_TIME);
					}

					\CCalendarSect::edit(
						[
							'arFields' =>
								[
									'ID' => $section['ID'],
									'SYNC_TOKEN' => $tokens['nextSyncToken'],
									'PAGE_TOKEN' => $tokens['nextPageToken'],
								]
						]
					);
				}

				\CCalendar::clearCache();
			}
		}
		elseif ($entityType === self::TYPE_CONNECTION)
		{
			$r = $DB->query("SELECT * FROM b_dav_connections WHERE ID=" . $entityId);
			if ($connection = $r->fetch())
			{
				if (
					self::isAuthError($connection['LAST_RESULT'])
					|| !Loader::includeModule('dav')
				)
				{
					return;
				}

//				\CCalendarSync::syncConnection($connection);
				\CCalendar::clearCache();
			}
		}
	}

	/**
	 * Agent method. Does nothing, just for compatibility
	 */
	public static function processPush()
	{
		return false;
	}

	public static function checkPushChannel(int $lastIdConnection = 0)
	{
		$connections = [];
		$connectionIds = [];

		if (!Loader::includeModule('dav'))
		{
			return false;
		}

		$davConnectionsDb = \CDavConnection::getList(
			["ID" => "ASC"],
			[
				'ACCOUNT_TYPE' => Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
				'>ID' => $lastIdConnection,
			],
			false,
			['nTopCount' => self::CHECK_LIMIT]
		);

		while ($davConnection = $davConnectionsDb->fetch())
		{
			if (self::isAuthError($davConnection['LAST_RESULT']))
			{
				continue;
			}

			$connections[$davConnection['ID']] = $davConnection;
			$connectionIds[] = $davConnection['ID'];
			$lastIdConnection = $davConnection['ID'];
		}

		if (!empty($connectionIds))
		{
			self::checkPushConnectionChannel($connectionIds, $connections);
			self::checkPushSectionChannel($connectionIds, $connections);

			return false;
		}

		return false;
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
					'=ENTITY_TYPE' => self::TYPE_CONNECTION,
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

				if (is_string($googleApiConnection->getTransportConnectionError()))
				{
					self::deletePushChannel($row);
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
				if (self::isAuthError($missedConnection['LAST_RESULT']))
				{
					continue;
				}

				$channelInfo = null;
				$connectionData = $connections[$missedConnection];
				$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
				$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => self::TYPE_CONNECTION,
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
	 * @throws \Exception
	 */
	private static function checkPushSectionChannel(array $connectionIds, array $connections): void
	{
		$existedSectionChannels = [];
		$sections = [];
		$sectionIds = [];

		$sectionsDb = Internals\SectionTable::getList(
			[
				'filter' => [
					'CAL_DAV_CON' => $connectionIds,
				],
				'order' => [
					'ID' => 'ASC',
				],
			]
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
						'=ENTITY_TYPE' => self::TYPE_SECTION,
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

					if (self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE']))
					{
						continue;
					}

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
					if (is_string($googleApiConnection->getTransportConnectionError()))
					{
						self::deletePushChannel($row);
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
					$section = $sections[$missedSection];
					if (
						self::isAuthError($connectionData['LAST_RESULT'])
						|| self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE'])
					)
					{
						continue;
					}

					$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $connectionData['ID']);
					$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
					if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
					{
						$googleApiConnection->updateSuccessLastResultConnection();
						$row = [
							'ENTITY_TYPE' => self::TYPE_SECTION,
							'ENTITY_ID' => $missedSection,
							'CHANNEL_ID' => $channelInfo['id'],
							'RESOURCE_ID' => $channelInfo['resourceId'],
							'EXPIRES' => $channelInfo['expiration'],
							'NOT_PROCESSED' => 'N'
						];
						self::deletePushChannel($row);
						PushTable::add($row);
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

	/**
	 * @param array $row
	 * @throws \Exception
	 */
	public static function deletePushChannel(array $row): void
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
		return preg_match('/(holiday.calendar.google.com)/', $gApiCalendarId)
			|| preg_match('/(group.v.calendar.google.com)/', $gApiCalendarId)
			|| preg_match('/(@virtual)/', $gApiCalendarId)
			|| preg_match('/(_readonly)/', $externalType)
			|| preg_match('/(_freebusy)/', $externalType);
	}

	/**
	 * @param array $localSections
	 * @param array $inactiveSections
	 * @param GoogleApiSync $googleApiConnection
	 * @throws \Exception
	 */
	private static function startChannelForInActiveSections(
		array $localSections,
		array $inactiveSections,
		GoogleApiSync $googleApiConnection
	): void
	{
		foreach ($localSections as $section)
		{
			if (self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE']))
			{
				continue;
			}

			if (isset($inactiveSections[$section['ID']]))
			{
				if (($push = self::getPush(self::TYPE_SECTION, $section['ID'])) && self::isValid($push))
				{
					continue;
				}

				$channelInfo = $googleApiConnection->startWatchEventsChannel($section['GAPI_CALENDAR_ID']);
				if ($channelInfo && isset($channelInfo['id'], $channelInfo['resourceId']))
				{
					self::deletePushChannel(
						[
							"ENTITY_TYPE" => self::TYPE_SECTION,
							'ENTITY_ID' => $section['ID']
						]
					);
					$googleApiConnection->updateSuccessLastResultConnection();
					PushTable::add([
						'ENTITY_TYPE' => self::TYPE_SECTION,
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
	}

	/**
	 * @param int $sectionId
	 * @return string|null
	 */
	private static function getLastResultBySectionId(int $sectionId): ?string
	{
		global $DB;

		$strSql = "SELECT c.ID as CONNECTON_ID, c.LAST_RESULT, s.ID as SECTION_ID
					FROM b_dav_connections c 
					INNER JOIN b_calendar_section s 
						ON s.ID = " . $sectionId
			. " WHERE s.CAL_DAV_CON = c.ID";

		$connectionDb = $DB->Query($strSql);
		if ($connection = $connectionDb->Fetch())
		{
			return $connection['LAST_RESULT'];
		}

		return null;
	}

	/**
	 * @param array $localSections
	 * @return array
	 */
	private static function getNotVirtualSectionIds(array $localSections): array
	{
		$sectionIds = [];
		foreach ($localSections as $section)
		{
			//Skip virtual calendars, because they are not pushable.
			if (self::isVirtualCalendar($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE']))
			{
				continue;
			}

			$sectionIds[] = (int)$section['ID'];
		}

		return $sectionIds;
	}

	/**
	 * @param int $id
	 * @return array|false
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getConnectionPushByConnectionId(int $id)
	{
		$pushResultDb = PushTable::getByPrimary([
			'ENTITY_TYPE' => self::TYPE_CONNECTION,
			'ENTITY_ID' => $id,
		]);

		return $pushResultDb->fetch();
	}

	/**
	 * @param string $type
	 * @param int $entityId
	 */
	public static function setBlockPush(string $type, int $entityId): void
	{
		global $DB;
		$push = self::getPush($type,$entityId);
		if (
			$push
			&& isset($push['NOT_PROCESSED'])
			&& !in_array($push['NOT_PROCESSED'], Google\Dictionary::PUSH_STATUS_PROCESS, true)
		)
		{
			$strSql = "UPDATE b_calendar_push"
				. " SET NOT_PROCESSED = '" . Google\Dictionary::PUSH_STATUS_PROCESS['block'] . "'"
				. " WHERE ENTITY_TYPE = '" . $type . "' AND ENTITY_ID = " . $entityId . ";";
			$DB->Query($strSql);
		}
	}

	/**
	 * @param string $type
	 * @param int $entityId
	 */
	public static function setUnblockPush(string $type, int $entityId): void
	{
		global $DB;

		$push = self::getPush($type, $entityId);
		if ($push !== null)
		{
			$strSql = "UPDATE b_calendar_push"
				. " SET NOT_PROCESSED = 'N'"
				. " WHERE ENTITY_TYPE = '" . $type . "' AND ENTITY_ID = " . $entityId . ";";
			$DB->Query($strSql);

			if ($push['NOT_PROCESSED'] === Dictionary::PUSH_STATUS_PROCESS['unprocessed'])
			{
				self::processIncomingPush($type, $entityId);
			}
		}
	}

	/**
	 * @param string $type
	 * @param int $entityId
	 */
	public static function setUnprocessedPush(string $type, int $entityId): void
	{
		global $DB;

		$push = self::getPush($type, $entityId);
		if (
			$push
			&& isset($push['NOT_PROCESSED'])
			&& $push['NOT_PROCESSED'] !== Google\Dictionary::PUSH_STATUS_PROCESS['unprocessed']
		)
		{
			$strSql = "UPDATE b_calendar_push"
				. " SET NOT_PROCESSED = '" . Google\Dictionary::PUSH_STATUS_PROCESS['unprocessed'] ."'"
				. " WHERE ENTITY_TYPE = '" . $type . "' AND ENTITY_ID = " . $entityId . ";";
			$DB->Query($strSql);
		}
	}

	/**
	 * @param string $type
	 * @param int $entityId
	 * @return array|null
	 */
	public static function getPush(string $type, int $entityId): ?array
	{
		global $DB;

		$strSql = "SELECT * FROM b_calendar_push"
			. " WHERE ENTITY_TYPE = '" . $type . "' AND ENTITY_ID = " . $entityId . ";";
		$pushDb = $DB->Query($strSql);

		if ($push = $pushDb->Fetch())
		{
			return $push;
		}

		return null;
	}

	private static function isValid(?array $push): bool
	{
		if ($push === null)
		{
			return false;
		}

		$now = time();
		$tsExpires = strtotime($push['EXPIRES']);

		return !($now > $tsExpires);
	}
}
