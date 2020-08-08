<?
namespace Bitrix\Calendar\Controller;

use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Error;
use Bitrix\Calendar\Internals;
use \Bitrix\Main\Engine\Response;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Calendar\UserSettings;

Loc::loadMessages(__FILE__);

/**
 * Class CalendarAjax
 */
class CalendarAjax extends \Bitrix\Main\Engine\Controller
{
	public function configureActions()
	{
		return [
			'getTimezoneList' => [
				'-prefilters' => [
					\Bitrix\Main\Engine\ActionFilter\Authentication::class
				]
			]
		];
	}

	public function getTimezoneListAction()
	{
		$timezones = \CCalendar::getTimezoneList();
		$defaultTimezone = \CCalendar::getGoodTimezoneForOffset(\CCalendar::getCurrentOffsetUTC(\CCalendar::getCurUserId()));
		if (isset($timezones[$defaultTimezone]))
		{
			$timezones[$defaultTimezone]['default'] = true;
		}

		return $timezones;
	}

	public function editCalendarSectionAction()
	{
		$request = $this->getRequest();
		$response = [];

		$id = $request->getPost('id');
		$isNew = (!isset($id) || !$id);
		$type = $request->getPost('type');
		$ownerId = intval($request->getPost('ownerId'));
		$name = trim($request->getPost('name'));
		$color = $request->getPost('color');
		$customization = $request->getPost('customization') === 'Y';
		$userId = \CCalendar::GetUserId();
		$isPersonal = $type == 'user' && $ownerId == $userId;

		if ($id === 'tasks')
		{
			$id .= $ownerId;
		}

		$fields = [
			'ID' => $id,
			'NAME' => $name,
			'COLOR' => $color,
			'CAL_TYPE' => $type,
			'OWNER_ID' => $ownerId,
			'ACCESS' => $request->getPost('access')
		];

		if ($customization && !$isNew)
		{
			UserSettings::setSectionCustomization($userId, [$id => ['name' => $name, 'color' => $color]]);
		}
		else
		{
			if($isNew) // For new sections
			{
				if($type === 'group')
				{
					// It's for groups
					if(!\CCalendarType::CanDo('calendar_type_edit_section', 'group'))
					{
						$this->addError(new Error('[se01]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
					}
				}
				else if($type === 'user')
				{
					if (!$isPersonal)
					{
						$this->addError(new Error('[se02]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
					}
				}
				else // other types
				{
					if (!\CCalendarType::CanDo('calendar_type_edit_section', $type))
					{
						$this->addError(new Error('[se03]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
					}
				}

				$fields['IS_EXCHANGE'] = $request->getPost('is_exchange') == 'Y';
			}
			else
			{
				$section = \CCalendarSect::GetById($id);
				if (!$section && !$isPersonal && !\CCalendarSect::CanDo('calendar_edit_section', $id, $userId))
				{
					$this->addError(new Error('[se04]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
				}

				$fields['CAL_TYPE'] = $section['CAL_TYPE'];
				$fields['OWNER_ID'] = $section['OWNER_ID'];
			}

			$id = intval(\CCalendar::SaveSection(['arFields' => $fields]));
			if ($id > 0)
			{
				\CCalendarSect::SetClearOperationCache(true);
				$response['section'] =  \CCalendarSect::GetById($id, true, true);
				if (!$response['section'])
				{
					$this->addError(new Error('[se05]'.Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error'));
				}
				$response['accessNames'] = \CCalendar::GetAccessNames();
			}
			else
			{
				$this->addError(new Error('[se06]'.Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error'));
			}
		}

		return $response;
	}

	public function hideExternalCalendarSectionAction()
	{
		$request = $this->getRequest();
		$response = [];
		$id = $request->getPost('id');

		if (!\CCalendar::IsPersonal() && !\CCalendarSect::CanDo('calendar_edit_section', $id, \CCalendar::GetUserId()))
		{
			$this->addError(new Error('[sd02]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
		}

		$section = \CCalendarSect::GetById($id);
		// For exchange we change only calendar name
		if ($section && $section['CAL_DAV_CON'])
		{
			\CCalendarSect::Edit([
				'arFields' => [
					"ID" => $id,
					"ACTIVE" => "N"
				]]);

			// Check if it's last section from connection - remove it
			$sections = \CCalendarSect::GetList(
				['arFilter' => [
					'CAL_DAV_CON' => $section['CAL_DAV_CON'],
					'ACTIVE' => 'Y'
				]]);

			if(!$sections || count($sections) == 0)
			{
				\CCalendar::RemoveConnection(['id' => intval($section['CAL_DAV_CON']), 'del_calendars' => 'Y']);
			}
		}

		return $response;
	}

	public function getTrackingSectionsAction()
	{
		$request = $this->getRequest();
		$codes = $request->get('codes');
		$mode = $request->get('type');

		$users = [];
		if ($mode == 'users')
		{
			$userIds = [];
			$users = \CCalendar::GetDestinationUsers($codes, true);
			foreach($users as $user)
			{
				$userIds[] = $user['ID'];
			}

			$sections = \CCalendarSect::getSuperposedList(['USERS' => $userIds]);
		}
		elseif($mode == 'groups')
		{
			$groupIds = [];
			foreach($codes as $code)
			{
				if (substr($code, 0, 2) === 'SG')
				{
					$groupIds[] = intval(substr($code, 2));
				}
			}

			$sections = \CCalendarSect::getSuperposedList(['GROUPS' => $groupIds]);

			if (Loader::includeModule('socialnetwork'))
			{
				foreach($groupIds as $groupId)
				{
					$groupId = intval($groupId);
					$createDefaultGroupSection = \CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $groupId, "calendar");
					if ($createDefaultGroupSection)
					{
						foreach($sections as $section)
						{
							if (intval($section['OWNER_ID']) === $groupId)
							{
								$createDefaultGroupSection = false;
								break;
							}
						}
					}

					if ($createDefaultGroupSection)
					{
						$sections[] = \CCalendarSect::createDefault([
							'type' => 'group',
							'ownerId' => $groupId
						]);
					}
				}
			}
		}
		else
		{
			$types = [];
			$typesRes = \CCalendarType::GetList();
			foreach($typesRes as $type)
			{
				if ($type['XML_ID'] != 'user' && $type['XML_ID'] !== 'group' && $type['XML_ID'] !== 'location')
				{
					$types[] = $type['XML_ID'];
				}
			}

			$sections = \CCalendarSect::getSuperposedList(['TYPES' => $types]);
		}

		return [
			'users' => $users,
			'sections' => $sections
		];
	}

	public function setTrackingSectionsAction()
	{
		$request = $this->getRequest();
		$codes = $request->get('codes');
		$type = $request->get('type');
		$sections = $request->get('sections');

		$userId = \CCalendar::getCurUserId();
		if ($type === 'users')
		{
			UserSettings::setTrackingUsers($userId, \CCalendar::getDestinationUsers($codes));
		}
		elseif($type === 'groups')
		{
			$groupIds = [];
			foreach($codes as $code)
			{
				if (substr($code, 0, 2) === 'SG')
				{
					$groupIds[] = intval(substr($code, 2));
				}
			}
			UserSettings::setTrackingGroups($userId, $groupIds);
		}
		\CCalendar::setDisplayedSuperposed($userId, $sections);
		return [];
	}

	public function getEditEventSliderAction()
	{
		$request = $this->getRequest();
		$additionalResponseParams = [];
		$uniqueId = 'calendar_edit_slider_'.rand();
		$formType = preg_replace('/[^\d|\w\_]/', '', $request->get('form_type'));
		$entryId = intval($request->get('event_id'));
		$userId = \CCalendar::GetCurUserId();
		$ownerId = intval($request->get('ownerId'));
		$type = $request->get('type');
		$sections = [];

		if ($entryId > 0)
		{
			$fromTs = !empty($_REQUEST['date_from_offset']) ? \CCalendar::Timestamp($_REQUEST['date_from']) - $_REQUEST['date_from_offset'] : \CCalendar::Timestamp($_REQUEST['date_from']);

			$entry = \CCalendarEvent::GetList([
				'arFilter' => [
					"ID" => $entryId,
					"DELETED" => "N",
					"FROM_LIMIT" => \CCalendar::Date($fromTs),
					"TO_LIMIT" => \CCalendar::Date($fromTs)
				],
				'parseRecursion' => true,
				'maxInstanceCount' => 1,
				'preciseLimits' => true,
				'fetchAttendees' => true,
				'checkPermissions' => true,
				'setDefaultLimit' => false
			]);

			if (!$entry || !is_array($entry[0]))
			{
				$entry = \CCalendarEvent::GetList([
					'arFilter' => [
						"ID" => $entryId,
						"DELETED" => "N"
					],
					'parseRecursion' => true,
					'maxInstanceCount' => 1,
					'fetchAttendees' => true,
					'checkPermissions' => true,
					'setDefaultLimit' => false
				]);
			}

			$entry = $entry[0];
		}
		else
		{
			$entry = [];
		}

		if (!$entryId || !empty($entry) && \CCalendarSceleton::CheckBitrix24Limits(array('id' => $uniqueId)))
		{
			if ($entry['ID'] && $entry['IS_MEETING'])
			{
				$selectedUserCodes = $entry['ATTENDEES_CODES'];
			}
			else
			{
				$selectedUserCodes = ['U'.\CCalendar::GetCurUserId()];
			}

			$additionalResponseParams['uniqueId'] = $uniqueId;
			$additionalResponseParams['userId'] = $userId;
			$additionalResponseParams['editorId'] = $uniqueId.'_entry_slider_editor';
			$additionalResponseParams['entry'] = $entry;
			$additionalResponseParams['timezoneList'] = \CCalendar::GetTimezoneList();
			$additionalResponseParams['socnetDestination'] = \CCalendar::GetSocNetDestination($userId, $selectedUserCodes);
			$additionalResponseParams['formSettings'] = \Bitrix\Calendar\UserSettings::getFormSettings($formType);

			if ($type)
			{
				if ($type === 'user' && $ownerId !== $userId || $type !== 'user')
				{
					$sectionList = \CCalendar::GetSectionList([
						'CAL_TYPE' => $type,
						'OWNER_ID' => $ownerId,
						'ACTIVE' => 'Y',
						'checkPermissions' => true,
						'getPermissions' => true
					]);

					foreach($sectionList as $section)
					{
						if ($section['PERM']['edit'] || $section['PERM']['add'])
						{
							$sections[] = $section;
						}
					}
				}
			}
			$sections = array_merge($sections, \CCalendar::getSectionListAvailableForUser($userId, [$entry['SECTION_ID']]));

			$additionalResponseParams['sections'] = [];
			foreach($sections as $section)
			{
				if ($entryId && \CCalendarSect::CanDo('calendar_edit', $section['ID'], $userId)
					||
					!$entryId && \CCalendarSect::CanDo('calendar_add', $section['ID'], $userId))
				{
					$additionalResponseParams['sections'][] = $section;
				}
			}

			$additionalResponseParams['lastSection'] = \CCalendarSect::GetLastUsedSection('user', $userId, $userId);
			$additionalResponseParams['trackingUsersList'] = \Bitrix\Calendar\UserSettings::getTrackingUsers($userId);
			$additionalResponseParams['userSettings'] = \Bitrix\Calendar\UserSettings::get($userId);

			$additionalResponseParams['iblockMeetingRoomList'] = \CCalendar::GetMeetingRoomList();
			$additionalResponseParams['locationFeatureEnabled'] = !\CCalendar::IsBitrix24() ||
		\Bitrix\Bitrix24\Feature::isFeatureEnabled("calendar_location");
			if ($additionalResponseParams['locationFeatureEnabled'])
			{
				$additionalResponseParams['locationList'] = \CCalendarLocation::GetList();
			}

			return new \Bitrix\Main\Engine\Response\Component(
				'bitrix:calendar.edit.slider',
				'',
				[
					'id' => $uniqueId,
					'event' => $entry,
					'formType' => $formType,
					'type' => \CCalendar::GetType(),
					'bIntranet' => \CCalendar::IsIntranetEnabled(),
					'bSocNet' => \CCalendar::IsSocNet(),
					'AVATAR_SIZE' => 21
				],
				$additionalResponseParams
			);
		}
		else
		{
			$this->addError(new Error('[se05] No entry found'));
		}

		return [];
	}

	public function getViewEventSliderAction()
	{
		$request = $this->getRequest();
		$additionalResponseParams = [];
		$uniqueId = 'calendar_view_slider_'.rand();
		$entryId = intval($request->get('entryId'));
		$userId = \CCalendar::GetCurUserId();
		$fromTs = \CCalendar::Timestamp($request->get('dateFrom')) - $request->get('timezoneOffset');

		if ($entryId)
		{
			$entry = \CCalendarEvent::getEventForViewInterface($entryId,
				[
					'eventDate' => \CCalendar::Date($fromTs),
					'userId' => $userId
				]
			);
		}
		else
		{
			$this->addError(new Error('[se06] No \'entryId\' given. No entry found'));
		}

		if ($entry)
		{
			$additionalResponseParams['uniqueId'] = $uniqueId;
			$additionalResponseParams['entry'] = $entry;
			$additionalResponseParams['userIndex'] = \CCalendarEvent::getUserIndex();
			$additionalResponseParams['userSettings'] = \Bitrix\Calendar\UserSettings::get($userId);
			$additionalResponseParams['entryUrl'] = \CHTTP::urlAddParams(
				\CCalendar::GetPath($entry['CAL_TYPE'], $entry['OWNER_ID'], true),
				[
					'EVENT_ID' => $entry['ID'],
					'EVENT_DATE' => $entry['DATE_FROM']
				]);

			$sections = \CCalendarSect::GetList([
				'arFilter' => [
					'ID' => $entry['SECTION_ID'],
					'ACTIVE' => 'Y',
				],
				'checkPermissions' => false,
				'getPermissions' => true
			]);

			$additionalResponseParams['section'] = isset($sections[0]) ? $sections[0] : null;

			return new \Bitrix\Main\Engine\Response\Component(
				'bitrix:calendar.view.slider',
				'',
				[
					'id' => $uniqueId,
					'event' => $entry,
					'type' => \CCalendar::GetType(),
					'sectionName' => $_REQUEST['section_name'],
					'bIntranet' => \CCalendar::IsIntranetEnabled(),
					'bSocNet' => \CCalendar::IsSocNet(),
					'AVATAR_SIZE' => 21
				],
				$additionalResponseParams
			);
		}
		else
		{
			$this->addError(new Error('[se05] No entry found'));
		}

		return [];
	}

	public function getCrmUserfieldAction()
	{
		$request = $this->getRequest();
		$UF = \CCalendarEvent::GetEventUserFields(['PARENT_ID' => intval($request->get('event_id'))]);
		if (isset($UF['UF_CRM_CAL_EVENT']))
		{
			$crmUF = $UF['UF_CRM_CAL_EVENT'];
			$additionalResponseParams = [];
			return new \Bitrix\Main\Engine\Response\Component(
				'bitrix:system.field.edit',
				$crmUF["USER_TYPE"]["USER_TYPE_ID"],
				[
					"bVarsFromForm" => false,
					"arUserField" => $crmUF,
					"form_name" => 'event_edit_form'
				],
				$additionalResponseParams
			);
		}

		return [];
	}

	public function updatePlannerAction()
	{
		$request = $this->getRequest();

		$entryId = intval($request['entryId']);
		if (!$entryId && $request['cur_event_id'])
		{
			$entryId = intval($request['cur_event_id']);
		}

		$userId = \CCalendar::getCurUserId();
		$codes = false;
		if (isset($request['codes']) && is_array($request['codes']))
		{
			$codes = [];
			foreach($request['codes'] as $code)
			{
				if($code && !in_array($code, $codes))
				{
					$codes[] = $code;
				}
			}
			if($request['add_cur_user_to_list'] === 'Y' || count($codes) <= 0)
			{
				$codes[] = 'U'.$userId;
			}
		}

		$dateFrom = isset($request['dateFrom']) ? $request['dateFrom'] : $request['date_from'];
		$dateTo = isset($request['dateTo']) ? $request['dateTo'] : $request['date_to'];

		return \CCalendarPlanner::prepareData([
			'entry_id' => $entryId,
			'user_id' => $userId,
			'codes' => $codes,
			'entries' => $request['entries'],
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'timezone' => $request['timezone'],
			'location' => trim($request['location']),
			'roomEventId' => intval($request['roomEventId'])
		]);
	}

	public function getPlannerAction()
	{
		$request = $this->getRequest();
		//global $APPLICATION;
		//$APPLICATION->ShowAjaxHead();
		\CCalendarPlanner::Init(array('id' => $request['planner_id']));
		return [];
	}

	public function saveLocationListAction()
	{
		$request = $this->getRequest();
		$locationList = $request['locationList'];
		foreach($locationList as $location)
		{
			if ($location['id'] && ($location['deleted'] == 'Y' || $location['name'] === ''))
			{
				\CCalendarLocation::delete($location['id']);
			}
			elseif ((!$location['id'] || $location['changed'] == 'Y') && $location['name'] !== '')
			{
				\CCalendarLocation::update(array(
					'id' => $location['id'],
					'name' => $location['name']
				));
			}
		}
		\CCalendarLocation::clearCache();

		return ['locationList' => \CCalendarLocation::getList()];
	}

	public function deleteCalendarEntryAction($entryId, $recursionMode)
	{
		$response = [];

		$response['result'] = \CCalendar::deleteEvent($entryId, true, array('recursionMode' => $recursionMode));

		if ($response['result'] !== true)
		{
			$this->addError(new Error('[ed01]'.Loc::getMessage('EC_EVENT_DEL_ERROR'), 'delete_entry_error'));
		}

		return $response;
	}

	public function changeRecurciveEntryUntilAction($entryId, $untilDate)
	{
		$response = ['result' => false];

		$event = \CCalendarEvent::GetById(intval($entryId));
		$untilTimestamp = \CCalendar::Timestamp($untilDate);
		$recId = false;

		if ($event)
		{
			if (\CCalendarEvent::CheckRecurcion($event))
			{
				$event['RRULE'] = \CCalendarEvent::ParseRRULE($event['RRULE']);
				$event['RRULE']['UNTIL'] = \CCalendar::Date($untilTimestamp, false);
				if (isset($event['RRULE']['COUNT']))
				{
					unset($event['RRULE']['COUNT']);
				}

				$id = \CCalendar::SaveEvent(array(
					'arFields' => array(
						"ID" => $event["ID"],
						"RRULE" => $event['RRULE']
					),
					'silentErrorMode' => false,
					'recursionEditMode' => 'skip',
					'editParentEvents' => true,
				));
				$recId = $event["ID"];
				$response['id'] = $id;
			}

			if($event["RECURRENCE_ID"] > 0)
			{
				$recParentEvent = \CCalendarEvent::GetById($event["RECURRENCE_ID"]);
				if ($recParentEvent && \CCalendarEvent::CheckRecurcion($recParentEvent))
				{
					$recParentEvent['RRULE'] = \CCalendarEvent::ParseRRULE($recParentEvent['RRULE']);

					if ($recParentEvent['RRULE']['UNTIL']
						&& \CCalendar::Timestamp($recParentEvent['RRULE']['UNTIL']) > $untilTimestamp)
					{
						$recParentEvent['RRULE']['UNTIL'] = \CCalendar::Date($untilTimestamp, false);

						if (isset($recParentEvent['RRULE']['COUNT']))
						{
							unset($recParentEvent['RRULE']['COUNT']);
						}

						$id = \CCalendar::SaveEvent(array(
							'arFields' => array(
								"ID" => $recParentEvent["ID"],
								"RRULE" => $recParentEvent['RRULE']
							),
							'silentErrorMode' => false,
							'recursionEditMode' => 'skip',
							'editParentEvents' => true,
						));
						$response['id'] = $id;
					}
				}

				$recId = $event["RECURRENCE_ID"];
			}

			if ($recId)
			{
				$recRelatedEvents = \CCalendarEvent::GetEventsByRecId($recId, false);
				foreach($recRelatedEvents as $ev)
				{
					if(\CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp)
					{
						\CCalendar::DeleteEvent(intval($ev['ID']), true, array('recursionMode' => 'this'));
					}
				}
			}

			$response['result'] = true;
		}

		if ($response['result'] !== true)
		{
			$this->addError(new Error('[ed01]'.Loc::getMessage('EC_EVENT_DEL_ERROR'), 'change_recurcive_entry_until'));
		}

		return $response;
	}

	public function excludeRecursionDateAction($entryId, $excludeDate)
	{
		$response = [];
		\CCalendarEvent::ExcludeInstance(intval($entryId), $excludeDate);
		return $response;
	}

	public function editEntryAction()
	{
		$response = [];
		$request = $this->getRequest();

		$id = intval($request['id']);
		$sectionId = intval($request['section']);
		$userId = \CCalendar::getCurUserId();

		if (!$id && !\CCalendarSect::CanDo('calendar_add', $sectionId, $userId)
			||
			$id && !\CCalendarSect::CanDo('calendar_edit', $sectionId, $userId))
		{
			$this->addError(new Error('[ee01]'.Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_access_denied'));
		}

		if(empty($this->getErrors()))
		{
			$sectionList = Internals\SectionTable::getList(
				array(
					"filter" => array(
						"=ACTIVE" => 'Y',
						"=ID" => $sectionId
					),
					"select" => array("ID", "CAL_TYPE", "OWNER_ID", "NAME")
				)
			);

			if (!($section = $sectionList->fetch()))
			{
				$this->addError(new Error('[ee02]'.Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
			}

			if(empty($this->getErrors()))
			{
				// Default name for events
				$name = trim($request['name']);
				if(empty($name))
				{
					$name = Loc::getMessage('EC_DEFAULT_EVENT_NAME');
				}
				$remind = \CCalendarReminder::prepareReminder($request['reminder']);

				$rrule = $request['EVENT_RRULE'];
				if (!isset($rrule['INTERVAL']) && $rrule['FREQ'] !== 'NONE')
				{
					$rrule['INTERVAL'] = 1;
				}
				if($request['rrule_endson'] === 'never')
				{
					unset($rrule['COUNT']);
					unset($rrule['UNTIL']);
				}
				elseif($request['rrule_endson'] === 'count')
				{
					if(intval($rrule['COUNT']) <= 0)
						$rrule['COUNT'] = 10;
					unset($rrule['UNTIL']);
				}
				elseif($request['rrule_endson'] === 'until')
				{
					unset($rrule['COUNT']);
				}

				// Date & Time
				$dateFrom = $request['date_from'];
				$dateTo = $request['date_to'];
				$skipTime = isset($request['skip_time']) && $request['skip_time'] == 'Y';
				if(!$skipTime)
				{
					$dateFrom .= ' '.$request['time_from'];
					$dateTo .= ' '.$request['time_to'];
				}
				$dateFrom = trim($dateFrom);
				$dateTo = trim($dateTo);

				// Timezone
				$tzFrom = $request['tz_from'];
				$tzTo = $request['tz_to'];
				if(!$tzFrom && isset($request['default_tz']))
				{
					$tzFrom = $request['default_tz'];
				}
				if(!$tzTo && isset($request['default_tz']))
				{
					$tzTo = $request['default_tz'];
				}

				if(isset($request['default_tz']) && $request['default_tz'] != '')
				{
					\CCalendar::SaveUserTimezoneName(CCalendar::GetUserId(), $request['default_tz']);
				}

				$entryFields = [
					"ID" => $id,
					"DATE_FROM" => $dateFrom,
					"DATE_TO" => $dateTo,
					"SKIP_TIME" => $skipTime,
					'TZ_FROM' => $tzFrom,
					'TZ_TO' => $tzTo,
					'NAME' => $name,
					'DESCRIPTION' => trim($request['desc']),
					'SECTIONS' => [$sectionId],
					'COLOR' => $request['color'],
					'ACCESSIBILITY' => $request['accessibility'],
					'IMPORTANCE' => isset($request['importance']) ? $request['importance'] : 'normal',
					'PRIVATE_EVENT' => $request['private_event'] === 'Y',
					'RRULE' => $rrule,
					'LOCATION' => $request['location'],
					"REMIND" => $remind,
					"IS_MEETING" => !!$request['is_meeting'],
					"SECTION_CAL_TYPE" => $section['CAL_TYPE'],
					"SECTION_OWNER_ID" => $section['OWNER_ID']
				];

				$accessCodes = [];

				if(isset($request['EVENT_DESTINATION']) && is_array($request['EVENT_DESTINATION']))
				{
					foreach($request["EVENT_DESTINATION"] as $v => $k)
					{
						if(strlen($v) > 0 && is_array($k) && !empty($k))
						{
							foreach($k as $vv)
							{
								if(strlen($vv) > 0)
								{
									$accessCodes[] = $vv;
								}
							}
						}
					}
				}

				if(empty($accessCodes))
				{
					$accessCodes[] = 'U'.\CCalendar::GetUserId();
				}
				$accessCodes = array_unique($accessCodes);
				$entryFields['IS_MEETING'] = !empty($accessCodes) && $accessCodes != ['U'.\CCalendar::GetUserId()];

				if($entryFields['IS_MEETING'])
				{
					$entryFields['ATTENDEES_CODES'] = $accessCodes;
					$entryFields['ATTENDEES'] = \CCalendar::GetDestinationUsers($accessCodes);
					$response['reload'] = true;
				}

				if($request['exclude_users'] && count($entryFields['ATTENDEES']) > 0)
				{
					$excludeUsers = explode(",", $request['exclude_users']);
					$entryFields['ATTENDEES_CODES'] = [];

					if(count($excludeUsers) > 0)
					{
						$entryFields['ATTENDEES'] = array_diff($entryFields['ATTENDEES'], $excludeUsers);
						foreach($entryFields['ATTENDEES'] as $attendee)
						{
							$entryFields['ATTENDEES_CODES'][] = 'U'.intval($attendee);
						}
					}
				}

				if(\CCalendar::GetType() == 'user' && \CCalendar::GetOwnerId() != \CCalendar::GetUserId())
				{
					$entryFields['MEETING_HOST'] = \CCalendar::GetOwnerId();
				}
				else
				{
					$entryFields['MEETING_HOST'] = \CCalendar::GetUserId();
				}

				$entryFields['MEETING'] = array(
					'HOST_NAME' => \CCalendar::GetUserName($entryFields['MEETING_HOST']),
					'NOTIFY' => $request['meeting_notify'] === 'Y',
					'REINVITE' => $request['meeting_reinvite'] === 'Y',
					'ALLOW_INVITE' => $request['allow_invite'] === 'Y',
					'MEETING_CREATOR' => \CCalendar::GetUserId()
				);

				if (!\CCalendarLocation::checkAccessibility($entryFields['LOCATION'], ['fields' => $entryFields]))
				{
					$this->addError(new Error(Loc::getMessage('EC_LOCATION_BUSY'), 'edit_entry_location_busy'));
				}

				if($entryFields['IS_MEETING'])
				{
					$usersToCheck = [];
					foreach ($entryFields['ATTENDEES'] as $attId)
					{
						if (intval($attId) !== \CCalendar::GetUserId())
						{
							$userSettings = \Bitrix\Calendar\UserSettings::get(intval($attId));
							if($userSettings && $userSettings['denyBusyInvitation'])
							{
								$usersToCheck[] = intval($attId);
							}
						}
					}

					if (count($usersToCheck) > 0)
					{
						$fromTs = \CCalendar::Timestamp($dateFrom);
						$toTs = \CCalendar::Timestamp($dateTo);
						$fromTs = $fromTs - \CCalendar::GetTimezoneOffset($tzFrom, $fromTs);
						$toTs = $toTs - \CCalendar::GetTimezoneOffset($tzTo, $toTs);

						$accessibility = \CCalendar::GetAccessibilityForUsers(array(
							'users' => $usersToCheck,
							'from' => \CCalendar::Date($fromTs, false), // date or datetime in UTC
							'to' => \CCalendar::Date($toTs, false), // date or datetime in UTC
							'curEventId' => $id,
							'getFromHR' => true,
							'checkPermissions' => false
						));

						$busyUsersList = [];
						foreach($accessibility as $accUserId => $entries)
						{
							foreach($entries as $entry)
							{
								$entFromTs = \CCalendar::Timestamp($entry["DATE_FROM"]);
								$entToTs = \CCalendar::Timestamp($entry["DATE_TO"]);

								if ($entry["DT_SKIP_TIME"] === 'Y')
								{
									$entToTs += \CCalendar::GetDayLen();
								}

								$entFromTs -= \CCalendar::GetTimezoneOffset($entry['TZ_FROM'], $entFromTs);
								$entToTs -= \CCalendar::GetTimezoneOffset($entry['TZ_TO'], $entToTs);

								if ($entFromTs < $toTs && $entToTs > $fromTs)
								{
									$busyUsersList[] = $accUserId;
									$this->addError(new Error(Loc::getMessage('EC_USER_BUSY', ["#USER#" => \CCalendar::GetUserName($accUserId)]), 'edit_entry_user_busy'));
									break;
								}
							}
						}


						if (count($busyUsersList) > 0)
						{
							$response['busyUsersList'] = \CCalendarEvent::getUsersDetails($busyUsersList);
						}
					}
				}

				// Userfields for event
				$arUFFields = [];
				foreach($request as $field => $value)
				{
					if(substr($field, 0, 3) == "UF_")
					{
						$arUFFields[$field] = $value;
					}
				}

				if(empty($this->getErrors()))
				{
					$newId = \CCalendar::SaveEvent([
						'arFields' => $entryFields,
						'UF' => $arUFFields,
						'silentErrorMode' => false,
						'recursionEditMode' => $request['rec_edit_mode'],
						'currentEventDateFrom' => \CCalendar::Date(\CCalendar::Timestamp($request['current_date_from']), false)
					]);

					$errors = \CCalendar::GetErrors();
					$eventList = [];
					$eventIdList = [$newId];

					if($newId && !count($errors))
					{
						$response['entryId'] = $newId;

						$filter = [
							"ID" => $newId,
							"FROM_LIMIT" => \CCalendar::Date(
									\CCalendar::Timestamp($entryFields["DATE_FROM"]) -
									\CCalendar::DAY_LENGTH * 10, false),
							"TO_LIMIT" => \CCalendar::Date(
									\CCalendar::Timestamp($entryFields["DATE_TO"]) +
									\CCalendar::DAY_LENGTH * 90, false)
						];

						$eventList = \CCalendarEvent::GetList([
							'arFilter' => $filter,
							'parseRecursion' => true,
							'fetchAttendees' => true,
							'userId' => \CCalendar::GetUserId()
						]);

						if($entryFields['IS_MEETING'])
						{
							\Bitrix\Main\FinderDestTable::merge(
								[
									"CONTEXT" => "CALENDAR",
									"CODE" => \Bitrix\Main\FinderDestTable::convertRights(
										$accessCodes,
										['U'.\CCalendar::GetUserId()]
									)
								]
							);
						}

						if(in_array($_REQUEST['rec_edit_mode'], ['this', 'next']))
						{
							unset($filter['ID']);
							$filter['RECURRENCE_ID'] = ($eventList && $eventList[0] && $eventList[0]['RECURRENCE_ID']) ? $eventList[0]['RECURRENCE_ID'] : $newId;

							$resRelatedEvents = \CCalendarEvent::GetList([
								'arFilter' => $filter,
								'parseRecursion' => true,
								'fetchAttendees' => true,
								'userId' => \CCalendar::GetUserId()
							]);

							foreach($resRelatedEvents as $ev)
							{
								$eventIdList[] = $ev['ID'];
							}
							$eventList = array_merge($eventList, $resRelatedEvents);
						}
						elseif($id && $eventList && $eventList[0] && \CCalendarEvent::CheckRecurcion($eventList[0]))
						{
							$recId = $eventList[0]['RECURRENCE_ID']
								? $eventList[0]['RECURRENCE_ID']
								: $eventList[0]['ID'];

							if($eventList[0]['RECURRENCE_ID'] && $eventList[0]['RECURRENCE_ID'] !== $eventList[0]['ID'])
							{
								unset($filter['RECURRENCE_ID']);
								$filter['ID'] = $eventList[0]['RECURRENCE_ID'];
								$resRelatedEvents = \CCalendarEvent::GetList([
									'arFilter' => $filter,
									'parseRecursion' => true,
									'fetchAttendees' => true,
									'userId' => \CCalendar::GetUserId()
								]);
								$eventIdList[] = $eventList[0]['RECURRENCE_ID'];
								$eventList = array_merge($eventList, $resRelatedEvents);
							}
							$name = trim($request['name']);

							if($recId)
							{
								unset($filter['ID']);
								$filter['RECURRENCE_ID'] = $recId;
								$resRelatedEvents = \CCalendarEvent::GetList([
									'arFilter' => $filter,
									'parseRecursion' => true,
									'fetchAttendees' => true,
									'userId' => \CCalendar::GetUserId()
								]);

								foreach($resRelatedEvents as $ev)
								{
									$eventIdList[] = $ev['ID'];
								}
								$eventList = array_merge($eventList, $resRelatedEvents);
							}
						}
					}
					$response['eventList'] = $eventList;
					$response['eventIdList'] = $eventIdList;

					$userSettings = \Bitrix\Calendar\UserSettings::get($userId);
					$userSettings['lastUsedSection'] = $sectionId;
					\Bitrix\Calendar\UserSettings::set($userSettings, $userId);
				}
			}
		}

		return $response;
	}

	public function deleteCalendarSectionAction()
	{
		$request = $this->getRequest();
		$response = [];
		$id = $request->getPost('id');

		if (!\CCalendar::IsPersonal() && !\CCalendarSect::CanDo('calendar_edit_section', $id, \CCalendar::GetUserId()))
		{
			$this->addError(new Error('[sd01]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
		}
		else
		{
			\CCalendar::DeleteSection($id);
		}

		return $response;
	}

	public function setMeetingStatusAction()
	{
		$request = $this->getRequest();
		$response = [];

		\CCalendarEvent::SetMeetingStatusEx([
			'attendeeId' => \CCalendar::getCurUserId(),
			'eventId' => intVal($request->getPost('entryId')),
			'parentId' => intVal($request->getPost('entryParentId')),
			'status' => $request->getPost('status'),
			'reccurentMode' => $request->getPost('recursionMode'),
			'currentDateFrom' => $request->getPost('currentDateDrom')
		]);
		return $response;
	}

	public function updateRemindersAction()
	{
		$request = $this->getRequest();
		$response = [];
		$entryId = intVal($request->getPost('entryId'));
		$userId = \CCalendar::GetUserId();
		$entry = \CCalendarEvent::GetById($entryId);

		if (\CCalendarSect::CanDo('calendar_edit', $entry['SECTION_ID'], $userId))
		{
			$entry['REMIND'] = \CCalendarReminder::prepareReminder($request->getPost('reminders'));
			$response['id'] = \CCalendar::SaveEvent([
				'arFields' => [
					'ID' => $entry['ID'],
					'REMIND' => \CCalendarReminder::prepareReminder($request->getPost('reminders'))
				]
			]);

			\CCalendar::ClearCache('event_list');
		}

		return $response;
	}


	public function sendAnalyticsLabelAction()
	{
		return null;
	}
}