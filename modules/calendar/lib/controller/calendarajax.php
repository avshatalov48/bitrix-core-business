<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Calendar\Util;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Ui\CountersManager;

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
					Authentication::class
				]
			],
			'handleMobileSync' => [
				'-prefilters' => [
					Authentication::class,
					Csrf::class
				]
			],
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
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$request = $this->getRequest();
		$response = [];

		$id = $request->getPost('id');
		$isNew = (!isset($id) || !$id);
		$type = $request->getPost('type');
		$ownerId = (int)$request->getPost('ownerId');
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
			'ACCESS' => $request->getPost('access'),
			'EXTERNAL_TYPE' => $request->getPost('external_type') ?? 'local',
		];

		if ($customization && !$isNew)
		{
			UserSettings::setSectionCustomization($userId, [$id => ['name' => $name, 'color' => $color]]);
		}
		else
		{
			if (Loader::includeModule('extranet'))
			{
				if (!\CExtranet::IsIntranetUser(SITE_ID, $userId))
				{
					if (
						$type === 'group'
						&& Loader::includeModule('socialnetwork')
					)
					{
						$r = \Bitrix\Socialnetwork\UserToGroupTable::getList([
							'filter' => [
								'@ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember(),
								'=GROUP_ID' => $ownerId,
								'=USER_ID' => $userId,
							],
						]);

						if (!$group = $r->Fetch())
						{
							$this->addError(
								new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_extranet_01')
							);
						}
					}
					else
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_extranet_02')
						);
					}
				}
			}

			if ($isNew) // For new sections
			{
				if ($type === 'group')
				{
					// It's for groups
					if (!\CCalendarType::CanDo('calendar_type_edit_section', 'group'))
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_01')
						);
					}
				}
				else if ($type === 'user')
				{
					if (!$isPersonal)
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_02')
						);
					}
				}
				else // other types
				{
					if (!\CCalendarType::CanDo('calendar_type_edit_section', $type))
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_03')
						);
					}
				}

				$fields['IS_EXCHANGE'] = $request->getPost('is_exchange') == 'Y';
			}
			else
			{
				$section = \CCalendarSect::GetById($id);
				if (!$section && !$isPersonal && !\CCalendarSect::CanDo('calendar_edit_section', $id, $userId))
				{
					$this->addError(
						new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_04')
					);
				}

				$fields['CAL_TYPE'] = $section['CAL_TYPE'];
				$fields['OWNER_ID'] = $section['OWNER_ID'];
			}

			if (empty($this->getErrors()))
			{
				$id = \CCalendar::SaveSection(['arFields' => $fields]);
				if ((int)$id > 0)
				{
					\CCalendarSect::SetClearOperationCache(true);
					$response['section'] = \CCalendarSect::GetById($id, true, true);
					if (!$response['section'])
					{
						$this->addError(
							new Error(Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error_05')
						);
					}
					$response['accessNames'] = \CCalendar::GetAccessNames();

					$response['sectionList'] = \CCalendar::getSectionList(
						[
							'CAL_TYPE' => $type,
							'OWNER_ID' => $ownerId,
							'ACTIVE' => 'Y',
							'ADDITIONAL_IDS' => UserSettings::getFollowedSectionIdList($userId),
							'checkPermissions' => true,
							'getPermissions' => true,
							'getImages' => true
						]
					);
				}
				else
				{
					$this->addError(
						new Error(Loc::getMessage('EC_CALENDAR_SAVE_ERROR'), 'saving_error_06')
					);
				}
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
					'ID' => $id,
					'ACTIVE' => 'N'
				]
            ]);

			// Check if it's last section from connection - remove it
			$sections = \CCalendarSect::GetList([
				'arFilter' => [
					'CAL_DAV_CON' => $section['CAL_DAV_CON'],
					'ACTIVE' => 'Y'
				]
			]);

			if (empty($sections))
			{
				\CCalendar::RemoveConnection(['id' => (int) $section['CAL_DAV_CON'], 'del_calendars' => 'Y']);
			}
		}

		return $response;
	}
	
	public function getAllSectionsForGoogleAction()
	{
		$type = 'user';
		$ownerId = \CCalendar::GetUserId();
		$response = [];
		
		$sections = \CCalendar::GetSectionList([
		    'CAL_TYPE' => $type,
		    'OWNER_ID' => $ownerId,
		    'checkPermissions' => true,
		    'getPermissions' => true,
		    'getImages' => true
		]);
		foreach ($sections as $section)
		{
			if (
				$section['GAPI_CALENDAR_ID']
				&& $section['CAL_DAV_CON']
				&& $section['EXTERNAL_TYPE'] !== 'local'
			)
			{
				$response[] = $section;
			}
		}
		
		return $response;
	}

	public function getTrackingSectionsAction()
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}

		$request = $this->getRequest();
		$mode = $request->get('type');

		$users = [];
		if ($mode === 'users')
		{
			$userIds = $request->get('userIdList');
			$ormRes = \Bitrix\Main\UserTable::getList([
				'filter' => ['=ID' => $userIds],
				'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME']
			]);
			while ($user = $ormRes->fetch())
			{
				$user['FORMATTED_NAME'] = \CCalendar::GetUserName($user);
				$users[] = $user;
			}

			$sections = \CCalendarSect::getSuperposedList(['USERS' => $userIds]);
		}
		elseif ($mode === 'groups')
		{
			$groupIds = $request->get('groupIdList');
			$sections = \CCalendarSect::getSuperposedList(['GROUPS' => $groupIds]);

			if (Loader::includeModule('socialnetwork'))
			{
				foreach($groupIds as $groupId)
				{
					$groupId = (int)$groupId;
					$createDefaultGroupSection = \CSocNetFeatures::isActiveFeature(
						SONET_ENTITY_GROUP,
						$groupId,
						"calendar"
					);

					if ($createDefaultGroupSection)
					{
						foreach($sections as $section)
						{
							if ((int)$section['OWNER_ID'] === $groupId)
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
				if ($type['XML_ID'] !== 'user'
					&& $type['XML_ID'] !== 'group'
					&& $type['XML_ID'] !== 'location')
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
		$type = $request->get('type');

		$userId = \CCalendar::getCurUserId();
		if ($type === 'users')
		{
			UserSettings::setTrackingUsers($userId, $request->get('userIdList'));
		}
		elseif ($type === 'groups')
		{
			$codes = $request->get('codes');
			$groupIds = [];
			foreach($codes as $code)
			{
				if (mb_substr($code, 0, 2) === 'SG')
				{
					$groupIds[] = intval(mb_substr($code, 2));
				}
			}
			UserSettings::setTrackingGroups($userId, $groupIds);
		}
		$sections = $request->get('sections');
		if (!$sections)
		{
			$sections = [];
		}

		\CCalendar::setDisplayedSuperposed($userId, $sections);
		return [];
	}

	public function getEditEventSliderAction()
	{
		$request = $this->getRequest();
		$responseParams = [];
		$uniqueId = 'calendar_edit_slider_'.rand();
		$formType = preg_replace('/[^\d|\w\_]/', '', $request->get('form_type'));
		$entryId = (int)$request->get('event_id');
		$userCodes = $request->get('userCodes');
		$userId = \CCalendar::GetCurUserId();
		$ownerId = (int)$request->get('ownerId');
		$type = $request->get('type');
		$sections = [];

		if ($entryId > 0)
		{
			$fromTs = !empty($_REQUEST['date_from_offset']) ? \CCalendar::Timestamp($_REQUEST['date_from']) - $_REQUEST['date_from_offset'] : \CCalendar::Timestamp($_REQUEST['date_from']);
			$entry = \CCalendarEvent::getEventForEditInterface($entryId, ['eventDate' => \CCalendar::Date($fromTs)]);
			$entryId = is_array($entry['ID']) && isset($entry['ID']) ? $entry['ID'] : $entryId;
		}
		else
		{
			$entry = [];
		}

		if (!$entryId || !empty($entry) && \CCalendarSceleton::CheckBitrix24Limits(array('id' => $uniqueId)))
		{
			$responseParams['uniqueId'] = $uniqueId;
			$responseParams['userId'] = $userId;
			$responseParams['editorId'] = $uniqueId.'_entry_slider_editor';
			$responseParams['entry'] = $entry;
			$responseParams['timezoneList'] = \CCalendar::GetTimezoneList();
			$responseParams['formSettings'] = UserSettings::getFormSettings($formType);

			if ($type)
			{
				if ($type === 'user' && $ownerId !== $userId || $type !== 'user')
				{
					$sectionList = \CCalendar::getSectionList([
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

				if (empty($sections) && $type === 'group')
				{
					$sections[] = \CCalendarSect::createDefault(array(
						'type' => $type,
						'ownerId' => $ownerId
					));
				}
			}
			$sections = array_merge($sections, \CCalendar::getSectionListAvailableForUser($userId, [$entry['SECTION_ID']]));

			$responseParams['sections'] = [];
			foreach($sections as $section)
			{
				if (!\CCalendarSect::CheckGoogleVirtualSection($section['GAPI_CALENDAR_ID'], $section['EXTERNAL_TYPE'])
					&&
					(
						($entryId && \CCalendarSect::CanDo('calendar_edit', $section['ID'], $userId))
						|| (!$entryId && \CCalendarSect::CanDo('calendar_add', $section['ID'], $userId))
					)
				)
				{
					$responseParams['sections'][] = $section;
				}
			}

			$responseParams['dayOfWeekMonthFormat'] = \Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getDayOfWeekMonthFormat();
			$responseParams['trackingUsersList'] = UserSettings::getTrackingUsers($userId);
			$responseParams['userSettings'] = UserSettings::get($userId);
			$responseParams['eventWithEmailGuestLimit'] = Bitrix24Manager::getEventWithEmailGuestLimit();
			$responseParams['countEventWithEmailGuestAmount'] = Bitrix24Manager::getCountEventWithEmailGuestAmount();
			$responseParams['iblockMeetingRoomList'] = Rooms\IBlockMeetingRoom::getMeetingRoomList();
			$responseParams['userIndex'] = \CCalendarEvent::getUserIndex();
			$responseParams['locationFeatureEnabled'] = Bitrix24Manager::isFeatureEnabled("calendar_location");
			if ($responseParams['locationFeatureEnabled'])
			{
				$responseParams['locationList'] = Rooms\Manager::getRoomsList();
				$responseParams['locationAccess'] = \CCalendarType::CanDo('calendar_type_edit', 'location');
			}
			$responseParams['plannerFeatureEnabled'] = Bitrix24Manager::isPlannerFeatureEnabled();
			$responseParams['attendeesEntityList'] = ($entryId > 0 && !empty($entry['attendeesEntityList']))
				? $entry['attendeesEntityList']
				: Util::getDefaultEntityList($userId, $type, $ownerId);

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
					'AVATAR_SIZE' => 21,
					'ATTENDEES_CODES' => $userCodes
				],
				$responseParams
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
		$responseParams = [];
		$uniqueId = 'calendar_view_slider_'.rand();
		$entryId = (int)$request->get('entryId');
		$userId = \CCalendar::GetCurUserId();
		$entry = null;

		if ($entryId)
		{
			$entry = \CCalendarEvent::getEventForViewInterface($entryId,
				[
					'eventDate' => $request->get('dateFrom'),
					'timezoneOffset' => (int)$request->get('timezoneOffset'),
					'userId' => $userId
				]
			);
		}
		else
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
		}

		if ($entry)
		{
			$responseParams['uniqueId'] = $uniqueId;
			$responseParams['userId'] = $userId;
			$responseParams['userTimezone'] = \CCalendar::GetUserTimezoneName($userId);
			$responseParams['entry'] = $entry;
			$responseParams['userIndex'] = \CCalendarEvent::getUserIndex();
			$responseParams['userSettings'] = UserSettings::get($userId);
			$responseParams['plannerFeatureEnabled'] = Bitrix24Manager::isPlannerFeatureEnabled();
			$responseParams['entryUrl'] = \CHTTP::urlAddParams(
				\CCalendar::GetPath($entry['CAL_TYPE'], $entry['OWNER_ID'], true),
				[
					'EVENT_ID' => (int)$entry['ID'],
					'EVENT_DATE' => urlencode($entry['DATE_FROM'])
				]);
			$responseParams['dayOfWeekMonthFormat'] = \Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getDayOfWeekMonthFormat();

			$sections = \CCalendarSect::GetList([
				'arFilter' => [
					'ID' => $entry['SECTION_ID'],
					'ACTIVE' => 'Y',
				],
				'checkPermissions' => false,
				'getPermissions' => true
			]);

			$responseParams['section'] = isset($sections[0]) ? $sections[0] : null;

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
				$responseParams
			);
		}
		else
		{
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_02'));
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
		$entryId = (int)$request['entryId'];
		$userId = \CCalendar::getCurUserId();
		$ownerId = (int)$request['ownerId'];
		$type = $request['type'];
		$entries = $request['entries'];
		$isExtranetUser = Util::isExtranetUser($userId);

		$hostId = (int)$request['hostId'];
		if (!$hostId && $type === 'user' && !$entryId)
		{
			$hostId = $ownerId;
		}

		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser($userId) && !$isExtranetUser)
			{
				$this->addError(new Error('[up01]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
				return [];
			}
		}

		if ($isExtranetUser)
		{
			$entries = \CExtranet::getMyGroupsUsersSimple(\CExtranet::GetExtranetSiteID());
		}

		if (!$entryId && $request['cur_event_id'])
		{
			$entryId = (int)$request['cur_event_id'];
		}

		$codes = [];
		if (isset($request['entityList']) && is_array($request['entityList']))
		{
			$codes = Util::convertEntitiesToCodes($request['entityList']);
		}
		elseif (isset($request['codes']) && is_array($request['codes']))
		{
			$codes = $request['codes'];
		}
		if ($request['add_cur_user_to_list'] === 'Y' || count($codes) == 0)
		{
			$codes[] = 'U'.$userId;
		}

		$prevUserList = is_array($request['prevUserList']) ? $request['prevUserList'] : [];

		$dateFrom = isset($request['dateFrom']) ? $request['dateFrom'] : $request['date_from'];
		$dateTo = isset($request['dateTo']) ? $request['dateTo'] : $request['date_to'];

		return \CCalendarPlanner::prepareData([
			'entry_id' => $entryId,
			'user_id' => $userId,
			'host_id' => $hostId,
			'codes' => $codes,
			'entryLocation' => trim($request['entryLocation']),
			'entries' => $entries,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'timezone' => $request['timezone'],
			'location' => trim($request['location']),
			'roomEventId' => (int)$request['roomEventId'],
			'initPullWatches' => true,
			'prevUserList' => $prevUserList
		]);
	}

	public function getPlannerAction()
	{
		$request = $this->getRequest();
		\CCalendarPlanner::Init(array('id' => $request['planner_id']));
		return [];
	}

	public function deleteCalendarEntryAction($entryId, $recursionMode, $requestUid)
	{
		$response = [];

		$response['result'] = \CCalendar::deleteEvent(
			$entryId,
			true,
			[
				'recursionMode' => $recursionMode,
				'requestUid' => (int)$requestUid
			]
		);

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

			if ($event["RECURRENCE_ID"] > 0)
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
					if (\CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp)
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


	public function deleteCalendarSectionAction()
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
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
		$userId = \CCalendar::GetCurUserId();
		$request = $this->getRequest();
		$response = [];

		\CCalendarEvent::SetMeetingStatusEx([
			'attendeeId' => $userId,
			'eventId' => (int)$request->getPost('entryId'),
			'parentId' => (int)$request->getPost('entryParentId'),
			'status' => $request->getPost('status'),
			'reccurentMode' => $request->getPost('recursionMode'),
			'currentDateFrom' => $request->getPost('currentDateFrom')
		]);

		\CCalendar::UpdateCounter([$userId]);
		$response['counters'] = CountersManager::getValues($userId);

		return $response;
	}

	public function updateRemindersAction()
	{
		$request = $this->getRequest();
		$response = [];
		$entryId = intval($request->getPost('entryId'));
		$userId = \CCalendar::GetUserId();
		$entry = \CCalendarEvent::GetById($entryId);

		if (\CCalendarSect::CanDo('calendar_edit', $entry['SECTION_ID'], $userId))
		{
			$entry['REMIND'] = \CCalendarReminder::prepareReminder($request->getPost('reminders'));
			$response['REMIND'] = $entry['REMIND'];
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

	public function getSyncInfoAction()
	{
		$params = [];
		$request = $this->getRequest();
		$params['type'] = $request->getPost('type');
		$params['userId'] = \CCalendar::getCurUserId();

		return \CCalendarSync::GetSyncInfo($params);
	}

	public function handleMobileSyncAction()
	{
		$request = $this->getRequest();
		$params = [
			'userId' => intval($request['userId'])
		];

		if (\CCalendarSync::checkSign($request['sign'], $params))
		{


		}
		else
		{
			$this->addError(new Error('Access denied. Unsigned parameters detected', 'sign_fault'));
		}
		return true;
	}

	public function removeConnectionAction($connectionId, $removeCalendars)
	{
		\CCalendar::setOwnerId(\CCalendar::getCurUserId());
		\CCalendar::RemoveConnection(['id' => (int)$connectionId, 'del_calendars' => $removeCalendars === 'Y']);

		return true;
	}

	public function setSectionStatusAction()
	{
		$attestedSectionsStatus = [];
		$request = $this->getRequest();
		$sectionsStatus = $request['sectionStatus'];
		$userId = \CCalendar::getCurUserId();

		foreach ($sectionsStatus as $sectionId => $sectionStatus)
		{
			$sectionStatus = json_decode($sectionStatus);
			if (is_int($sectionId) && is_bool($sectionStatus))
			{
				$attestedSectionsStatus[$sectionId] = $sectionStatus;
			}
		}

		if ($attestedSectionsStatus && is_int($userId) && $userId > 0)
		{
			\CCalendarSync::SetSectionStatus($userId, $attestedSectionsStatus);
			return true;
		}

		return false;

	}

	/**
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function addConnectionAction(): void
	{
		$request = $this->getRequest();
		$params['user_id'] = \CCalendar::getCurUserId();
		$params['user_name'] = $request['userName'];
		$params['name'] = $request['name'];
		$params['link'] = $request['server'];
		$params['pass'] = $request['pass'];

		foreach ($params as $parameter)
		{
			if ($parameter === '')
			{
				$this->addError(new Error(Loc::getMessage('EC_CALDAV_URL_ERROR'), 'incorrect_parameters'));
				break;
			}
		}

		if (Loader::IncludeModule('dav'))
		{
			$res = \CCalendar::AddConnection($params);

			if ($res === true)
			{
				\CDavGroupdavClientCalendar::DataSync("user", $params['userId']);
			}
			else
			{
				$this->addError(new Error($res, 'incorrect_parameters'));
			}
		}
	}

	public function editConnectionAction()
	{

	}

	public function updateConnectionAction()
	{
		$params = [];
		$request = $this->getRequest();
		$params['type'] = $request->getPost('type');
		$params['userId'] = \CCalendar::getCurUserId();
		$requestUid = $request->getPost('requestUid');
		if (!empty($requestUid))
		{
			Util::setRequestUid($requestUid);
		}

		\CCalendarSync::UpdateUserConnections();

		Util::setRequestUid();

		return \CCalendarSync::GetSyncInfo($params);
	}

	public function sendAnalyticsLabelAction()
	{
		return null;
	}

	public function getAuthLinkAction()
	{
		$type = $this->getRequest()->getPost('type');
		$type = in_array($type, ['slider', 'banner'], true)
			? $type
			: 'banner'
		;
		if (\Bitrix\Main\Loader::includeModule("mobile"))
		{
			return ['link' => \Bitrix\Mobile\Deeplink::getAuthLink("calendar_sync_".$type)];
		}
		return null;
	}

	public function getUserSelectorComponentAction()
	{
		$request = $this->getRequest();
		$selectedUserCodes = $request->getPost('codes');
		$additionalResponseParams = [];

		return new \Bitrix\Main\Engine\Response\Component(
			'bitrix:main.user.selector',
			'',
			[
				"ID" => $request->getPost('selectorId'),
				"LIST" => $selectedUserCodes,
				"LAZYLOAD" => "Y",
				"INPUT_NAME" => 'EVENT_DESTINATION[]',
				"USE_SYMBOLIC_ID" => true,
				"API_VERSION" => 3,
				"SELECTOR_OPTIONS" => [
					'lazyLoad' => 'Y',
					'context' => Util::getUserSelectorContext(),
					'contextCode' => '',
					'enableSonetgroups' => 'Y',
					'departmentSelectDisable' => 'N',
					'showVacations' => 'Y',
					'enableAll' => 'Y',
					'allowSearchEmailUsers' => 'Y',
					'allowEmailInvitation' => 'Y'
				]
			],
			$additionalResponseParams
		);
	}

	public function updateColorAction()
	{
		$request = $this->getRequest();
		$response = [];
		$entryId = intVal($request->getPost('entryId'));
		$userId = \CCalendar::GetUserId();
		$entry = \CCalendarEvent::GetById($entryId);

		if (\CCalendarSect::CanDo('calendar_edit', $entry['SECTION_ID'], $userId))
		{
			\CCalendarEvent::updateColor($entryId, $request->getPost('color'));
			\CCalendar::ClearCache('event_list');
		}

		return $response;
	}

	public function getSettingsSliderAction($uid, $showPersonalSettings, $showGeneralSettings, $showAccessControl)
	{
		$uid = preg_replace('/[^\d|\w\_]/', '', $uid);

		$userId = \CCalendar::getCurUserId();
		$additionalResponseParams = [
			'uid' => $uid,
			'mailboxList' => \Bitrix\Calendar\Integration\Sender\AllowedSender::getList($userId)
		];

		return new \Bitrix\Main\Engine\Response\Component(
			'bitrix:calendar.settings.slider',
			'',
			[
				'id' => $uid,
				'is_personal' => $showPersonalSettings === 'Y',
				'show_general_settings' => $showGeneralSettings === 'Y',
				'show_access_control' => $showAccessControl === 'Y'
			],
			$additionalResponseParams
		);
	}

	public function getAllowedMailboxDataAction()
	{
		$userId = \CCalendar::getCurUserId();
		return new \Bitrix\Main\Engine\Response\Component(
			'bitrix:main.mail.confirm',
			'',
			[],
			[
				'mailboxList' => \Bitrix\Calendar\Integration\Sender\AllowedSender::getList($userId)
			]
		);
	}

	public function getAllowedMailboxListAction()
	{
		$userId = \CCalendar::getCurUserId();
		return [
			'mailboxList' => \Bitrix\Calendar\Integration\Sender\AllowedSender::getList($userId)
		];
	}

	public function getCompactFormDataAction($entryId)
	{
		$userId = \CCalendar::GetCurUserId();
		$request = $this->getRequest();
		$loadSectionId = (int)$request['loadSectionId'];
		$result = [];
		if ($loadSectionId > 0)
		{
			$result['section'] = \CCalendarSect::GetById($loadSectionId);
		}
		return $result;
	}

	public function getSectionListAction(): array
	{
		$userId = \CCalendar::GetCurUserId();
		$request = $this->getRequest();
		$type = $request['type'];
		$ownerId = (int)$request['ownerId'];
		$followedSectionList = UserSettings::getFollowedSectionIdList($userId);

		$sectionList = \CCalendar::getSectionList([
			'CAL_TYPE' => $type,
			'OWNER_ID' => $ownerId,
			'ACTIVE' => 'Y',
			'ADDITIONAL_IDS' => UserSettings::getFollowedSectionIdList($userId),
			'checkPermissions' => true,
			'getPermissions' => true,
			'getImages' => true
		]);
		if ($type === 'location')
		{
			$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));
		}

		foreach ($sectionList as $i => $section)
		{
			if (in_array($section['ID'], $followedSectionList))
			{
				$sectionList[$i]['SUPERPOSED'] = true;
			}
		}

		return [
			'sections' => $sectionList
		];
	}

	public function updateCountersAction(): array
	{
		$userId = \CCalendar::GetCurUserId();
		\CCalendar::UpdateCounter([$userId]);

		return [
			'counters' => CountersManager::getValues($userId)
		];
	}

	public function updateDefaultSectionIdAction(string $key, int $sectionId): void
	{
		$userId = \CCalendar::GetCurUserId();
		$key = preg_replace("/[^a-zA-Z0-9_:\.]/is", "", $key);
		if ($key && $sectionId)
		{
			$userSettings = UserSettings::get($userId);
			$userSettings['defaultSections'][$key] = $sectionId;
			UserSettings::set($userSettings, $userId);
		}
	}

	public function analyticalAction(): void
	{

	}

	public function saveSettingsAction(string $type, array $user_settings = [], string $user_timezone_name = '', array $settings = []): void
	{
		$request = $this->getRequest();
		$userId = \CCalendar::GetCurUserId();

		// Personal
		UserSettings::set($user_settings);

		// Save access for type
		if (\CCalendarType::CanDo('calendar_type_edit_access', $type))
		{
			// General
			if (!empty($settings) )
			{
				\CCalendar::SetSettings($settings);
			}

			if (!empty($request['type_access']))
			{
				\CCalendarType::Edit([
					'arFields' => [
						'XML_ID' => $type,
						'ACCESS' => $request['type_access']
					]
				]);
			}
		}

		if (!empty($user_timezone_name))
		{
			\CCalendar::SaveUserTimezoneName($userId, $user_timezone_name);
		}
	}
	
	public function getFilterDataAction()
	{
		if (Loader::includeModule('intranet'))
		{
			if (!\Bitrix\Intranet\Util::isIntranetUser())
			{
				return [];
			}
		}
		
		$request = $this->getRequest();
		
		$params = [
			'ownerId' => $request->getPost('ownerId'),
			'userId' => $request->getPost('userId'),
			'type' => $request->getPost('type'),
		];
		
		return CalendarFilter::getFilterData($params);
	}
}
