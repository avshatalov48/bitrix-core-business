<?php
namespace Bitrix\Calendar\Controller;

use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\SectionAccessController;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Sharing;
use Bitrix\Calendar\Ui\CalendarFilter;
use Bitrix\Calendar\Util;
use Bitrix\Intranet\Settings\Tools\ToolsManager;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Ui\CountersManager;
use Bitrix\Calendar\Access\ActionDictionary;

Loc::loadMessages(__FILE__);

/**
 * Class CalendarAjax
 */
class CalendarAjax extends \Bitrix\Main\Engine\Controller
{
	public const EVENT_EDIT_FORM_FIELDS_THAT_CAN_BE_HIDDEN = [
		'repeatRule' => 'rrule',
		'crm' => 'crm',
		'accessibility' => 'accessibility',
	];

	public function configureActions()
	{
		return [
			'getTimezoneList' => [
				'-prefilters' => [
					Authentication::class
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
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
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
		$isPersonal = $type === 'user' && $ownerId === $userId;

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
			if (Loader::includeModule('extranet') && !\CExtranet::IsIntranetUser(SITE_ID, $userId))
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

					if (!$group = $r->fetch())
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

			$accessController = new SectionAccessController($userId);

			if ($isNew) // For new sections
			{
				$sectionModel =
					SectionModel::createNew()
						->setType($type ?? '')
						->setOwnerId($userId ?? 0);
				if (!$accessController->check(ActionDictionary::ACTION_SECTION_ADD, $sectionModel))
				{
					$this->addError(
						new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied')
					);
				}

				if ($type === 'group' && Loader::includeModule('socialnetwork'))
				{
					$result = \Bitrix\Socialnetwork\UserToGroupTable::getList([
						'filter' => [
							'@ROLE' => \Bitrix\Socialnetwork\UserToGroupTable::getRolesMember(),
							'=GROUP_ID' => $ownerId,
							'=USER_ID' => $userId,
						],
					]);

					$group = $result->fetch();
					if (!$group)
					{
						$this->addError(
							new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied_user')
						);
					}
				}

				$fields['IS_EXCHANGE'] = $request->getPost('is_exchange') === 'Y';
			}
			else
			{
				$section = \CCalendarSect::GetById($id);
				$sectionModel = SectionModel::createFromArray($section);

				if (
					!$section
					|| !$accessController->check(ActionDictionary::ACTION_SECTION_EDIT, $sectionModel, [])

				)
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

					$response['sectionList'] = \CCalendarSect::prepareSectionListResponse($type, $ownerId);
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

		if (
			!\CCalendar::IsPersonal()
			&& !SectionAccessController::can(\CCalendar::GetUserId(), ActionDictionary::ACTION_SECTION_EDIT, $id)
		)
		{
			$this->addError(new Error('[sd02]' . Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
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
				\CCalendar::setOwnerId(\CCalendar::GetUserId());
				\CCalendar::RemoveConnection(['id' => (int)$section['CAL_DAV_CON'], 'del_calendars' => true]);
			}
		}

		return $response;
	}

	public function getTrackingSectionsAction()
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return [];
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
			$groupIds = $request->get('groupIdList') ?? [];
			$sections = \CCalendarSect::getSuperposedList(['GROUPS' => $groupIds]);

			if (Loader::includeModule('socialnetwork'))
			{
				foreach ($groupIds as $groupId)
				{
					$groupId = (int)$groupId;
					$createDefaultGroupSection = \CSocNetFeatures::isActiveFeature(
						SONET_ENTITY_GROUP,
						$groupId,
						"calendar"
					);

					if ($createDefaultGroupSection)
					{
						foreach ($sections as $section)
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
			foreach ($typesRes as $type)
			{
				if (
					$type['XML_ID'] !== 'user'
					&& $type['XML_ID'] !== 'group'
					&& $type['XML_ID'] !== 'location'
				)
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
		if (
			Loader::includeModule('intranet')
			&& !ToolsManager::getInstance()->checkAvailabilityByToolId('calendar')
		)
		{
			$this->addError(new Error('Tool not available'));

			return [
				'isAvailable' => false,
			];
		}
		$request = $this->getRequest();
		$responseParams = [];
		$uniqueId = 'calendar_edit_slider_' . mt_rand();
		$formType = preg_replace('/\W/', '', $request->get('form_type'));
		$entryId = (int)$request->get('event_id');
		$userCodes = $request->get('userCodes');
		$userId = \CCalendar::GetCurUserId();
		$ownerId = (int)$request->get('ownerId');
		$type = $request->get('type');
		$sections = [];

		if ($entryId > 0)
		{
			$fromTs = !empty($_REQUEST['date_from_offset']) ? \CCalendar::Timestamp($_REQUEST['date_from'])
				- $_REQUEST['date_from_offset'] : \CCalendar::Timestamp($_REQUEST['date_from']);
			$entry = \CCalendarEvent::getEventForEditInterface($entryId, ['eventDate' => \CCalendar::Date($fromTs)]);
			$entryId = is_array($entry) && isset($entry['ID']) ? (int)$entry['ID'] : $entryId;
		}
		else
		{
			$entry = [];
		}

		if (!$entryId || (!empty($entry) && \CCalendarSceleton::CheckBitrix24Limits(['id' => $uniqueId])))
		{
			$responseParams['uniqueId'] = $uniqueId;
			$responseParams['userId'] = $userId;
			$responseParams['editorId'] = $uniqueId . '_entry_slider_editor';
			$responseParams['entry'] = $entry;
			$responseParams['timezoneHint'] = !empty($entry) ? Util::getTimezoneHint($userId, $entry) : '';
			$responseParams['timezoneList'] = \CCalendar::GetTimezoneList();
			$responseParams['formSettings'] = UserSettings::getFormSettings($formType);

			if ($type)
			{
				if (($type === 'user' && $ownerId !== $userId) || $type !== 'user')
				{
					$sectionList = \CCalendar::getSectionList([
						'CAL_TYPE' => $type,
						'OWNER_ID' => $ownerId,
						'ACTIVE' => 'Y',
						'checkPermissions' => true,
						'getPermissions' => true
					]);

					foreach ($sectionList as $section)
					{
						if ($section['PERM']['edit'] || $section['PERM']['add'])
						{
							$sections[] = $section;
						}
					}
				}

				if (empty($sections) && $type === 'group')
				{
					$sections[] = \CCalendarSect::createDefault([
						'type' => $type,
						'ownerId' => $ownerId
					]);

					\CCalendarSect::setClearOperationCache();
				}
			}
			$sections = array_merge(
				$sections,
				\CCalendar::getSectionListAvailableForUser($userId, (array)($entry['SECTION_ID'] ?? null))
			);

			$responseParams['sections'] = [];
			foreach ($sections as $section)
			{
				if (
					($section['PERM']['edit'] ?? false)
					&& !\CCalendarSect::CheckGoogleVirtualSection(
						$section['GAPI_CALENDAR_ID'] ?? null,
						$section['EXTERNAL_TYPE'] ?? null,
					)
				)
				{
					$responseParams['sections'][] = $section;
				}
			}

			$responseParams['dayOfWeekMonthFormat'] = (
				\Bitrix\Main\Context::getCurrent()
					->getCulture()
					->getDayOfWeekMonthFormat()
			);
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
				$responseParams['locationAccess'] = Rooms\Util::getLocationAccess($userId);
			}
			$responseParams['plannerFeatureEnabled'] = Bitrix24Manager::isPlannerFeatureEnabled();
			$responseParams['attendeesEntityList'] = ($entryId > 0 && !empty($entry['attendeesEntityList']))
				? $entry['attendeesEntityList']
				: Util::getDefaultEntityList($userId, $type, $ownerId);
			$responseParams['meetSection'] = null;
			if ($type === Dictionary::CALENDAR_TYPE['user'])
			{
				$responseParams['meetSection'] = UserSettings::get($ownerId)['meetSection'] ?? null;
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
					'AVATAR_SIZE' => 21,
					'ATTENDEES_CODES' => $userCodes,
					'hiddenFields' => $this->getEventEditFormHiddenFields($entry),
				],
				$responseParams
			);
		}

		$this->addError(new Error('[se05] No entry found'));

		return [];
	}

	private function getEventEditFormHiddenFields(array $entry): array
	{
		$hiddenFields = [];

		if ($this->isSharingEvent($entry))
		{
			$hiddenFields = array_merge(
				$hiddenFields,
				[
					self::EVENT_EDIT_FORM_FIELDS_THAT_CAN_BE_HIDDEN['crm'],
				]
			);
		}

		return $hiddenFields;
	}

	private function isSharingEvent(array $entry): bool
	{
		return
			isset($entry['EVENT_TYPE'])
			&& in_array($entry['EVENT_TYPE'], Sharing\SharingEventManager::getSharingEventTypes())
		;
	}

	public function getViewEventSliderAction()
	{
		$request = $this->getRequest();
		$responseParams = [];
		$uniqueId = 'calendar_view_slider_' . mt_rand();
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
			$responseParams['dayOfWeekMonthFormat'] = (
				\Bitrix\Main\Context::getCurrent()
					->getCulture()
					->getDayOfWeekMonthFormat()
			);

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

		$this->addError(new Error(Loc::getMessage('EC_EVENT_NOT_FOUND'), 'EVENT_NOT_FOUND_02'));

		return [];
	}

	public function getCrmUserfieldAction()
	{
		$request = $this->getRequest();
		$UF = \CCalendarEvent::GetEventUserFields(['PARENT_ID' => (int)$request->get('event_id')]);
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
		$parentId = (int)($request['entry']['parentId'] ?? 0);
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
				$this->addError(new Error('[up01]' . Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));
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

		if ($entryId > 0 && empty($codes))
		{
			$codes[] = 'U' . $hostId;
		}
		if ($request['add_cur_user_to_list'] === 'Y' || empty($codes))
		{
			$codes[] = 'U' . $userId;
		}

		$prevUserList = is_array($request['prevUserList']) ? $request['prevUserList'] : [];

		$dateFrom = isset($request['dateFrom']) ? $request['dateFrom'] : $request['date_from'];
		$dateTo = isset($request['dateTo']) ? $request['dateTo'] : $request['date_to'];

		return \CCalendarPlanner::prepareData([
			'parent_id' => $parentId,
			'entry_id' => $entryId,
			'user_id' => $userId,
			'host_id' => $hostId,
			'codes' => $codes,
			'entryLocation' => trim($request['entryLocation'] ?? ""),
			'entries' => $entries,
			'date_from' => $dateFrom,
			'date_to' => $dateTo,
			'timezone' => $request['timezone'],
			'location' => trim($request['location'] ?? ""),
			'roomEventId' => (int)$request['roomEventId'],
			'initPullWatches' => true,
			'prevUserList' => $prevUserList
		]);
	}

	public function getPlannerAction()
	{
		$request = $this->getRequest();
		\CCalendarPlanner::Init(['id' => $request['planner_id']]);
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
			$this->addError(new Error('[ed01]' . Loc::getMessage('EC_EVENT_DEL_ERROR'), 'delete_entry_error'));
		}

		return $response;
	}

	public function changeRecurciveEntryUntilAction($entryId, $untilDate)
	{
		$response = ['result' => false];

		$event = \CCalendarEvent::GetById((int)$entryId);
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

				$id = \CCalendar::SaveEvent([
					'arFields' => [
						"ID" => $event["ID"],
						"RRULE" => $event['RRULE']
					],
					'silentErrorMode' => false,
					'recursionEditMode' => 'skip',
					'editParentEvents' => true,
					'editEntryUntil' => true,
				]);
				$recId = $event["ID"];
				$response['id'] = $id;
			}

			if ($event["RECURRENCE_ID"] > 0)
			{
				$recParentEvent = \CCalendarEvent::GetById($event["RECURRENCE_ID"]);
				if ($recParentEvent && \CCalendarEvent::CheckRecurcion($recParentEvent))
				{
					$recParentEvent['RRULE'] = \CCalendarEvent::ParseRRULE($recParentEvent['RRULE']);

					if (
						$recParentEvent['RRULE']['UNTIL']
						&& \CCalendar::Timestamp($recParentEvent['RRULE']['UNTIL']) > $untilTimestamp
					)
					{
						$recParentEvent['RRULE']['UNTIL'] = \CCalendar::Date($untilTimestamp, false);

						if (isset($recParentEvent['RRULE']['COUNT']))
						{
							unset($recParentEvent['RRULE']['COUNT']);
						}

						$id = \CCalendar::SaveEvent([
							'arFields' => [
								"ID" => $recParentEvent["ID"],
								"RRULE" => $recParentEvent['RRULE']
							],
							'silentErrorMode' => false,
							'recursionEditMode' => 'skip',
							'editParentEvents' => true,
							'editEntryUntil' => true,
						]);
						$response['id'] = $id;
					}
				}

				$recId = $event["RECURRENCE_ID"];
			}

			if ($recId)
			{
				$recRelatedEvents = \CCalendarEvent::GetEventsByRecId($recId, false);
				foreach ($recRelatedEvents as $ev)
				{
					if (\CCalendar::Timestamp($ev['DATE_FROM']) > $untilTimestamp)
					{
						\CCalendar::DeleteEvent((int)$ev['ID'], true, ['recursionMode' => 'this']);
					}
				}
			}

			$response['result'] = true;
		}

		if ($response['result'] !== true)
		{
			$this->addError(new Error('[ed01]' . Loc::getMessage('EC_EVENT_DEL_ERROR'),
				'change_recurcive_entry_until'));
		}

		return $response;
	}

	public function excludeRecursionDateAction($entryId, $excludeDate)
	{
		$response = [];
		\CCalendarEvent::ExcludeInstance((int)$entryId, $excludeDate);
		return $response;
	}

	public function deleteCalendarSectionAction($id)
	{
		$response = [];
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return $response;
		}

		$sectionList = SectionTable::getList([
				'filter' => [
					'=ACTIVE' => 'Y',
					'=ID' => (int)$id
				],
			]
		);

		if (!($section = $sectionList->fetch()))
		{
			$this->addError(new Error(Loc::getMessage('EC_SECTION_NOT_FOUND'), 'section_not_found'));

			return $response;
		}

		$accessController = new SectionAccessController(\CCalendar::GetUserId());
		$sectionModel = SectionModel::createFromArray($section);

		if (!$accessController->check(ActionDictionary::ACTION_SECTION_EDIT, $sectionModel))
		{
			$this->addError(new Error(Loc::getMessage('EC_ACCESS_DENIED'), 'access_denied'));

			return $response;
		}

		\CCalendar::DeleteSection($id);

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
		$entryId = (int)$request->getPost('entryId');
		$userId = \CCalendar::GetUserId();
		$entry = \CCalendarEvent::GetById($entryId);

		if (empty($entry))
		{
			$this->addError(new Error('Event not found'));

			return $response;
		}

		$accessController = new EventAccessController($userId);
		$eventModel = \CCalendarEvent::getEventModelForPermissionCheck($entryId, $entry, $userId);

		if ($accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel, ['checkCurrentEvent' => 'Y']))
		{
			$entry['REMIND'] = \CCalendarReminder::prepareReminder($request->getPost('reminders'));
			$response['REMIND'] = $entry['REMIND'];
			$response['id'] = \CCalendar::SaveEvent([
				'arFields' => [
					'ID' => $entry['ID'],
					'REMIND' => $entry['REMIND']
				],
				'updateReminders' => true,
				'checkPermission' => false,
			]);

			\CCalendar::ClearCache('event_list');
		}
		else
		{
			$this->addError(new Error('Access denied'));
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

		if ($attestedSectionsStatus && $userId > 0)
		{
			\CCalendarSync::SetSectionStatus($userId, $attestedSectionsStatus);
			return true;
		}

		return false;
	}

	public function sendAnalyticsLabelAction()
	{
		return null;
	}

	public function updateColorAction()
	{
		$request = $this->getRequest();
		$response = [];
		$entryId = intVal($request->getPost('entryId'));
		$userId = \CCalendar::GetUserId();
		$entry = \CCalendarEvent::GetById($entryId);

		if (empty($entry))
		{
			$this->addError(new Error('Event not found'));

			return $response;
		}

		$accessController = new EventAccessController($userId);
		$eventModel = \CCalendarEvent::getEventModelForPermissionCheck($entryId, $entry, $userId);

		if ($accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel, ['checkCurrentEvent' => 'Y']))
		{
			\CCalendarEvent::updateColor($entryId, $request->getPost('color'));
			\CCalendar::ClearCache('event_list');
		}
		else
		{
			$this->addError(new Error('Access denied'));
		}

		return $response;
	}

	public function getSettingsSliderAction($uid, $showPersonalSettings, $showGeneralSettings, $showAccessControl)
	{
		$uid = preg_replace('/\W/', '', $uid);

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

	public function getCompactFormDataAction()
	{
		$request = $this->getRequest();
		$loadSectionId = (int)$request['loadSectionId'];
		$result = [];
		if ($loadSectionId > 0)
		{
			$result['section'] = \CCalendarSect::GetById($loadSectionId);
		}
		return $result;
	}

	public function getSectionListAction($type, $ownerId): array
	{
		return [
			'sections' => \CCalendarSect::prepareSectionListResponse($type, (int)$ownerId)
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

	public function saveSettingsAction(string $type, array $user_settings = [], string $user_timezone_name = '',
		array $settings = []): void
	{
		$request = $this->getRequest();
		$userId = \CCalendar::GetCurUserId();

		// Personal
		UserSettings::set($user_settings);

		// Save access for type
		$accessController = new TypeAccessController($userId);
		$typeModel = TypeModel::createFromXmlId($type);

		if ($accessController->check(ActionDictionary::ACTION_TYPE_ACCESS, $typeModel))
		{
			// General
			if (!empty($settings))
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
			\CCalendar::ClearCache('event_list');
		}
	}

	public function getFilterDataAction()
	{
		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			$this->addError(new Error('Intranet user only'));

			return [];
		}

		$request = $this->getRequest();

		$type = $request->getPost('type');

		if ($type === 'user')
		{
			$params = [
				'ownerId' => \CCalendar::GetCurUserId(),
				'userId' => \CCalendar::GetCurUserId(),
				'type' => $type,
			];
		}
		else if (in_array($type, ['company_calendar', 'calendar_company', 'company', 'group'], true))
		{
			$accessController = new TypeAccessController(\CCalendar::GetCurUserId());
			$typeModel = TypeModel::createFromXmlId($type);

			if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, $typeModel))
			{
				$this->addError(new Error('Type access denied'));

				return [];
			}

			$params = [
				'ownerId' => $request->getPost('ownerId'),
				'userId' => \CCalendar::GetCurUserId(),
				'type' => $type,
			];
		}
		else
		{
			$this->addError(new Error('Type not found'));

			return [];
		}

		return CalendarFilter::getFilterData($params);
	}

	public function getConferenceChatIdAction(int $eventId)
	{
		$result = [];

		if (Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser())
		{
			return $result;
		}

		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory())->getEventLinkByEventId($eventId);
		if (!$eventLink)
		{
			$this->addError(new Error('Event not found'));

			return $result;
		}

		$chatId = (new Sharing\SharingConference($eventLink))->getConferenceChatId();

		if (!$chatId)
		{
			$this->addError(new Error('Conference not found'));

			return $result;
		}

		$result['chatId'] = $chatId;

		return $result;
	}
}