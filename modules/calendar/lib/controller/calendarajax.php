<?
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Util;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Error;
use Bitrix\Calendar\Internals;
use \Bitrix\Main\Engine\Response;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Calendar\UserSettings;
use \Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use \Bitrix\Calendar\Integration\Bitrix24\Limitation;

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
//				'+prefilters' => [
//					new CloseSession()
//				],
				'-prefilters' => [
					Authentication::class,
					Csrf::class
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
			'ACCESS' => $request->getPost('access')
		];

		if ($customization && !$isNew)
		{
			UserSettings::setSectionCustomization($userId, [$id => ['name' => $name, 'color' => $color]]);
		}
		else
		{
			if (Loader::includeModule('extranet')
				&& !\CExtranet::IsIntranetUser(SITE_ID, $userId))
			{
				if ($type === 'group'
					&& Loader::includeModule('socialnetwork'))
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

			if($isNew) // For new sections
			{
				if($type === 'group')
				{
					// It's for groups
					if(!\CCalendarType::CanDo('calendar_type_edit_section', 'group'))
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_01')
						);
					}
				}
				else if($type === 'user')
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

			if(empty($this->getErrors()))
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
					"ID" => $id,
					"ACTIVE" => "N"
				]]);

			// Check if it's last section from connection - remove it
			$sections = \CCalendarSect::GetList(
				['arFilter' => [
					'CAL_DAV_CON' => $section['CAL_DAV_CON'],
					'ACTIVE' => 'Y'
				]]);

			if(!$sections || count($sections) === 0)
			{
				\CCalendar::RemoveConnection(['id' => (int) $section['CAL_DAV_CON'], 'del_calendars' => 'Y']);
			}
		}

		return $response;
	}

	public function getTrackingSectionsAction()
	{
		$request = $this->getRequest();
		$mode = $request->get('type');

		$users = [];
		if ($mode == 'users')
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
		elseif($mode == 'groups')
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
		$type = $request->get('type');

		$userId = \CCalendar::getCurUserId();
		if ($type === 'users')
		{
			UserSettings::setTrackingUsers($userId, $request->get('userIdList'));
		}
		elseif($type === 'groups')
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

		\CCalendar::setDisplayedSuperposed($userId, $request->get('sections'));
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

			$responseParams['trackingUsersList'] = UserSettings::getTrackingUsers($userId);
			$responseParams['userSettings'] = UserSettings::get($userId);
			$responseParams['eventWithEmailGuestLimit'] = Limitation::getEventWithEmailGuestLimit();
			$responseParams['countEventWithEmailGuestAmount'] = Limitation::getCountEventWithEmailGuestAmount();
			$responseParams['iblockMeetingRoomList'] = \CCalendar::GetMeetingRoomList();
			$responseParams['userIndex'] = \CCalendarEvent::getUserIndex();
			$responseParams['locationFeatureEnabled'] = !\CCalendar::IsBitrix24() ||
		\Bitrix\Bitrix24\Feature::isFeatureEnabled("calendar_location");
			if ($responseParams['locationFeatureEnabled'])
			{
				$responseParams['locationList'] = \CCalendarLocation::GetList();
			}

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
			$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_01'));
		}

		if ($entry)
		{
			$additionalResponseParams['uniqueId'] = $uniqueId;
			$additionalResponseParams['userId'] = $userId;
			$additionalResponseParams['userTimezone'] = \CCalendar::GetUserTimezoneName($userId);
			$additionalResponseParams['entry'] = $entry;
			$additionalResponseParams['userIndex'] = \CCalendarEvent::getUserIndex();
			$additionalResponseParams['userSettings'] = UserSettings::get($userId);
			$additionalResponseParams['entryUrl'] = \CHTTP::urlAddParams(
				\CCalendar::GetPath($entry['CAL_TYPE'], $entry['OWNER_ID'], true),
				[
					'EVENT_ID' => (int)$entry['ID'],
					'EVENT_DATE' => urlencode($entry['DATE_FROM'])
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

		if (!Loader::includeModule('intranet')
			|| (!\Bitrix\Intranet\Util::isIntranetUser($userId) && !$isExtranetUser)
		)
		{
			$this->addError(new Error('[up01]'.Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
			return [];
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
		elseif(isset($request['codes']) && is_array($request['codes']))
		{
			$codes = $request['codes'];
		}
		if ($request['add_cur_user_to_list'] === 'Y' || count($codes) == 0)
		{
			$codes[] = 'U'.$userId;
		}

		$dateFrom = isset($request['dateFrom']) ? $request['dateFrom'] : $request['date_from'];
		$dateTo = isset($request['dateTo']) ? $request['dateTo'] : $request['date_to'];

		return \CCalendarPlanner::prepareData([
			'entry_id' => $entryId,
			'user_id' => $userId,
			'host_id' => $hostId,
			'codes' => $codes,
			'entries' => $entries,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'timezone' => $request['timezone'],
			'location' => trim($request['location']),
			'roomEventId' => (int)$request['roomEventId'],
			'initPullWatches' => true
		]);
	}

	public function getPlannerAction()
	{
		$request = $this->getRequest();
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

		$id = (int)$request['id'];
		$sectionId = (int)$request['section'];
		$requestUid = (int)$request['requestUid'];
		$userId = \CCalendar::getCurUserId();

		if (!$id && !\CCalendarSect::CanDo('calendar_add', $sectionId, $userId)
			||
			$id && !\CCalendarSect::CanDo('calendar_edit', $sectionId, $userId))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'edit_entry_access_denied'));
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
				$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'edit_entry_section_not_found'));
			}

			if(empty($this->getErrors()))
			{
				// Default name for events
				$name = trim($request['name']);
				if(empty($name))
				{
					$name = Loc::getMessage('EC_DEFAULT_EVENT_NAME');
				}
				$reminderList = \CCalendarReminder::prepareReminder($request['reminder']);

				$rrule = $request['EVENT_RRULE'];
				if (isset($rrule) && !isset($rrule['INTERVAL']) && $rrule['FREQ'] !== 'NONE')
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
					\CCalendar::SaveUserTimezoneName(\CCalendar::GetUserId(), $request['default_tz']);
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
					"REMIND" => $reminderList,
					"IS_MEETING" => !!$request['is_meeting'],
					"SECTION_CAL_TYPE" => $section['CAL_TYPE'],
					"SECTION_OWNER_ID" => $section['OWNER_ID']
				];

				$codes = [];
				if (isset($request['attendeesEntityList']) && is_array($request['attendeesEntityList']))
				{
					$codes = Util::convertEntitiesToCodes($request['attendeesEntityList']);
				}

				$accessCodes = \CCalendarEvent::handleAccessCodes($codes, ['userId' => $userId]);

				$entryFields['IS_MEETING'] = $accessCodes != ['U'.$userId];

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
					'MEETING_CREATOR' => \CCalendar::GetUserId(),
					'HIDE_GUESTS' => $request['hide_guests'] === 'Y'
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
							$userSettings = UserSettings::get(intval($attId));
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
					if(mb_substr($field, 0, 3) == "UF_")
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
						'currentEventDateFrom' => \CCalendar::Date(\CCalendar::Timestamp($request['current_date_from']), false),
						'sendInvitesToDeclined' => $request['sendInvitesAgain'] === 'Y',
						'requestUid' => $requestUid
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
									"CONTEXT" => Util::getUserSelectorContext(),
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
					else
					{
						if (is_iterable($errors))
						{
							foreach ($errors as $error)
							{
								if (is_string($error))
									 $this->addError(new Error($error, 'send_invite_failed'));
							}
						}
					}

					$pathToCalendar = \CCalendar::GetPathForCalendarEx($userId);
					foreach($eventList as $ind => $event)
					{
						$eventList[$ind]['~URL'] = \CHTTP::urlAddParams($pathToCalendar, ['EVENT_ID' => $event['ID']]);
					}

					$response['eventList'] = $eventList;
					$response['eventIdList'] = $eventIdList;
					$response['displayMobileBanner'] = Util::isShowDailyBanner();
					$response['countEventWithEmailGuestAmount'] = Limitation::getCountEventWithEmailGuestAmount();

					$userSettings = UserSettings::get($userId);
					$userSettings['defaultReminders'][$skipTime ? 'fullDay' : 'withTime'] = $reminderList;
					UserSettings::set($userSettings, $userId);
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
		$response['counters'] = [
			'invitation' => \CUserCounter::GetValue($userId, 'calendar')
		];

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

	public function removeConnectionAction()
	{
		$request = $this->getRequest();
		$connectId = (int)$request['connectionId'];
		\CCalendar::setOwnerId(\CCalendar::getCurUserId());
		\CCalendar::RemoveConnection(['id' => $connectId, 'del_calendars' => 'Y']);
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
			$response['id'] = \CCalendar::SaveEvent([
				'arFields' => [
					'ID' => $entryId,
					'COLOR' => $request->getPost('color')
				]
			]);

			\CCalendar::ClearCache('event_list');
		}

		return $response;
	}

	public function getSettingsSliderAction($uid, $isPersonal, $showGeneralSettings)
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
				'is_personal' => $isPersonal === 'Y',
				'show_general_settings' => $showGeneralSettings === 'Y'
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

		$sectionList = \CCalendar::getSectionList([
			'CAL_TYPE' => $type,
			'OWNER_ID' => $ownerId,
			'ADDITIONAL_IDS' => UserSettings::getFollowedSectionIdList($userId),
			'checkPermissions' => true,
			'getPermissions' => true,
			'getImages' => true
		]);

		return [
			'sections' => $sectionList
		];
	}

	public function updateCountersAction(): array
	{
		$userId = \CCalendar::GetCurUserId();
		\CCalendar::UpdateCounter([$userId]);

		return [
			'counters' => [
				'invitation' => \CUserCounter::GetValue($userId, 'calendar')
			]
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
	{}
}
