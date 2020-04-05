<?

use \Bitrix\Calendar\Sync\GoogleApiSync;
use \Bitrix\Calendar\Sync\GoogleApiPush;
use \Bitrix\Calendar\PushTable;

class CCalendarSync
{
	public static $handleExchangeMeeting = false;
	public static $doNotSendToGoogle = false;

	public static function doSync()
	{
		if (CCalendar::isGoogleApiEnabled())
		{
			self::$doNotSendToGoogle = true;
			self::dataSync();
			self::$doNotSendToGoogle = false;
		}
		return "CCalendarSync::doSync();";
	}

	public static function dataSync($connectionData = array())
	{
		if (!CModule::includeModule('dav') || !CModule::includeModule('socialservices'))
		{
			return;
		}

		$bShouldClearCache = false;
		$pushEnabled = CCalendar::IsBitrix24() || COption::GetOptionString('calendar', 'sync_by_push', false);

		if (!empty($connectionData))
		{
			if ($pushEnabled)
			{
				GoogleApiPush::createWatchChannels($connectionData['ID'] - 1);
			}
			$forceSync = false;
			if (isset($connectionData['forceSync']))
				$forceSync = $connectionData['forceSync'];

			$bShouldClearCache = (self::syncConnection($connectionData, $forceSync)) ? true : $bShouldClearCache;
		}
		else
		{
			$davConnections = \CDavConnection::GetList(
				array("SYNCHRONIZED" => "ASC"),
				array('ACCOUNT_TYPE' => 'google_api_oauth'),
				false,
				array('nTopCount' => GoogleApiSync::MAXIMUM_CONNECTIONS_TO_SYNC)
			);

			while($connection = $davConnections->fetch())
			{
				if ($pushEnabled)
				{
					$pushResult = PushTable::getByPrimary(array(
						'ENTITY_TYPE' => 'CONNECTION',
						'ENTITY_ID' => $connection['ID'])
					);

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
		$bShouldClearCache = false;
		CDavConnection::Update($connectionData["ID"], array("LAST_RESULT" => "[0]"), false);

		$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID']);
		if ($error = $googleApiConnection->getTransportConnectionError())
		{
			CDavConnection::Update($connectionData["ID"], array("LAST_RESULT" => $error), false);
			return false;
		}
		$googleCalendars = $googleApiConnection->getCalendarItems();

		$localCalendars = CCalendarSect::GetList(array(
			'arFilter' => array(
				'OWNER_ID' => $connectionData['ENTITY_ID'],
				'CAL_TYPE' => 'user',
				'CAL_DAV_CON' => $connectionData['ID']
			),
			'arOrder' => array('ID' => 'DESC'),
			'checkPermissions' => false,
			'getPermissions' => false
		));

		$localSections = array();
		$localSectionIndex = array();
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
					PushTable::delete(array('ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $section["ID"]));
				}
			}
		}

		if ($googleCalendars)
		{
			foreach ($googleCalendars as $externalCalendar)
			{
				$localCalendarIndex = isset($localSectionIndex[$externalCalendar['id']]) ? $localSectionIndex[$externalCalendar['id']] : false;

				if ($localCalendarIndex === false)
				{
					$arFields = array(
						'COLOR' => $externalCalendar['backgroundColor'],
						'TEXT_COLOR' => $externalCalendar['textColor'],
						'GAPI_CALENDAR_ID' => $externalCalendar['id'],
						'NAME' => $externalCalendar['summary'],
						'DESCRIPTION' => (!empty($externalCalendar['description'])) ? $externalCalendar['description'] : '',
						'OWNER_ID' => $connectionData["ENTITY_ID"],
						'CAL_TYPE' => 'user',
						'CAL_DAV_CON' => $connectionData["ID"],
					);
					$localSections[] = array_merge($arFields, array('ID' => CCalendarSect::Edit(array('arFields' => $arFields))));
				}
				elseif (empty($externalCalendar['deleted']) || !$externalCalendar['deleted'])
				{
					$arFields = array(
						'COLOR' => $externalCalendar['backgroundColor'],
						'TEXT_COLOR' => $externalCalendar['textColor'],
						'GAPI_CALENDAR_ID' => $externalCalendar['id'],
						'NAME' => $externalCalendar['summary'],
						'DESCRIPTION' => (!empty($externalCalendar['description'])) ? $externalCalendar['description'] : '',
						'OWNER_ID' => $connectionData["ENTITY_ID"],
						'CAL_TYPE' => 'user',
						'CAL_DAV_CON' => $connectionData["ID"],
						'ID' => $localSections[$localCalendarIndex]['ID']
					);
					CCalendarSect::Edit(array('arFields' => $arFields));
				}
				elseif (!empty($externalCalendar['deleted']) && $externalCalendar['deleted'])
				{
					CCalendarSect::Edit(array('arFields' => array('ID' => $localSections[$localCalendarIndex]['ID'], 'ACTIVE' => 'N')));
				}
			}
			CDavConnection::SetLastResult($connectionData["ID"], "[200] OK");
		}

		$pushOptionEnabled = COption::GetOptionString('calendar', 'sync_by_push', false) || CCalendar::IsBitrix24();
		if (!$forceSync && $pushOptionEnabled)
		{
			$pushResult = PushTable::getByPrimary(array('ENTITY_TYPE' => 'CONNECTION', 'ENTITY_ID' => $connectionData['ID']));

			if ($row = $pushResult->fetch())
			{
				GoogleApiPush::checkSectionsPush($localSections, $connectionData['ENTITY_ID']);
				$bShouldClearCache = true;
			}
		}

		foreach ($localSections as $localCalendar)
		{
			$eventsSyncToken = self::syncCalendarEvents($localCalendar);

			CCalendarSect::Edit(array('arFields' => array('ID' => $localCalendar['ID'], 'SYNC_TOKEN' => $eventsSyncToken)));
		}

		return $bShouldClearCache;
	}

	public static function syncCalendarEvents($localCalendar)
	{
		$googleApiConnection = new GoogleApiSync($localCalendar['OWNER_ID']);

		if ($error = $googleApiConnection->getTransportConnectionError())
		{
			CDavConnection::Update($localCalendar['CAL_DAV_CON'], array("LAST_RESULT" => $error), false);
			return false;
		}

		self::$doNotSendToGoogle = true;

		$localEventsList = CCalendarEvent::getList(array(
			'userId' => $localCalendar['OWNER_ID'],
			'arFilter' => array('SECTION' => $localCalendar['ID'])
		));
		$localEvents = array();

		foreach ($localEventsList as $localEvent)
		{
			if (!empty($localEvent['DAV_XML_ID']))
				$localEvents[$localEvent['DAV_XML_ID']]	= $localEvent;
		}
		unset($localEvent);

		$externalEvents = $googleApiConnection->getEvents($localCalendar);
		$eventsSyncToken = $googleApiConnection->getEventsSyncToken();
		foreach ($externalEvents as $externalEvent)
		{
			$eventExists = !empty($localEvents[$externalEvent['DAV_XML_ID']]);
			if ($externalEvent['status'] == 'cancelled')
			{
				if ($eventExists)
				{
					if (!empty($externalEvent[$externalEvent['recurringEventId']]) && $externalEvent[$externalEvent['recurringEventId']]['status'] == 'cancelled' && !empty($localEvents[$externalEvent['recurringEventId']]))
					{
						CCalendarEvent::Delete(array(
							'id' => $localEvents[$externalEvent['recurringEventId']]['ID'],
							'bMarkDeleted' => true,
							'Event' => $localEvents[$externalEvent['recurringEventId']],
							'userId' => $localCalendar['OWNER_ID'],
							'sendNotification' => false
						));
					}

					if (!empty($localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING']) && $localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING'])
						self::$doNotSendToGoogle = false;
					CCalendarEvent::Delete(array(
						'id' => $localEvents[$externalEvent['DAV_XML_ID']]['ID'],
						'bMarkDeleted' => true,
						'Event' => $localEvents[$externalEvent['DAV_XML_ID']],
						'userId' => $localCalendar['OWNER_ID'],
						'sendNotification' => false
					));
					if (!empty($localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING']) && $localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING'])
						self::$doNotSendToGoogle = true;

					foreach ($localEvents as $localEvent)
					{
						if (!empty($localEvent['RECURRENCE_ID']) && $localEvent['RECURRENCE_ID'] == $localEvents[$externalEvent['DAV_XML_ID']]['ID'])
						{
							CCalendarEvent::Delete(array(
								'id' => $localEvent['ID'],
								'bMarkDeleted' => true,
								'Event' => $localEvent,
								'userId' => $localCalendar['OWNER_ID'],
								'sendNotification' => false
							));
						}
					}
				}
				elseif (!empty($externalEvent['recurringEventId']) && !empty($localEvents[$externalEvent['recurringEventId']]) && $externalEvent['isRecurring'] == 'Y')
				{
					$excludeDates = CCalendarEvent::GetExDate($localEvents[$externalEvent['recurringEventId']]['EXDATE']);
					$excludeDates[] = $externalEvent['EXDATE'];
					$localEvents[$externalEvent['recurringEventId']]['EXDATE'] = implode(';', $excludeDates);

					$newParentData = array('arFields' => $localEvents[$externalEvent['recurringEventId']], 'userId' => $localCalendar['OWNER_ID'], 'fromWebservice' => true);

					$newParentData['arFields']['EXDATE'] = CCalendarEvent::SetExDate($excludeDates);
					$newParentData['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($newParentData['arFields']['RRULE']);
					CCalendar::SaveEvent($newParentData);

				}
				continue;
			}
			if ($externalEvent['isRecurring'] == "N")
			{
				if (!$eventExists)
				{
					$newEventData = array_merge(
						$externalEvent,
						array(
							'SECTIONS' => array($localCalendar['ID']),
							'OWNER_ID' => $localCalendar['OWNER_ID'],
							'userId' => $localCalendar['OWNER_ID']
						)
					);

					$newEvent = array_merge(array('ID' => CCalendarEvent::Edit(array('arFields' => $newEventData))), $newEventData);
					$localEvents[$externalEvent['DAV_XML_ID']] = $newEvent;
					$bShouldClearCache = true;
				}
				else
				{
					$newParentData = array(
						'arFields' => array_merge(array(
							'ID' => $localEvents[$externalEvent['DAV_XML_ID']]['ID'],
						), $externalEvent),
						'userId' => $localCalendar['OWNER_ID'],
						'fromWebservice' => true
					);
					if (!empty($localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING']) && $localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING'])
						self::$doNotSendToGoogle = false;
					CCalendar::SaveEvent($newParentData);
					if (!empty($localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING']) && $localEvents[$externalEvent['DAV_XML_ID']]['IS_MEETING'])
						self::$doNotSendToGoogle = true;
				}
			}
			elseif ($externalEvent['isRecurring'] == "Y")
			{
				if ($externalEvent['status'] == 'confirmed' && $externalEvent['hasMoved'] == "Y")
				{

					$recurrentParentEventExists = !empty($localEvents[$externalEvent['recurringEventId']]);
					if ($recurrentParentEventExists)
					{

						$eventId = !empty($localEvents[$externalEvent['DAV_XML_ID']]) ? $localEvents[$externalEvent['DAV_XML_ID']]['ID'] : NULL;

						if (!$eventId && preg_match('/_/', $externalEvent['DAV_XML_ID']))
						{
							unset($externalEvent['DAV_XML_ID']);
						}
						$recurrenceId = NULL;
						if ($localEvents[$externalEvent['recurringEventId']])
						{
							$recurrenceId = $localEvents[$externalEvent['recurringEventId']]['ID'];
							$excludeDates = CCalendarEvent::GetExDate($localEvents[$externalEvent['recurringEventId']]['EXDATE']);
							$excludeDates[] = $externalEvent['EXDATE'];
							$localEvents[$externalEvent['recurringEventId']]['EXDATE'] = implode(';', $excludeDates);
							$newParentData = array('arFields' => $localEvents[$externalEvent['recurringEventId']], 'userId' => $localCalendar['OWNER_ID'], 'fromWebservice' => true);

							$newParentData['arFields']['EXDATE'] = CCalendarEvent::SetExDate($excludeDates);
							$newParentData['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($newParentData['arFields']['RRULE']);
							CCalendar::SaveEvent($newParentData);
						}

						CCalendar::SaveEvent(array(
							'arFields' => array_merge($externalEvent, array(
								'RECURRENCE_ID' => $recurrenceId,
								'DATE_FROM' => $externalEvent['DATE_FROM'],
								'SECTIONS' => array($localCalendar['ID']),
								'DATE_TO' => $externalEvent['DATE_TO'],
							)),
							'userId' => $localCalendar['OWNER_ID'],
							'fromWebservice' => true
						));
					}
				}
			}
		}
		self::$doNotSendToGoogle = false;
		return $eventsSyncToken;
	}

	public static function ModifyEvent($calendarId, $arFields, $params = array())
	{
		list($sectionId, $entityType, $entityId) = $calendarId;
		$userId = $entityType == 'user' ? $entityId : 0;
		$eventId = false;

		$bExchange = CCalendar::IsExchangeEnabled($userId) && $entityType == 'user';
		$saveEvent = true;

		CCalendar::SetSilentErrorMode();
		if ($sectionId && CCalendarSect::GetById($sectionId, false))
		{
			CCalendar::SetOffset(false, CCalendar::GetOffset($userId));
			$entityType = strtolower($entityType);
			$eventId = ((isset($arFields["ID"]) && (intval($arFields["ID"]) > 0)) ? intval($arFields["ID"]) : 0);
			$arNewFields = array(
				"DAV_XML_ID" => $arFields['XML_ID'],
				"CAL_DAV_LABEL" => (isset($arFields['PROPERTY_BXDAVCD_LABEL']) && strlen($arFields['PROPERTY_BXDAVCD_LABEL']) > 0) ? $arFields['PROPERTY_BXDAVCD_LABEL'] : '',
				"DAV_EXCH_LABEL" => (isset($arFields['PROPERTY_BXDAVEX_LABEL']) && strlen($arFields['PROPERTY_BXDAVEX_LABEL']) > 0) ? $arFields['PROPERTY_BXDAVEX_LABEL'] : '',
				"ID" => $eventId,
				'NAME' => $arFields["NAME"] ? $arFields["NAME"] : GetMessage('EC_NONAME_EVENT'),
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId,
				'DESCRIPTION' => isset($arFields['DESCRIPTION']) ? $arFields['DESCRIPTION'] : '',
				'SECTIONS' => $sectionId,
				'ACCESSIBILITY' => isset($arFields['ACCESSIBILITY']) ? $arFields['ACCESSIBILITY'] : 'busy',
				'IMPORTANCE' => isset($arFields['IMPORTANCE']) ? $arFields['IMPORTANCE'] : 'normal',
				"REMIND" => is_array($arFields['REMIND']) ? $arFields['REMIND'] : array(),
				"RRULE" => is_array($arFields['RRULE']) ? is_array($arFields['RRULE']) : array(),
				"VERSION" => isset($arFields['VERSION']) ? intVal($arFields['VERSION']) : 1,
				"PRIVATE_EVENT" => !!$arFields['PRIVATE_EVENT']
			);

			$arNewFields["DATE_FROM"] = $arFields['DATE_FROM'];
			$arNewFields["DATE_TO"] = $arFields['DATE_TO'];
			$arNewFields["TZ_FROM"] = $arFields['TZ_FROM'];
			$arNewFields["TZ_TO"] = $arFields['TZ_TO'];
			$arNewFields["SKIP_TIME"] = $arFields['SKIP_TIME'];

			if (isset($arFields['RECURRENCE_ID']))
				$arNewFields['RECURRENCE_ID'] = $arFields['RECURRENCE_ID'];

			if ($arNewFields["SKIP_TIME"])
			{
				$arNewFields["DATE_FROM"] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_FROM']), false);
				$arNewFields["DATE_TO"] = CCalendar::Date(CCalendar::Timestamp($arNewFields['DATE_TO']) - CCalendar::GetDayLen(), false);
			}

			if (!empty($arFields['PROPERTY_REMIND_SETTINGS']))
			{
				$ar = explode("_", $arFields["PROPERTY_REMIND_SETTINGS"]);
				if(count($ar) == 2)
					$arNewFields["REMIND"][] = array('type' => $ar[1],'count' => floatVal($ar[0]));
			}

			if (!empty($arFields['PROPERTY_ACCESSIBILITY']))
				$arNewFields["ACCESSIBILITY"] = $arFields['PROPERTY_ACCESSIBILITY'];
			if (!empty($arFields['PROPERTY_IMPORTANCE']))
				$arNewFields["IMPORTANCE"] = $arFields['PROPERTY_IMPORTANCE'];
			if (!empty($arFields['PROPERTY_LOCATION']))
				$arNewFields["LOCATION"] = CCalendar::UnParseTextLocation($arFields['PROPERTY_LOCATION']);
			if (!empty($arFields['DETAIL_TEXT']))
				$arNewFields["DESCRIPTION"] = $arFields['DETAIL_TEXT'];

			$arNewFields["DESCRIPTION"] = CCalendar::ClearExchangeHtml($arNewFields["DESCRIPTION"]);
			if (isset($arFields["PROPERTY_PERIOD_TYPE"]) && in_array($arFields["PROPERTY_PERIOD_TYPE"], array("DAILY", "WEEKLY", "MONTHLY", "YEARLY")))
			{
				$arNewFields['RRULE']['FREQ'] = $arFields["PROPERTY_PERIOD_TYPE"];
				$arNewFields['RRULE']['INTERVAL'] = $arFields["PROPERTY_PERIOD_COUNT"];

				if (!isset($arNewFields['DT_LENGTH']) && !empty($arFields['PROPERTY_EVENT_LENGTH']))
				{
					$arNewFields['DT_LENGTH'] = intval($arFields['PROPERTY_EVENT_LENGTH']);
				}
				else
				{
					$arNewFields['DT_LENGTH'] = $arFields['DT_TO_TS'] - $arFields['DT_FROM_TS'];
				}

				if ($arNewFields['RRULE']['FREQ'] == "WEEKLY" && !empty($arFields['PROPERTY_PERIOD_ADDITIONAL']))
				{
					$arNewFields['RRULE']['BYDAY'] = array();
					$bydays = explode(',',$arFields['PROPERTY_PERIOD_ADDITIONAL']);
					foreach($bydays as $day)
					{
						$day = CCalendar::WeekDayByInd($day, false);
						if ($day !== false)
							$arNewFields['RRULE']['BYDAY'][] = $day;
					}
					$arNewFields['RRULE']['BYDAY'] = implode(',',$arNewFields['RRULE']['BYDAY']);
				}

				if (isset($arFields['PROPERTY_RRULE_COUNT']))
					$arNewFields['RRULE']['COUNT'] = $arFields['PROPERTY_RRULE_COUNT'];
				elseif (isset($arFields['PROPERTY_PERIOD_UNTIL']))
					$arNewFields['RRULE']['UNTIL'] = $arFields['PROPERTY_PERIOD_UNTIL'];
				else
					$arNewFields['RRULE']['UNTIL'] = $arFields['DT_TO_TS'];

				if (isset($arFields['EXDATE']))
					$arNewFields['EXDATE'] = $arFields["EXDATE"];
			}

			if ($arFields['IS_MEETING'] && $bExchange && self::isExchangeMeetingEnabled())
			{
				$arNewFields['IS_MEETING'] = $arFields['IS_MEETING'];
				$arNewFields['MEETING_HOST'] = $arFields['MEETING_HOST'];
				$arNewFields['MEETING'] = $arFields['MEETING'];
				$arNewFields['ATTENDEES_CODES'] = $arFields['ATTENDEES_CODES'];
			}

			if ($saveEvent)
			{
				$eventId = CCalendar::SaveEvent(
					array(
						'arFields' => $arNewFields,
						'userId' => $userId,
						'bAffectToDav' => false, // Used to prevent syncro with calDav again
						'bSilentAccessMeeting' => true,
						'autoDetectSection' => false
					)
				);
			}

			if ($eventId && $arFields['IS_MEETING'] && $arFields['ATTENDEES_RESPONSE'] && $bExchange && self::isExchangeMeetingEnabled())
			{
				foreach($arFields['ATTENDEES_RESPONSE'] as $attendeeId => $status)
				{
					CCalendarEvent::SetMeetingStatus(array(
						'userId' => $attendeeId,
						'eventId' => $eventId,
						'status' => $status,
						'personalNotification' => false,
						'hostNotification' => false,
						'affectRecRelatedEvents' => false
					));
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
				$arFields['RECURRENCE_ID'] = $parentEvent['ID'];
				self::ModifyEvent($params['calendarId'], $arFields);

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

	public static function DoSaveToDav($params = array(), &$arFields, $event = false)
	{
		if (self::$doNotSendToGoogle)
		{
			return true;
		}
		$sectionId = $params['sectionId'];

		$bExchange = $params['bExchange'];
		$bCalDav = $params['bCalDav'];

		if (isset($event['DAV_XML_ID']))
			$arFields['DAV_XML_ID'] = $event['DAV_XML_ID'];
		if (isset($event['DAV_EXCH_LABEL']))
			$arFields['DAV_EXCH_LABEL'] = $event['DAV_EXCH_LABEL'];
		if (isset($event['CAL_DAV_LABEL']))
			$arFields['CAL_DAV_LABEL'] = $event['CAL_DAV_LABEL'];
		if (!isset($arFields['DATE_CREATE']) && isset($event['DATE_CREATE']))
			$arFields['DATE_CREATE'] = $event['DATE_CREATE'];

		$section = CCalendarSect::GetById($sectionId, false);

		if ($event)
		{
			if ($event['SECT_ID'] != $sectionId)
			{
				$bCalDavCur = CCalendar::IsCalDAVEnabled() && $event['CAL_TYPE'] == 'user' && strlen($event['CAL_DAV_LABEL']) > 0;
				$bExchangeEnabledCur = CCalendar::IsExchangeEnabled() && $event['CAL_TYPE'] == 'user';

				if ($bExchangeEnabledCur || $bCalDavCur)
				{
					$res = CCalendarSync::DoDeleteToDav(array(
						'bCalDav' => $bCalDavCur,
						'bExchangeEnabled' => $bExchangeEnabledCur,
						'sectionId' => $event['SECT_ID']
					), $event);

					if ($event['DAV_EXCH_LABEL'])
						$event['DAV_EXCH_LABEL'] = '';

					if ($res !== true)
						return CCalendar::ThrowError($res);
				}
			}
		}

		$arDavFields = $arFields;
		CCalendarEvent::CheckFields($arDavFields);
		if ($arDavFields['RRULE'] != '')
			$arDavFields['RRULE'] = $arFields['RRULE'];

		if ($arDavFields['LOCATION']['NEW'] !== '')
			$arDavFields['LOCATION']['NEW'] = CCalendar::GetTextLocation($arDavFields['LOCATION']['NEW']);
		$arDavFields['PROPERTY_IMPORTANCE'] = $arDavFields['IMPORTANCE'];
		$arDavFields['PROPERTY_LOCATION'] = $arDavFields['LOCATION']['NEW'];

		$arDavFields['REMIND_SETTINGS'] = '';
		if ($arFields['REMIND'] && is_array($arFields['REMIND']) && is_array($arFields['REMIND'][0]))
			$arDavFields['REMIND_SETTINGS'] = floatVal($arFields['REMIND'][0]['count']).'_'.$arFields['REMIND'][0]['type'];

		if (isset($arDavFields['RRULE'], $arDavFields['RRULE']['BYDAY']) && is_array($arDavFields['RRULE']['BYDAY']))
			$arDavFields['RRULE']['BYDAY'] = implode(',',$arDavFields['RRULE']['BYDAY']);

		// **** Synchronize with GoogleApi ****
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && !is_null($section['GAPI_CALENDAR_ID']);
		if ($bGoogleApi)
		{
			$googleApiCalendar = new GoogleApiSync($arFields['OWNER_ID']);

			$arFields['DAV_XML_ID'] = $googleApiCalendar->saveEvent($arDavFields, $section['GAPI_CALENDAR_ID']);


			return true;
		}
		// **** Synchronize with CalDav ****
		if ($bCalDav && $section['CAL_DAV_CON'] > 0)
		{
			// New event or move existent event to DAV calendar
			if($arFields['ID'] <= 0 || ($event && !$event['CAL_DAV_LABEL']))
			{
				$DAVRes = CDavGroupdavClientCalendar::DoAddItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $arDavFields);
			}
			else // Edit existent event
			{
				$DAVRes = CDavGroupdavClientCalendar::DoUpdateItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $event['DAV_XML_ID'], $event['CAL_DAV_LABEL'], $arDavFields);
			}

			if (!is_array($DAVRes) || !array_key_exists("XML_ID", $DAVRes))
				return CCalendar::CollectCalDAVErros($DAVRes);

			// // It's ok, we successfuly save event to caldav calendar - and save it to DB
			$arFields['DAV_XML_ID'] = $DAVRes['XML_ID'];
			$arFields['CAL_DAV_LABEL'] = $DAVRes['MODIFICATION_LABEL'];
		}
		// **** Synchronize with Exchange ****
		elseif ($bExchange && $section['IS_EXCHANGE'] && strlen($section['DAV_EXCH_CAL']) > 0 && $section['DAV_EXCH_CAL'] !== 0)
		{
			$ownerId = $arFields['OWNER_ID'];

			// Here we check if parent event was created in exchange calendar and if it is meeting
			// If yes, we expect that it was already created in MS Exchange server
			// and we don't need to dublicate this entry.
			if (self::isExchangeMeetingEnabled()
				&& $arFields['IS_MEETING']
				&& $arFields['MEETING_HOST'] != $ownerId
				&& CCalendar::IsExchangeEnabled($arFields['MEETING_HOST']))
			{
				$parentEvent = CCalendarEvent::GetById($arFields['PARENT_ID']);
				if ($parentEvent['DAV_EXCH_LABEL'])
				{
					$parentSection = CCalendarSect::GetById($parentEvent['SECT_ID'], false);
					if ($parentSection['IS_EXCHANGE'] &&
						strlen($parentSection['DAV_EXCH_CAL']) > 0 && $parentSection['DAV_EXCH_CAL'] !== 0)
					{
						return;
					}
				}
			}

			if (self::isExchangeMeetingEnabled()
				&& $arFields['IS_MEETING']
				&& $arFields['MEETING_HOST'] == $ownerId
				&& count($arFields['ATTENDEES']) > 0)
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

			// New event  or move existent event to Exchange calendar
			if ($arFields['ID'] <= 0 || ($event && !$event['DAV_EXCH_LABEL']))
			{
				$exchRes = CDavExchangeCalendar::DoAddItem($ownerId, $section['DAV_EXCH_CAL'], $arDavFields);
			}
			else
			{
				$exchRes = CDavExchangeCalendar::DoUpdateItem($ownerId, $event['DAV_XML_ID'], $event['DAV_EXCH_LABEL'], $arDavFields);
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
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && $event['CAL_TYPE'] == 'user' && !is_null($section['GAPI_CALENDAR_ID']);

		if (!empty($section) && $section['CAL_DAV_CON'] && \Bitrix\Main\Loader::includeModule('dav'))
		{
			$calendarConnection = CDavConnection::getById($section['CAL_DAV_CON']);
			if ($calendarConnection['ACCOUNT_TYPE'] == 'caldav_google_oauth' || empty($section['GAPI_CALENDAR_ID']))
			{
				$bGoogleApi = false;
			}
		}

		if ($bGoogleApi)
		{
			if (!self::$doNotSendToGoogle)
			{
				$googleApiCalendar = new GoogleApiSync($event['OWNER_ID']);
				$googleApiCalendar->deleteEvent($event['DAV_XML_ID'], $section['GAPI_CALENDAR_ID']);
			}
			return true;
		}

		$bExchangeEnabled = $params['bExchangeEnabled'];
		$bCalDav = $params['bCalDav'];

		// Google and other caldav
		if ($bCalDav && $section['CAL_DAV_CON'] > 0)
		{
			$DAVRes = CDavGroupdavClientCalendar::DoDeleteItem($section['CAL_DAV_CON'], $section['CAL_DAV_CAL'], $event['DAV_XML_ID']);

			if ($DAVRes !== true)
				return CCalendar::CollectCalDAVErros($DAVRes);
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

	public static function SyncCalendarSections($connectionType, $arCalendars, $entityType, $entityId, $connectionId = null)
	{
		CCalendar::SetSilentErrorMode();
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

		$entityType = strtolower($entityType);
		$entityId = intVal($entityId);

		$tempUser = CCalendar::TempUser(false, true);

		$calendarNames = array();
		foreach ($arCalendars as $value)
			$calendarNames[$value["XML_ID"]] = $value;

		if ($connectionType == 'exchange')
		{
			$xmlIdField = "DAV_EXCH_CAL";
			$xmlIdModLabel = "DAV_EXCH_MOD";
		}
		elseif ($connectionType == 'caldav')
		{
			$xmlIdField = "CAL_DAV_CAL";
			$xmlIdModLabel = "CAL_DAV_MOD";
		}
		else
			return array();

		$arFilter = array(
			'CAL_TYPE' => $entityType,
			'OWNER_ID' => $entityId,
			'!'.$xmlIdField => false
		);

		if ($connectionType == 'caldav')
			$arFilter["CAL_DAV_CON"] = $connectionId;
		if ($connectionType == 'exchange')
			$arFilter["IS_EXCHANGE"] = 1;

		$arResult = array();
		$res = CCalendarSect::GetList(array(
			'arFilter' => $arFilter,
			'checkPermissions' => false,
			'getPermissions' => false
		));

		foreach($res as $section)
		{
			$xmlId = $section[$xmlIdField];
			$modificationLabel = $section[$xmlIdModLabel];

			if ($connectionType == 'caldav' && $section['DAV_EXCH_CAL'])
				continue;

			if (empty($xmlId))
				continue;

			if (!array_key_exists($xmlId, $calendarNames))
			{
				CCalendarSect::Delete($section["ID"]);
			}
			else
			{
				if ($modificationLabel != $calendarNames[$xmlId]["MODIFICATION_LABEL"])
				{
					CCalendarSect::Edit(array(
						'arFields' => array(
							"ID" => $section["ID"],
							"NAME" => $calendarNames[$xmlId]["NAME"],
							"OWNER_ID" => $entityType == 'user' ? $entityId : 0,
							"CREATED_BY" => $entityType == 'user' ? $entityId : 0,
							"DESCRIPTION" => $calendarNames[$xmlId]["DESCRIPTION"],
							"COLOR" => $calendarNames[$xmlId]["COLOR"],
							$xmlIdModLabel => $calendarNames[$xmlId]["MODIFICATION_LABEL"],
						)
					));
				}

				if (empty($modificationLabel) || ($modificationLabel != $calendarNames[$xmlId]["MODIFICATION_LABEL"]))
				{
					$arResult[] = array(
						"XML_ID" => $xmlId,
						"CALENDAR_ID" => array($section["ID"], $entityType, $entityId)
					);
				}

				unset($calendarNames[$xmlId]);
			}
		}

		foreach($calendarNames as $key => $value)
		{
			$arFields = Array(
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId,
				'NAME' => $value["NAME"],
				'DESCRIPTION' => $value["DESCRIPTION"],
				'COLOR' => $value["COLOR"],
				'EXPORT' => array('ALLOW' => false),
				"CREATED_BY" => $entityType == 'user' ? $entityId : 0,
				'ACCESS' => array(),
				$xmlIdField => $key,
				$xmlIdModLabel => $value["MODIFICATION_LABEL"]
			);

			if ($connectionType == 'caldav')
				$arFields["CAL_DAV_CON"] = $connectionId;
			if ($entityType == 'user')
				$arFields["CREATED_BY"] = $entityId;
			if ($connectionType == 'exchange')
				$arFields["IS_EXCHANGE"] = 1;

			$id = intVal(CCalendar::SaveSection(array('arFields' => $arFields, 'bAffectToDav' => false)));
			if ($id)
				$arResult[] = array("XML_ID" => $key, "CALENDAR_ID" => array($id, $entityType, $entityId));
		}

		CCalendar::TempUser($tempUser, false);
		CCalendar::SetSilentErrorMode(false);

		return $arResult;
	}

	public static function GetGoogleCalendarConnection()
	{
		$userId = CCalendar::GetCurUserId();
		$result = array();
		if (\Bitrix\Main\Loader::includeModule('socialservices'))
		{
			$client = new CSocServGoogleOAuth($userId);
			$client->getEntityOAuth()->addScope(array('https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'));

			$id = false;
			if($client->getEntityOAuth()->GetAccessToken())
			{
				$url = "https://www.googleapis.com/calendar/v3/users/me/calendarList";
				$h = new \Bitrix\Main\Web\HttpClient();
				$h->setHeader('Authorization', 'Bearer '.$client->getEntityOAuth()->getToken());
				$response = \Bitrix\Main\Web\Json::decode($h->get($url));
				$id = self::GetGoogleOauthPrimaryId($response);
				$result['googleCalendarPrimaryId'] = $id;
			}

			if(!$id)
			{
				$curPath = CCalendar::GetPath();
				if($curPath)
					$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
				$result['authLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
			}
		}
		return $result;
	}

	private static function GetGoogleOauthPrimaryId($data = array())
	{
		$id = false;
		if (is_array($data['items']) && count($data['items']) > 0)
		{
			foreach($data['items'] as $item)
			{
				if (is_array($item) && $item['primary'] && $item['accessRole'] == 'owner')
				{
					$id = $item['id'];
					break;
				}
			}
		}
		return $id;
	}

	public static function GetExchangeEmailForUser($idList = array())
	{
		global $DB;

		$users = array();

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
				if(intVal($id) > 0)
				{
					$strIdList .= ','.intVal($id);
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
					$users[$entry['ID']] = (($exchangeUseLogin == "Y") ? $entry["LOGIN"].$exchangeMailbox : $entry["UF_BXDAVEX_MAILBOX"]);
					if (empty($users[$entry['ID']]))
						$users[$entry['ID']] = $entry['EMAIL'];
				}
			}
		}

		return $users;
	}

	public static function GetUsersByEmailList($emailList = array())
	{
		global $DB;
		$users = array();

		if (CCalendar::IsSocNet())
		{
			$exchangeMailbox = COption::GetOptionString("dav", "exchange_mailbox", "");
			$exchangeUseLogin = COption::GetOptionString("dav", "exchange_use_login", "Y");
			$exchangeMailboxStrlen = strlen($exchangeMailbox);

			$strValue = "";
			foreach($emailList as $email)
			{
				$strValue .= ",'".CDatabase::ForSql($email)."'";
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
				$checkedEmails = array();
				while($entry = $res->Fetch())
				{
					$checkedEmails[] = strtolower($entry["UF_BXDAVEX_MAILBOX"]);
					$users[] = $entry['ID'];
				}

				if ($exchangeUseLogin == "Y")
				{
					$strLogins = '';
					foreach($emailList as $email)
					{
						if(!in_array(strtolower($email), $checkedEmails) && strtolower(substr($email, strlen($email) - $exchangeMailboxStrlen)) == strtolower($exchangeMailbox))
						{
							$value = substr($email, 0, strlen($email) - $exchangeMailboxStrlen);
							$strLogins .= ",'".CDatabase::ForSql($value)."'";
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
		return self::$handleExchangeMeeting;
	}
}
?>