<?php

use Bitrix\Calendar\Internals;
use Bitrix\Calendar\PushTable;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\Office365;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Calendar\Sync\GoogleApiSection;
use Bitrix\Calendar\Sync\GoogleApiSync;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Type;
use Bitrix\Main\Type\DateTime;

class CCalendarSync
{
	public const SYNC_TIME = 86400;
	public static $attendeeList = [];
	public static $handleExchangeMeeting;
	public static $doNotSendToGoogle = false;
	private static $mobileBannerDisplay;

	public static function doSync()
	{
		// if (CModule::includeModule('calendar') && CCalendar::isGoogleApiEnabled())
		// {
			// self::$doNotSendToGoogle = true;
			// self::dataSync();
			// self::$doNotSendToGoogle = false;
		// }
		// return "CCalendarSync::doSync();";
	}

	/**
	 * @param array|null $connectionData
	 * @throws CDavArgumentNullException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function dataSync(array $connectionData = null): void
	{
		if (!CModule::includeModule('dav') || !CModule::includeModule('socialservices'))
		{
			return;
		}

		$bShouldClearCache = false;
		$pushEnabled = CCalendar::IsBitrix24() || COption::GetOptionString('calendar', 'sync_by_push', false);

		if ($connectionData !== null)
		{
			$bShouldClearCache = (self::syncConnection($connectionData)) ? true : $bShouldClearCache;
		}
		else
		{
			$davConnections = \CDavConnection::GetList(
				["SYNCHRONIZED" => "ASC"],
				['ACCOUNT_TYPE' => \Bitrix\Calendar\Sync\Google\Helper::GOOGLE_ACCOUNT_TYPE_API],
				null,
				['nTopCount' => GoogleApiSync::MAXIMUM_CONNECTIONS_TO_SYNC]
			);

			while($connection = $davConnections->fetch())
			{
				if ($pushEnabled)
				{
					if (GoogleApiPush::isSyncTokenExpiresError($connection['LAST_RESULT']))
					{
						unset($connection['SYNC_TOKEN']);
						$bShouldClearCache = (self::syncConnection($connection)) ? true : $bShouldClearCache;
						continue;
					}

					if (GoogleApiPush::isAuthError($connection['LAST_RESULT']))
					{
						continue;
					}

					$pushResult = PushTable::getByPrimary([
						'ENTITY_TYPE' => 'CONNECTION',
						'ENTITY_ID' => $connection['ID']
					]);

					if ($row = $pushResult->fetch())
					{
						CDavConnection::SetLastResult($connection["ID"], '[204] SYNCED BY PUSH');
						continue;
					}
				}

				$bShouldClearCache = (self::syncConnection($connection)) ? true : $bShouldClearCache;
			}
		}

		if ($bShouldClearCache)
		{
			CCalendar::ClearCache();
		}
	}

	public static function syncConnection($connectionData, $forceSync = false)
	{
		GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_CONNECTION, (int)$connectionData['ID']);
		$bShouldClearCache = false;
		$syncDelayedSections = [];
		if ($tzEnabled = CTimeZone::Enabled())
		{
			CTimeZone::Disable();
		}

		CDavConnection::Update($connectionData["ID"], [
			"SYNCHRONIZED" => ConvertTimeStamp(time(), "FULL")
		]);

		$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID']);
		if (self::isErrorToGoogle($googleApiConnection, $connectionData))
		{
			return false;
		}

		$googleCalendars = $googleApiConnection->getCalendarItems($connectionData['SYNC_TOKEN']);
		//todo handle this error in new code
		if (self::checkToSyncTokenExpiresError($googleApiConnection))
		{
			unset($connectionData['SYNC_TOKEN']);
			$googleCalendars = $googleApiConnection->getCalendarItems();
		}

		// identifying candidates for google upload
			[
				$localSections,
				$localSectionIndex,
				$sectionsToSendToGoogle
			] = self::sortLocalSections(self::getCalendarsByUserId((int)$connectionData['ENTITY_ID']));

			if ($sectionsToSendToGoogle)
			{
				array_map(function ($section) use ($connectionData) {
					self::sendLocalDataToGoogle($section, (int)$connectionData['ID']);
				}, $sectionsToSendToGoogle);
			}

		if ($googleCalendars)
		{
			foreach ($googleCalendars as $externalCalendar)
			{
				$localCalendarIndex = isset($localSectionIndex[$externalCalendar['id']]) ? $localSectionIndex[$externalCalendar['id']] : false;

				if ($localCalendarIndex === false && empty($externalCalendar['deleted']))
				{
					//new calendar from google
					$arFields = [
						'COLOR' => $externalCalendar['backgroundColor'],
						'TEXT_COLOR' => $externalCalendar['textColor'],
						'GAPI_CALENDAR_ID' => $externalCalendar['id'],
						'NAME' => $externalCalendar['summary'],
						'DESCRIPTION' => (!empty($externalCalendar['description'])) ? $externalCalendar['description'] : '',
						'OWNER_ID' => $connectionData["ENTITY_ID"],
						'CAL_TYPE' => 'user',
						'CAL_DAV_CON' => $connectionData["ID"],
						'EXTERNAL_TYPE' => Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE[$externalCalendar['accessRole']],
					];

					$section = array_merge(
						$arFields,
						['ID' => CCalendarSect::Edit(['arFields' => $arFields])]
					);

					Internals\SectionConnectionTable::add([
						'SECTION_ID' => $section['ID'],
						'CONNECTION_ID' => $connectionData['ID'],
						'VENDOR_SECTION_ID' => $arFields['GAPI_CALENDAR_ID'],
						'ACTIVE' => 'Y',
						'LAST_SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
						'VERSION_ID' => '1',
						'LAST_SYNC_DATE' => new \Bitrix\Main\Type\DateTime(),
					]);

					$localSections[] = $section;
				}
				elseif (empty($externalCalendar['deleted']) || !$externalCalendar['deleted'])
				{
					$externalType = !empty($localSections[$localCalendarIndex]['EXTERNAL_TYPE'])
						? $localSections[$localCalendarIndex]['EXTERNAL_TYPE']
						: Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE[$externalCalendar['accessRole']];
					//edit calendar from google
					$arFields = [
						// TODO: mantis #0124678
//						'COLOR' => $externalCalendar['backgroundColor'],
						'TEXT_COLOR' => $externalCalendar['textColor'],
						'GAPI_CALENDAR_ID' => $externalCalendar['id'],
						'DESCRIPTION' => (!empty($externalCalendar['description'])) ? $externalCalendar['description'] : '',
						'OWNER_ID' => $connectionData["ENTITY_ID"],
						'CAL_TYPE' => 'user',
						'CAL_DAV_CON' => $connectionData["ID"],
						'ID' => $localSections[$localCalendarIndex]['ID'],
						'EXTERNAL_TYPE' => $externalType,
						'NAME' => $externalType === CCalendarSect::EXTERNAL_TYPE_LOCAL
							? $localSections[$localCalendarIndex]['NAME']
							: $externalCalendar['summary'],
					];
					$id = CCalendarSect::Edit(['arFields' => $arFields]);


					Internals\SectionConnectionTable::add([
						'SECTION_ID' => $id,
						'CONNECTION_ID' => $connectionData['ID'],
						'VENDOR_SECTION_ID' => $arFields['GAPI_CALENDAR_ID'],
						'ACTIVE' => 'Y',
						'LAST_SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
						'VERSION_ID' => '1',
						'LAST_SYNC_DATE' => new \Bitrix\Main\Type\DateTime(),
					]);

					if ($externalType === CCalendarSect::EXTERNAL_TYPE_LOCAL)
					{
						$syncDelayedSections[] = (int)$arFields['ID'];
					}

					unset($localSectionIndex[$externalCalendar['id']]);
				}
				elseif (isset($externalCalendar['deleted']))
				{
					if (
						isset($localSections[$localCalendarIndex]['EXTERNAL_TYPE'])
						&& in_array(
							$localSections[$localCalendarIndex]['EXTERNAL_TYPE'],
							Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE,
							true
						)
					)
					{
						CCalendarSect::Delete($localSections[$localCalendarIndex]['ID'], false);
//						CCalendarSect::Edit(['arFields' => [
//							'ID' => $localSections[$localCalendarIndex]['ID'],
//							'ACTIVE' => 'N'
//						]]);
					}
					elseif (
						isset($localSections[$localCalendarIndex]['EXTERNAL_TYPE'])
						&& $localSections[$localCalendarIndex]['EXTERNAL_TYPE'] === CCalendarSect::EXTERNAL_TYPE_LOCAL
					)
					{
						\CCalendarSect::CleanFieldsValueById(
								(int)$localSections[$localCalendarIndex]['ID'],
								[
									'GAPI_CALENDAR_ID',
									'SYNC_TOKEN',
									'PAGE_TOKEN',
									'CAL_DAV_CON',
								]
							);
						\CCalendarEvent::cleanFieldsValueBySectionId(
								(int)$localSections[$localCalendarIndex]['ID'],
								[
//									'DAV_XML_ID',
//									'G_EVENT_ID',
//									'CAL_DAV_LABEL',
									'SYNC_STATUS',
								]
							);
					}
					unset($localSectionIndex[$externalCalendar['id']]);
				}
			}

			if (is_array($googleCalendars) && empty($connectionData['SYNC_TOKEN']))
			{
				self::checkNotActualDataSection(
					self::getNotActualCalendars($connectionData, $localSectionIndex),
					(int)$connectionData['ID'],
					$googleCalendars
				);
			}

			CDavConnection::SetLastResult($connectionData["ID"], "[200] OK", $googleApiConnection->getNextSyncToken());

			self::sendPull($connectionData['ENTITY_ID']);
		}

		$pushOptionEnabled = COption::GetOptionString('calendar', 'sync_by_push', false) || CCalendar::IsBitrix24();
		if ($pushOptionEnabled)
		{
			$connectionPush = GoogleApiPush::getConnectionPushByConnectionId((int)$connectionData['ID']);
			if (!$connectionPush)
			{
				GoogleApiPush::createWatchChannels($connectionData['ID'] - 1);
			}

			if (!GoogleApiPush::isConnectionError($connectionData['LAST_RESULT']))
			{
				if (!GoogleApiPush::checkSectionsPush($localSections, $connectionData['ENTITY_ID'], $connectionData['ID']))
				{
					return false;
				}

				$bShouldClearCache = true;
			}
		}

		foreach ($localSections as $localCalendar)
		{
			GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, (int)$localCalendar['ID']);

			$tokens = self::syncCalendarEvents($localCalendar);
			// Exit if we've got an error during connection to Google API
			if (empty($tokens))
			{
				return false;
			}

			if (empty($tokens['nextSyncToken']))
			{
				Google\QueueManager::setIntervalForAgent(
					Google\QueueManager::PERMANENT_UPDATE_TIME,
					Google\QueueManager::PERMANENT_UPDATE_TIME
				);
			}

			CCalendarSect::Edit([
				'arFields' => [
					'ID' => $localCalendar['ID'],
					'SYNC_TOKEN' => $tokens['nextSyncToken'],
					'PAGE_TOKEN' => $tokens['nextPageToken'],
				],
			]);

			GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, (int)$localCalendar['ID']);
		}

		// if ($syncDelayedSections)
		// {
		// 	foreach ($syncDelayedSections as $sectionId)
		// 	{
		// 		self::sendLocalEventsToGoogle($sectionId);
		// 	}
		// }

		if ($tzEnabled)
		{
			CTimeZone::Enable();
		}

		GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_CONNECTION, (int)$connectionData['ID']);

		return $bShouldClearCache;
	}

	public static function syncCalendarEvents($localCalendar): array
	{
		$googleApiConnection = new GoogleApiSync($localCalendar['OWNER_ID']);
		// If we've got error from Google: save it and exit.
		if ($error = $googleApiConnection->getTransportConnectionError())
		{
			CDavConnection::Update($localCalendar['CAL_DAV_CON'], ["LAST_RESULT" => $error], false);
			return [];
		}

		self::$doNotSendToGoogle = true;

		$loop = 0;
		do
		{
			$localCalendar['PAGE_TOKEN'] = !empty($localCalendar['PAGE_TOKEN'])
				? $localCalendar['PAGE_TOKEN']
				: $googleApiConnection->getNextPageToken()
			;

			$externalEvents = $googleApiConnection->getEvents($localCalendar);
			if ($externalEvents)
			{
				$localEvents = self::getLocalEventsList($localCalendar, array_keys($externalEvents));
				self::SaveExternalEvents($externalEvents, $localEvents, $localCalendar);
			}

			$loop++;
		} while($googleApiConnection->hasMoreEvents() && $loop < 3);

		self::$doNotSendToGoogle = false;

		return [
			'nextSyncToken' => $googleApiConnection->getNextSyncToken() ?: false,
			'nextPageToken' => $googleApiConnection->getNextPageToken() ?: false,
		];
	}

	/**
	 * @param $calendarId
	 * @param $arFields
	 * @param array $params
	 *
	 * @return array|array[]|bool|mixed|string|void|null
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function ModifyEvent($calendarId, $arFields, $params = [])
	{
		[$sectionId, $entityType, $entityId] = $calendarId;
		$userId = $entityType === 'user' ? $entityId : 0;
		$eventId = false;

		$bExchange = CCalendar::IsExchangeEnabled($userId) && $entityType === 'user';

		CCalendar::SetSilentErrorMode();
		if ($sectionId && ($section = CCalendarSect::GetById((int)$sectionId, false)))
		{
			CCalendar::SetOffset(false, CCalendar::GetOffset($userId));
			$eventId = ((isset($arFields['ID']) && ((int)$arFields['ID'] > 0)) ? (int)$arFields['ID'] : 0);
			$arNewFields = [
				'DAV_XML_ID' => $arFields['XML_ID'],
				'CAL_DAV_LABEL' => (isset($arFields['PROPERTY_BXDAVCD_LABEL']) && $arFields['PROPERTY_BXDAVCD_LABEL']) ? $arFields['PROPERTY_BXDAVCD_LABEL'] : '',
				'DAV_EXCH_LABEL' => (isset($arFields['PROPERTY_BXDAVEX_LABEL']) && $arFields['PROPERTY_BXDAVEX_LABEL']) ? $arFields['PROPERTY_BXDAVEX_LABEL'] : '',
				'ID' => $eventId,
				'NAME' => $arFields['NAME'] ?: GetMessage('EC_NONAME_EVENT'),
				'CAL_TYPE' => $section['CAL_TYPE'],
				'OWNER_ID' => $section['OWNER_ID'],
				'DESCRIPTION' => $arFields['DESCRIPTION'] ?? '',
				'SECTIONS' => [$sectionId],
				'ACCESSIBILITY' => $arFields['ACCESSIBILITY'] ?? 'busy',
				'IMPORTANCE' => $arFields['IMPORTANCE'] ?? 'normal',
				'REMIND' => (isset($arFields['REMIND']) && is_array($arFields['REMIND'])) ? $arFields['REMIND'] : [],
				'RRULE' => (isset($arFields['RRULE']) && is_array($arFields['RRULE'])) ? $arFields['RRULE'] : [],
				'VERSION' => isset($arFields['VERSION']) ? (int)$arFields['VERSION'] : 1,
				'PRIVATE_EVENT' => (bool)($arFields['PRIVATE_EVENT'] ?? null)
			];

			$currentEvent = CCalendarEvent::getList([
				'arFilter' => [
					'DAV_XML_ID' => $arNewFields['DAV_XML_ID'],
					'DELETED' => 'N'
				]
			]);
			if ($currentEvent)
			{
				$currentEvent = $currentEvent[0];
			}

			$arNewFields['DATE_FROM'] = $arFields['DATE_FROM'];
			$arNewFields['DATE_TO'] = $arFields['DATE_TO'];
			$arNewFields['TZ_FROM'] = $arFields['TZ_FROM'];
			$arNewFields['TZ_TO'] = $arFields['TZ_TO'];
			$arNewFields['SKIP_TIME'] = $arFields['SKIP_TIME'];

			if (isset($arFields['RECURRENCE_ID']))
			{
				$arNewFields['RECURRENCE_ID'] = $arFields['RECURRENCE_ID'];
			}

			if (isset($arFields['RECURRENCE_ID_DATE']) && $arFields['RECURRENCE_ID_DATE'])
			{
				$arNewFields['ORIGINAL_DATE_FROM'] = $arFields['RECURRENCE_ID_DATE'];
			}

			if (isset($arNewFields['SKIP_TIME']) && $arNewFields['SKIP_TIME'])
			{
				$arNewFields['DATE_FROM'] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_FROM']), false);
				$arNewFields['DATE_TO'] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_TO']) - CCalendar::GetDayLen(), false);
			}

			if (isset($arFields['PROPERTY_REMIND_SETTINGS']) && !empty($arFields['PROPERTY_REMIND_SETTINGS']))
			{
				if (is_array($arFields['PROPERTY_REMIND_SETTINGS']))
				{
					foreach ($arFields['PROPERTY_REMIND_SETTINGS'] as $remindSetting)
					{
						$ar = explode('_', $remindSetting);
						$arNewFields = self::prepareRemind($ar, $arNewFields);
					}
				}
				else
				{
					$ar = explode('_', $arFields['PROPERTY_REMIND_SETTINGS']);
					$arNewFields = self::prepareRemind($ar, $arNewFields);
				}
			}

			if (!empty($arFields['PROPERTY_ACCESSIBILITY']))
			{
				$arNewFields['ACCESSIBILITY'] = $arFields['PROPERTY_ACCESSIBILITY'];
			}
			if (!empty($arFields['PROPERTY_IMPORTANCE']))
			{
				$arNewFields['IMPORTANCE'] = $arFields['PROPERTY_IMPORTANCE'];
			}
			if (!empty($arFields['PROPERTY_LOCATION']))
			{
				$arNewFields['LOCATION'] = Bitrix\Calendar\Rooms\Util::unParseTextLocation($arFields['PROPERTY_LOCATION']);
			}
			if (!empty($arFields['DETAIL_TEXT']))
			{
				if (empty($arFields['MEETING']['LANGUAGE_ID']))
				{
					$arNewFields['MEETING']['LANGUAGE_ID'] = CCalendar::getUserLanguageId((int)$arNewFields['OWNER_ID']);
				}
				$arNewFields['DESCRIPTION'] = self::CutAttendeesFromDescription(
					$arFields['DETAIL_TEXT'],
					self::getAttendeesCodesForCut($currentEvent['ATTENDEES_CODES'] ?? null),
					$arNewFields['MEETING']['LANGUAGE_ID']
				);
			}

			$arNewFields['DESCRIPTION'] = CCalendar::ClearExchangeHtml($arNewFields['DESCRIPTION']);

			if (isset($arFields['PROPERTY_PERIOD_TYPE']) && in_array($arFields['PROPERTY_PERIOD_TYPE'], ["DAILY", "WEEKLY", "MONTHLY", "YEARLY"]))
			{
				$arNewFields['RRULE']['FREQ'] = $arFields["PROPERTY_PERIOD_TYPE"];
				$arNewFields['RRULE']['INTERVAL'] = $arFields["PROPERTY_PERIOD_COUNT"];

				if (!isset($arNewFields['DT_LENGTH']) && !empty($arFields['PROPERTY_EVENT_LENGTH']))
				{
					$arNewFields['DT_LENGTH'] = (int)$arFields['PROPERTY_EVENT_LENGTH'];
				}
				else if (isset($arFields['DT_TO_TS'], $arFields['DT_FROM_TS']))
				{
					$arNewFields['DT_LENGTH'] = $arFields['DT_TO_TS'] - $arFields['DT_FROM_TS'];
				}
				else
				{
					$arNewFields['DT_LENGTH'] = null;
				}

				if ($arNewFields['RRULE']['FREQ'] === "WEEKLY" && !empty($arFields['PROPERTY_PERIOD_ADDITIONAL']))
				{
					$arNewFields['RRULE']['BYDAY'] = [];
					$bydays = explode(',',$arFields['PROPERTY_PERIOD_ADDITIONAL']);
					foreach($bydays as $day)
					{
						$day = CCalendar::WeekDayByInd($day, false);
						if ($day !== false)
						{
							$arNewFields['RRULE']['BYDAY'][] = $day;
						}
					}

					$arNewFields['RRULE']['BYDAY'] = implode(',',$arNewFields['RRULE']['BYDAY']);
				}

				if (isset($arFields['PROPERTY_RRULE_COUNT']))
				{
					$arNewFields['RRULE']['COUNT'] = $arFields['PROPERTY_RRULE_COUNT'];
				}
				elseif (isset($arFields['PROPERTY_PERIOD_UNTIL']))
				{
					$arNewFields['RRULE']['UNTIL'] = $arFields['PROPERTY_PERIOD_UNTIL'];
				}
				else
				{
					$arNewFields['RRULE']['UNTIL'] = $arFields['DT_TO_TS'] ?? null;
				}

				if (isset($arFields['EXDATE']))
				{
					$arNewFields['EXDATE'] = $arFields["EXDATE"];
				}
			}

			if (
				isset($arFields['IS_MEETING'])
				&& $arFields['IS_MEETING']
				&& (($bExchange && self::isExchangeMeetingEnabled()) || $params['handleMeetingParams'])
			)
			{
				$arNewFields['IS_MEETING'] = $arFields['IS_MEETING'];
				$arNewFields['MEETING_HOST'] = $arFields['MEETING_HOST'];
				$arNewFields['MEETING'] = $arFields['MEETING'];
				$arNewFields['ATTENDEES_CODES'] = $arFields['ATTENDEES_CODES'];
			}

			$eventId = CCalendar::SaveEvent(
				[
					'arFields' => $arNewFields,
					'userId' => $userId,
					'bAffectToDav' => false, // Used to prevent synchro with calDav again
					'bSilentAccessMeeting' => true,
					'autoDetectSection' => false,
					'sendInvitations' => ($params['sendInvitations'] ?? null) !== false,
					'syncCaldav' => $params['caldav'] ?? null,
				]
			);

			if ($eventId)
			{
				// Event actually is editing, but when it changes calendar category and
				// comes from the external device it looks like its new event.
				// But here we're trying to find original event and
				// if it was in DB - we delete it to avoid duplication
				if (
					$currentEvent && $currentEvent['ID']
					&& (int)$sectionId !== (int)$currentEvent['SECTION_ID']
					&& !$currentEvent['RECURRENCE_ID']
					&& $arNewFields['OWNER_ID'] === $currentEvent['OWNER_ID']
				)
				{
					CCalendar::DeleteEvent($currentEvent['ID']);
				}
			}
			else
			{
				CCalendarSect::UpdateModificationLabel($sectionId);
			}

			if (
				$eventId
				&& ($arFields['IS_MEETING'] ?? null)
				&& ($arFields['ATTENDEES_RESPONSE'] ?? null)
				&& $bExchange
				&& self::isExchangeMeetingEnabled()
			)
			{
				foreach($arFields['ATTENDEES_RESPONSE'] as $attendeeId => $status)
				{
					CCalendarEvent::SetMeetingStatus([
						'userId' => $attendeeId,
						'eventId' => $eventId,
						'status' => $status,
						'personalNotification' => false,
						'hostNotification' => false,
						'affectRecRelatedEvents' => false
					]);
				}
			}
		}

		CCalendar::SetSilentErrorMode(false);

		return $eventId;
	}

	public static function ModifyReccurentInstances($params = array())
	{
		CCalendar::SetSilentErrorMode();
		$parentEvent = CCalendarEvent::GetById($params['parentId']);

		if ($parentEvent && CCalendarEvent::CheckRecurcion($parentEvent))
		{
			$excludeDates = CCalendarEvent::GetExDate($parentEvent['EXDATE']);

			foreach ($params['events'] as $arFields)
			{
				if (isset($parentEvent['IS_MEETING']) && $parentEvent['IS_MEETING'])
				{
					$arFields['IS_MEETING'] = $parentEvent['IS_MEETING'];
					$arFields['MEETING_HOST'] = $parentEvent['MEETING_HOST'];
					$arFields['MEETING'] = $parentEvent['MEETING'];
					$arFields['ATTENDEES_CODES'] = $parentEvent['ATTENDEES_CODES'];
				}

				$arFields['RECURRENCE_ID'] = $parentEvent['ID'];
				self::ModifyEvent(
					$params['calendarId'],
					$arFields,
					[
						'handleMeetingParams' => $parentEvent['IS_MEETING'],
						'sendInvitations' => false,
					]);

				if ($arFields['RECURRENCE_ID_DATE'])
				{
					$excludeDates[] = CCalendar::Date(CCalendar::Timestamp($arFields['RECURRENCE_ID_DATE']), false);
				}
			}

			$res = CCalendar::SaveEventEx(array(
				'arFields' => array(
					'ID' => $parentEvent['ID'],
					'EXDATE' => CCalendarEvent::SetExDate($excludeDates)
				),
				'bSilentAccessMeeting' => true,
				'recursionEditMode' => 'skip',
				'silentErrorMode' => true,
				'sendInvitations' => false,
				'bAffectToDav' => false,
				'sendEditNotification' => false
			));
		}

		CCalendar::SetSilentErrorMode(false);
	}

	public static function DoSaveToDav(&$arFields, $params = [], $event = false)
	{
		// if (self::$doNotSendToGoogle)
		// {
		// 	return true;
		// }

		$sectionId = $params['sectionId'];
		$modeSync = $params['modeSync'];
		$parameters['editInstance'] = $params['editInstance'];
		// $parameters['originalDavXmlId'] = $params['originalDavXmlId'];
		// $parameters['editParentEvents'] = $params['editParentEvents'];
		// $parameters['editNextEvents'] = $params['editNextEvents'];
		// $parameters['instanceTz'] = $params['instanceTz'];
		$bExchange = $params['bExchange'];
		$bCalDav = $params['bCalDav'];
		$parameters['syncCaldav'] = $params['syncCaldav'];

		if (isset($event['DAV_XML_ID']))
		{
			$arFields['DAV_XML_ID'] = $event['DAV_XML_ID'];
		}
		if (isset($event['DAV_EXCH_LABEL']))
		{
			$arFields['DAV_EXCH_LABEL'] = $event['DAV_EXCH_LABEL'];
		}
		if (isset($event['CAL_DAV_LABEL']))
		{
			$arFields['CAL_DAV_LABEL'] = $event['CAL_DAV_LABEL'];
		}
		if (!isset($arFields['DATE_CREATE']) && isset($event['DATE_CREATE']))
		{
			$arFields['DATE_CREATE'] = $event['DATE_CREATE'];
		}
		if (!isset($arFields['G_EVENT_ID']) && isset($event['G_EVENT_ID']))
		{
			$arFields['G_EVENT_ID'] = $event['G_EVENT_ID'];
		}


		$section = CCalendarSect::GetById($sectionId, false);

		if ($event && (int)$event['SECT_ID'] !== (int)$sectionId)
		{
			$bCalDavCur = CCalendar::IsCalDAVEnabled() && $event['CAL_TYPE'] === 'user' && $event['CAL_DAV_LABEL'] <> '';
			$bExchangeEnabledCur = CCalendar::IsExchangeEnabled() && $event['CAL_TYPE'] === 'user';

			if ($bExchangeEnabledCur || $bCalDavCur)
			{
				$res = self::DoDeleteToDav(array(
					'bCalDav' => $bCalDavCur,
					'bExchangeEnabled' => $bExchangeEnabledCur,
					'sectionId' => $event['SECT_ID']
				), $event);

				if ($event['DAV_EXCH_LABEL'])
				{
					$event['DAV_EXCH_LABEL'] = '';
				}

				if ($res !== true)
				{
					return CCalendar::ThrowError($res);
				}

				//to save as a new event, not update an existing one
				$newSection = CCalendarSect::GetById($event['SECT_ID'], false);

				if ($section['CAL_DAV_CON'] === $newSection['CAL_DAV_CON']
					&& $section['ID'] !== $newSection['ID'])
				{
					unset($arFields['DAV_XML_ID']);
				}
			}
		}

		$arDavFields = $arFields;
		CCalendarEvent::CheckFields($arDavFields);

		if ($arDavFields['RRULE'] != '')
		{
			$arDavFields['RRULE'] = $arFields['RRULE'];
		}

		if ($arDavFields['LOCATION']['NEW'] !== '')
		{
			$arDavFields['LOCATION']['NEW'] = CCalendar::GetTextLocation($arDavFields['LOCATION']['NEW']);
		}
		$arDavFields['PROPERTY_IMPORTANCE'] = $arDavFields['IMPORTANCE'];
		$arDavFields['PROPERTY_LOCATION'] = $arDavFields['LOCATION']['NEW'];

		$arDavFields['PROPERTY_REMIND_SETTINGS'] = '';
		if (!empty($arFields['REMIND'][0]) && is_array($arFields['REMIND'][0]))
		{
			$arDavFields['PROPERTY_REMIND_SETTINGS'] = floatVal($arFields['REMIND'][0]['count'] ?? null)
				. '_' . $arFields['REMIND'][0]['type'];
		}

		if (isset($arDavFields['RRULE']['BYDAY']) && is_array($arDavFields['RRULE']['BYDAY']))
		{
			$arDavFields['RRULE']['BYDAY'] = implode(',',$arDavFields['RRULE']['BYDAY']);
		}

		// **** Synchronize with CalDav ****
		if ($bCalDav && ($section['CAL_DAV_CON'] ?? null) > 0 && empty($parameters['syncCaldav']))
		{
			// New event or move existent event to DAV calendar
			if ((int)($arFields['ID'] ?? null) <= 0 || ($event && empty($event['CAL_DAV_LABEL'])))
			{
				$DAVRes = CDavGroupdavClientCalendar::DoAddItem(
					$section['CAL_DAV_CON'],
					$section['CAL_DAV_CAL'] ?? null,
					$arDavFields
				);
			}
			else // Edit existent event
			{
				$DAVRes = CDavGroupdavClientCalendar::DoUpdateItem(
					$section['CAL_DAV_CON'],
					$section['CAL_DAV_CAL'] ?? null,
					$event['DAV_XML_ID'] ?? null,
					$event['CAL_DAV_LABEL'] ?? null,
					$arDavFields
				);
			}

			if (!is_array($DAVRes) || !isset($DAVRes['XML_ID']))
			{
				return CCalendar::CollectCalDAVErros($DAVRes);
			}

			// // It's ok, we successfuly save event to caldav calendar - and save it to DB
			$arFields['DAV_XML_ID'] = $DAVRes['XML_ID'];
			$arFields['CAL_DAV_LABEL'] = $DAVRes['MODIFICATION_LABEL'];
		}
		// **** Synchronize with Exchange ****
		elseif ($bExchange && $section['IS_EXCHANGE'] && $section['DAV_EXCH_CAL'] <> '' && $section['DAV_EXCH_CAL'] !== 0 && $modeSync)
		{
			$ownerId = $arFields['OWNER_ID'] ?? null;

			// Here we check if parent event was created in exchange calendar and if it is meeting
			// If yes, we expect that it was already created in MS Exchange server
			// and we don't need to dublicate this entry.
			if (
				self::isExchangeMeetingEnabled()
				&& ($arFields['IS_MEETING'] ?? null)
				&& ($arFields['MEETING_HOST'] ?? null) != $ownerId
				&& CCalendar::IsExchangeEnabled($arFields['MEETING_HOST'] ?? null)
			)
			{
				$parentEvent = CCalendarEvent::GetById($arFields['PARENT_ID']);
				if ($parentEvent['DAV_EXCH_LABEL'])
				{
					$parentSection = CCalendarSect::GetById($parentEvent['SECT_ID'], false);
					if (
						$parentSection['IS_EXCHANGE']
						&& $parentSection['DAV_EXCH_CAL'] <> ''
						&& $parentSection['DAV_EXCH_CAL'] !== 0)
					{
						return;
					}
				}
			}

			if (
				self::isExchangeMeetingEnabled()
				&& $arFields['IS_MEETING']
				&& (int)$arFields['MEETING_HOST'] === (int)$ownerId
				&& !empty($arFields['ATTENDEES'])
			)
			{
				$arDavFields['REQUIRED_ATTENDEES'] = self::GetExchangeEmailForUser($arFields['ATTENDEES']);
				if (empty($arDavFields['REQUIRED_ATTENDEES']))
				{
					unset($arDavFields['REQUIRED_ATTENDEES']);
				}
			}

			$fromTo = CCalendarEvent::GetEventFromToForUser($arDavFields, $ownerId);
			$arDavFields["DATE_FROM"] = $fromTo['DATE_FROM'];
			$arDavFields["DATE_TO"] = $fromTo['DATE_TO'];

			// Convert BBcode to HTML for exchange
			$arDavFields["DESCRIPTION"] = CCalendarEvent::ParseText($arDavFields['DESCRIPTION']);
			$updateEvent = !($params['editInstance']);

			if ($params['editParentEvents'] && !empty($arDavFields['RRULE']['UNTIL']))
			{
				$until = Type\Date::createFromTimestamp($arDavFields['DATE_TO_TS_UTC']);
				$arDavFields['DATE_TO_TS_UTC'] = $until->add('-1 day')->getTimestamp();
			}

			// New event  or move existent event to Exchange calendar
			if (
				(int)($arFields['ID'] ?? null) === 0
				|| ($event && !$event['DAV_EXCH_LABEL'] && $updateEvent)
			)
			{
				$exchRes = CDavExchangeCalendar::DoAddItem($ownerId, $section['DAV_EXCH_CAL'], $arDavFields);
			}
			else
			{
				$exchRes = CDavExchangeCalendar::DoUpdateItem($ownerId, $event['DAV_XML_ID'], $event['DAV_EXCH_LABEL'], $arDavFields, $params);
			}

			if (!is_array($exchRes) || !array_key_exists("XML_ID", $exchRes))
			{
				return CCalendar::CollectExchangeErrors($exchRes);
			}

			// It's ok, we successfuly save event to exchange calendar - and save it to DB
			$arFields['DAV_XML_ID'] = $exchRes['XML_ID'];
			$arFields['DAV_EXCH_LABEL'] = $exchRes['MODIFICATION_LABEL'];
		}

		return true;
	}

	public static function DoDeleteToDav($params, $event)
	{
		$sectionId = $params['sectionId'];
		$section = CCalendarSect::GetById($sectionId, false);
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && $event['CAL_TYPE'] === 'user' && !is_null($section['GAPI_CALENDAR_ID']);

		if (!empty($section) && $section['CAL_DAV_CON'] && Loader::includeModule('dav'))
		{
			$calendarConnection = CDavConnection::getById($section['CAL_DAV_CON']);
			if (
				$calendarConnection['ACCOUNT_TYPE'] === Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV
				|| empty($section['GAPI_CALENDAR_ID'])
			)
			{
				$bGoogleApi = false;
			}
		}

		if ($bGoogleApi)
		{
			if (!self::$doNotSendToGoogle)
			{
				$googleApiCalendar = new GoogleApiSync($event['OWNER_ID']);
				$googleApiCalendar->deleteEvent($event['G_EVENT_ID'], $section['GAPI_CALENDAR_ID']);
				if ($errors = $googleApiCalendar->getTransportErrors())
				{
					$isUpdatedResult = false;
					/** @var Google\Helper $googleHelper */
					$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
					if (is_array($errors))
					{
						foreach ($errors as $error)
						{
							if ($googleHelper->isDeletedResource($error['message']))
							{
								$isUpdatedResult = true;
								CCalendarEvent::updateSyncStatus((int)$event['ID'], Google\Dictionary::SYNC_STATUS['success']);
							}
						}
					}

					if (!$isUpdatedResult)
					{
						CCalendarEvent::updateSyncStatus((int)$event['ID'], Google\Dictionary::SYNC_STATUS['delete']);
					}
				}
				else
				{
					CCalendarEvent::updateSyncStatus((int)$event['ID'], Google\Dictionary::SYNC_STATUS['success']);
				}
			}

			return true;
		}

		$bExchangeEnabled = $params['bExchangeEnabled'];
		$bCalDav = $params['bCalDav'];

		// Google and other caldav
		if ($bCalDav && $section['CAL_DAV_CON'] > 0 && $event['CAL_DAV_LABEL'])
		{
			$DAVRes = CDavGroupdavClientCalendar::DoDeleteItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $event['DAV_XML_ID']);$service = new \Bitrix\Calendar\Sync\Icloud\VendorSyncService();
			if ($DAVRes !== true)
			{
				return CCalendar::CollectCalDAVErros($DAVRes);
			}
		}
		// Exchange
		if ($bExchangeEnabled && $section['IS_EXCHANGE'])
		{
			$exchRes = CDavExchangeCalendar::DoDeleteItem($event['OWNER_ID'], $event['DAV_XML_ID']);
			if ($exchRes !== true)
				return CCalendar::CollectExchangeErrors($exchRes);
		}

		return true;
	}

	public static function syncCalendarSections($connectionType, $arCalendars, $entityType, $entityId, $connectionId = null): array
	{
		//Array(
		//	[0] => Array(
		//		[XML_ID] => calendar
		//		[NAME] => calendar
		//	)
		//	[1] => Array(
		//		[XML_ID] => AQATAGFud...
		//		[NAME] => geewgvwe 1
		//		[DESCRIPTION] => gewgvewgvw
		//		[COLOR] => #FF0000
		//		[MODIFICATION_LABEL] => af720e7c7b6a
		//	)
		//)

		$result = [];
		if (
            $connectionType === Bitrix\Calendar\Sync\Caldav\Helper::EXCHANGE_TYPE
			|| $connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE
        )
		{
			CCalendar::SetSilentErrorMode();
			$entityType = mb_strtolower($entityType);
			$entityId = (int)$entityId;

			$tempUser = CCalendar::TempUser(false, true);
			$calendarNames = [];
			foreach ($arCalendars as $value)
			{
				$calendarNames[$value["XML_ID"]] = $value;
			}

			if ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE)
			{
				$arFilter = [
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId,
					'!CAL_DAV_CAL' => false,
					'CAL_DAV_CON' => $connectionId
				];
				$xmlIdField = "CAL_DAV_CAL";
				$xmlIdModLabel = "CAL_DAV_MOD";
			}
 			else // Exchange
			{
				$arFilter = [
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId,
					'!DAV_EXCH_CAL' => false,
					'IS_EXCHANGE' => 1
				];
				$xmlIdField = "DAV_EXCH_CAL";
				$xmlIdModLabel = "DAV_EXCH_MOD";
			}

			$res = CCalendarSect::GetList([
				'arFilter' => $arFilter,
				'checkPermissions' => false,
				'getPermissions' => false
			]);

			foreach($res as $section)
			{
				$xmlId = $section[$xmlIdField];
				$modificationLabel = $section[$xmlIdModLabel];

				if (
					empty($xmlId)
					|| ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE && $section['DAV_EXCH_CAL'])
				)
				{
					continue;
				}

				if (!array_key_exists($xmlId, $calendarNames))
				{
					CCalendarSect::Delete($section["ID"]);
				}
				else
				{
					if ($modificationLabel !== $calendarNames[$xmlId]["MODIFICATION_LABEL"])
					{
						CCalendarSect::Edit([
							'arFields' => [
								"ID" => $section["ID"],
								"NAME" => $calendarNames[$xmlId]["NAME"],
								"OWNER_ID" => $entityType === 'user' ? $entityId : 0,
								"CREATED_BY" => $entityType === 'user' ? $entityId : 0,
								"DESCRIPTION" => $calendarNames[$xmlId]["DESCRIPTION"],
								"COLOR" => $calendarNames[$xmlId]["COLOR"],
								$xmlIdModLabel => $calendarNames[$xmlId]["MODIFICATION_LABEL"],
							]
						]);
					}

					if (empty($modificationLabel) || ($modificationLabel !== $calendarNames[$xmlId]["MODIFICATION_LABEL"]))
					{
						$result[] = [
							"XML_ID" => $xmlId,
							"CALENDAR_ID" => [$section["ID"], $entityType, $entityId]
						];
					}

					unset($calendarNames[$xmlId]);
				}
			}

			foreach($calendarNames as $key => $value)
			{
				$arFields = [
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId,
					'NAME' => $value["NAME"],
					'DESCRIPTION' => $value["DESCRIPTION"] ?? null,
					'COLOR' => $value["COLOR"] ?? null,
					'EXPORT' => ['ALLOW' => false],
					"CREATED_BY" => $entityType === 'user' ? $entityId : 0,
					'ACCESS' => [],
					$xmlIdField => $key,
					$xmlIdModLabel => $value["MODIFICATION_LABEL"] ?? null
				];

				if ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE)
				{
					$arFields["CAL_DAV_CON"] = $connectionId;
				}
				if ($entityType === 'user')
				{
					$arFields["CREATED_BY"] = $entityId;
				}
				if ($connectionType === 'exchange')
				{
					$arFields["IS_EXCHANGE"] = 1;
				}

				$id = (int)CCalendar::SaveSection(['arFields' => $arFields, 'bAffectToDav' => false]);
				if ($id)
				{
					$result[] =
						[
							"XML_ID" => $key,
							"CALENDAR_ID" => [
								$id,
								$entityType,
								$entityId
							]
						];
				}
			}

			CCalendar::TempUser($tempUser, false);
			CCalendar::SetSilentErrorMode(false);
		}

		return $result;
	}

	public static function GetGoogleCalendarConnection()
	{
		$userId = CCalendar::GetCurUserId();
		$result = [];
		if (Loader::includeModule('socialservices'))
		{
			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$client = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$client = new \CSocServGoogleOAuth($userId);
			}

			$client->getEntityOAuth()->addScope([
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			]);
			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$client = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$client = new \CSocServGoogleOAuth($userId);
			}

			$client->getEntityOAuth()->addScope([
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			]);
			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$client = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$client = new \CSocServGoogleOAuth($userId);
			}

			$client->getEntityOAuth()->addScope([
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			]);
			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$client = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$client = new \CSocServGoogleOAuth($userId);
			}

			$client->getEntityOAuth()->addScope([
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			]);

			$id = false;
			if($client->getEntityOAuth()->GetAccessToken())
			{
				$url = \Bitrix\Calendar\Sync\Google\Helper::GOOGLE_SERVER_PATH_V3."/users/me/calendarList";
				$h = new \Bitrix\Main\Web\HttpClient();
				$h->setHeader('Authorization', 'Bearer '.$client->getEntityOAuth()->getToken());
				$response = \Bitrix\Main\Web\Json::decode($h->get($url));
				$id = self::GetGoogleOauthPrimaryId($response);
				$result['googleCalendarPrimaryId'] = $id;
			}

			if(!$id)
			{
				$curPath = \CCalendar::GetPath('user', $userId);
				$curPath = \CHTTP::urlDeleteParams(
					$curPath,
					["action", "sessid", "bx_event_calendar_request", "EVENT_ID", "EVENT_DATE", "current_fieldset"]
				);
				$curPath = \CHTTP::urlAddParams($curPath, ['googleAuthSuccess' => 'y']);
				$result['authLink'] = $client->getUrl('opener', null, ['BACKURL' => $curPath]);
			}
		}
		return $result;
	}

	private static function GetGoogleOauthPrimaryId($data = [])
	{
		$id = false;
		if (is_array($data['items']) && !empty($data['items']))
		{
			foreach($data['items'] as $item)
			{
				if (is_array($item) && $item['primary'] && $item['accessRole'] === 'owner')
				{
					$id = $item['id'];
					break;
				}
			}
		}
		return $id;
	}

	public static function GetExchangeEmailForUser($idList = [])
	{
		global $DB;

		$users = [];

		if (CCalendar::IsSocNet())
		{
			if(is_array($idList))
			{
				$idList = array_unique($idList);
			}
			else
			{
				$idList = array($idList);
			}

			$strIdList = "";
			foreach($idList as $id)
			{
				if((int)$id > 0)
				{
					$strIdList .= ','.(int)$id;
				}
			}
			$strIdList = trim($strIdList, ', ');

			if($strIdList != '')
			{
				$exchangeMailbox = COption::GetOptionString("dav", "exchange_mailbox", "");
				$exchangeUseLogin = COption::GetOptionString("dav", "exchange_use_login", "Y");

				$strSql = "SELECT U.ID, U.LOGIN, U.EMAIL, BUF.UF_BXDAVEX_MAILBOX
					FROM b_user U
					LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = U.ID)
					WHERE
						U.ACTIVE = 'Y' AND
						U.ID in (".$strIdList.")";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($entry = $res->Fetch())
				{
					$users[$entry['ID']] = (($exchangeUseLogin === "Y")
						? $entry["LOGIN"].$exchangeMailbox
						: $entry["UF_BXDAVEX_MAILBOX"])
					;
					if (empty($users[$entry['ID']]))
					{
						$users[$entry['ID']] = $entry['EMAIL'];
					}
				}
			}
		}

		return $users;
	}

	public static function GetUsersByEmailList($emailList = array())
	{
		global $DB;
		$users = [];

		if (CCalendar::IsSocNet())
		{
			$exchangeMailbox = COption::GetOptionString("dav", "exchange_mailbox", "");
			$exchangeUseLogin = COption::GetOptionString("dav", "exchange_use_login", "Y");
			$exchangeMailboxStrlen = mb_strlen($exchangeMailbox);

			$strValue = "";
			foreach($emailList as $email)
			{
				$strValue .= ",'".$DB->ForSql($email)."'";
			}
			$strValue = trim($strValue, ', ');

			if($strValue != '')
			{
				$strSql = "SELECT U.ID, BUF.UF_BXDAVEX_MAILBOX
						FROM b_user U
						LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = U.ID)
						WHERE
							U.ACTIVE = 'Y' AND
							BUF.UF_BXDAVEX_MAILBOX in (".$strValue.")";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$checkedEmails = [];
				while($entry = $res->Fetch())
				{
					$checkedEmails[] = mb_strtolower($entry["UF_BXDAVEX_MAILBOX"]);
					$users[] = $entry['ID'];
				}

				if ($exchangeUseLogin == "Y")
				{
					$strLogins = '';
					foreach($emailList as $email)
					{
						if(!in_array(mb_strtolower($email), $checkedEmails) && mb_strtolower(mb_substr($email, mb_strlen($email) - $exchangeMailboxStrlen)) == mb_strtolower($exchangeMailbox))
						{
							$value = mb_substr($email, 0, mb_strlen($email) - $exchangeMailboxStrlen);
							$strLogins .= ",'".$DB->ForSql($value)."'";
						}
					}
					$strLogins = trim($strLogins, ', ');

					if ($strLogins !== '')
					{
						$res = $DB->Query("SELECT U.ID, U.LOGIN FROM b_user U WHERE U.ACTIVE = 'Y' AND U.LOGIN in (".$strLogins.")", false, "File: ".__FILE__."<br>Line: ".__LINE__);

						while($entry = $res->Fetch())
						{
							$users[] = $entry['ID'];
						}
					}
				}
			}
		}

		return $users;
	}

	public static function isExchangeMeetingEnabled()
	{
		if (!isset(self::$handleExchangeMeeting))
		{
			self::$handleExchangeMeeting = COption::GetOptionString('calendar', 'sync_exchange_meeting', false);
		}
		return self::$handleExchangeMeeting;
	}

	public static function isTaskListSyncEnabled()
	{
		$userSettings = UserSettings::get();
		return $userSettings['showTasks'] === 'Y' && $userSettings['syncTasks'] === 'Y';
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private static function GetPassDates($id, $exDates)
	{
		$excludeDates = [];

		$instancesDM = Internals\EventTable::query()
			->addFilter('=RECURRENCE_ID', $id)
			->setSelect(['ORIGINAL_DATE_FROM'])
			->exec()
		;
		// $instances = Internals\EventTable::getList([
		// 	'filter' => [
		// 		'=RECURRENCE_ID' => $id,
		// 	],
		// 	'select' => [
		// 		'ORIGINAL_DATE_FROM'
		// 	],
		// ]);

		while ($instance = $instancesDM->fetch())
		{
			if ($instance['ORIGINAL_DATE_FROM'] instanceof Type\Date)
			{
				$excludeDates[] = $instance['ORIGINAL_DATE_FROM']->format('d.m.Y');
			}
		}

		if ($excludeDates)
		{
			$eventExDate = explode(';', $exDates);
			$result = array_diff($eventExDate, $excludeDates);

			return implode(';', $result);
		}

		return $exDates;
	}

	/**
	 * @param string|null $description
	 * @param array|null $attendeesCodes
	 * @param string $languageId
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function CutAttendeesFromDescription(
		?string $description,
		?array $attendeesCodes,
		?string $languageId
	): ?string
	{
		if (empty($attendeesCodes))
		{
			return $description;
		}

		IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/lib/sync/googleapisync.php");

		$deleteParts = Util::getAttendees($attendeesCodes, "%");
		$countSeparators = count($attendeesCodes) - 1;
		$deleteParts[] = '%' . \Bitrix\Main\Localization\Loc::getMessage('ATTENDEES_EVENT', null, $languageId) . ':%';
		$description = preg_replace($deleteParts, '', $description, 1);

		return trim(preg_replace("%,%", "", $description, $countSeparators));
	}

	/**
	 * @param array $externalEvents
	 * @param array $localEvents
	 * @param $localCalendar
	 */
	private static function SaveExternalEvents(array $externalEvents, array $localEvents, $localCalendar): void
	{
		foreach ($externalEvents as $externalEvent)
		{
			$eventExists = !empty($localEvents[$externalEvent['G_EVENT_ID']]);
			// find parent event
			if (
				isset($externalEvent['recurringEventId'])
				&& $externalEvent['isRecurring'] === 'Y'
				&& !isset($localEvents[$externalEvent['recurringEventId']])
			)
			{
				$parentEvent = self::getLocalEventsList($localCalendar, [$externalEvent['recurringEventId']]);
				if (!empty($parentEvent))
				{
					$localEvents[$externalEvent['recurringEventId']] = $parentEvent[$externalEvent['recurringEventId']];
				}
			}

			// delete events
			if ($externalEvent['status'] === 'cancelled')
			{
				$originalEvent = $localEvents[$externalEvent['G_EVENT_ID']];

				// event exists
				if ($eventExists)
				{
					if (!empty($externalEvents[$externalEvent['recurringEventId']])
						&& $externalEvents[$externalEvent['recurringEventId']]['status'] === 'cancelled'
						&& !empty($localEvents[$externalEvent['recurringEventId']])
						&& (int)$originalEvent['ID'] === (int)$originalEvent['PARENT_ID']
					)
					{
						CCalendarEvent::Delete([
							'id' => $localEvents[$externalEvent['recurringEventId']]['ID'],
							'bMarkDeleted' => true,
							'Event' => $localEvents[$externalEvent['recurringEventId']],
							'userId' => $localCalendar['OWNER_ID'],
							'sendNotification' => false
						]);
					}

					if (!empty($localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING']) && $localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING'])
					{
						self::$doNotSendToGoogle = false;
					}

					//if host single event then delete instance and  change meeting status if invitation
					if ((int)$originalEvent['ID'] !== (int)$originalEvent['PARENT_ID'])
					{
						CCalendarEvent::SetMeetingStatus([
							'userId' => (int)$originalEvent['OWNER_ID'],
							'eventId' => (int)$originalEvent['ID'],
							'status' => 'N',
							'personalNotification' => true
						]);
					}
					else // delete event
					{
						CCalendarEvent::Delete([
							'id' => $originalEvent['ID'],
							'bMarkDeleted' => true,
							'Event' => $originalEvent,
							'userId' => $localCalendar['OWNER_ID'],
							'sendNotification' => false
						]);
					}

					if (!empty($originalEvent['IS_MEETING']) && $originalEvent['IS_MEETING'])
					{
						self::$doNotSendToGoogle = true;
					}

					// enumeration local instances and change status if invitation or delete if host instance
					foreach ($localEvents as $localEvent)
					{
						if (
							!empty($localEvent['RECURRENCE_ID'])
							&& $localEvent['RECURRENCE_ID'] === $originalEvent['ID']
						)
						{
							if ((int)$localEvent['ID'] !== (int)$localEvent['PARENT_ID'])
							{
								CCalendarEvent::SetMeetingStatus([
									'userId' => (int)$localEvent['OWNER_ID'],
									'eventId' => (int)$localEvent['ID'],
									'status' => 'N',
									'personalNotification' => true
								]);
							}
							else
							{
								CCalendarEvent::Delete([
									'id' => $localEvent['ID'],
									'bMarkDeleted' => true,
									'Event' => $localEvent,
									'userId' => $localCalendar['OWNER_ID'],
									'sendNotification' => false
								]);
							}
						}
					}
				}
				elseif (!empty($externalEvent['recurringEventId'])
					&& !empty($localEvents[$externalEvent['recurringEventId']])
					&& $externalEvent['isRecurring'] === 'Y'
				) // update parent event exdate
				{
					$excludeDates = CCalendarEvent::GetExDate($localEvents[$externalEvent['recurringEventId']]['EXDATE']);
					$excludeDates[] = $externalEvent['EXDATE'];
					$localEvents[$externalEvent['recurringEventId']]['EXDATE'] = implode(';', $excludeDates);

					$newParentData = [
						'arFields' => $localEvents[$externalEvent['recurringEventId']],
						'userId' => $localCalendar['OWNER_ID'],
						'fromWebservice' => true
					];

					$newParentData['arFields']['EXDATE'] = CCalendarEvent::SetExDate($excludeDates);
					$newParentData['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($newParentData['arFields']['RRULE']);
					CCalendar::SaveEvent($newParentData);
				}
				continue;
			}

			// handle single or recurrence events
			if ($externalEvent['isRecurring'] === "N")
			{
				//new event
				if (!$eventExists)
				{
					$newEventData = array_merge(
						$externalEvent,
						[
							'SECTIONS' => [$localCalendar['ID']],
							'OWNER_ID' => $localCalendar['OWNER_ID'],
							'userId' => $localCalendar['OWNER_ID']
						]
					);

					$newEvent = $newEventData;
					$newEvent['ID'] = CCalendarEvent::Edit(['arFields' => $newEventData, 'path' => CCalendar::GetPath('user', $newEventData['OWNER_ID'])]);
					$localEvents[$externalEvent['DAV_XML_ID']] = $newEvent;
				}
				else // event exists
				{
					$newParentData = [
						'arFields' => array_merge([
							'ID' => $localEvents[$externalEvent['G_EVENT_ID']]['ID'],
							'MEETING' => $localEvents[$externalEvent['G_EVENT_ID'] . '@google.com']['MEETING'],
						], $externalEvent),
						'userId' => $localCalendar['OWNER_ID'],
						'fromWebservice' => true
					];

					// update do not sync with google
					if (!empty($localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING']) && $localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING'])
					{
						self::$doNotSendToGoogle = false;
					}

					if (empty($newParentData['arFields']['MEETING']['LANGUAGE_ID']))
					{
						$newParentData['arFields']['MEETING']['LANGUAGE_ID'] = CCalendar::getUserLanguageId((int)$newParentData['arFields']['OWNER_ID']);
					}

					$newParentData['arFields']['DESCRIPTION'] = self::CutAttendeesFromDescription(
						$newParentData['arFields']['DESCRIPTION'],
						self::getAttendeesCodesForCut($localEvents[$externalEvent['G_EVENT_ID']]['ATTENDEES_CODES'] ?? null),
						$newParentData['arFields']['MEETING']['LANGUAGE_ID']
					);

					$newParentData['dontSyncParent'] = true;
					$newParentData['arFields']['SYNC_STATUS'] = Google\Dictionary::SYNC_STATUS['success'];

					CCalendar::SaveEvent($newParentData);

					if (!empty($localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING']) && $localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING'])
					{
						self::$doNotSendToGoogle = true;
					}
				}
			}
			elseif ($externalEvent['isRecurring'] === "Y") // is instance
			{
				// change time
				if ($externalEvent['status'] === 'confirmed' && $externalEvent['hasMoved'] === "Y")
				{
					// find parent event
					$recurrentParentEventExists = !empty($localEvents[$externalEvent['recurringEventId']]);
					if (empty($recurrentParentEventExists))
					{
//						$parentGEventId = str_replace('@google.com', '',  $externalEvent['recurringEventId']);
						$parentEvent = self::getLocalEventsList($localCalendar, [$externalEvent['recurringEventId']]);
						if (!empty($parentEvent))
						{
							$localEvents[$externalEvent['recurringEventId']] = $parentEvent[$externalEvent['recurringEventId']];
							$recurrentParentEventExists = $parentEvent[$externalEvent['recurringEventId']];
						}
					}

					// update exdate to parent event
					if ($recurrentParentEventExists)
					{
						$recurrenceId = NULL;

						if ($localEvents[$externalEvent['recurringEventId']])
						{
							$recurrenceId = $localEvents[$externalEvent['recurringEventId']]['ID'];

							$excludeDates = CCalendarEvent::GetExDate($localEvents[$externalEvent['recurringEventId']]['EXDATE']);
							$excludeDates[] = $externalEvent['EXDATE'];
							$localEvents[$externalEvent['recurringEventId']]['EXDATE'] = implode(';', $excludeDates);
							$newParentData = [
								'arFields' => $localEvents[$externalEvent['recurringEventId']],
								'userId' => $localCalendar['OWNER_ID'],
								'fromWebservice' => true
							];

							$newParentData['arFields']['EXDATE'] = CCalendarEvent::SetExDate($excludeDates);
							$newParentData['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($newParentData['arFields']['RRULE']);
							$newParentData['arFields']['SYNC_STATUS'] = Google\Dictionary::SYNC_STATUS['success'];

							$newParentData['sync'] = true;

							CCalendar::SaveEvent($newParentData);
						}


						// find meeting field in instance exists or parent event
						if (empty($externalEvent['MEETING']))
						{
							$externalEvent['MEETING'] = !empty($localEvents[$externalEvent['G_EVENT_ID'] . '@google.com']['MEETING'])
								? $localEvents[$externalEvent['G_EVENT_ID'] . '@google.com']['MEETING']
								: $localEvents[$externalEvent['DAV_XML_ID']]['MEETING'];
						}

						// set lang id
						if (empty($externalEvent['MEETING']['LANGUAGE_ID']))
						{
							$externalEvent['MEETING']['LANGUAGE_ID'] = CCalendar::getUserLanguageId((int)$externalEvent['OWNER_ID']);
						}

						// handle description
						if (isset($externalEvent['DESCRIPTION']))
						{
							$externalEvent['DESCRIPTION'] = self::CutAttendeesFromDescription(
								$externalEvent['DESCRIPTION'],
								self::getAttendeesCodesForCut($localEvents[$externalEvent['recurringEventId']]['ATTENDEES_CODES'] ?? null),
								$externalEvent['MEETING']['LANGUAGE_ID']
							);
						}

						// save instance
						CCalendar::SaveEvent([
							'arFields' => array_merge($externalEvent, [
								'RECURRENCE_ID' => $recurrenceId,
								'DATE_FROM' => $externalEvent['DATE_FROM'],
								'SECTIONS' => [$localCalendar['ID']],
								'DATE_TO' => $externalEvent['DATE_TO'],
								'SYNC_STATUS' => Google\Dictionary::SYNC_STATUS['success'],
								'ATTENDEES' => !empty($localEvents[$externalEvent['G_EVENT_ID']]['ATTENDEES'])
									? $localEvents[$externalEvent['G_EVENT_ID']]['ATTENDEES']
									: $localEvents[$externalEvent['DAV_XML_ID']]['ATTENDEES'],
								'ATTENDEES_CODES' => !empty($localEvents[$externalEvent['G_EVENT_ID']]['ATTENDEES_CODES'])
									? $localEvents[$externalEvent['G_EVENT_ID']]['ATTENDEES_CODES']
									: $localEvents[$externalEvent['DAV_XML_ID']]['ATTENDEES_CODES'],
								'IS_MEETING' => !empty($localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING'])
									? $localEvents[$externalEvent['G_EVENT_ID']]['IS_MEETING']
									: $localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING'],
								'MEETING_STATUS' => !empty($localEvents[$externalEvent['G_EVENT_ID']]['MEETING_STATUS'])
									? $localEvents[$externalEvent['G_EVENT_ID']]['MEETING_STATUS']
									: $localEvents[$externalEvent['DAV_XML_ID']]['MEETING_STATUS'],
							]),
							'userId' => $localCalendar['OWNER_ID'],
							'fromWebservice' => true,
							'sync' => true,
						]);
					}
				}
			}
		}
	}

	/**
	 * @param $localCalendar
	 * @return array|mixed|null
	 */
	public static function getLocalEventsList($localCalendar, $externalEventsGEventId)
	{
		$eventsDb = Internals\EventTable::query()
			->where('SECTION_ID', $localCalendar['ID'])
			->whereIn('G_EVENT_ID', $externalEventsGEventId)
			->setSelect([
				"ID",
				"CAL_TYPE",
				"DT_SKIP_TIME",
				"DATE_FROM",
				"DATE_TO",
				"TZ_FROM",
				"TZ_TO",
				"PARENT_ID",
				"IS_MEETING",
				"MEETING_STATUS",
				"MEETING",
				"LOCATION",
				"RRULE",
				"EXDATE",
				"DAV_XML_ID",
				"RECURRENCE_ID",
				"G_EVENT_ID",
				"ATTENDEES_CODES",
				"OWNER_ID",
			])->exec();

		$eventList = [];
		while ($event = $eventsDb->fetch())
		{
			if (!static::$attendeeList[$event['PARENT_ID']])
			{
				static::$attendeeList[$event['PARENT_ID']] = !CCalendarEvent::getAttendeeList($event['PARENT_ID'])
					?: CCalendarEvent::getAttendeeList($event['PARENT_ID'])['attendeeList'][$event['PARENT_ID']];
			}

			if (is_array(static::$attendeeList[$event['PARENT_ID']]))
			{
				$event['ATTENDEES'] = array_column(static::$attendeeList[$event['PARENT_ID']], 'id');
			}

			if (is_string($event['MEETING']))
			{
				$event['MEETING'] = unserialize($event['MEETING'], ['allowed_classes' => false]);
			}

			if (!empty($event['G_EVENT_ID']))
			{
				$eventList[$event['G_EVENT_ID']] = $event;
			}
			elseif (!empty($event['DAV_XML_ID']))
			{
				$eventList[str_replace('@google', '',$event['DAV_XML_ID'])] = $event;
			}
		}

		return $eventList;
	}

	/**
	 * @param $attendeesCodes
	 * @return array|null
	 */
	public static function getAttendeesCodesForCut($attendeesCodes): ?array
	{
		if (is_array($attendeesCodes))
		{
			return $attendeesCodes;
		}

		if (is_string($attendeesCodes) && $res = explode(',', $attendeesCodes))
		{
			return $res;
		}

		return null;
	}

	public static function GetUserSyncInfo($userId)
	{
		require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/dav/classes/mysql/connection.php');
		require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/dav/classes/general/dav.php');
		$connectionList = [];
		$davConnections = \CDavConnection::GetList(
			["ID" => "ASC"],
			[
				'ENTITY_ID' => $userId,
				'IS_DELETED' => 'N',
			],
		);

		while($connection = $davConnections->fetch())
		{
			$connectionList[] = $connection;
		}

		return $connectionList;
	}

	public static function getTimestampWithUserOffset($userId): Closure
	{
		$offset = \CTimeZone::GetOffset($userId, true);
		return static function($date) use ($offset) {
			return $date
				? \CCalendar::Timestamp($date, false, true) - $offset
				: null;
		};
	}

	public static function GetSyncInfo($params = [])
	{
		$userId = \CCalendar::getCurUserId();
		$macSyncInfo = self::GetSyncInfoItem($userId, 'mac');
		$iphoneSyncInfo = self::GetSyncInfoItem($userId, 'iphone');
		$androidSyncInfo = self::GetSyncInfoItem($userId, 'android');
		$outlookSyncInfo = self::GetMultipleSyncInfoItem($userId, 'outlook');
		$exchangeSyncInfo = self::GetSyncInfoItem($userId, 'exchange');

		$bExchangeConnected = false;
		$bExchange = false;
		if (Loader::includeModule('dav'))
		{
			$bExchange = \CCalendar::IsExchangeEnabled() && $params['type'] === 'user';
			$bExchangeConnected = $bExchange && \CDavExchangeCalendar::IsExchangeEnabledForUser($userId);
		}

		$calculateTimestamp = self::getTimestampWithUserOffset($userId);

		$syncInfo = [
			'mac' => [
				'type' => 'mac',
				'active' => true,
				'connected' => $macSyncInfo['connected'],
				'status' => $macSyncInfo['status'],
				'syncOffset' => time() - $calculateTimestamp($macSyncInfo['date']),
			],
			'iphone' => [
				'type' => 'iphone',
				'active' => true,
				'connected' => $iphoneSyncInfo['connected'],
				'status' => $iphoneSyncInfo['status'],
				'syncOffset' => time() - $calculateTimestamp($iphoneSyncInfo['date']),

			],
			'android' => [
				'type' => 'android',
				'active' => true,
				'connected' => $androidSyncInfo['connected'],
				'status' => $androidSyncInfo['status'],
				'syncOffset' => time() - $calculateTimestamp($androidSyncInfo['date']),
			],
			'outlook' => [
				'type' => 'outlook',
				'active' => true,
				'connected' => $outlookSyncInfo['connected'],
				'status' => $outlookSyncInfo['status'],
				'infoBySections' => ($outlookSyncInfo['infoBySections'] ?? ''),
				'syncOffset' => time() - $calculateTimestamp($outlookSyncInfo['date'] ?? false),
			],
		];

		if (!Loader::includeModule('bitrix24'))
		{
			$syncInfo['exchange'] = [
				'type' => 'exchange',
				'active' => $bExchange,
				'connected' => $bExchangeConnected,
				'status' => $exchangeSyncInfo['status'],
				'syncOffset' => time() - $calculateTimestamp($exchangeSyncInfo['date']),
			];
		}

		$caldavConnections = self::GetCaldavItemsInfo($userId, $params['type'], $calculateTimestamp);
		if (is_array($caldavConnections))
		{
			$syncInfo = array_merge($syncInfo, $caldavConnections);
		}

		return $syncInfo;
	}

	/**
	 * @param $userId
	 * @param $syncType
	 * @return false[]
	 */
	public static function GetSyncInfoItem($userId, $syncType): array
	{
		$activeSyncPeriod = self::SYNC_TIME;
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'office365', 'icloud');
		$result = [
			'connected' => false,
			'status' => false,
		];

		if (in_array($syncType, $syncTypes, true))
		{
			$result['date'] = CUserOptions::GetOption("calendar", "last_sync_".$syncType, false, $userId);
		}

		if ($result['date'])
		{
			$result['date'] = CCalendar::Date(CCalendar::Timestamp($result['date']) + CCalendar::GetOffset($userId), true, true, true);
			$period = time() - CCalendar::Timestamp($result['date']);

			if ($period <= $activeSyncPeriod)
			{
				$result['connected'] = true;
				$result['status'] = true;
			}
		}

		return $result;
	}

	/**
	 * @param $userId
	 * @param $syncType
	 * @return false[]
	 */
	public static function GetMultipleSyncInfoItem($userId, $syncType): array
	{
		$activeSyncPeriod = 604800; // 3600 * 24 * 7 - one week
		$syncTypes = ['outlook'];
		$lastSync = null;
		$result = [
			'connected' => false,
			'status' => false,
			'syncOffset' => $activeSyncPeriod
		];

		if (in_array($syncType, $syncTypes, true))
		{
			$options = CUserOptions::GetOption("calendar", "last_sync_".$syncType, false, $userId);
		}

		if ($options !== false)
		{
			if (is_array($options))
			{
				foreach ($options as $key => &$date)
				{
					$dateTs = \CCalendar::Timestamp($date, false);
					$period = time() - $dateTs;

					if ($dateTs > $lastSync)
					{
						$lastSync = $dateTs;
					}

					if ($period <= $activeSyncPeriod)
					{
						$result['connected'] = true;
						$result['status'] = true;
						$result['syncOffset'] = $period;
					}
				}

				$result['infoBySections'] = $options;
			}
			else
			{
				$lastSync = \CCalendar::Timestamp($options, false);
				$period = time() - $lastSync;
				if ($period <= $activeSyncPeriod)
				{
					$result['connected'] = true;
					$result['status'] = true;
					$result['syncOffset'] = $period;
				}
			}
		}

		return $result;
	}

	/**
	 * @param $userId
	 * @param $type
	 * @param $calculateTimestamp
	 *
	 * @return array|null
	 * @throws ObjectNotFoundException
	 */
	public static function GetCaldavItemsInfo($userId, $type, $calculateTimestamp): ?array
	{
		$connections = [];
		$bCalDAV = CCalendar::IsCalDAVEnabled() && $type === 'user';
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && $type === 'user';

		if ($bCalDAV || $bGoogleApi)
		{
			$res = CDavConnection::GetList(
				['ID' => 'DESC'],
				[
					'ENTITY_TYPE' => 'user',
					'ENTITY_ID' => $userId,
					'ACCOUNT_TYPE' =>
						[
							Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV,
							Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
							Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
							Bitrix\Calendar\Sync\Icloud\Helper::ACCOUNT_TYPE,
							Bitrix\Calendar\Sync\Office365\Helper::ACCOUNT_TYPE
						],
					'IS_DELETED' => 'N'
				]
			);
			$isRussian = Util::checkRuZone();
			/** @var Google\Helper $googleHelper */
			$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
			/** @var Bitrix\Calendar\Sync\Caldav\Helper $caldavHelper */
			$caldavHelper = ServiceLocator::getInstance()->get('calendar.service.caldav.helper');
			/** @var Bitrix\Calendar\Sync\Icloud\Helper $iCloudHelper */
			$iCloudHelper = ServiceLocator::getInstance()->get('calendar.service.icloud.helper');
			/** @var Bitrix\Calendar\Sync\Office365\Helper $office365Helper */
			$office365Helper = ServiceLocator::getInstance()->get('calendar.service.office365.helper');
			while ($connection = $res->Fetch())
			{
				if ($connection['ACCOUNT_TYPE'] === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE)
				{
					if ($caldavHelper->isYandex($connection['SERVER_HOST']) && $isRussian)
					{
						$connections[Bitrix\Calendar\Sync\Caldav\Helper::YANDEX_TYPE . $connection['ID']] = [
							'id' => $connection['ID'],
							'active' => true,
							'connected' => true,
							'userName' => $connection['SERVER_USERNAME'],
							'connectionName' => $connection['NAME'],
							'type' => Bitrix\Calendar\Sync\Caldav\Helper::YANDEX_TYPE,
							'status' => self::isConnectionSuccess($connection['LAST_RESULT']),
							'server' => $connection['SERVER'],
							'syncOffset' => time() - $calculateTimestamp($connection['SYNCHRONIZED']),
						];
					}
					else
					{
						$connections[Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE . $connection['ID']] = [
							'id' => $connection['ID'],
							'active' => true,
							'connected' => true,
							'userName' => $connection['SERVER_USERNAME'],
							'connectionName' => $connection['NAME'],
							'type' => Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
							'status' => self::isConnectionSuccess($connection['LAST_RESULT']),
							'server' => $connection['SERVER'],
							'syncOffset' => time() - $calculateTimestamp($connection['SYNCHRONIZED']),
						];
					}
				}
				else if (
					$iCloudHelper->isVendorConnection($connection['ACCOUNT_TYPE'])
					|| $office365Helper->isVendorConnection($connection['ACCOUNT_TYPE'])
				)
				{
					$connections[$connection['ACCOUNT_TYPE']] = [
						'id' => $connection['ID'],
						'active' => true,
						'connected' => true,
						'userName' => $connection['SERVER_USERNAME'],
						'connectionName' => $connection['NAME'],
						'type' => $connection['ACCOUNT_TYPE'],
						'status' => self::isConnectionSuccess($connection['LAST_RESULT']),
						'server' => $connection['SERVER'],
						'syncOffset' => time() - $calculateTimestamp($connection['SYNCHRONIZED']),
					];
				}
				else if($googleHelper->isGoogleConnection($connection['ACCOUNT_TYPE']))
				{
					$googleAccountInfo = self::GetGoogleAccountInfo($bGoogleApi, $userId, $type);
					$connections['google'] = [
						'type' => 'google',
						'id' => $connection['ID'],
						'active' => true,
						'connected' => true,
						'userName' => $connection['SERVER_USERNAME']
							?? $googleAccountInfo['googleCalendarPrimaryId'] ?? null,
						'connectionName' => $connection['NAME'],
						'status' => self::isConnectionSuccess($connection['LAST_RESULT']),
						'syncOffset' => time() - $calculateTimestamp($connection['SYNCHRONIZED']),
					];
				}
			}

			return $connections;
		}

		return null;
	}

	/**
	 * @param $isGoogleApiEnabled
	 * @param $userId
	 * @param $type
	 * @return array
	 */
	private static function GetGoogleAccountInfo($isGoogleApiEnabled, $userId, $type): array
	{
		$googleApiStatus = [];

		if ($isGoogleApiEnabled === true)
		{
			$googleApiConnection = new GoogleApiSync($userId);
			$transportErrors = $googleApiConnection->getTransportErrors();

			if (!$transportErrors)
			{
				$googleApiStatus['googleCalendarPrimaryId'] = $googleApiConnection->getPrimaryId();
			}
		}

		return $googleApiStatus;
	}

	/**
	 * @param array $params
	 *
	 * @return string[]
	 *
	 * @throws LoaderException
	 * @throws ObjectNotFoundException
	 */
	public static function GetSyncLinks($params = []): array
	{
		$userId = $params['userId'];
		$type = $params['type'];
		$googleAuthLink = self::GetGoogleAuthLink($userId, $type);
		$office365AuthLink = self::GetOffice365AuthLink($userId, $type);

		return [
			'google' => $googleAuthLink,
			'office365' => $office365AuthLink,
		];
	}

	/**
	 * @param $userId
	 * @param $type
	 * @return string
	 */
	public static function GetGoogleAuthLink($userId, $type): string
	{
		$isCaldavEnabled = CCalendar::IsCalDAVEnabled() && $type === 'user';
		$isGoogleApiEnabled = CCalendar::isGoogleApiEnabled() && $type === 'user';
		$googleAuthLink = '';

		if (Loader::includeModule('socialservices')
			&& $isGoogleApiEnabled && $isCaldavEnabled)
		{
			$curPath = '#googleAuthSuccess';

			if (\CSocServGoogleProxyOAuth::isProxyAuth())
			{
				$client = new \CSocServGoogleProxyOAuth($userId);
			}
			else
			{
				$client = new CSocServGoogleOAuth($userId);
			}

			$client->getEntityOAuth()->addScope([
				'https://www.googleapis.com/auth/calendar',
				'https://www.googleapis.com/auth/calendar.readonly'
			]);
			/** @var Google\Helper $googleHelper */
			$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');

			return $client->getUrl(
				'opener',
				null,
				[
					'BACKURL' => $curPath,
					'APIKEY' => $googleHelper->getApiKey()
				]
			);
		}

		return $googleAuthLink;
	}

	/**
	 * @param $userId
	 * @param $type
	 *
	 * @return string
	 *
	 * @throws LoaderException
	 * @throws ObjectNotFoundException
	 */
	protected static function GetOffice365AuthLink($userId, $type): string
	{
		$isCaldavEnabled = CCalendar::IsCalDAVEnabled() && $type === 'user';
		$isOfficeApiEnabled = CCalendar::isOffice365ApiEnabled() && $type === 'user';
		$officeApiLink = '';

		if ($isOfficeApiEnabled && $isCaldavEnabled)
		{
			$curPath = '#office365AuthSuccess';

			$client = new CSocServOffice365OAuth($userId);

			/** @var Sync\Office365\Helper $helper */
			$helper = ServiceLocator::getInstance()->get('calendar.service.office365.helper');
			$client->getEntityOAuth()->addScope($helper::NEED_SCOPE);

			return $client->getUrl(
				'opener',
				null,
				[
					'BACKURL' => $curPath,
				],
			);
		}

		return $officeApiLink;
	}

	/**
	 * @param int|null $userId
	 * @param array|null $sectionsStatus
	 */
	public static function SetSectionStatus(?int $userId = 0, ?array $sectionsStatus = []): void
	{
		if (is_array($sectionsStatus))
		{
			foreach ($sectionsStatus as $id => $status)
			{
				$section = CCalendarSect::GetById($id);

				if ((int)$section['OWNER_ID'] === $userId)
				{
					$sectionStatus = [
						'ID' => $id,
						'ACTIVE' => $status
							? 'Y'
							: 'N',
					];

					$params['arFields'] = $sectionStatus;
					$params['userId'] = $userId;
					\CCalendarSect::Edit($params);
				}
			}
		}
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function UpdateUserConnections(): bool
	{
		$userId = \CCalendar::getCurUserId();
		if (Loader::includeModule('dav'))
		{
			\CDavGroupdavClientCalendar::DataSync("user", $userId);

			Sync\Managers\DataSyncManager::createInstance()->dataSync($userId);

			if (\CCalendar::isGoogleApiEnabled() || \CCalendar::isOffice365ApiEnabled())
			{
				$manager = new Sync\Managers\DataExchangeManager(
					FactoriesCollection::createByUserId(
						$userId, [
							Google\Factory::SERVICE_NAME,
							Office365\Factory::SERVICE_NAME,
						]
					)
				);
				$manager->import();
			}

			if (CCalendar::IsExchangeEnabled($userId))
			{
				$error = "";
				\CDavExchangeCalendar::DoDataSync($userId, $error);
				echo $error;
			}
		}

		return true;
	}

	/**
	 * @param string|null $lastResult
	 * @return bool
	 */
	public static function isConnectionSuccess(string $lastResult = null): bool
	{
		return (!is_null($lastResult) && preg_match("/^\[(2\d\d|0)\][a-z0-9 _]*/i", $lastResult));
	}

	/**
	 * @param array $section
	 * @param int $connectionId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function sendLocalDataToGoogle(array $section, int $connectionId): array
	{
		$google = GoogleApiSection::createInstance();
		$syncData = $google->createSection($section);
		sleep(1);

		if ($syncData === null)
		{
			return [];
		}

		$google->updateSectionList($syncData['GAPI_CALENDAR_ID'], $section);

		$section['GAPI_CALENDAR_ID'] = $syncData['GAPI_CALENDAR_ID'];
		$section['CAL_DAV_CON'] = $connectionId;
		self::updateSection($section);
		\CCalendar::clearCache();


		// if (isset($section['ID']))
		// {
		// 	self::sendLocalEventsToGoogle((int)$section['ID']);
		// }

		return $section;
	}

	/**
	 * @param array $section
	 * @return bool
	 */
	private static function updateSection(array $section): bool
	{
		$id = \CCalendarSect::Edit([
			'arFields' => [
				'ID' => $section['ID'],
				'GAPI_CALENDAR_ID' => $section['GAPI_CALENDAR_ID'],
				'CAL_DAV_CON' => $section['CAL_DAV_CON'],
			]
		]);

		return (bool)$id;
	}


	/**
	 * @param array $localCalendars
	 * @param int $connectionId
	 */
	private static function checkNotActualDataSection(array $localCalendars, int $connectionId, array $googleCalendars): void
	{
		if (!$localCalendars)
		{
			return;
		}

		$findAppropriateCalendar = function ($gApiCalendarId) use ($googleCalendars) {
			return array_filter($googleCalendars, function ($calendar) use ($gApiCalendarId) {
				return $calendar['id'] === $gApiCalendarId;
			});
		};

		foreach ($localCalendars as $section)
		{

			if ($section['EXTERNAL_TYPE'] !== CCalendarSect::EXTERNAL_TYPE_LOCAL)
			{
				continue;
			}

			if (
				isset($section['GAPI_CALENDAR_ID'])
				&& (int)$section['CAL_DAV_CON'] !== $connectionId
			)
			{
				\CCalendarEvent::cleanFieldsValueBySectionId(
					(int)$section['ID'],
					['SYNC_STATUS']
//					['DAV_XML_ID', 'G_EVENT_ID', 'CAL_DAV_LABEL']
				);
				\CCalendarSect::CleanFieldsValueById((int)$section['ID'], ['SYNC_TOKEN', 'PAGE_TOKEN']);
				self::sendLocalDataToGoogle($section, $connectionId);

				continue;
			}

			if (!isset($section['GAPI_CALENDAR_ID'])
				|| !$findAppropriateCalendar($section['GAPI_CALENDAR_ID']))
			{
				\CCalendarEvent::cleanFieldsValueBySectionId(
					(int)$section['ID'],
					['SYNC_STATUS']
//					['DAV_XML_ID', 'G_EVENT_ID', 'CAL_DAV_LABEL']
				);
				\CCalendarSect::CleanFieldsValueById((int)$section['ID'], ['SYNC_TOKEN', 'PAGE_TOKEN']);
				self::sendLocalDataToGoogle($section, $connectionId);
			}
		}
	}

	/**
	 * @param int $userId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private static function getCalendarsByUserId(int $userId): array
	{
		return Internals\SectionTable::query()
			->where('CAL_TYPE', 'user')
			->where('OWNER_ID', $userId)
			->where(\Bitrix\Main\ORM\Query\Query::filter()
				->logic('or')
				->where('EXTERNAL_TYPE', 'local')
				->where(\Bitrix\Main\ORM\Query\Query::filter()
					->where(\Bitrix\Main\ORM\Query\Query::filter()
						->logic('or')
						->whereNull('DAV_EXCH_CAL')
						->where('DAV_EXCH_CAL', '')
					)
					->where(\Bitrix\Main\ORM\Query\Query::filter()
						->logic('or')
						->whereNull('CAL_DAV_CAL')
						->where('CAL_DAV_CAL', '')
					)
				)
			)
			->setSelect(['*'])
			->addOrder('ID', 'DESC')
			->exec()
			->fetchAll()
		;
	}

	/**
	 * @param $connectionData
	 * @return array
	 */
	private static function getNotActualCalendars($connectionData, array $localSectionIndex): array
	{
		global $DB;
		$sections = [];

		$strSql = "SELECT DISTINCT *
					FROM b_calendar_section
					WHERE 
							(EXTERNAL_TYPE = 'local'
							AND CAL_TYPE = 'user'
							AND ((CAL_DAV_CON IS NULL AND GAPI_CALENDAR_ID IS NULL)
								OR (GAPI_CALENDAR_ID IS NOT NULL AND (CAL_DAV_CON IS NULL OR CAL_DAV_CON != " . (int)$connectionData['ID'] . ")))"
						." AND OWNER_ID = " . (int)$connectionData['ENTITY_ID'] . ")"
		;

		if ($localSectionIndex)
		{
			$prepareIndexedSection = function ($item) use ($DB) {
				return "'" . $DB->ForSql($item) . "'";
			};
			$strSql .= " OR GAPI_CALENDAR_ID IN (" . implode(", ", array_map($prepareIndexedSection, array_keys($localSectionIndex))).");";
		}
		else
		{
			$strSql .= ";";
		}

		$sectionsDB = $DB->Query($strSql);
		while ($section = $sectionsDB->Fetch())
		{
			$sections[] = $section;
		}

		return $sections;
	}

	/**
	 * @param int $userId
	 * @return array|false
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getGoogleConnectionByUserId(int $userId)
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		global $DB;
		$strSql = "SELECT ID, LAST_RESULT, SYNC_TOKEN, ENTITY_ID, ENTITY_TYPE"
			. " FROM b_dav_connections"
			. " WHERE"
			. " ENTITY_TYPE = 'user'"
			. " AND ACCOUNT_TYPE = '" . \Bitrix\Calendar\Sync\Google\Helper::GOOGLE_ACCOUNT_TYPE_API . "'"
			. " AND ENTITY_ID = " . $userId
			. ";";

		$connectionDb = $DB->Query($strSql);
		if ($connection = $connectionDb->Fetch())
		{
			return $connection;
		}

		return null;
	}

	/**
	 * @param GoogleApiSync $googleApiConnection
	 * @return bool
	 */
	private static function checkToSyncTokenExpiresError(GoogleApiSync $googleApiConnection): bool
	{
		$errors = $googleApiConnection->getTransportErrors();
		if ($errors && is_array($errors))
		{
			foreach ($errors as $error)
			{
				if (GoogleApiPush::isSyncTokenExpiresError($error['message']))
				{
					return true;
				}
			}
		}

		return false;
	}

	private static function isErrorToGoogle(GoogleApiSync $googleApiConnection, array $connectionData): bool
	{
		if ($error = $googleApiConnection->getTransportConnectionError())
		{
			CDavConnection::Update(
				$connectionData["ID"],
				[
					"LAST_RESULT" => $error,
					"SYNCHRONIZED" => ConvertTimeStamp(time(), "FULL")
				]
			);

			return true;
		}

		return false;
	}

	/**
	 * @param int $id
	 */
	private static function sendLocalEventsToGoogle(int $id): void
	{
		\Bitrix\Calendar\Update\SyncLocalDataSection::bind(0, [$id]);
	}

	/**
	 * @param $entityId
	 * @return void
	 */
	private static function sendPull($entityId): void
	{
		Util::addPullEvent(
			'refresh_sync_status',
			$entityId,
			[
				'syncInfo' => [
					'google' => [
						'status' => true,
						'type' => 'google',
						'connected' => true,
						'syncOffset' => 0,
					],
				],
				'requestUid' => Util::getRequestUid(),
			]
		);
	}

	/**
	 * @param array$localCalendars
	 * @return array
	 * @throws Exception
	 */
	private static function sortLocalSections(array $localCalendars): array
	{
		$localSections = [];
		$localSectionIndex = [];
		$sectionsToSendToGoogle = [];

		foreach ($localCalendars as $section)
		{
			if ($section['GAPI_CALENDAR_ID'])
			{
				if (!isset($localSectionIndex[$section['GAPI_CALENDAR_ID']]))
				{
					$localSections[] = $section;
					$localSectionIndex[$section['GAPI_CALENDAR_ID']] = count($localSections) - 1;
				}
				else
				{
					CCalendarSect::Delete($section["ID"], false);
					PushTable::delete(
						[
							'ENTITY_TYPE' => 'SECTION',
							'ENTITY_ID' => $section["ID"]
						]
					);
				}
			}
			elseif (empty($section['DAV_EXCH_CAL']) && empty($section['CAL_DAV_CON']))
			{
				$sectionsToSendToGoogle[] = $section;
//					self::sendLocalDataToGoogle($section, (int)$connectionData['ID']);
			}
		}

		return [$localSections, $localSectionIndex, $sectionsToSendToGoogle];
	}

	private static function prepareContext(): EventContext
	{
		// TODO: looking strange
		$context = new EventContext();
		return $context;
	}

	/**
	 * @param $ar
	 * @param array $arNewFields
	 *
	 * @return array
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function prepareRemind($ar, array $arNewFields): array
	{
		if (count($ar) === 2 && $ar[1] === 'date')
		{
			$arNewFields["REMIND"][] = [
				'type' => $ar[1],
				'value' => new DateTime($ar[0], 'Ymd\\THis\\Z'),
			];
		}
		else if (count($ar) === 2)
		{
			$arNewFields["REMIND"][] =
				[
					'type' => $ar[1],
					'count' => floatVal($ar[0])
				];
		}

		return $arNewFields;
	}
}