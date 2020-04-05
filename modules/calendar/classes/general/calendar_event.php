<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/classes/general/calendar.php");

use \Bitrix\Main\Loader;
use \Bitrix\Disk\Uf\FileUserType;
use \Bitrix\Disk\AttachedObject;
use \Bitrix\Main\Localization\Loc;

class CCalendarEvent
{
	public static $eventUFDescriptions;
	public static $TextParser;
	private static $fields = array(), $lastAttendeesList = array();

	public static function GetLastAttendees()
	{
		$res = array();
		if (isset(self::$lastAttendeesList) && is_array(self::$lastAttendeesList))
		{
			foreach(self::$lastAttendeesList as $id => $attendees)
			{
				$res[$id] = array();
				foreach($attendees as $user)
				{
					$name = trim($user["USER_NAME"]);

					$type = (intVal($user["USER_ID"]) > 0) ? "int" : "ext";
					if ($type == "int")
					{
						$user["ID"] = intVal($user["USER_ID"]);
						$name = CCalendar::GetUserName($user);
					}

					$res[$id][] = array(
						"type" => $type,
						"id" => intVal($user["USER_ID"]),
						"name" => $name,
						"email" => trim($user["USER_EMAIL"]), // For ext only
						"photo" => $user["PERSONAL_PHOTO"],
						"status" => trim($user["STATUS"]),
						"desc" => trim($user["DESCRIPTION"]),
						"color" => trim($user["COLOR"]),
						"text_color" => trim($user["TEXT_COLOR"]),
						"accessibility" => trim($user["ACCESSIBILITY"])
					);
				}
			}
		}
		return $res;
	}

	public static function SetLastAttendees($attendees)
	{
		self::$lastAttendeesList = $attendees;
	}

	public static function CheckRRULE($RRule = array())
	{
		if ($RRule['FREQ'] != 'WEEKLY' && isset($RRule['BYDAY']))
			unset($RRule['BYDAY']);
		return $RRule;
	}

	public static function Edit($params = array())
	{
		global $DB, $CACHE_MANAGER;
		$arFields = $params['arFields'];

		$arAffectedSections = array();
		$significantChanges = isset($params['significantChanges']) ? $params['significantChanges'] : false;
		$sendInvitations = $params['sendInvitations'] !== false;
		$sendEditNotification = $params['sendEditNotification'] !== false;

		$result = false;
		$attendeesCodes = array();
		// Get current user id
		$userId = (isset($params['userId']) && intVal($params['userId']) > 0) ? intVal($params['userId']) : CCalendar::GetCurUserId();
		if (!$userId && isset($arFields['CREATED_BY']))
			$userId = intVal($arFields['CREATED_BY']);
		$path = !empty($params['path']) ? $params['path'] : CCalendar::GetPath($arFields['CAL_TYPE'], $arFields['OWNER_ID'], true);

		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		$arFields['TIMESTAMP_X'] = CCalendar::Date(mktime(), true, false);
		if ($isNewEvent)
		{
			if (!isset($arFields['CREATED_BY']))
			{
				$arFields['CREATED_BY'] = ($arFields['IS_MEETING'] && $arFields['CAL_TYPE'] == 'user' && $arFields['OWNER_ID']) ? $arFields['OWNER_ID'] : $userId;
			}

			if (!isset($arFields['DATE_CREATE']))
				$arFields['DATE_CREATE'] = $arFields['TIMESTAMP_X'];
		}

		if (!isset($arFields['OWNER_ID']) || !$arFields['OWNER_ID'])
			$arFields['OWNER_ID'] = 0;

		// Current event
		$currentEvent = array();

		if ($arFields['IS_MEETING'] && !isset($arFields['ATTENDEES']) && isset($arFields['ATTENDEES_CODES']))
		{
			$arFields['ATTENDEES'] = \CCalendar::getDestinationUsers($arFields['ATTENDEES_CODES']);
		}

		if (!$isNewEvent)
		{
			if (isset($params['currentEvent']))
				$currentEvent = $params['currentEvent'];
			else
				$currentEvent = CCalendarEvent::GetById($arFields['ID']);

			if (empty($arFields['LOCATION']['OLD']))
			{
				if (!isset($arFields['LOCATION']))
				{
					$arFields['LOCATION'] = array('NEW' => '');
				}
				$arFields['LOCATION']['OLD'] = $currentEvent['LOCATION'];
			}


			if ($currentEvent['IS_MEETING'] && !isset($arFields['ATTENDEES']) && $currentEvent['PARENT_ID'] == $currentEvent['ID'] && $arFields['IS_MEETING'])
			{
				$arFields['ATTENDEES'] = array();
				$attendees = self::GetAttendees($currentEvent['PARENT_ID']);
				if ($attendees[$currentEvent['PARENT_ID']])
				{
					for($i = 0, $l = count($attendees[$currentEvent['PARENT_ID']]); $i < $l; $i++)
					{
						$arFields['ATTENDEES'][] = $attendees[$currentEvent['PARENT_ID']][$i]['USER_ID'];
					}
				}
			}

			if ($currentEvent['PARENT_ID'])
				$arFields['PARENT_ID'] = $currentEvent['PARENT_ID'];
		}


		if ($userId > 0 && self::CheckFields($arFields, $currentEvent, $userId))
		{
			if (!$isNewEvent && !isset($params['significantChanges']) && $arFields)
			{
				$significantChanges = self::CheckSignificantChangesFields($arFields, $currentEvent);
			}

			if ($arFields['CAL_TYPE'] == 'user')
				$CACHE_MANAGER->ClearByTag('calendar_user_'.$arFields['OWNER_ID']);
			$attendees = is_array($arFields['ATTENDEES']) ? $arFields['ATTENDEES'] : array();

			if (!$arFields['PARENT_ID'] || $arFields['PARENT_ID'] == $arFields['ID'])
			{
				$fromTs = $arFields['DATE_FROM_TS_UTC'];
				$toTs = $arFields['DATE_TO_TS_UTC'];
				if ($arFields['DT_SKIP_TIME'] == "Y")
				{
					//$toTs += CCalendar::GetDayLen();
				}
				else
				{
					$fromTs += date('Z', $arFields['DATE_FROM_TS_UTC']);
					$toTs += date('Z', $arFields['DATE_TO_TS_UTC']);
				}

				$arFields['LOCATION'] = CCalendar::SetLocation(
					$arFields['LOCATION']['OLD'],
					$arFields['LOCATION']['NEW'],
					array(
						// UTC timestamp + date('Z', $timestamp) /*offset of the server*/
						'dateFrom' => CCalendar::Date($fromTs, $arFields['DT_SKIP_TIME'] !== "Y"),
						'dateTo' => CCalendar::Date($toTs, $arFields['DT_SKIP_TIME'] !== "Y"),
						'parentParams' => $params,
						'name' => $arFields['NAME'],
						'persons' => count($attendees),
						'attendees' => $attendees,
						'bRecreateReserveMeetings' => $arFields['LOCATION']['RE_RESERVE'] !== 'N'
					)
				);
			}
			else
			{
				$arFields['LOCATION'] = CCalendar::GetTextLocation($arFields['LOCATION']['NEW']);
			}

			if (!isset($arFields['IS_MEETING']) &&
				isset($arFields['ATTENDEES']) && is_array($arFields['ATTENDEES']) && empty($arFields['ATTENDEES']))
			{
				$arFields['IS_MEETING'] = false;
			}

			if (is_array($arFields['MEETING']))
			{
				$arFields['~MEETING'] = $arFields['MEETING'];
				$arFields['MEETING']['REINVITE'] = false;
				$arFields['MEETING'] = serialize($arFields['MEETING']);
			}

			if ($arFields['IS_MEETING'])
			{
				$attendeesCodes = $arFields['ATTENDEES_CODES'];
				if (is_array($arFields['ATTENDEES_CODES']) && !empty($arFields['ATTENDEES_CODES']))
				{
					$arFields['ATTENDEES_CODES'] = implode(',', $arFields['ATTENDEES_CODES']);
				}

				if (!isset($arFields['MEETING_STATUS']) && $arFields['MEETING_HOST'] == $arFields['CREATED_BY'])
				{
					$arFields['MEETING_STATUS'] = 'H';
				}
			}

			if (is_array($arFields['RELATIONS']))
			{
				$arFields['~RELATIONS'] = $arFields['RELATIONS'];
				$arFields['RELATIONS'] = serialize($arFields['RELATIONS']);
			}

			$arReminders = array();
			if (is_array($arFields['REMIND']))
			{
				foreach ($arFields['REMIND'] as $remind)
				{
					if (is_array($remind) && isset($remind['type']) && in_array($remind['type'], array('min', 'hour', 'day')))
					{
						$arReminders[] = array('type' => $remind['type'], 'count' => floatVal($remind['count']));
					}
				}
			}
			elseif($currentEvent['REMIND'])
			{
				$arReminders = $currentEvent['REMIND'];
			}
			$arFields['REMIND'] = count($arReminders) > 0 ? serialize($arReminders) : '';

			$AllFields = self::GetFields();
			$dbFields = array();
			foreach($arFields as $field => $val)
			{
				if(isset($AllFields[$field]) && $field != "ID")
					$dbFields[$field] = $arFields[$field];
			}
			CTimeZone::Disable();

			if ($isNewEvent) // Add
			{
				$eventId = $DB->Add("b_calendar_event", $dbFields, array('DESCRIPTION', 'MEETING', 'EXDATE'));
			}
			else // Update
			{
				$eventId = $arFields['ID'];
				$strUpdate = $DB->PrepareUpdate("b_calendar_event", $dbFields);
				$strSql =
					"UPDATE b_calendar_event SET ".
						$strUpdate.
						" WHERE ID=".IntVal($eventId);

				$DB->QueryBind($strSql, array(
					'DESCRIPTION' => $arFields['DESCRIPTION'],
					'MEETING' => $arFields['MEETING'],
					'EXDATE' => $arFields['EXDATE']
				));
			}

			CTimeZone::Enable();

			if ($isNewEvent && !isset($dbFields['DAV_XML_ID']))
			{
				$strSql =
					"UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", array('DAV_XML_ID' => $eventId)).
						" WHERE ID=".IntVal($eventId);
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			// *** Check and update section links ***
			$sectionId = (is_array($arFields['SECTIONS']) && $arFields['SECTIONS'][0]) ? intVal($arFields['SECTIONS'][0]) : false;

			if ($sectionId)
			{
				if (!$isNewEvent)
				{
					$arAffectedSections[] = $currentEvent['SECT_ID'];
				}
				self::ConnectEventToSection($eventId, $sectionId);
			}
			else
			{
				// It's new event we have to find section where to put it automatically
				if ($isNewEvent)
				{
					if ($arFields['IS_MEETING'] && $arFields['PARENT_ID'] && $arFields['CAL_TYPE'] == 'user')
					{
						$sectionId = CCalendar::GetMeetingSection($arFields['OWNER_ID']);
					}
					else
					{
						$sectionId = CCalendarSect::GetLastUsedSection($arFields['CAL_TYPE'], $arFields['OWNER_ID'], $userId);
					}

					if ($sectionId)
					{
						$res = CCalendarSect::GetList(array('arFilter' => array('CAL_TYPE' => $arFields['CAL_TYPE'],'OWNER_ID' => $arFields['OWNER_ID'], 'ID'=> $sectionId)));
						if (!$res || !$res[0])
							$sectionId = false;
					}
					else
					{
						$sectionId = false;
					}

					if (!$sectionId)
					{
						$sectRes = CCalendarSect::GetSectionForOwner($arFields['CAL_TYPE'], $arFields['OWNER_ID'], true);
						$sectionId = $sectRes['sectionId'];
					}
					self::ConnectEventToSection($eventId, $sectionId);
				}
				else
				{
					// It's existing event, we take it's section to update modification lables (no db changes in b_calendar_event_sect)
					$sectionId = $currentEvent['SECT_ID'];
				}
			}
			$arAffectedSections[] = $sectionId;

			if (count($arAffectedSections) > 0)
				CCalendarSect::UpdateModificationLabel($arAffectedSections);

			if ($arFields['IS_MEETING'] || (!$isNewEvent && $currentEvent['IS_MEETING']))
			{
				if (!$arFields['PARENT_ID'])
				{
					$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=".intVal($eventId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
				}

				if (!$arFields['PARENT_ID'] || $arFields['PARENT_ID'] == $eventId)
				{
					self::CreateChildEvents($eventId, $arFields, $params);
				}

				if (!$arFields['PARENT_ID'])
				{
					$arFields['PARENT_ID'] = intVal($eventId);
				}
			}
			else
			{
				if (($isNewEvent && !$arFields['PARENT_ID']) || (!$isNewEvent && !$currentEvent['PARENT_ID']))
				{
					$DB->Query("UPDATE b_calendar_event SET ".$DB->PrepareUpdate("b_calendar_event", array("PARENT_ID" => $eventId))." WHERE ID=".intVal($eventId), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					if (!$arFields['PARENT_ID'])
						$arFields['PARENT_ID'] = intVal($eventId);
				}

				if (Loader::includeModule("pull"))
				{
					$curUserId = $userId;
					if ($arFields['PARENT_ID'] && $arFields['PARENT_ID'] !== $arFields['ID'])
						$curUserId = $arFields['OWNER_ID'];

					\Bitrix\Pull\Event::add($curUserId, Array(
						'module_id' => 'calendar',
						'command' => 'event_update',
						'params' => array(
							'EVENT' => CCalendarEvent::OnPullPrepareArFields($arFields),
							'ATTENDEES' => array(),
							'NEW' => $isNewEvent ? 'Y' : 'N'
						)
					));
				}
			}

			// Clean old reminders and add new reminders
			if ($arFields["CAL_TYPE"] != 'user' ||
				$arFields['OWNER_ID'] != $userId ||
				$eventId == $arFields['PARENT_ID'])
			{
				CCalendarReminder::UpdateReminders(
					array(
						'id' => $eventId,
						'reminders' => $arReminders,
						'arFields' => $arFields,
						'userId' => $userId,
						'path' => $path,
						'bNew' => $isNewEvent
					)
				);
			}

			// Update search index
			self::updateSearchIndex($eventId);

			// Send invitations and notifications
			if ($arFields['IS_MEETING'])
			{
				if ($params['saveAttendeesStatus'] && $sendEditNotification)
				{
					if ($arFields['PARENT_ID'] != $eventId && ($arFields['MEETING_STATUS'] == "Y" || $arFields['MEETING_STATUS'] == "Q"))
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$arFields['OWNER_ID']);
						$fromTo = CCalendarEvent::GetEventFromToForUser($arFields, $arFields['OWNER_ID']);
						CCalendarNotify::Send(array(
							'mode' => 'change_notify',
							'name' => $arFields['NAME'],
							"from" => $fromTo['DATE_FROM'],
							"to" => $fromTo['DATE_TO'],
							"location" => CCalendar::GetTextLocation($arFields["LOCATION"]),
							"guestId" => $arFields['OWNER_ID'],
							"eventId" => $arFields['PARENT_ID'],
							"userId" => $userId,
							"fields" => $arFields
						));
					}
				}
				elseif ($sendInvitations && $arFields['PARENT_ID'] != $eventId && $arFields['MEETING_STATUS'] == 'Q')
				{
					$CACHE_MANAGER->ClearByTag('calendar_user_'.$arFields['OWNER_ID']);
					$fromTo = CCalendarEvent::GetEventFromToForUser($arFields, $arFields['OWNER_ID']);
					CCalendarNotify::Send(array(
						"mode" => 'invite',
						"name" => $arFields['NAME'],
						"from" => $fromTo['DATE_FROM'],
						"to" => $fromTo['DATE_TO'],
						"location" => CCalendar::GetTextLocation($arFields["LOCATION"]),
						"guestId" => $arFields['OWNER_ID'],
						"eventId" => $arFields['PARENT_ID'],
						"userId" => $userId,
						"fields" => $arFields
					));
				}
				elseif ($sendEditNotification)
				{
					if ($arFields['PARENT_ID'] != $eventId && $arFields['MEETING_STATUS'] == "Y" && $significantChanges)
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$arFields['OWNER_ID']);
						$fromTo = CCalendarEvent::GetEventFromToForUser($arFields, $arFields['OWNER_ID']);
						CCalendarNotify::Send(array(
							'mode' => 'change_notify',
							'name' => $arFields['NAME'],
							"from" => $fromTo['DATE_FROM'],
							"to" => $fromTo['DATE_TO'],
							"location" => CCalendar::GetTextLocation($arFields["LOCATION"]),
							"guestId" => $arFields['OWNER_ID'],
							"eventId" => $arFields['PARENT_ID'],
							"userId" => $userId,
							"fields" => $arFields
						));
					}
				}
			}

			if ($arFields['IS_MEETING'] && !empty($arFields['ATTENDEES_CODES']) && $arFields['PARENT_ID'] == $eventId)
			{
				CCalendarLiveFeed::OnEditCalendarEventEntry(array(
					'eventId' => $eventId,
					'arFields' => $arFields,
					'attendeesCodes' => $attendeesCodes
				));
			}

			CCalendar::ClearCache('event_list');

			$result = $eventId;
		}

		return $result;
	}

	public static function GetById($ID, $checkPermissions = true)
	{
		if ($ID > 0)
		{
			$Event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $ID,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => $checkPermissions,
					'checkPermissions' => $checkPermissions,
					'setDefaultLimit' => false
				)
			);
			if ($Event && is_array($Event[0]))
				return $Event[0];
		}
		return false;
	}

	public static function GetList($params = array())
	{
		global $DB, $USER_FIELD_MANAGER;
		$getUF = $params['getUserfields'] !== false;
		$checkPermissions = $params['checkPermissions'] !== false;
		$bCache = CCalendar::CacheTime() > 0;
		$params['setDefaultLimit'] = $params['setDefaultLimit'] === true;
		$userId = isset($params['userId']) ? intVal($params['userId']) : CCalendar::GetCurUserId();
		$fetchSection = $params['fetchSection'];
		$attendees = array();
		$result = null;

		CTimeZone::Disable();
		if($bCache)
		{
			$cache = new CPHPCache;
			$cacheId = 'event_list_'.md5(serialize($params));
			if ($checkPermissions)
				$cacheId .= 'chper'.CCalendar::GetCurUserId().'|';
			if (CCalendar::IsSocNet() && CCalendar::IsSocnetAdmin())
				$cacheId .= 'socnetAdmin|';
			$cacheId .= CCalendar::GetOffset();

			$cachePath = CCalendar::CachePath().'event_list';

			if ($cache->InitCache(CCalendar::CacheTime(), $cacheId, $cachePath))
			{
				$res = self::PrepareFromCache($cache->GetVars());
				$result = $res["result"];
				$attendees = $res["attendees"];
			}
		}

		if (!$bCache || !isset($result))
		{
			$arFilter = $params['arFilter'];
			if ($getUF)
			{
				$obUserFieldsSql = new CUserTypeSQL();
				$obUserFieldsSql->SetEntity("CALENDAR_EVENT", "CE.ID");
				$obUserFieldsSql->SetSelect(array("UF_*"));
				$obUserFieldsSql->SetFilter($arFilter);
			}

			$params['fetchAttendees'] = $params['fetchAttendees'] !== false;

			if ($params['setDefaultLimit'] !== false) // Deprecated
			{
				if (!isset($arFilter["FROM_LIMIT"])) // default 3 month back
					$arFilter["FROM_LIMIT"] = CCalendar::Date(time() - 31 * 3 * 24 * 3600, false);

				if (!isset($arFilter["TO_LIMIT"])) // default one year into the future
					$arFilter["TO_LIMIT"] = CCalendar::Date(time() + 365 * 24 * 3600, false);
			}

			// Array('ID' => 'asc')
			$arOrder = isset($params['arOrder']) ? $params['arOrder'] : Array();
			$arFields = self::GetFields();

			if ($arFilter["DELETED"] === false)
				unset($arFilter["DELETED"]);
			elseif (!isset($arFilter["DELETED"]))
				$arFilter["DELETED"] = "N";

			$join = '';

			$arSqlSearch = array();
			if(is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for($i = 0, $l = count($filter_keys); $i<$l; $i++)
				{
					$n = strtoupper($filter_keys[$i]);
					$val = $arFilter[$filter_keys[$i]];
					if(is_string($val) && strlen($val) <=0 || strval($val) == "NOT_REF")
						continue;

					if($n == 'FROM_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
							$arSqlSearch[] = "CE.DATE_TO_TS_UTC>=".$ts;
					}
					elseif($n == 'TO_LIMIT')
					{
						$ts = CCalendar::Timestamp($val, false);
						if ($ts > 0)
							$arSqlSearch[] = "CE.DATE_FROM_TS_UTC<=".($ts + 86399);
					}
					elseif($n == 'ID')
					{
						if(is_array($val))
						{
							$val = array_map(intval, $val);
							$arSqlSearch[] = 'CE.ID IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intVal($val) > 0)
						{
							$arSqlSearch[] = "CE.ID=".intVal($val);
						}
					}
					elseif($n == '>ID' && intVal($val) > 0)
					{
						$arSqlSearch[] = "CE.ID > ".intVal($val);
					}
					elseif($n == 'OWNER_ID')
					{
						if(is_array($val))
						{
							$val = array_map(intval, $val);
							$arSqlSearch[] = 'CE.OWNER_ID IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intVal($val) > 0)
						{
							$arSqlSearch[] = "CE.OWNER_ID=".intVal($val);
						}
					}
					elseif($n == 'MEETING_HOST')
					{
						if(is_array($val))
						{
							$val = array_map(intval, $val);
							$arSqlSearch[] = 'CE.MEETING_HOST IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intVal($val) > 0)
						{
							$arSqlSearch[] = "CE.MEETING_HOST=".intVal($val);
						}
					}
					elseif($n == 'NAME')
					{
						$arSqlSearch[] = "CE.NAME='".CDatabase::ForSql($val)."'";
					}
					elseif($n == 'CREATED_BY')
					{
						if(is_array($val))
						{
							$val = array_map(intval, $val);
							$arSqlSearch[] = 'CE.CREATED_BY IN (\''.implode('\',\'', $val).'\')';
						}
						else if (intVal($val) > 0)
						{
							$arSqlSearch[] = "CE.CREATED_BY=".intVal($val);
						}
					}
					elseif($n == 'SECTION')
					{
						if (!is_array($val))
							$val = array($val);

						$q = "";
						if (is_array($val))
						{
							$sval = '';
							foreach($val as $sectid)
								if (intVal($sectid) > 0)
									$sval .= intVal($sectid).',';
							$sval = trim($sval, ' ,');
							if ($sval != '')
								$q = 'CES.SECT_ID in ('.$sval.')';
						}

						if ($q != "")
							$arSqlSearch[] = $q;
					}
					elseif($n == 'ACTIVE_SECTION' && $val == "Y")
					{
						$arSqlSearch[] = "CS.ACTIVE='Y'";
						$join .= 'LEFT JOIN b_calendar_section CS ON (CES.SECT_ID=CS.ID)';
					}
					elseif($n == 'DAV_XML_ID' && is_array($val))
					{
						$val = array_map(array($DB, 'ForSQL'), $val);
						$arSqlSearch[] = 'CE.DAV_XML_ID IN (\''.implode('\',\'', $val).'\')';
					}
					elseif($n == 'DAV_XML_ID' && is_string($val))
					{
						$arSqlSearch[] = "CE.DAV_XML_ID='".CDatabase::ForSql($val)."'";
					}
					elseif($n == '*SEARCHABLE_CONTENT') // Full text index match
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->match('SEARCHABLE_CONTENT', $val, true);
					}
					elseif($n == '*%SEARCHABLE_CONTENT') // partial full text match based on LIKE
					{
						$sqlWhere = new CSQLWhere();
						$arSqlSearch[] = $sqlWhere->matchLike('SEARCHABLE_CONTENT', $val);
					}
					elseif(isset($arFields[$n]))
					{
						$arSqlSearch[] = GetFilterQuery($arFields[$n]["FIELD_NAME"], $val, 'N');
					}
				}
			}

			if ($getUF)
			{
				$r = $obUserFieldsSql->GetFilter();
				if (strlen($r) > 0)
				{
					$arSqlSearch[] = "(".$r.")";
				}
			}

			$selectList = "";
			foreach($arFields as $field)
			{
				$selectList .= $field['FIELD_NAME'].", ";
			}

			if ($fetchSection && $arFilter['ACTIVE_SECTION'] == 'Y')
			{
				$selectList .= "CS.CAL_DAV_CAL as SECTION_DAV_XML_ID,";
			}

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
			$strOrderBy = '';
			foreach($arOrder as $by=>$order)
			{
				if(isset($arFields[strtoupper($by)]))
				{
					$strOrderBy .= $arFields[strtoupper($by)]["FIELD_NAME"].' '.(strtolower($order)=='desc'?'desc'.(strtoupper($DB->type) == "ORACLE"?" NULLS LAST":""):'asc'.(strtoupper($DB->type)=="ORACLE"?" NULLS FIRST":"")).',';
				}
			}

			if(strlen($strOrderBy) > 0)
			{
				$strOrderBy = "ORDER BY ".rtrim($strOrderBy, ",");
			}

			$strLimit = '';
			if (isset($params['limit']) && intVal($params['limit']) > 0)
			{
				$strLimit = 'LIMIT '.intVal($params['limit']);
			}

			$strSql = "
				SELECT ".
					$selectList.
					"CES.SECT_ID, CES.REL
					".($getUF ? $obUserFieldsSql->GetSelect() : '')."
				FROM
					b_calendar_event CE
				LEFT JOIN b_calendar_event_sect CES ON (CE.ID=CES.EVENT_ID)
				".$join."
				".($getUF ? $obUserFieldsSql->GetJoin("CE.ID") : '')."
				WHERE
					$strSqlSearch
				$strOrderBy
				$strLimit";

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($getUF)
			{
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT"));
			}

			$result = Array();
			$arMeetingIds = array();
			$arEvents = array();
			$bIntranet = CCalendar::IsIntranetEnabled();

			$defaultMeetingSection = false;
			while($event = $res->Fetch())
			{
				$event['IS_MEETING'] = intVal($event['IS_MEETING']) > 0;

				if ($event['IS_MEETING'] && $event['CAL_TYPE'] == 'user' && $event['OWNER_ID'] == $userId && !$event['SECT_ID'])
				{
					if (!$defaultMeetingSection)
					{
						$defaultMeetingSection = CCalendar::GetMeetingSection($userId);
						if (!$defaultMeetingSection || !CCalendarSect::GetById($defaultMeetingSection, false))
						{
							$sectRes = CCalendarSect::GetSectionForOwner($event['CAL_TYPE'], $userId);
							$defaultMeetingSection = $sectRes['sectionId'];
						}
					}
					self::ConnectEventToSection($event['ID'], $defaultMeetingSection);
					$event['SECT_ID'] = $defaultMeetingSection;
				}

				$arEvents[] = $event;
				if ($bIntranet && $event['IS_MEETING'])
				{
					$arMeetingIds[] = $event['PARENT_ID'];
				}
			}

			if ($params['fetchAttendees'] && count($arMeetingIds) > 0)
				$attendees = self::GetAttendees($arMeetingIds);

			foreach($arEvents as $event)
			{
				$event["ACCESSIBILITY"] = trim($event["ACCESSIBILITY"]);
				if ($bIntranet && isset($event['MEETING']) && $event['MEETING'] != "")
				{
					$event['MEETING'] = unserialize($event['MEETING']);
					if (!is_array($event['MEETING']))
						$event['MEETING'] = array();
				}

				if (isset($event['RELATIONS']) && $event['RELATIONS'] != "")
				{
					$event['RELATIONS'] = unserialize($event['RELATIONS']);
					if (!is_array($event['RELATIONS']))
						$event['RELATIONS'] = array();
				}

				if (isset($event['REMIND']) && $event['REMIND'] != "")
				{
					$event['REMIND'] = unserialize($event['REMIND']);
					if (!is_array($event['REMIND']))
						$event['REMIND'] = array();
				}

				if ($bIntranet && $event['IS_MEETING'] && isset($attendees[$event['PARENT_ID']]) && count($attendees[$event['PARENT_ID']]) > 0)
				{
					$event['~ATTENDEES'] = $attendees[$event['PARENT_ID']];
				}
				$checkPermissionsForEvent = $userId != $event['CREATED_BY']; // It's creator

				// It's event in user's calendar
				if ($checkPermissionsForEvent && $event['CAL_TYPE'] == 'user' && $userId == $event['OWNER_ID'])
					$checkPermissionsForEvent = false;
				if ($checkPermissionsForEvent && $event['IS_MEETING'] && $event['USER_MEETING'] && $event['USER_MEETING']['ATTENDEE_ID'] == $userId)
					$checkPermissionsForEvent = false;

				if ($checkPermissionsForEvent && $event['IS_MEETING'] && is_array($event['~ATTENDEES']))
				{
					foreach($event['~ATTENDEES'] as $att)
					{
						if ($att['USER_ID'] == $userId)
						{
							$checkPermissionsForEvent = false;
							break;
						}
					}
				}

				if ($checkPermissions && $checkPermissionsForEvent)
					$event = self::ApplyAccessRestrictions($event, $userId);

				if ($event === false)
					continue;

				$event = self::PreHandleEvent($event);

				if ($params['parseRecursion'] && self::CheckRecurcion($event))
				{
					self::ParseRecursion($result, $event, array(
						'fromLimit' => $arFilter["FROM_LIMIT"],
						'toLimit' => $arFilter["TO_LIMIT"],
						'loadLimit' => $params["limit"],
						'instanceCount' => isset($params['maxInstanceCount']) ? $params['maxInstanceCount'] : false,
						'preciseLimits' => isset($params['preciseLimits']) ? $params['preciseLimits'] : false
					));
				}
				else
				{
					self::HandleEvent($result, $event);
				}
			}

			if ($bCache)
			{
				$cache->StartDataCache(CCalendar::CacheTime(), $cacheId, $cachePath);
				$cache->EndDataCache(self::PrepareForCache(array(
					"result" => $result,
					"attendees" => $attendees
				)));
			}
		}

		CTimeZone::Enable();

		if (!is_array(self::$lastAttendeesList))
		{
			self::$lastAttendeesList = $attendees;
		}
		elseif(is_array($attendees))
		{
			foreach($attendees as $eventId => $att)
				self::$lastAttendeesList[$eventId] = $att;
		}

		return $result;
	}

	private static function PrepareFromCache($data = array())
	{
		if (is_array($data['result']))
		{
			foreach ($data['result'] as $i => $event)
			{
				if ($event['IS_MEETING'] && is_array($event['~ATTENDEES']))
				{
					foreach ($event['~ATTENDEES'] as $j => $attender)
					{
						$tmp = $attender['STATUS'];
						$data['result'][$i]['~ATTENDEES'][$j] = $data['users'][$attender['USER_ID']];
						$data['result'][$i]['~ATTENDEES'][$j]['STATUS'] = $attender['STATUS'];
					}
				}
			}
		}

		if (is_array($data['attendees']))
		{
			foreach($data['attendees'] as $eventId => $att)
			{
				foreach ($att as $j => $at)
				{
					$data['attendees'][$eventId][$j] = $data['users'][$at['USER_ID']];
					$data['attendees'][$eventId][$j]['STATUS'] = $at['STATUS'];
				}
			}
		}
		unset($data['users']);
		return $data;
	}

	private static function PrepareForCache($data = array())
	{
		$data['users'] = array();

		if (is_array($data['result']))
		{
			foreach ($data['result'] as $i => $event)
			{
				if ($event['IS_MEETING'] && is_array($event['~ATTENDEES']))
				{
					foreach ($event['~ATTENDEES'] as $j => $user)
					{
						$data['result'][$i]['~ATTENDEES'][$j] = array(
							'USER_ID' => $user['USER_ID'],
							'STATUS' => $user['STATUS']
						);

						unset($user['STATUS']);
						//if (!array_key_exists($user['USER_ID'], $data['users']))
						$data['users'][$user['USER_ID']] = $user;
					}
				}
			}
		}

		if (is_array($data['attendees']))
		{
			foreach($data['attendees'] as $eventId => $att)
			{
				foreach ($att as $j => $user)
				{
					$data['attendees'][$eventId][$j] = array(
						'USER_ID' => $user['USER_ID'],
						'STATUS' => $user['STATUS']
					);

					unset($user['STATUS']);
					//if (!array_key_exists($user['USER_ID'], $data['users']))
					$data['users'][$user['USER_ID']] = $user;
				}
			}
		}

		return $data;
	}

	private static function GetFields()
	{
		global $DB;
		if (!count(self::$fields))
		{
			CTimeZone::Disable();
			self::$fields = array(
				"ID" => Array("FIELD_NAME" => "CE.ID", "FIELD_TYPE" => "int"),
				"PARENT_ID" => Array("FIELD_NAME" => "CE.PARENT_ID", "FIELD_TYPE" => "int"),
				"ACTIVE" => Array("FIELD_NAME" => "CE.ACTIVE", "FIELD_TYPE" => "string"),
				"DELETED" => Array("FIELD_NAME" => "CE.DELETED", "FIELD_TYPE" => "string"),
				"CAL_TYPE" => Array("FIELD_NAME" => "CE.CAL_TYPE", "FIELD_TYPE" => "string"),
				"OWNER_ID" => Array("FIELD_NAME" => "CE.OWNER_ID", "FIELD_TYPE" => "int"),
				"EVENT_TYPE" => Array("FIELD_NAME" => "CE.EVENT_TYPE", "FIELD_TYPE" => "string"),
				"CREATED_BY" => Array("FIELD_NAME" => "CE.CREATED_BY", "FIELD_TYPE" => "int"),
				"NAME" => Array("FIELD_NAME" => "CE.NAME", "FIELD_TYPE" => "string"),
				"DATE_FROM" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_FROM").' as DATE_FROM', "FIELD_TYPE" => "date"),
				"DATE_TO" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_TO").' as DATE_TO', "FIELD_TYPE" => "date"),
				"TZ_FROM" => Array("FIELD_NAME" => "CE.TZ_FROM", "FIELD_TYPE" => "string"),
				"TZ_TO" => Array("FIELD_NAME" => "CE.TZ_TO", "FIELD_TYPE" => "string"),
				"TZ_OFFSET_FROM" => Array("FIELD_NAME" => "CE.TZ_OFFSET_FROM", "FIELD_TYPE" => "int"),
				"TZ_OFFSET_TO" => Array("FIELD_NAME" => "CE.TZ_OFFSET_TO", "FIELD_TYPE" => "int"),
				"DATE_FROM_TS_UTC" => Array("FIELD_NAME" => "CE.DATE_FROM_TS_UTC", "FIELD_TYPE" => "int"),
				"DATE_TO_TS_UTC" => Array("FIELD_NAME" => "CE.DATE_TO_TS_UTC", "FIELD_TYPE" => "int"),

				"TIMESTAMP_X" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.TIMESTAMP_X").' as TIMESTAMP_X', "FIELD_TYPE" => "date"),
				"DATE_CREATE" => Array("FIELD_NAME" => $DB->DateToCharFunction("CE.DATE_CREATE").' as DATE_CREATE', "FIELD_TYPE" => "date"),
				"DESCRIPTION" => Array("FIELD_NAME" => "CE.DESCRIPTION", "FIELD_TYPE" => "string"),
				"DT_SKIP_TIME" => Array("FIELD_NAME" => "CE.DT_SKIP_TIME", "FIELD_TYPE" => "string"),
				"DT_LENGTH" => Array("FIELD_NAME" => "CE.DT_LENGTH", "FIELD_TYPE" => "int"),
				"PRIVATE_EVENT" => Array("FIELD_NAME" => "CE.PRIVATE_EVENT", "FIELD_TYPE" => "string"),
				"ACCESSIBILITY" => Array("FIELD_NAME" => "CE.ACCESSIBILITY", "FIELD_TYPE" => "string"),
				"IMPORTANCE" => Array("FIELD_NAME" => "CE.IMPORTANCE", "FIELD_TYPE" => "string"),
				"IS_MEETING" => Array("FIELD_NAME" => "CE.IS_MEETING", "FIELD_TYPE" => "string"),
				"MEETING_HOST" => Array("FIELD_NAME" => "CE.MEETING_HOST", "FIELD_TYPE" => "int"),
				"MEETING_STATUS" => Array("FIELD_NAME" => "CE.MEETING_STATUS", "FIELD_TYPE" => "string"),
				"MEETING" => Array("FIELD_NAME" => "CE.MEETING", "FIELD_TYPE" => "string"),
				"LOCATION" => Array("FIELD_NAME" => "CE.LOCATION", "FIELD_TYPE" => "string"),
				"REMIND" => Array("FIELD_NAME" => "CE.REMIND", "FIELD_TYPE" => "string"),
				"COLOR" => Array("FIELD_NAME" => "CE.COLOR", "FIELD_TYPE" => "string"),
				"TEXT_COLOR" => Array("FIELD_NAME" => "CE.TEXT_COLOR", "FIELD_TYPE" => "string"),
				"RRULE" => Array("FIELD_NAME" => "CE.RRULE", "FIELD_TYPE" => "string"),
				"EXDATE" => Array("FIELD_NAME" => "CE.EXDATE", "FIELD_TYPE" => "string"),
				"ATTENDEES_CODES" => Array("FIELD_NAME" => "CE.ATTENDEES_CODES", "FIELD_TYPE" => "string"),
				"DAV_XML_ID" => Array("FIELD_NAME" => "CE.DAV_XML_ID", "FIELD_TYPE" => "string"), //
				"DAV_EXCH_LABEL" => Array("FIELD_NAME" => "CE.DAV_EXCH_LABEL", "FIELD_TYPE" => "string"), // Exchange sync label
				"CAL_DAV_LABEL" => Array("FIELD_NAME" => "CE.CAL_DAV_LABEL", "FIELD_TYPE" => "string"), // CalDAV sync label
				"VERSION" => Array("FIELD_NAME" => "CE.VERSION", "FIELD_TYPE" => "string"), // Version used for outlook sync
				"RECURRENCE_ID" => Array("FIELD_NAME" => "CE.RECURRENCE_ID", "FIELD_TYPE" => "int"),
				"RELATIONS" => Array("FIELD_NAME" => "CE.RELATIONS", "FIELD_TYPE" => "int"),
				"SEARCHABLE_CONTENT" => Array("FIELD_NAME" => "CE.SEARCHABLE_CONTENT", "FIELD_TYPE" => "string")
			);
			CTimeZone::Enable();
		}
		return self::$fields;
	}

	public static function ConnectEventToSection($eventId, $sectionId)
	{
		global $DB;
		$DB->Query(
			"DELETE FROM b_calendar_event_sect WHERE EVENT_ID=".intVal($eventId),
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$DB->Query(
			"INSERT INTO b_calendar_event_sect(EVENT_ID, SECT_ID) ".
			"SELECT ".intVal($eventId).", ID ".
			"FROM b_calendar_section ".
			"WHERE ID=".intVal($sectionId),
			false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	public static function GetAttendees($arEventIds = array())
	{
		global $DB;

		$arAttendees = array();

		if (CCalendar::IsSocNet())
		{
			if(is_array($arEventIds))
			{
				$arEventIds = array_unique($arEventIds);
			}
			else
			{
				$arEventIds = array($arEventIds);
			}

			$strMeetIds = "";
			foreach($arEventIds as $id)
				if(intVal($id) > 0)
					$strMeetIds .= ','.intVal($id);
			$strMeetIds = trim($strMeetIds, ', ');

			if($strMeetIds != '')
			{
				$strSql = "
				SELECT
					CE.OWNER_ID AS USER_ID,
					CE.ID, CE.PARENT_ID, CE.MEETING_STATUS, CE.MEETING_HOST,
					U.LOGIN, U.NAME, U.LAST_NAME, U.SECOND_NAME, U.EMAIL, U.PERSONAL_PHOTO, U.WORK_POSITION,
					BUF.UF_DEPARTMENT
				FROM
					b_calendar_event CE
					LEFT JOIN b_user U ON (U.ID=CE.OWNER_ID)
					LEFT JOIN b_uts_user BUF ON (BUF.VALUE_ID = CE.OWNER_ID)
				WHERE
					U.ACTIVE = 'Y' AND
					CE.ACTIVE = 'Y' AND
					CE.CAL_TYPE = 'user' AND
					CE.DELETED = 'N' AND
					CE.PARENT_ID in (".$strMeetIds.")";

				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($entry = $res->Fetch())
				{
					$parentId = $entry['PARENT_ID'];
					$attendeeId = $entry['USER_ID'];
					if(!isset($arAttendees[$parentId]))
						$arAttendees[$parentId] = array();
					$entry["STATUS"] = trim($entry["MEETING_STATUS"]);
					if ($parentId == $entry['ID'] || $entry['USER_ID'] == $entry['MEETING_HOST'])
					{
						$entry["STATUS"] = "H";
					}

					CCalendar::SetUserDepartment($attendeeId, (empty($entry['UF_DEPARTMENT']) ? array() : unserialize($entry['UF_DEPARTMENT'])));
					$entry['DISPLAY_NAME'] = CCalendar::GetUserName($entry);
					$entry['URL'] = CCalendar::GetUserUrl($attendeeId);
					$entry['AVATAR'] = CCalendar::GetUserAvatarSrc($entry);
					$entry['EVENT_ID'] = $entry['ID'];
					unset($entry['ID'], $entry['PARENT_ID'], $entry['MEETING_STATUS'], $entry['UF_DEPARTMENT']);

					$arAttendees[$parentId][] = $entry;
				}
			}
		}

		return $arAttendees;
	}

	public static function ApplyAccessRestrictions($event, $userId = false)
	{
		$sectId = $event['SECT_ID'];
		if (!$event['ACCESSIBILITY'])
			$event['ACCESSIBILITY'] = 'busy';

		$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] == 'user';
		$bManager = false;
		$bAttendee = false;

		if (isset($event['~ATTENDEES']))
		{
			foreach($event['~ATTENDEES'] as $user)
			{
				if ($user['USER_ID'] == $userId)
					$bAttendee = true;
			}
		}

		if(!$userId)
			$userId = CCalendar::GetUserId();

		$settings = CCalendar::GetSettings(array('request' => false));
		if (Loader::includeModule('intranet') && $event['CAL_TYPE'] == 'user' && $settings['dep_manager_sub'])
			$bManager = in_array($userId, CCalendar::GetUserManagers($event['OWNER_ID'], true));

		if ($event['CAL_TYPE'] == 'user' && $event['IS_MEETING'] && $event['OWNER_ID'] != $userId)
		{
			if ($bAttendee)
			{
				$sectId = CCalendar::GetMeetingSection($userId);
			}
			elseif (isset($event['USER_MEETING']['ATTENDEE_ID']) && $event['USER_MEETING']['ATTENDEE_ID'] !== $userId)
			{
				$sectId = CCalendar::GetMeetingSection($event['USER_MEETING']['ATTENDEE_ID']);
				$event['SECT_ID'] = $sectId;
				$event['OWNER_ID'] = $event['USER_MEETING']['ATTENDEE_ID'];
			}
		}

		if ($private || (!CCalendarSect::CanDo('calendar_view_full', $sectId, $userId) && !$bManager && !$bAttendee))
		{
			if ($private)
			{
				$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.strtoupper($event['ACCESSIBILITY'])).']';
				if (!$bManager && !CCalendarSect::CanDo('calendar_view_time', $sectId, $userId))
					return false;
			}
			else
			{
				if (!CCalendarSect::CanDo('calendar_view_title', $sectId, $userId))
				{
					if (CCalendarSect::CanDo('calendar_view_time', $sectId, $userId))
						$event['NAME'] = '['.GetMessage('EC_ACCESSIBILITY_'.strtoupper($event['ACCESSIBILITY'])).']';
					else
						return false;
				}
				else
				{
					$event['NAME'] = $event['NAME'].' ['.GetMessage('EC_ACCESSIBILITY_'.strtoupper($event['ACCESSIBILITY'])).']';
				}
			}
			$event['~IS_MEETING'] = $event['IS_MEETING'];

			// Clear information about
			unset($event['DESCRIPTION'], $event['IS_MEETING'],$event['MEETING_HOST'],$event['MEETING'],$event['LOCATION'],$event['REMIND'],$event['USER_MEETING'],$event['~ATTENDEES'],$event['ATTENDEES_CODES']);

			foreach($event as $k => $value)
			{
				if (substr($k, 0, 3) == 'UF_')
					unset($event[$k]);
			}
		}

		return $event;
	}

	private static function PreHandleEvent($item)
	{
		$item['LOCATION'] = trim($item['LOCATION']);

		if ($item['IS_MEETING'] && $item['MEETING'] != "" && !is_array($item['MEETING']))
		{
			$item['MEETING'] = unserialize($item['MEETING']);
			if (!is_array($item['MEETING']))
				$item['MEETING'] = array();
		}

		if (self::CheckRecurcion($item))
		{
			$item['~RRULE_DESCRIPTION'] = CCalendarEvent::GetRRULEDescription($item, false);
			$tsFrom = CCalendar::Timestamp($item['DATE_FROM']);
			$tsTo = CCalendar::Timestamp($item['DATE_TO']);
			if (($tsTo - $tsFrom) > $item['DT_LENGTH'] + CCalendar::DAY_LENGTH)
			{
				$toTS = $tsFrom + $item['DT_LENGTH'];
				if ($item['DT_SKIP_TIME'] == 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}
				$item['DATE_TO'] = CCalendar::Date($toTS);
			}
		}

		if ($item['IS_MEETING'])
		{
			if ($item['ATTENDEES_CODES'] != '')
			{
				$item['ATTENDEES_CODES'] = explode(',', $item['ATTENDEES_CODES']);
			}

			if ($item['ID'] == $item['PARENT_ID'])
				$item['MEETING_STATUS'] = 'H';
		}

		if (!isset($item['~IS_MEETING']))
			$item['~IS_MEETING'] = $item['IS_MEETING'];

		$item['DT_SKIP_TIME'] = $item['DT_SKIP_TIME'] === 'Y' ? 'Y' : 'N';

		$item['ACCESSIBILITY'] = trim($item['ACCESSIBILITY']);
		$item['IMPORTANCE'] = trim($item['IMPORTANCE']);
		if ($item['IMPORTANCE'] == '')
			$item['IMPORTANCE'] = 'normal';
		$item['PRIVATE_EVENT'] = trim($item['PRIVATE_EVENT']);

		$curUserId = CCalendar::GetCurUserId();
		if ($curUserId)
		{
			if($item['PARENT_ID'])
			{
				$item['~DESCRIPTION'] = self::ParseText($item['DESCRIPTION'], $item['PARENT_ID'], $item['UF_WEBDAV_CAL_EVENT']);
			}
			else
			{
				$item['~DESCRIPTION'] = self::ParseText($item['DESCRIPTION'], $item['ID'], $item['UF_WEBDAV_CAL_EVENT']);
			}
		}

		if (isset($item['UF_CRM_CAL_EVENT']) && is_array($item['UF_CRM_CAL_EVENT']) && count($item['UF_CRM_CAL_EVENT']) == 0)
			$item['UF_CRM_CAL_EVENT'] = '';

		unset($item['SEARCHABLE_CONTENT']);
		return $item;
	}

	public static function CheckRecurcion($event)
	{
		return $event['RRULE'] != '';
	}

	public static function ParseText($text = "", $eventId = 0, $arUFWDValue = array())
	{
		if ($text != "")
		{
			if (!is_object(self::$TextParser))
			{
				self::$TextParser = new CTextParser();
				self::$TextParser->allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "N", "ALIGN" => "Y", "USER" => "Y");
			}

			self::$TextParser->allow["USERFIELDS"] = self::__GetUFForParseText($eventId, $arUFWDValue);
			$text = self::$TextParser->convertText($text);
		}
		return $text;
	}

	public static function __GetUFForParseText($eventId = 0, $arUFWDValue = array())
	{
		if (!isset(self::$eventUFDescriptions))
		{
			global $USER_FIELD_MANAGER;
			$USER_FIELDS = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $eventId, LANGUAGE_ID);
			$USER_FIELDS = array(
				'UF_WEBDAV_CAL_EVENT' => $USER_FIELDS['UF_WEBDAV_CAL_EVENT']
			);
			self::$eventUFDescriptions = $USER_FIELDS;
		}
		else
		{
			$USER_FIELDS = self::$eventUFDescriptions;
		}

		if (empty($arUFWDValue))
		{
			$arUFWDValue = $USER_FIELDS['UF_WEBDAV_CAL_EVENT']['VALUE'];
		}

		$USER_FIELDS['UF_WEBDAV_CAL_EVENT']['VALUE'] = $arUFWDValue;
		$USER_FIELDS['UF_WEBDAV_CAL_EVENT']['ENTITY_VALUE_ID'] = $eventId;

		return $USER_FIELDS;
	}

	public static function ParseRecursion(&$res, $event, $params = array())
	{
		$event['DT_LENGTH'] = intVal($event['DT_LENGTH']);// length in seconds
		$length = $event['DT_LENGTH'];

		$rrule = self::ParseRRULE($event['RRULE']);
		$exDate = self::GetExDate($event['EXDATE']);
		$tsFrom = CCalendar::Timestamp($event['DATE_FROM']);
		$tsTo = CCalendar::Timestamp($event['DATE_TO']);

		if (($tsTo - $tsFrom) > $event['DT_LENGTH'] + CCalendar::DAY_LENGTH)
		{
			$toTS = $tsFrom + $event['DT_LENGTH'];
			if ($event['DT_SKIP_TIME'] == 'Y')
			{
				$toTS -= CCalendar::GetDayLen();
			}
			$event['DATE_TO'] = CCalendar::Date($toTS);
		}

		$h24 = CCalendar::GetDayLen();
		$instanceCount = ($params['instanceCount'] && $params['instanceCount'] > 0) ? $params['instanceCount'] : false;
		$loadLimit = ($params['loadLimit'] && $params['loadLimit'] > 0) ? $params['loadLimit'] : false;

		$preciseLimits = $params['preciseLimits'];

		if ($length < 0) // Protection from infinite recursion
			$length = $h24;

		// Time boundaries
		if (isset($params['fromLimitTs']))
			$limitFromTS = intVal($params['fromLimitTs']);
		else if ($params['fromLimit'])
			$limitFromTS = CCalendar::Timestamp($params['fromLimit']);
		else
			$limitFromTS = CCalendar::Timestamp(CCalendar::GetMinDate());

		if (isset($params['toLimitTs']))
			$limitToTS = intVal($params['toLimitTs']);
		else if ($params['toLimit'])
			$limitToTS = CCalendar::Timestamp($params['toLimit']);
		else
			$limitToTS = CCalendar::Timestamp(CCalendar::GetMaxDate());

		$evFromTS = CCalendar::Timestamp($event['DATE_FROM']);

		$limitFromTS += $event['TZ_OFFSET_FROM'];
		$limitToTS += $event['TZ_OFFSET_TO'];
		$limitToTS += CCalendar::GetDayLen();
		$limitFromTSReal = $limitFromTS;

		if ($limitFromTS < $event['DATE_FROM_TS_UTC'])
			$limitFromTS = $event['DATE_FROM_TS_UTC'];
		if ($limitToTS > $event['DATE_TO_TS_UTC'])
			$limitToTS = $event['DATE_TO_TS_UTC'];

		$skipTime = $event['DT_SKIP_TIME'] === 'Y';
		$fromTS = $evFromTS;

		if ($skipTime)
		{
			$event['~DATE_FROM'] = CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
			$event['~DATE_TO'] = CCalendar::Date(CCalendar::Timestamp($event['DATE_TO']), false);
		}
		else
		{
			$event['~DATE_FROM'] = $event['DATE_FROM'];
			$event['~DATE_TO'] = $event['DATE_TO'];
		}

		$hour = date("H", $fromTS);
		$min = date("i", $fromTS);
		$sec = date("s", $fromTS);

		$orig_d = date("d", $fromTS);
		$orig_m = date("m", $fromTS);
		$orig_y = date("Y", $fromTS);

		$count = 0;
		$realCount = 0;
		$dispCount = 0;

		while(true)
		{
			$d = date("d", $fromTS);
			$m = date("m", $fromTS);
			$y = date("Y", $fromTS);
			$toTS = mktime($hour, $min, $sec + $length, $m, $d, $y);

			if (
				(!$fromTS || $fromTS < $evFromTS - CCalendar::GetDayLen()) // Emergensy exit (mantis: 56981)
				|| ($rrule['COUNT'] > 0 && $realCount >= $rrule['COUNT'])
				|| (!$rrule['COUNT'] && $fromTS >= $limitToTS)
				|| ($instanceCount && $dispCount >= $instanceCount)
				|| ($loadLimit && $dispCount >= $loadLimit)
			)
			{
				break;
			}

			// Common handling
			$event['DATE_FROM'] = CCalendar::Date($fromTS, !$skipTime, false);
			$event['RRULE'] = $rrule;
			$event['RINDEX'] = $realCount;
			$exclude = false;

			if (count($exDate) > 0)
			{
				$fromDate = CCalendar::Date($fromTS, false);
				$exclude = in_array($fromDate, $exDate);
			}

			if ($rrule['FREQ'] == 'WEEKLY')
			{
				$weekDay = CCalendar::WeekDayByInd(date("w", $fromTS));

				if ($rrule['BYDAY'][$weekDay])
				{
					if (($preciseLimits && $toTS >= $limitFromTSReal) || (!$preciseLimits && $toTS > $limitFromTS - $h24))
					{
						if ($event['DT_SKIP_TIME'] == 'Y')
						{
							$toTS -= CCalendar::GetDayLen();
						}
						$event['DATE_TO'] = CCalendar::Date($toTS, !$skipTime, false);

						if (!$exclude)
						{
							self::HandleEvent($res, $event);
							$dispCount++;
						}
					}
					$realCount++;
				}

				if (isset($weekDay) && $weekDay == 'SU')
					$delta = ($rrule['INTERVAL'] - 1) * 7 + 1;
				else
					$delta = 1;

				$fromTS = mktime($hour, $min, $sec, $m, $d + $delta, $y);
			}
			else // HOURLY, DAILY, MONTHLY, YEARLY
			{
				if ($event['DT_SKIP_TIME'] == 'Y')
				{
					$toTS -= CCalendar::GetDayLen();
				}

				if (($preciseLimits && $toTS >= $limitFromTSReal) ||
					(!$preciseLimits && $toTS > $limitFromTS - $h24))
				{
					$event['DATE_TO'] = CCalendar::Date($toTS, !$skipTime, false);
					if (!$exclude)
					{
						self::HandleEvent($res, $event);
						$dispCount++;
					}
				}
				$realCount++;
				switch ($rrule['FREQ'])
				{
					case 'DAILY':
						$fromTS = mktime($hour, $min, $sec, $m, $d + $rrule['INTERVAL'], $y);
						break;
					case 'MONTHLY':
						$durOffset = $realCount * $rrule['INTERVAL'];

						$day = $orig_d;
						$month = $orig_m + $durOffset;
						$year = $orig_y;

						if ($month > 12)
						{
							$delta_y = floor($month / 12);
							$delta_m = $month - $delta_y * 12;

							$month = $delta_m;
							$year = $orig_y + $delta_y;
						}

						// 1. Check only for 29-31 dates. 2.We are out of range in this month
						if ($orig_d > 28 && $orig_d > date("t", mktime($hour, $min, $sec, $month, 1, $year)))
						{
							$month++;
							$day = 1;
						}

						$fromTS = mktime($hour, $min, $sec, $month, $day, $year);
						break;
					case 'YEARLY':
						$fromTS = mktime($hour, $min, $sec, $orig_m, $orig_d, $y + $rrule['INTERVAL']);
						break;
				}
			}
			$count++;
		}
	}

	public static function ParseRRULE($rule = '')
	{
		$res = array();
		if (!$rule || $rule === '')
			return $res;
		if (is_array($rule))
			return isset($rule['FREQ']) ? $rule : $res;

		$arRule = explode(";", $rule);
		if (!is_array($arRule))
			return $res;
		foreach($arRule as $par)
		{
			$arPar = explode("=", $par);
			if ($arPar[0])
			{
				switch($arPar[0])
				{
					case 'FREQ':
						if (in_array($arPar[1], array('HOURLY', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY')))
							$res['FREQ'] = $arPar[1];
						break;
					case 'COUNT':
					case 'INTERVAL':
						if (intVal($arPar[1]) > 0)
							$res[$arPar[0]] = intVal($arPar[1]);
						break;
					case 'UNTIL':
						$res['UNTIL'] = CCalendar::Timestamp($arPar[1]) ? $arPar[1] : CCalendar::Date(intVal($arPar[1]), false, false);
						break;
					case 'BYDAY':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
						{
							$matches = array();
							if (preg_match('/((\-|\+)?\d+)?(MO|TU|WE|TH|FR|SA|SU)/', $day, $matches))
								$res[$arPar[0]][$matches[3]] = $matches[1] == '' ? $matches[3] : $matches[1];
						}
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYMONTHDAY':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 31)
								$res[$arPar[0]][intVal($day)] = intVal($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYYEARDAY':
					case 'BYSETPOS':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 366)
								$res[$arPar[0]][intVal($day)] = intVal($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYWEEKNO':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $day)
							if (abs($day) > 0 && abs($day) <= 53)
								$res[$arPar[0]][intVal($day)] = intVal($day);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
					case 'BYMONTH':
						$res[$arPar[0]] = array();
						foreach(explode(',', $arPar[1]) as $m)
							if ($m > 0 && $m <= 12)
								$res[$arPar[0]][intVal($m)] = intVal($m);
						if (count($res[$arPar[0]]) == 0)
							unset($res[$arPar[0]]);
						break;
				}
			}
		}

		if ($res['FREQ'] == 'WEEKLY' && (!isset($res['BYDAY']) || !is_array($res['BYDAY']) || count($res['BYDAY']) == 0))
			$res['BYDAY'] = array('MO' => 'MO');

		if ($res['FREQ'] != 'WEEKLY' && isset($res['BYDAY']))
			unset($res['BYDAY']);

		$res['INTERVAL'] = intVal($res['INTERVAL']);
		if ($res['INTERVAL'] <= 1)
			$res['INTERVAL'] = 1;

		$res['~UNTIL'] = $res['UNTIL'];
		if ($res['UNTIL'] == CCalendar::GetMaxDate())
		{
			$res['~UNTIL'] = '';
		}
		return $res;
	}

	public static function GetExDate($exDate = '')
	{
		if (!is_array($exDate))
			$exDate = $exDate == '' ? array() : explode(';', $exDate);
		return $exDate;
	}

	private static function HandleEvent(&$res, $item = array())
	{
		$userId = CCalendar::GetCurUserId();

		$item['~USER_OFFSET_FROM'] = $item['~USER_OFFSET_TO'] = CCalendar::GetTimezoneOffset($item['TZ_FROM']) - CCalendar::GetCurrentOffsetUTC($userId);
		if ($item['TZ_FROM'] !== $item['TZ_TO'])
			$item['~USER_OFFSET_TO'] = CCalendar::GetTimezoneOffset($item['TZ_TO']) - CCalendar::GetCurrentOffsetUTC($userId);

		$res[] = $item;
	}

	public static function CheckFields(&$arFields, $currentEvent = array(), $userId = false)
	{
		if (!isset($arFields['TIMESTAMP_X']))
			$arFields['TIMESTAMP_X'] = CCalendar::Date(mktime(), true, false);

		if (!$userId)
			$userId = CCalendar::GetCurUserId();

		if (!isset($arFields['DT_SKIP_TIME']) && isset($currentEvent['DT_SKIP_TIME']))
		{
			$arFields['DT_SKIP_TIME'] = $currentEvent['DT_SKIP_TIME'];
		}
		if (!isset($arFields['DATE_FROM']) && isset($currentEvent['DATE_FROM']))
		{
			$arFields['DATE_FROM'] = $currentEvent['DATE_FROM'];
		}
		if (!isset($arFields['DATE_TO']) && isset($currentEvent['DATE_TO']))
		{
			$arFields['DATE_TO'] = $currentEvent['DATE_TO'];
		}

		$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;
		if (!isset($arFields['DATE_CREATE']) && $isNewEvent)
		{
			$arFields['DATE_CREATE'] = $arFields['TIMESTAMP_X'];
		}

		// Skip time
		if (isset($arFields['SKIP_TIME']))
		{
			$arFields['DT_SKIP_TIME'] = $arFields['SKIP_TIME'] ? 'Y' : 'N';
			unset($arFields['SKIP_TIME']);
		}
		elseif(isset($arFields['DT_SKIP_TIME']) && $arFields['DT_SKIP_TIME'] != 'Y' && $arFields['DT_SKIP_TIME'] != 'N')
		{
			unset($arFields['DT_SKIP_TIME']);
		}

		unset($arFields['DT_FROM']);
		unset($arFields['DT_TO']);

		$arFields['DT_SKIP_TIME'] = $arFields['DT_SKIP_TIME'] !== 'Y' ? 'N' : 'Y';
		$fromTs = CCalendar::Timestamp($arFields['DATE_FROM'], false, $arFields['DT_SKIP_TIME'] !== 'Y');
		$toTs = CCalendar::Timestamp($arFields['DATE_TO'], false, $arFields['DT_SKIP_TIME'] !== 'Y');

		$arFields['DATE_FROM'] = CCalendar::Date($fromTs);
		$arFields['DATE_TO'] = CCalendar::Date($toTs);

		if (!$fromTs)
		{
			$arFields['DATE_FROM'] = FormatDate("SHORT", time());
			$fromTs = CCalendar::Timestamp($arFields['DATE_FROM'], false, false);
			if (!$toTs)
			{
				$arFields['DATE_TO'] = $arFields['DATE_FROM'];
				$toTs = $fromTs;
				$arFields['DT_SKIP_TIME'] = 'Y';
			}
		}
		elseif (!$toTs)
		{
			$arFields['DATE_TO'] = $arFields['DATE_FROM'];
			$toTs = $fromTs;
		}

		if ($arFields['DT_SKIP_TIME'] !== 'Y')
		{
			$arFields['DT_SKIP_TIME'] = 'N';
			if (!isset($arFields['TZ_FROM']) && isset($currentEvent['TZ_FROM']))
			{
				$arFields['TZ_FROM'] = $currentEvent['TZ_FROM'];
			}
			if (!isset($arFields['TZ_TO']) && isset($currentEvent['TZ_TO']))
			{
				$arFields['TZ_TO'] = $currentEvent['TZ_TO'];
			}

			if (!isset($arFields['TZ_FROM']) && !isset($arFields['TZ_TO']))
			{
				$userTimezoneOffsetUTC = CCalendar::GetCurrentOffsetUTC($userId);
				$userTimezoneName = CCalendar::GetUserTimezoneName($userId);
				if (!$userTimezoneName)
					$userTimezoneName = CCalendar::GetGoodTimezoneForOffset($userTimezoneOffsetUTC);

				$arFields['TZ_FROM'] = $userTimezoneName;
				$arFields['TZ_TO'] = $userTimezoneName;
			}

			if (!isset($arFields['TZ_OFFSET_FROM']))
			{
				$arFields['TZ_OFFSET_FROM'] = CCalendar::GetTimezoneOffset($arFields['TZ_FROM'], $fromTs);
			}
			if (!isset($arFields['TZ_OFFSET_TO']))
			{
				$arFields['TZ_OFFSET_TO'] = CCalendar::GetTimezoneOffset($arFields['TZ_TO'], $toTs);
			}
		}

		if (!isset($arFields['TZ_OFFSET_FROM']))
		{
			$arFields['TZ_OFFSET_FROM'] = 0;
		}
		if (!isset($arFields['TZ_OFFSET_TO']))
		{
			$arFields['TZ_OFFSET_TO'] = 0;
		}

		if (!isset($arFields['DATE_FROM_TS_UTC']))
		{
			$arFields['DATE_FROM_TS_UTC'] = $fromTs - $arFields['TZ_OFFSET_FROM'];
		}
		if (!isset($arFields['DATE_TO_TS_UTC']))
		{
			$arFields['DATE_TO_TS_UTC'] = $toTs - $arFields['TZ_OFFSET_TO'];
		}

		if ($arFields['DATE_FROM_TS_UTC'] > $arFields['DATE_TO_TS_UTC'])
		{
			$arFields['DATE_TO'] = $arFields['DATE_FROM'];
			$arFields['DATE_TO_TS_UTC'] = $arFields['DATE_FROM_TS_UTC'];
			$arFields['TZ_OFFSET_TO'] = $arFields['TZ_OFFSET_FROM'];
			$arFields['TZ_TO'] = $arFields['TZ_FROM'];
		}

		$h24 = 60 * 60 * 24; // 24 hours
		if ($arFields['DT_SKIP_TIME'] == 'Y')
		{
			unset($arFields['TZ_FROM']);
			unset($arFields['TZ_TO']);
			unset($arFields['TZ_OFFSET_FROM']);
			unset($arFields['TZ_OFFSET_TO']);
		}

		// Event length in seconds
		if (!isset($arFields['DT_LENGTH']) || $arFields['DT_LENGTH'] == 0)
		{
			if($fromTs == $toTs && date('H:i', $fromTs) == '00:00' && $arFields['DT_SKIP_TIME'] == 'Y') // One day
			{
				$arFields['DT_LENGTH'] = $h24;
			}
			else
			{
				$arFields['DT_LENGTH'] = intVal($arFields['DATE_TO_TS_UTC'] - $arFields['DATE_FROM_TS_UTC']);
				if ($arFields['DT_SKIP_TIME'] == "Y") // We have dates without times
				{
					$arFields['DT_LENGTH'] += $h24;
				}
			}
		}

		if (!$arFields['VERSION'])
			$arFields['VERSION'] = 1;

		// Accessibility
		$arFields['ACCESSIBILITY'] = trim(strtolower($arFields['ACCESSIBILITY']));
		if (!in_array($arFields['ACCESSIBILITY'], array('busy', 'quest', 'free', 'absent')))
			$arFields['ACCESSIBILITY'] = 'busy';

		// Importance
		$arFields['IMPORTANCE'] = trim(strtolower($arFields['IMPORTANCE']));
		if (!in_array($arFields['IMPORTANCE'], array('high', 'normal', 'low')))
			$arFields['IMPORTANCE'] = 'normal';

		// Color
		$arFields['COLOR'] = CCalendar::Color($arFields['COLOR'], false);

		// Section
		if (!is_array($arFields['SECTIONS']) && intVal($arFields['SECTIONS']) > 0)
			$arFields['SECTIONS'] = array(intVal($arFields['SECTIONS']));

		// Check rrules
		if (is_array($arFields['RRULE']) && isset($arFields['RRULE']['FREQ']) && in_array($arFields['RRULE']['FREQ'], array('HOURLY','DAILY','MONTHLY','YEARLY','WEEKLY')))
		{
			// Interval
			if (isset($arFields['RRULE']['INTERVAL']) && intval($arFields['RRULE']['INTERVAL']) > 1)
				$arFields['RRULE']['INTERVAL'] = intval($arFields['RRULE']['INTERVAL']);

			// Until date
			$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'], false, false);
			if (!$untilTs)
			{
				$arFields['RRULE']['UNTIL'] = CCalendar::GetMaxDate();
				$untilTs = CCalendar::Timestamp($arFields['RRULE']['UNTIL'], false, false);
			}
			$arFields['DATE_TO_TS_UTC'] = $untilTs + CCalendar::GetDayLen();
			$arFields['RRULE']['UNTIL'] = CCalendar::Date($untilTs, false);

			if (isset($arFields['RRULE']['COUNT']))
				$arFields['RRULE']['COUNT'] = intval($arFields['RRULE']['COUNT']);

			if (isset($arFields['RRULE']['BYDAY']))
			{
				if (is_array($arFields['RRULE']['BYDAY']))
				{
					$BYDAY = $arFields['RRULE']['BYDAY'];
				}
				else
				{
					$BYDAY = array();
					$days = array('SU','MO','TU','WE','TH','FR','SA');
					$bydays = explode(',', $arFields['RRULE']['BYDAY']);
					foreach($bydays as $day)
					{
						$day = strtoupper($day);
						if (in_array($day, $days))
							$BYDAY[] = $day;
					}
				}
				$arFields['RRULE']['BYDAY'] = implode(',',$BYDAY);
			}
			unset($arFields['RRULE']['~UNTIL']);

			if (isset($arFields["EXDATE"]))
				$excludeDates = self::GetExDate($arFields["EXDATE"]);
			else
				$excludeDates = self::GetExDate($currentEvent['EXDATE']);

			if (!empty($excludeDates) && $untilTs)
			{
				$arFields["EXDATE"] = self::SetExDate($excludeDates, $untilTs);
			}

			$arFields['RRULE'] = self::PackRRule($arFields['RRULE']);
		}
		else
		{
			$arFields['RRULE'] = '';
			$arFields['EXDATE'] = '';
		}

		// Location
		if (!is_array($arFields['LOCATION']))
			$arFields['LOCATION'] = Array("NEW" => is_string($arFields['LOCATION']) ? $arFields['LOCATION'] : "");

		// Private
		$arFields['PRIVATE_EVENT'] = isset($arFields['PRIVATE_EVENT']) && $arFields['PRIVATE_EVENT'];

		//$arFields['SEARCHABLE_CONTENT'] = self::formatSearchIndexContent($arFields);

		return true;
	}

	public static function CheckSignificantChangesFields($newFields = array(), $currentFields = array())
	{
		$significantChanges = false;
		$significantFieldList = array(
			'DATE_FROM',
			'DATE_TO',
			'RRULE',
			'EXDATE',
			'NAME',
			'DESCRIPTION',
			'LOCATION'
		);

		foreach ($significantFieldList as $fieldKey)
		{
			if ($newFields[$fieldKey] !== $currentFields[$fieldKey] && $fieldKey != 'LOCATION')
			{
				$significantChanges = true;
				break;
			}
			else if ($fieldKey == 'LOCATION' && $newFields['LOCATION']['NEW'] != $currentFields[$fieldKey])
			{
				$significantChanges = true;
				break;
			}
		}

		return $significantChanges;
	}

	private static function PackRRule($RRule = array())
	{
		$strRes = "";
		if (is_array($RRule))
		{
			foreach($RRule as $key => $val)
				$strRes .= $key.'='.$val.';';
		}
		$strRes = trim($strRes, ', ');
		return $strRes;
	}

	public static function CreateChildEvents($parentId, $arFields, $params)
	{
		global $DB, $CACHE_MANAGER;
		$parentId = intVal($parentId);
		$attendees = $arFields['ATTENDEES'];
		$bCalDav = CCalendar::IsCalDAVEnabled();
		$involvedAttendees = array();

		if ($parentId)
		{
			// It's new event
			$isNewEvent = !isset($arFields['ID']) || $arFields['ID'] <= 0;

			$curAttendeesIndex = array();
			$deletedAttendees = array();
			if (!$isNewEvent)
			{
				$curAttendees = self::GetAttendees($parentId);
				$curAttendees = $curAttendees[$parentId];

				if (is_array($curAttendees))
				{
					foreach($curAttendees as $user)
					{
						$curAttendeesIndex[$user['USER_ID']] = $user;
						if ($user['USER_ID'] !== $arFields['MEETING_HOST'] &&
							($user['USER_ID'] !== $arFields['OWNER_ID'] || $arFields['CAL_TYPE'] !== 'user'))
						{
							$deletedAttendees[$user['USER_ID']] = $user['USER_ID'];
							$involvedAttendees[] = $user['USER_ID'];
						}
					}
				}
			}

			if (is_array($attendees))
			{
				foreach($attendees as $userKey)
				{
					$attendeeId = intVal($userKey);
					$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendeeId);
					if ($attendeeId)
					{
						// Skip creation of child event if it's event inside his own user calendar
						if ($arFields['CAL_TYPE'] == 'user' && $arFields['OWNER_ID'] == $attendeeId)
						{
							continue;
						}

						$childParams = $params;
						$childParams['arFields']['CAL_TYPE'] = 'user';
						$childParams['arFields']['PARENT_ID'] = $parentId;
						$childParams['arFields']['OWNER_ID'] = $attendeeId;
						$childParams['arFields']['CREATED_BY'] = $attendeeId;

						if (intVal($arFields['CREATED_BY']) == $attendeeId)
						{
							$childParams['arFields']['MEETING_STATUS'] = 'Y';
						}
						elseif ($isNewEvent && $arFields['~MEETING']['MEETING_CREATOR'] == $attendeeId)
						{
							$childParams['arFields']['MEETING_STATUS'] = 'Y';
						}
						else
						{
							if ($params['saveAttendeesStatus'] && $params['currentEvent'] && $params['currentEvent']['~ATTENDEES'])
							{
								foreach($params['currentEvent']['~ATTENDEES'] as $currentAttendee)
								{
									if ($currentAttendee['USER_ID'] == $attendeeId)
									{
										$childParams['arFields']['MEETING_STATUS'] = $currentAttendee['STATUS'];
										break;
									}
								}
							}
							else
							{
								$childParams['arFields']['MEETING_STATUS'] = 'Q';
							}
						}

						unset($childParams['arFields']['SECTIONS']);
						unset($childParams['currentEvent']);
						unset($childParams['arFields']['ID']);
						unset($childParams['arFields']['DAV_XML_ID']);

						$bExchange = CCalendar::IsExchangeEnabled($attendeeId);

						if ($isNewEvent || !$curAttendeesIndex[$attendeeId])
						{
							$childSectId = CCalendar::GetMeetingSection($attendeeId, true);
							if ($childSectId)
							{
								$childParams['arFields']['SECTIONS'] = array($childSectId);
							}

							// CalDav & Exchange
							if ($bExchange || $bCalDav)
							{
								CCalendarSync::DoSaveToDav(array(
									'bCalDav' => $bCalDav,
									'bExchange' => $bExchange,
									'sectionId' => $childSectId
								), $childParams['arFields']);
							}
						}

						$childParams['sendInvitations'] = $params['sendInvitations'];

						if (!$isNewEvent && $curAttendeesIndex[$attendeeId])
						{
							$childParams['arFields']['ID'] = $curAttendeesIndex[$attendeeId]['EVENT_ID'];

							if (!$arFields['~MEETING']['REINVITE'])
							{
								$childParams['arFields']['MEETING_STATUS'] = $curAttendeesIndex[$attendeeId]['STATUS'];

								$childParams['sendInvitations'] = $childParams['sendInvitations'] &&  $curAttendeesIndex[$attendeeId]['STATUS'] != 'Q';
							}

							if ($bExchange || $bCalDav)
							{
								$childParams['currentEvent'] = CCalendarEvent::GetById($childParams['arFields']['ID'], false);
								CCalendarSync::DoSaveToDav(array(
									'bCalDav' => $bCalDav,
									'bExchange' => $bExchange,
									'sectionId' => $childParams['currentEvent']['SECT_ID']
								), $childParams['arFields'], $childParams['currentEvent']);
							}
						}

						self::Edit($childParams);
						$involvedAttendees[] = $attendeeId;
						unset($deletedAttendees[$attendeeId]);
					}
				}
			}

			// Delete
			$delIdStr = '';
			if (!$isNewEvent && count($deletedAttendees) > 0)
			{
				foreach($deletedAttendees as $attendeeId)
				{
					$att = $curAttendeesIndex[$attendeeId];
					if ($params['sendInvitations'] !== false && $att['STATUS'] == 'Y')
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$att["USER_ID"]);
						$fromTo = CCalendarEvent::GetEventFromToForUser($arFields, $att["USER_ID"]);
						CCalendarNotify::Send(array(
							"mode" => 'cancel',
							"name" => $arFields['NAME'],
							"from" => $fromTo['DATE_FROM'],
							"to" => $fromTo['DATE_TO'],
							"location" => CCalendar::GetTextLocation($arFields["LOCATION"]),
							"guestId" => $att["USER_ID"],
							"eventId" => $parentId,
							"userId" => $arFields['MEETING_HOST'],
							"fields" => $arFields
						));
					}
					$delIdStr .= ','.intVal($att['EVENT_ID']);

					$bExchange = CCalendar::IsExchangeEnabled($attendeeId);
					if ($bExchange || $bCalDav)
					{
						$currentEvent = CCalendarEvent::GetList(
							array(
								'arFilter' => array(
									"PARENT_ID" => $parentId,
									"OWNER_ID" => $attendeeId,
									"IS_MEETING" => 1,
									"DELETED" => "N"
								),
								'parseRecursion' => false,
								'fetchAttendees' => true,
								'fetchMeetings' => true,
								'checkPermissions' => false,
								'setDefaultLimit' => false
							)
						);
						$currentEvent = $currentEvent[0];

						if ($currentEvent)
						{
							CCalendarSync::DoDeleteToDav(array(
									'bCalDav' => $bCalDav,
									'bExchangeEnabled' => $bExchange,
									'sectionId' => $currentEvent['SECT_ID']
							), $currentEvent);
						}
					}
				}
			}

			$delIdStr = trim($delIdStr, ', ');

			if ($delIdStr != '')
			{
				$strSql =
					"UPDATE b_calendar_event SET ".
					$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
					" WHERE PARENT_ID=".intval($parentId)." AND ID IN(".$delIdStr.")";
				$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			}

			if (count($involvedAttendees) > 0)
			{
				$involvedAttendees = array_unique($involvedAttendees);
				CCalendar::UpdateCounter($involvedAttendees);
			}
		}
	}

	public static function GetEventFromToForUser($params, $userId)
	{
		$skipTime = $params['DT_SKIP_TIME'] !== 'N';

		$fromTs = CCalendar::Timestamp($params['DATE_FROM'], false, !$skipTime);
		$toTs = CCalendar::Timestamp($params['DATE_TO'], false, !$skipTime);

		if (!$skipTime)
		{
			$fromTs = $fromTs - (CCalendar::GetTimezoneOffset($params['TZ_FROM']) - CCalendar::GetCurrentOffsetUTC($userId));
			$toTs = $toTs - (CCalendar::GetTimezoneOffset($params['TZ_TO']) - CCalendar::GetCurrentOffsetUTC($userId));
		}

		$dateFrom = CCalendar::Date($fromTs, !$skipTime);
		$dateTo = CCalendar::Date($toTs, !$skipTime);

		return array(
			"DATE_FROM" => $dateFrom,
			"DATE_TO" => $dateTo,
			"TS_FROM" => $fromTs,
			"TS_TO" => $toTs
		);
	}

	public static function OnPullPrepareArFields($arFields = array())
	{
		$arFields['~DESCRIPTION'] = self::ParseText($arFields['DESCRIPTION']);

		$arFields['~LOCATION'] = '';
		if ($arFields['LOCATION'] !== '')
		{
			$arFields['~LOCATION'] = $arFields['LOCATION'];
			$arFields['LOCATION'] = CCalendar::GetTextLocation($arFields["LOCATION"]);
		}

		if (isset($arFields['~MEETING']))
			$arFields['MEETING'] = $arFields['~MEETING'];


		if ($arFields['REMIND'] !== '' && !is_array($arFields['REMIND']))
		{
			$arFields['REMIND'] = unserialize($arFields['REMIND']);
			if (!is_array($arFields['REMIND']))
				$arFields['REMIND'] = array();
		}

		if ($arFields['RRULE'] != '')
			$arFields['RRULE'] = self::ParseRRULE($arFields['RRULE']);

		return $arFields;
	}

	public static function GetCurrentSectionIds($eventId)
	{
		global $DB;
		$strSql = "SELECT SECT_ID FROM b_calendar_event_sect WHERE EVENT_ID=".intVal($eventId);
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$result = array();
		while($e = $res->Fetch())
			$result[] = intVal($e['SECT_ID']);

		return $result;
	}

	public static function UpdateUserFields($eventId, $arFields = array())
	{
		$eventId = intVal($eventId);
		if (!is_array($arFields) || count($arFields) == 0 || $eventId <= 0)
			return false;

		global $USER_FIELD_MANAGER;
		if ($USER_FIELD_MANAGER->CheckFields("CALENDAR_EVENT", $eventId, $arFields))
			$USER_FIELD_MANAGER->Update("CALENDAR_EVENT", $eventId, $arFields);

		foreach(GetModuleEvents("calendar", "OnAfterCalendarEventUserFieldsUpdate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array('ID' => $eventId,'arFields' => $arFields));

		self::updateSearchIndex($eventId);

		return true;
	}

	public static function GetChildEvents($parentId)
	{
		global $DB;

		$arFields = self::GetFields();
		$childEvents = array();
		$selectList = "";
		foreach($arFields as $field)
			$selectList .= $field['FIELD_NAME'].", ";
		$selectList = trim($selectList, ' ,').' ';

		if ($parentId > 0)
		{

			$strSql = "
				SELECT ".
				$selectList.
				"FROM b_calendar_event CE WHERE CE.PARENT_ID=".intval($parentId);

			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			while($event = $res->Fetch())
			{
				$childEvents[] = $event;
			}
		}
		return false;
	}

	public static function Delete($params)
	{
		global $DB, $CACHE_MANAGER;
		$bCalDav = CCalendar::IsCalDAVEnabled();
		$id = intVal($params['id']);
		$sendNotification = $params['sendNotification'] !== false;

		if ($id)
		{
			$userId = (isset($params['userId']) && $params['userId'] > 0) ? $params['userId'] : CCalendar::GetCurUserId();
			$arAffectedSections = array();
			$event = $params['Event'];

			if (!isset($event) || !is_array($event))
			{
				CCalendar::SetOffset(false, 0);
				$res = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $id
						),
						'parseRecursion' => false
					)
				);
				$event = $res[0];
			}

			if ($event)
			{
				if ($event['IS_MEETING'] && $event['PARENT_ID'] !== $event['ID'])
				{
					if ($event['MEETING_STATUS'] == 'Y' || $event['MEETING_STATUS'] == 'Q')
					{
						self::SetMeetingStatus(array(
							'userId' => $userId,
							'eventId' => $event['ID'],
							'status' => 'N'
						));
					}
				}
				else
				{
					foreach(GetModuleEvents("calendar", "OnBeforeCalendarEventDelete", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array($id, $event));

					if ($event['PARENT_ID'])
						CCalendarLiveFeed::OnDeleteCalendarEventEntry($event['PARENT_ID'], $event);
					else
						CCalendarLiveFeed::OnDeleteCalendarEventEntry($event['ID'], $event);

					$arAffectedSections[] = $event['SECT_ID'];
					// Check location: if reserve meeting was reserved - clean reservation
					if ($event['LOCATION'] != "")
					{
						$loc = CCalendar::ParseLocation($event['LOCATION']);
						if ($loc['mrevid'] || $loc['room_event_id'])
						{
							CCalendar::ReleaseLocation($loc);
						}
					}

					if ($event['CAL_TYPE'] == 'user')
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$event['OWNER_ID']);

					if ($event['IS_MEETING'])
					{
						CCalendarNotify::ClearNotifications($event['PARENT_ID']);

						if (Loader::includeModule("im"))
						{
							CIMNotify::DeleteBySubTag("CALENDAR|INVITE|".$event['PARENT_ID']);
							CIMNotify::DeleteBySubTag("CALENDAR|STATUS|".$event['PARENT_ID']);
						}

						$involvedAttendees = array();

						$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
						$childEvents = CCalendarEvent::GetList(
							array(
								'arFilter' => array(
									"PARENT_ID" => $id
								),
								'parseRecursion' => false,
								'checkPermissions' => false,
								'setDefaultLimit' => false
							)
						);

						$chEventIds = array();
						foreach($childEvents as $chEvent)
						{
							$CACHE_MANAGER->ClearByTag('calendar_user_'.$chEvent["OWNER_ID"]);
							if ($chEvent["MEETING_STATUS"] != "N" && $sendNotification)
							{
								if ($chEvent['DATE_TO_TS_UTC'] + date("Z", $chEvent['DATE_TO_TS_UTC']) > (time() - 60 * 5))
								{
									$fromTo = CCalendarEvent::GetEventFromToForUser($event, $chEvent["OWNER_ID"]);
									CCalendarNotify::Send(array(
										'mode' => 'cancel',
										'name' => $chEvent['NAME'],
										"from" => $fromTo["DATE_FROM"],
										"to" => $fromTo["DATE_TO"],
										"location" => CCalendar::GetTextLocation($chEvent["LOCATION"]),
										"guestId" => $chEvent["OWNER_ID"],
										"eventId" => $id,
										"userId" => $userId
									));
								}
							}
							$chEventIds[] = $chEvent["ID"];

							if ($chEvent["MEETING_STATUS"] == "Q")
								$involvedAttendees[] = $chEvent["OWNER_ID"];

							$bExchange = CCalendar::IsExchangeEnabled($chEvent["OWNER_ID"]);
							if ($bExchange || $bCalDav)
							{
								CCalendarSync::DoDeleteToDav(array(
										'bCalDav' => $bCalDav,
										'bExchangeEnabled' => $bExchange,
										'sectionId' => $chEvent['SECT_ID']
								), $chEvent);
							}
						}

						// Set flag
						if ($params['bMarkDeleted'])
						{
							$strSql =
								"UPDATE b_calendar_event SET ".
								$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
								" WHERE PARENT_ID=".$id;
							$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
						}
						else // Actual deleting
						{
							$strSql = "DELETE from b_calendar_event WHERE PARENT_ID=".$id;
							$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

							$strChEvent = join(',', $chEventIds);
							if (count($chEventIds) > 0)
							{
								// Del link from table
								$strSql = "DELETE FROM b_calendar_event_sect WHERE EVENT_ID in (".$strChEvent.")";
								$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
							}
						}

						if (count($involvedAttendees) > 0)
						{
							CCalendar::UpdateCounter($involvedAttendees);
						}
					}

					if ($params['bMarkDeleted'])
					{
						$strSql =
							"UPDATE b_calendar_event SET ".
							$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
							" WHERE ID=".$id;
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					}
					else
					{
						// Real deleting
						$strSql = "DELETE from b_calendar_event WHERE ID=".$id;
						$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

						// Del link from table
						$strSql = "DELETE FROM b_calendar_event_sect WHERE EVENT_ID=".$id;
						$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					}

					if (count($arAffectedSections) > 0)
						CCalendarSect::UpdateModificationLabel($arAffectedSections);

					foreach(GetModuleEvents("calendar", "OnAfterCalendarEventDelete", true) as $arEvent)
						ExecuteModuleEventEx($arEvent, array($id, $event));

					CCalendar::ClearCache('event_list');
				}
				return true;
			}
		}
		return false;
	}

	public static function SetMeetingStatusEx($params)
	{
		if ($params['reccurentMode'] && $params['currentDateFrom'])
		{
			$event = self::GetById($params['parentId'], false);
			$recurrenceId = $event['RECURRENCE_ID'] ? $event['RECURRENCE_ID'] : $event['ID'];

			if ($params['reccurentMode'] != 'all')
			{
				$res = CCalendar::SaveEventEx(array(
					'arFields' => array(
						"ID" => $params['parentId']
					),
					'silentErrorMode' => false,
					'recursionEditMode' => $params['reccurentMode'],
					'userId' => $event['MEETING_HOST'],
					'checkPermission' => false,
					'currentEventDateFrom' => $params['currentDateFrom'],
					'sendEditNotification' => false
				));

				if ($res && $res['recEventId'])
				{
					self::SetMeetingStatus(array(
						'userId' => $params['attendeeId'],
						'eventId' => $res['recEventId'],
						'status' => $params['status'],
						'personalNotification' => true
					));
				}
			}

			if ($params['reccurentMode'] == 'all' || $params['reccurentMode'] == 'next')
			{
				$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recurrenceId, false);

				if ($params['reccurentMode'] == 'next')
				{
					$untilTimestamp = CCalendar::Timestamp($params['currentDateFrom']);
				}
				else
				{
					$untilTimestamp = false;
					self::SetMeetingStatus(array(
						'userId' => $params['attendeeId'],
						'eventId' => $params['eventId'],
						'status' => $params['status'],
						'personalNotification' => true
					));
				}

				foreach($recRelatedEvents as $ev)
				{
					if ($ev['ID'] == $params['eventId'])
						continue;

					if($params['reccurentMode'] == 'all' ||
						($untilTimestamp && CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp))
					{
						self::SetMeetingStatus(array(
							'userId' => $params['attendeeId'],
							'eventId' => $ev['ID'],
							'status' => $params['status']
						));
					}
				}
			}
		}
		else
		{
			self::SetMeetingStatus(array(
				'userId' => $params['attendeeId'],
				'eventId' => $params['eventId'],
				'status' => $params['status']
			));
		}
	}

	public static function SetMeetingStatus($params)
	{
		CTimeZone::Disable();
		global $DB, $CACHE_MANAGER;
		$eventId = $params['eventId'] = intVal($params['eventId']);
		$userId = $params['userId'] = intVal($params['userId']);
		$status = strtoupper($params['status']);
		if(!in_array($status, array("Q", "Y", "N", "H", "M")))
			$status = $params['status'] = "Q";

		$event = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"IS_MEETING" => 1,
					"DELETED" => "N"
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'fetchMeetings' => true,
				'checkPermissions' => false,
				'setDefaultLimit' => false
			));

		if ($event && count($event) > 0)
		{
			$event = $event[0];
		}

		if ($event && $event['IS_MEETING'] && intVal($event['PARENT_ID']) > 0)
		{
			$strSql = "UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("MEETING_STATUS" => $status)).
				" WHERE PARENT_ID=".intVal($event['PARENT_ID'])." AND OWNER_ID=".$userId;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			CCalendarSect::UpdateModificationLabel($event['SECT_ID']);

			// Clear invitation in messager
			CCalendarNotify::ClearNotifications($event['PARENT_ID'], $userId);

			// Add new notification in messenger
			if ($params['personalNotification'] && intVal(CCalendar::getCurUserId()) == $userId)
			{
				$fromTo = CCalendarEvent::GetEventFromToForUser($event, $userId);
				CCalendarNotify::Send(array(
					'mode' => $status == "Y" ? 'status_accept' : 'status_decline',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"guestId" => $userId,
					"eventId" => $event['PARENT_ID'],
					"userId" => $userId,
					"markRead" => true,
					"fields" => $event
				));
			}

			// If it's open meeting and our attendee is not on the list
			if ($event['MEETING'] && $event['MEETING']['OPEN'] && ($status == 'Y' || $status == 'M'))
			{
				$arAttendees = self::GetAttendees(array($event['PARENT_ID']));
				$arAttendees = $arAttendees[$event['PARENT_ID']];
				$attendeeExist = false;
				foreach($arAttendees as $attendee)
				{
					if ($attendee['USER_ID'] == $userId)
					{
						$attendeeExist = true;
						break;
					}
				}

				if (!$attendeeExist && is_array($event))
				{
					// 1. Create another childEvent for new attendee
					$AllFields = self::GetFields();
					$dbFields = array();
					foreach($event as $field => $val)
					{
						if(isset($AllFields[$field]) && $field != "ID" && $field != "ATTENDEES_CODES")
						{
							$dbFields[$field] = $event[$field];
						}
					}
					$dbFields['MEETING_STATUS'] = $status;
					$dbFields['CAL_TYPE'] = 'user';
					$dbFields['OWNER_ID'] = $userId;
					$dbFields['PARENT_ID'] = $event['PARENT_ID'];
					$dbFields['MEETING'] = serialize($event['MEETING']);
					$dbFields['REMIND'] = serialize($event['REMIND']);
					$eventId = $DB->Add("b_calendar_event", $dbFields, array('DESCRIPTION', 'MEETING', 'EXDATE'));

					$DB->Query("UPDATE b_calendar_event SET ".
						$DB->PrepareUpdate("b_calendar_event", array('DAV_XML_ID' => $eventId)).
						" WHERE ID=".IntVal($eventId), false, "File: ".__FILE__."<br>Line: ".__LINE__);

					$sectionId = CCalendarSect::GetLastUsedSection('user', $userId, $userId);
					if (!$sectionId || !CCalendarSect::GetById($sectionId, false))
					{
						$sectRes = CCalendarSect::GetSectionForOwner('user', $userId);
						$sectionId = $sectRes['sectionId'];
					}
					if ($eventId && $sectionId)
					{
						self::ConnectEventToSection($eventId, $sectionId);
					}

					// 2. Update ATTENDEES_CODES
					$attendeesCodes = $event['ATTENDEES_CODES'];
					$attendeesCodes[] = 'U'.intVal($userId);

					$attendeesCodes = array_unique($attendeesCodes);
					$DB->Query("UPDATE b_calendar_event SET ".
						"ATTENDEES_CODES='".implode(',', $attendeesCodes)."'".
						" WHERE PARENT_ID=".intVal($event['PARENT_ID']), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

					CCalendarSect::UpdateModificationLabel(array($sectionId));
				}
			}

			// Notify author of event
			if ($event['MEETING']['NOTIFY'] && $userId != $event['MEETING_HOST'] &&
				$params['hostNotification'] !== false)
			{
				// Send message to the author
				$fromTo = CCalendarEvent::GetEventFromToForUser($event, $event['MEETING_HOST']);
				CCalendarNotify::Send(array(
					'mode' => $status == "Y" ? 'accept' : 'decline',
					'name' => $event['NAME'],
					"from" => $fromTo["DATE_FROM"],
					"to" => $fromTo["DATE_TO"],
					"location" => CCalendar::GetTextLocation($event["LOCATION"]),
					"guestId" => $userId,
					"eventId" => $event['PARENT_ID'],
					"userId" => $event['MEETING_HOST'],
					"fields" => $event
				));
			}
			CCalendarSect::UpdateModificationLabel(array($event['SECTIONS'][0]));

			if ($status == "N")
			{
				$childEvent = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"PARENT_ID" => $event['PARENT_ID'],
							"CREATED_BY" => $userId,
							"IS_MEETING" => 1,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					)
				);

				if ($childEvent && $childEvent[0])
				{
					$childEvent = $childEvent[0];
					$bCalDav = CCalendar::IsCalDAVEnabled();
					$bExchange = CCalendar::IsExchangeEnabled($userId);

					if ($bExchange || $bCalDav)
					{
						CCalendarSync::DoDeleteToDav(array(
							'bCalDav' => $bCalDav,
							'bExchangeEnabled' => $bExchange,
							'sectionId' => $childEvent['SECT_ID']
						), $childEvent);
					}
				}
			}

			if ($status == "Y" && $params['affectRecRelatedEvents'] !== false)
			{
				//$event = self::GetById($event['PARENT_ID'], false);
				$event = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"IS_MEETING" => 1,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => true,
						'checkPermissions' => false,
						'setDefaultLimit' => false
					));

				if ($event && count($event) > 0)
				{
					$event = $event[0];
				}

				$recurrenceId = $event['RECURRENCE_ID'] ? $event['RECURRENCE_ID'] : $event['ID'];

				if ($recurrenceId)
				{
					$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recurrenceId, false);
					foreach($recRelatedEvents as $ev)
					{
						if ($ev['ID'] == $params['eventId'])
							continue;

						self::SetMeetingStatus(array(
							'userId' => $userId,
							'eventId' => $ev['ID'],
							'status' => $status,
							'personalNotification' => false,
							'hostNotification' => false,
							'affectRecRelatedEvents' => false
						));
					}
				}
			}

			CCalendarLiveFeed::OnChangeMeetingStatusEventEntry(array(
				'userId' => $userId,
				'eventId' => $eventId,
				'status' => $status,
				'event' => $event
			));

			CCalendar::UpdateCounter($userId);

			$CACHE_MANAGER->ClearByTag('calendar_user_'.$userId);
			$CACHE_MANAGER->ClearByTag('calendar_user_'.$event['CREATED_BY']);
		}
		else
		{
			CCalendarNotify::ClearNotifications($eventId);
		}

		CTimeZone::Enable();
		CCalendar::ClearCache(array('attendees_list', 'event_list'));
	}

	/*
	 * $params['dateFrom']
	 * $params['dateTo']
	 *
	 * */

	public static function GetMeetingStatus($userId, $eventId)
	{
		global $DB;
		$eventId = intVal($eventId);
		$userId = intVal($userId);
		$status = false;
		$event = CCalendarEvent::GetById($eventId, false);
		if ($event && $event['IS_MEETING'] && intVal($event['PARENT_ID']) > 0)
		{
			if ($event['CREATED_BY'] == $userId)
			{
				$status = $event['MEETING_STATUS'];
			}
			else
			{
				$res = $DB->Query("SELECT MEETING_STATUS from b_calendar_event WHERE PARENT_ID=".intVal($event['PARENT_ID'])." AND CREATED_BY=".$userId, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$event = $res->Fetch();
				$status = $event['MEETING_STATUS'];
			}
		}
		return $status;
	}

	public static function SetMeetingParams($userId, $eventId, $arFields)
	{
		$eventId = intVal($eventId);
		$userId = intVal($userId);

		// Check $arFields
		if (!in_array($arFields['ACCESSIBILITY'], array('busy', 'quest', 'free', 'absent')))
			$arFields['ACCESSIBILITY'] = 'busy';

		$event = CCalendarEvent::GetById($eventId);
		if (!$event)
			return false;

		$res = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"PARENT_ID" => $eventId,
					"CREATED_BY" => $userId,
					"IS_MEETING" => 1,
					"DELETED" => "N"
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'fetchMeetings' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			)
		);

		if (!$res || !$res[0])
		{
			$res = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"CREATED_BY" => $userId,
						"IS_MEETING" => 1,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'fetchMeetings' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				)
			);
		}

		if ($res[0])
		{
			$event = $res[0];
			$arReminders = array();
			if (isset($arFields['REMIND']))
			{
				if ($arFields['REMIND'] && is_array($arFields['REMIND']))
				{
					foreach ($arFields['REMIND'] as $remind)
					{
						if(is_array($remind) && isset($remind['type']) && in_array($remind['type'], array('min', 'hour', 'day')))
						{

							$arReminders[] = array('type' => $remind['type'], 'count' => floatVal($remind['count']));
						}
					}
				}
			}

			$arFields = array(
				"ID" => $event['ID'],
				"REMIND" => $arReminders,
				"ACCESSIBILITY" => $arFields['ACCESSIBILITY']
			);
			//SaveEvent
			CCalendar::SaveEvent(array('arFields' => $arFields));
		}
		return true;
	}

	public static function GetAccessibilityForUsers($params = array())
	{
		$curEventId = intVal($params['curEventId']);
		if (!is_array($params['users']) || count($params['users']) == 0)
			return array();

		if (!isset($params['checkPermissions']))
			$params['checkPermissions'] = true;

		$users = array();
		$accessibility = array();
		foreach($params['users'] as $userId)
		{
			$userId = intVal($userId);
			if ($userId)
			{
				$users[] = $userId;
				$accessibility[$userId] = array();
			}
		}

		if (count($users) == 0)
			return array();

		$events = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"FROM_LIMIT" => $params['from'],
					"TO_LIMIT" => $params['to'],
					"CAL_TYPE" => 'user',
					"OWNER_ID" => $users,
					"ACTIVE_SECTION" => "Y"
				),
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'fetchSection' => true,
				'setDefaultLimit' => false,
				'checkPermissions' => $params['checkPermissions']
			)
		);

		foreach($events as $event)
		{
			if ($curEventId && ($event["ID"] == $curEventId || $event["PARENT_ID"] == $curEventId))
				continue;
			if ($event["ACCESSIBILITY"] == 'free')
				continue;
			if ($event["IS_MEETING"] && ($event["MEETING_STATUS"] == "N" || $event["MEETING_STATUS"] == "Q"))
				continue;
			if (CCalendarSect::CheckGoogleVirtualSection($event['SECTION_DAV_XML_ID']))
				continue;

			$accessibility[$event['OWNER_ID']][] = array(
				"ID" => $event["ID"],
				"NAME" => $event["NAME"],
				"DATE_FROM" => $event["DATE_FROM"],
				"DATE_TO" => $event["DATE_TO"],
				"~USER_OFFSET_FROM" => $event["~USER_OFFSET_FROM"],
				"~USER_OFFSET_TO" => $event["~USER_OFFSET_TO"],
				"DT_SKIP_TIME" => $event["DT_SKIP_TIME"],
				"TZ_FROM" => $event["TZ_FROM"],
				"TZ_TO" => $event["TZ_TO"],
				"ACCESSIBILITY" => $event["ACCESSIBILITY"],
				"IMPORTANCE" => $event["IMPORTANCE"],
				"EVENT_TYPE" => $event["EVENT_TYPE"]
			);
		}

		return $accessibility;
	}

	public static function GetAbsent($users = false, $params = array())
	{
		// Can be called from agent... So we have to create $USER if it is not exists
		$tempUser = CCalendar::TempUser(false, true);

		$curUserId = isset($params['userId']) ? intVal($params['userId']) : CCalendar::GetCurUserId();
		$arUsers = array();

		if ($users !== false && is_array($users))
		{
			foreach($users as $id)
			{
				if ($id > 0)
					$arUsers[] = intVal($id);
			}
		}
		if (!count($arUsers))
			$users = false;

		$arFilter = array(
			'DELETED' => 'N',
			'ACCESSIBILITY' => 'absent',
		);

		if ($users)
			$arFilter['CREATED_BY'] = $users;

		if (isset($params['fromLimit']))
			$arFilter['FROM_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['fromLimit'], false), true, false);
		if (isset($params['toLimit']))
			$arFilter['TO_LIMIT'] = CCalendar::Date(CCalendar::Timestamp($params['toLimit'], false), true, false);

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'parseRecursion' => true,
				'getUserfields' => false,
				'userId' => $curUserId,
				'preciseLimits' => true,
				'checkPermissions' => false,
				'skipDeclined' => true
			)
		);

		$bSocNet = Loader::includeModule("socialnetwork");
		$result = array();
		$settings = CCalendar::GetSettings(array('request' => false));

		foreach($arEvents as $event)
		{
			$userId = isset($event['USER_ID']) ? $event['USER_ID'] : $event['CREATED_BY'];
			if ($users !== false && !in_array($userId, $arUsers))
				continue;

			if ($bSocNet && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $userId, "calendar"))
				continue;

			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] == 'N')
				continue;

			if ((!$event['CAL_TYPE'] != 'user' || $curUserId != $event['OWNER_ID']) && $curUserId != $event['CREATED_BY'] && !isset($arUserMeeting[$event['ID']]))
			{
				$sectId = $event['SECT_ID'];
				if (!$event['ACCESSIBILITY'])
					$event['ACCESSIBILITY'] = 'busy';

				$private = $event['PRIVATE_EVENT'] && $event['CAL_TYPE'] == 'user';
				$bManager = false;
				if (!$private && CCalendar::IsIntranetEnabled() && Loader::includeModule('intranet') && $event['CAL_TYPE'] == 'user' && $settings['dep_manager_sub'])
					$bManager = in_array($curUserId, CCalendar::GetUserManagers($event['OWNER_ID'], true));

				if ($private || (!CCalendarSect::CanDo('calendar_view_full', $sectId) && !$bManager))
				{
					$event = self::ApplyAccessRestrictions($event, $userId);
				}
			}

			$skipTime = $event['DT_SKIP_TIME'] === 'Y';
			$fromTs = CCalendar::Timestamp($event['DATE_FROM'], false, !$skipTime);
			$toTs = CCalendar::Timestamp($event['DATE_TO'], false, !$skipTime);
			if ($event['DT_SKIP_TIME'] !== 'Y')
			{
				$fromTs -= $event['~USER_OFFSET_FROM'];
				$toTs -= $event['~USER_OFFSET_TO'];
			}
			$result[] = array(
				'ID' => $event['ID'],
				'NAME' => $event['NAME'],
				'DATE_FROM' => CCalendar::Date($fromTs, !$skipTime, false),
				'DATE_TO' => CCalendar::Date($toTs, !$skipTime, false),
				'DT_FROM_TS' => $fromTs,
				'DT_TO_TS' => $toTs,
				'CREATED_BY' => $userId,
				'DETAIL_TEXT' => '',
				'USER_ID' => $userId
			);
		}

		// Sort by DATE_FROM_TS_UTC
		usort($result, array('CCalendar', '_NearestSort'));

		CCalendar::TempUser($tempUser, false);
		return $result;
	}

	public static function DeleteEmpty()
	{
		global $DB;
		$strSql = 'SELECT CE.ID, CE.LOCATION
			FROM b_calendar_event CE
			LEFT JOIN b_calendar_event_sect CES ON (CE.ID=CES.EVENT_ID)
			WHERE CES.SECT_ID is null';
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strItems = "0";
		while($arRes = $res->Fetch())
		{
			$loc = $arRes['LOCATION'];
			if ($loc && strlen($loc) > 5 && substr($loc, 0, 5) == 'ECMR_')
			{
				$loc = CCalendar::ParseLocation($loc);
				if ($loc['mrid'] !== false && $loc['mrevid'] !== false) // Release MR
					CCalendar::ReleaseLocation($loc);
			}
			$strItems .= ",".IntVal($arRes['ID']);
		}

		// Clean from 'b_calendar_event'
		if ($strItems != "0")
			$DB->Query("DELETE FROM b_calendar_event WHERE ID in (".$strItems.")", false,
				"FILE: ".__FILE__."<br> LINE: ".__LINE__);

		CCalendar::ClearCache(array('section_list', 'event_list'));
	}

	public static function CleanEventsWithDeadParents()
	{
		global $DB;
		$strSql = "SELECT PARENT_ID from b_calendar_event where PARENT_ID in (SELECT ID from b_calendar_event where MEETING_STATUS='H' and DELETED='Y') AND DELETED='N'";
		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strItems = "0";
		while($res = $res->Fetch())
		{
			$strItems .= ",".intval($res['ID']);
		}

		if ($strItems != "0")
		{
			$strSql =
				"UPDATE b_calendar_event SET ".
				$DB->PrepareUpdate("b_calendar_event", array("DELETED" => "Y")).
				" WHERE PARENT_ID in (".$strItems.")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		CCalendar::ClearCache(array('section_list', 'event_list'));
	}

	public static function CheckEndUpdateAttendeesCodes($event)
	{
		if ($event['ID'] > 0 && $event['IS_MEETING'] && empty($event['ATTENDEES_CODES']) && is_array($event['~ATTENDEES']))
		{
			$event['ATTENDEES_CODES'] = array();
			foreach($event['~ATTENDEES'] as $attendee)
			{
				if (intval($attendee['USER_ID']) > 0)
				{
					$event['ATTENDEES_CODES'][] = 'U'.IntVal($attendee['USER_ID']);
				}
			}
			$event['ATTENDEES_CODES'] = array_unique($event['ATTENDEES_CODES']);

			global $DB;
			$strSql =
				"UPDATE b_calendar_event SET ".
				"ATTENDEES_CODES='".implode(',', $event['ATTENDEES_CODES'])."'".
				" WHERE ID=".IntVal($event['ID']);
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			CCalendar::ClearCache(array('event_list'));
		}
		return $event['ATTENDEES_CODES'];
	}

	public static function CanView($eventId, $userId)
	{
		Loader::includeModule("calendar");
		$event = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'userId' => $userId,
			)
		);

		if (!$event || !is_array($event[0]))
		{
			$event = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"PARENT_ID" => $eventId,
						"CREATED_BY" => $userId
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'userId' => $userId,
				)
			);
		}

		if ($event && is_array($event[0]))
		{
			// Event is not partly accessible - so it was not cleaned before by ApplyAccessRestrictions
			if (isset($event[0]['DESCRIPTION']) || isset($event[0]['IS_MEETING']) || isset($event[0]['LOCATION']))
				return true;
		}

		return false;
	}

	public static function GetEventUserFields($event)
	{
		global $USER_FIELD_MANAGER;
		if ($event['PARENT_ID'])
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['PARENT_ID'], LANGUAGE_ID);
		}
		else
		{
			$UF = $USER_FIELD_MANAGER->GetUserFields("CALENDAR_EVENT", $event['ID'], LANGUAGE_ID);
		}
		return $UF;
	}

	public static function SetExDate($exDate = array(), $untilTimestamp = false)
	{
		if ($untilTimestamp && !empty($exDate) && is_array($exDate))
		{
			$exDateRes = array();
			foreach($exDate as $date)
			{
				if (CCalendar::Timestamp($date) <= $untilTimestamp)
					$exDateRes[] = $date;
			}
			$exDate = $exDateRes;
		}

		return implode(';', $exDate);
	}

	public static function GetEventsByRecId($recurrenceId, $checkPermissions = true)
	{
		if ($recurrenceId > 0)
		{
			$events = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"RECURRENCE_ID" => $recurrenceId,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => false,
					'checkPermissions' => $checkPermissions,
					'setDefaultLimit' => false
				)
			);
			return $events;
		}
		return array();
	}

	public static function GetEventCommentXmlId($event)
	{
		if (is_array($event['RELATIONS']) && array_key_exists('COMMENT_XML_ID', $event['RELATIONS']) && $event['RELATIONS']['COMMENT_XML_ID'])
		{
			$commentXmlId = $event['RELATIONS']['COMMENT_XML_ID'];
		}
		else
		{
			$eventCommentId = $event['PARENT_ID'] ? $event['PARENT_ID'] : $event['ID'];
			$commentXmlId = "EVENT_".$eventCommentId;

			if (CCalendarEvent::CheckRecurcion($event))
			{
				// We have reccurent event which was created from another reccurent event
				if ($event['RECURRENCE_ID'])
				{
					$commentXmlId = "EVENT_".$event['RECURRENCE_ID'];
					$commentXmlId .= '_'.CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
				}
				else
				{
					if (CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false) !== CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false) && $event['RINDEX'] > 0)
						$commentXmlId .= '_'.CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false);
				}
			}
		}

		return $commentXmlId;
	}

	public static function ExtractDateFromCommentXmlId($xmlId = '')
	{
		$date = false;
		if ($xmlId)
		{
			$xmlAr = explode('_', $xmlId);
			if (is_array($xmlAr) && isset($xmlAr[2]) && $xmlAr[2])
			{
				$date = CCalendar::Date(CCalendar::Timestamp($xmlAr[2]), false);
			}
		}
		return $date;
	}

	public static function GetRRULEDescription($event, $html = false, $showUntil = true)
	{
		$res = '';
		if($event['RRULE'])
		{
			if(!is_array($event['RRULE']))
				$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);

			switch($event['RRULE']['FREQ'])
			{
				case 'DAILY':
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_DAY');
					else
						$res = GetMessage('EC_RRULE_EVERY_DAY_1', array('#DAY#' => $event['RRULE']['INTERVAL']));
					break;
				case 'WEEKLY':
					$daysList = array();
					foreach($event['RRULE']['BYDAY'] as $day)
						$daysList[] = GetMessage('EC_'.$day);
					$daysList = implode(', ', $daysList);
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_WEEK', array('#DAYS_LIST#' => $daysList));
					else
						$res = GetMessage('EC_RRULE_EVERY_WEEK_1', array('#WEEK#' => $event['RRULE']['INTERVAL'], '#DAYS_LIST#' => $daysList));
					break;
				case 'MONTHLY':
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_MONTH');
					else
						$res = GetMessage('EC_RRULE_EVERY_MONTH_1', array('#MONTH#' => $event['RRULE']['INTERVAL']));
					break;
				case 'YEARLY':
					if($event['RRULE']['INTERVAL'] == 1)
						$res = GetMessage('EC_RRULE_EVERY_YEAR', array('#DAY#' => $event['FROM_MONTH_DAY'], '#MONTH#' => $event['FROM_MONTH']));
					else
						$res = GetMessage('EC_RRULE_EVERY_YEAR_1', array('#YEAR#' => $event['RRULE']['INTERVAL'], '#DAY#' => $event['FROM_MONTH_DAY'], '#MONTH#' => $event['FROM_MONTH']));
					break;
			}

			if ($html)
				$res .= '<br>';
			else
				$res .= ', ';

			if (isset($event['~DATE_FROM']))
			{
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['~DATE_FROM']), false)));
			}
			else
			{
				$res .= GetMessage('EC_RRULE_FROM', array('#FROM_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['DATE_FROM']), false)));
			}

			if($showUntil && $event['RRULE']['UNTIL'] != CCalendar::GetMaxDate())
			{
				$res .= ' '.GetMessage('EC_RRULE_UNTIL', array('#UNTIL_DATE#' => CCalendar::Date(CCalendar::Timestamp($event['RRULE']['UNTIL']), false)));
			}
			elseif($showUntil && $event['RRULE']['COUNT'] > 0)
			{
				$res .= ', '.GetMessage('EC_RRULE_COUNT', array('#COUNT#' => $event['RRULE']['COUNT']));
			}
		}

		return $res;
	}

	public static function ExcludeInstance($eventId, $excludeDate)
	{
		global $CACHE_MANAGER;
		$eventId = intval($eventId);
		$excludeDateTs = CCalendar::Timestamp($excludeDate);
		$excludeDate = CCalendar::Date($excludeDateTs, false);

		$event = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					"ID" => $eventId,
					"DELETED" => "N"
				),
				'parseRecursion' => false,
				'fetchAttendees' => true,
				'setDefaultLimit' => false
			)
		);
		if ($event && is_array($event[0]))
			$event = $event[0];

		if ($event && CCalendarEvent::CheckRecurcion($event) && $excludeDate)
		{
			$excludeDates = CCalendarEvent::GetExDate($event['EXDATE']);
			$excludeDates[] = $excludeDate;

			$id = CCalendar::SaveEvent(array(
				'arFields' => array(
					'ID' => $event['ID'],
					'DATE_FROM' => $event['DATE_FROM'],
					'DATE_TO' => $event['DATE_TO'],
					'EXDATE' => CCalendarEvent::SetExDate($excludeDates)
				),
				'silentErrorMode' => false,
				'recursionEditMode' => 'skip'
			));

			foreach($event['~ATTENDEES'] as $attendee)
			{
				if ($attendee['STATUS'] == 'Y')
				{
					if ($event['DT_SKIP_TIME'] !== 'Y')
					{
						$excludeDate = CCalendar::Date(CCalendar::DateWithNewTime(CCalendar::Timestamp($event['DATE_FROM']), $excludeDateTs));
					}

					$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendee["USER_ID"]);
					CCalendarNotify::Send(array(
						"mode" => 'cancel_this',
						"name" => $event['NAME'],
						"from" => $excludeDate,
						"guestId" => $attendee["USER_ID"],
						"eventId" => $event['PARENT_ID'],
						"userId" => $event['MEETING_HOST'],
						"fields" => $event
					));
				}
			}
		}
	}

	public static function GetTextReminders($valueList = array())
	{
		if (is_array($valueList))
		{
			foreach($valueList as $i => $value)
			{
				$text = '';
				if($value['type'] == 'min')
				{
					$value['text'] = Loc::getMessage('EC_REMIND_VIEW_'.$value['count']);
					if(!$value['text'])
					{
						$value['text'] = Loc::getMessage('EC_REMIND_VIEW_MIN_COUNT', array('#COUNT#' => intval($value['count'])));
					}
				}
				elseif($value['type'] == 'hour')
				{
					$value['text'] = Loc::getMessage('EC_REMIND_VIEW_HOUR_COUNT', array('#COUNT#' => intval($value['count'])));
				}
				elseif($value['type'] == 'day')
				{
					$value['text'] = Loc::getMessage('EC_REMIND_VIEW_DAY_COUNT', array('#COUNT#' => intval($value['count'])));
				}
				$valueList[$i] = $value;
			}
		}
		return $valueList;
	}

	public static function getDiskUFFileNameList($valueList = array())
	{
		$result = array();

		if (
			!empty($valueList)
			&& is_array($valueList)
			&& Loader::includeModule('disk')
		)
		{
			$attachedIdList = array();
			foreach($valueList as $value)
			{
				list($type, $realValue) = FileUserType::detectType($value);
				if($type == FileUserType::TYPE_NEW_OBJECT)
				{
					$file = \Bitrix\Disk\File::loadById($realValue, array('STORAGE'));
					$result[] = strip_tags($file->getName());
				}
				else
				{
					$attachedIdList[] = $realValue;
				}
			}

			if(!empty($attachedIdList))
			{
				$attachedObjects = AttachedObject::getModelList(array(
					'with' => array('OBJECT'),
					'filter' => array(
						'ID' => $attachedIdList
					),
				));
				foreach($attachedObjects as $attachedObject)
				{
					$file = $attachedObject->getFile();
					$result[] = strip_tags($file->getName());
				}
			}
		}

		return $result;
	}

	public static function getSearchIndexContent($eventId)
	{
		$res = '';
		if (intval($eventId) > 0)
		{
			$events = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"ID" => $eventId,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);
			$res = is_array($events[0]) && is_array($events[0]) ? self::formatSearchIndexContent($events[0]) : '';
		}
		return $res;
	}

	public static function getSearchIndexContentBatch($eventIdList = array())
	{
		$res = array();
		if (is_array($eventIdList))
		{
			$events = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"ID" => $eventIdList,
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);

			foreach($events as $event)
			{
				$res[$event['ID']] = self::formatSearchIndexContent($event);
			}
		}
		return $res;
	}

	public static function updateSearchIndex($eventIdList = array(), $params = array())
	{
		global $DB;

		if (isset($params['events']))
		{
			$events = $params['events'];
		}
		else
		{
			if (!is_array($eventIdList))
				$eventIdList = array($eventIdList);

			$events = \CCalendarEvent::getList(
				array(
					'arFilter' => array(
						"ID" => $eventIdList,
						"DELETED" => false
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'checkPermissions' => false,
					'setDefaultLimit' => false
				)
			);
		}

		if (is_array($events))
		{
			foreach($events as $event)
			{
				$content = self::formatSearchIndexContent($event);
				$strSql = "UPDATE b_calendar_event SET ".
					$DB->PrepareUpdate("b_calendar_event", array('SEARCHABLE_CONTENT' => $content)).
					" WHERE ID=".IntVal($event['ID']);
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
		}
	}

	public static function formatSearchIndexContent($entry = array())
	{
		$content = '';
		if (!empty($entry))
		{
			$content = static::prepareToken($entry['NAME'].' '.$entry['DESCRIPTION']);

			if ($entry['IS_MEETING'])
			{
				if(!empty($entry['~ATTENDEES']))
				{
					foreach($entry['~ATTENDEES'] as $user)
					{
						$content .= ' '.static::prepareToken($user['DISPLAY_NAME']);
					}
				}

				if(!empty($entry['ATTENDEES_CODES']))
				{
					$content .= ' '.static::prepareToken(join(' ', Bitrix\Socialnetwork\Item\LogIndex::getEntitiesName($entry['ATTENDEES_CODES'])));
				}
			}
			else
			{
				$content .= ' '.static::prepareToken(CCalendar::GetUserName($entry['CREATED_BY']));
			}

			try {
				if (!empty($entry['UF_WEBDAV_CAL_EVENT'])
					&& \Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
				)
				{
					$fileNameList = self::getDiskUFFileNameList($entry['UF_WEBDAV_CAL_EVENT']);
					if (!empty($fileNameList))
					{
						$content .= ' '.static::prepareToken(join(' ', $fileNameList));
					}
				}
			}
			catch (RuntimeException $e) {
			}

			try {
				if (!empty($entry['UF_CRM_CAL_EVENT']) && Loader::includeModule('crm'))
				{
					$uf = $entry['UF_CRM_CAL_EVENT'];

					foreach ($uf as $item)
					{
						$crmElement = explode('_', $item);
						$type = $crmElement[ 0 ];

						$typeId = \CCrmOwnerType::ResolveID(\CCrmOwnerTypeAbbr::ResolveName($type));
						$title = \CCrmOwnerType::GetCaption($typeId, $crmElement[ 1 ]);

						$index[] = $title;
						$content .= ' '.static::prepareToken($title);
					}
				}
			}
			catch (RuntimeException $e) {
			}
		}

		return $content;
	}

	public static function GetCount()
	{
		global $DB;
		$count = 0;
		$res = $DB->Query('select count(*) as c  from b_calendar_event', false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if($res = $res->Fetch())
		{
			$count = $res['c'];
		}

		return $count;
	}

	public static function updateColor($eventId, $color = '')
	{
		global $DB;
		$strSql = "UPDATE b_calendar_event SET ".
			$DB->PrepareUpdate("b_calendar_event", array('COLOR' => $color)).
			" WHERE ID=".IntVal($eventId);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	/**
	 * Applies ROT13 transform to search token, in order to bypass default mysql search blacklist.
	 * @param string $token Search token.
	 * @return string
	 */
	public static function prepareToken($token)
	{
		return str_rot13($token);
	}

	public static function isFullTextIndexEnabled()
	{
		return COption::GetOptionString("calendar", "~ft_b_calendar_event", false);
	}
}
?>