<?php

/** var CMain $APPLICATION */

use Bitrix\Calendar\Access\ActionDictionary;
use Bitrix\Calendar\Access\EventAccessController;
use Bitrix\Calendar\Access\Model\EventModel;
use Bitrix\Calendar\Access\Model\SectionModel;
use Bitrix\Calendar\Access\Model\TypeModel;
use Bitrix\Calendar\Access\SectionAccessController;
use Bitrix\Calendar\Access\TypeAccessController;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Integration\Tasks\TaskQueryParameter;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Sharing\SharingEventManager;
use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Factories\FactoriesCollection;
use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Calendar\Core\Event\Tools\UidGenerator;
use Bitrix\Calendar\Sync\Managers\Synchronization;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Ui\CountersManager;
use Bitrix\Calendar\UserSettings;
use Bitrix\Calendar\Util;
use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sharing;
use Bitrix\Tasks\Internals\Task\Status;
use Bitrix\Tasks\Provider\TaskList;
use Bitrix\Tasks\Provider\TaskQuery;

IncludeModuleLangFile(__FILE__);

class CCalendar
{
	const
		INTEGRATION_GOOGLE_API = "googleapi",
		CALENDAR_MAX_TIMESTAMP = 2145938400,
		DEFAULT_TASK_COLOR = '#ff5b55',
		TASK_SECTION_ID = '1_tasks',
		CALENDAR_CHAT_ENTITY_TYPE = 'CALENDAR',
		DAY_LENGTH = 86400; // 60 * 60 * 24

	public const EDIT_PREFIX = 'EDIT';

	private static
		$id = false,
		$instance,
		$CALENDAR_MAX_DATE,
		$CALENDAR_MIN_DATE,
		$type,
		$arTypes,
		$ownerId = 0,
		$settings,
		$siteId,
		$userSettings = [],
		$pathToUser,
		$bOwner,
		$userId,
		$curUserId,
		$userMeetingSection,
		$meetingSections = [],
		$crmSections = [],
		$offset,
		$arTimezoneOffsets = [],
		$perm = [],
		$isArchivedGroup = false,
		$userNameTemplate = "#NAME# #LAST_NAME#",
		$bSuperpose,
		$bExtranet,
		$bIntranet,
		$bWebservice,
		$userTimezoneList = [],
		$showTasks,
		$viewTaskPath = '',
		$editTaskPath = '',
		$actionUrl,
		$path = '',
		$outerUrl,
		$accessNames = [],
		$bSocNet,
		$bAnonym,
		$allowReserveMeeting = true,
		$SectionsControlsDOMId = 'sidebar',
		$arAccessTask = [],
		$ownerNames = [],
		$meetingRoomList,
		$cachePath = "calendar/",
		$cacheTime = 2592000, // 30 days by default
		$bCache = true,
		$readOnly,
		$pathesForSite = false,
		$pathes = [], // links for several sites
		$arUserDepartment = [],
		$bAMPM = false,
		$bWideDate = false,
		$arExchEnabledCache = [],
		$silentErrorMode = false,
		$weekStart,
		$bCurUserSocNetAdmin,
		$serverPath,
		$pathesList = array('path_to_user','path_to_user_calendar','path_to_group','path_to_group_calendar','path_to_vr','path_to_rm'),
		$pathesListEx = null,
		$isGoogleApiEnabled = null,
		$isOffice365ApiEnabled = null,
		$errors = [],
		$timezones = [],
		$userLanguageId = [];

	function Init($params)
	{
		global $USER, $APPLICATION;
		$access = new CAccess();
		$access->UpdateCodes();
		if (!$USER || !is_object($USER))
			$USER = new CUser;
		// Owner params
		self::$siteId = isset($params['siteId']) ? $params['siteId'] : SITE_ID;
		self::$type = $params['type'];
		self::$arTypes = CCalendarType::GetList();
		self::$bIntranet = self::IsIntranetEnabled();
		self::$bSocNet = self::IsSocNet();
		self::$userId = (isset($params['userId']) && $params['userId'] > 0) ? (int)$params['userId'] : self::GetCurUserId(true);
		self::$bOwner = self::$type === 'user' || self::$type === 'group';
		self::$settings = self::GetSettings();
		self::$userSettings = Bitrix\Calendar\UserSettings::get();
		self::$pathesForSite = self::GetPathes(self::$siteId);
		self::$pathToUser = self::$pathesForSite['path_to_user'];
		self::$bSuperpose = $params['allowSuperpose'] != false && self::$bSocNet;
		self::$bAnonym = !$USER || !$USER->IsAuthorized();
		self::$userNameTemplate = self::$settings['user_name_template'];
		self::$bAMPM = IsAmPmMode();
		self::$bWideDate = mb_strpos(FORMAT_DATETIME, 'MMMM') !== false;
		self::$id = $this->GetId();

		if (isset($params['SectionControlsDOMId']))
		{
			self::$SectionsControlsDOMId = $params['SectionControlsDOMId'];
		}

		if (self::$bOwner && isset($params['ownerId']) && $params['ownerId'] > 0)
		{
			self::$ownerId = (int)$params['ownerId'];
		}

		self::$showTasks = (self::$type === 'user' || self::$type === 'group')
			&& ($params['showTasks'] ?? '') !== false
			&& $params['viewTaskPath']
			&& Loader::includeModule('tasks')
			&& self::$userSettings['showTasks'] !== 'N';

		if (self::$showTasks)
		{
			self::$viewTaskPath = $params['viewTaskPath'];
			self::$editTaskPath = $params['editTaskPath'];
		}

		self::GetPermissions(array(
			'type' => self::$type,
			'bOwner' => self::$bOwner,
			'userId' => self::$userId,
			'ownerId' => self::$ownerId,
		));

		// Cache params
		if (isset($params['cachePath']))
		{
			self::$cachePath = $params['cachePath'];
		}
		if (isset($params['cacheTime']))
		{
			self::$cacheTime = $params['cacheTime'];
		}
		self::$bCache = self::$cacheTime > 0;

		// Urls
		$page = preg_replace(
			array(
				"/EVENT_ID=.*?\&/i",
				"/EVENT_DATE=.*?\&/i",
				"/CHOOSE_MR=.*?\&/i",
				"/action=.*?\&/i",
				"/bx_event_calendar_request=.*?\&/i",
				"/clear_cache=.*?\&/i",
				"/bitrix_include_areas=.*?\&/i",
				"/bitrix_show_mode=.*?\&/i",
				"/back_url_admin=.*?\&/i",
				"/IFRAME=.*?\&/i",
				"/IFRAME_TYPE=.*?\&/i",
			),
			"", $params['pageUrl'].'&'
		);
		$page = preg_replace(array("/^(.*?)\&$/i","/^(.*?)\?$/i"), "\$1", $page);
		self::$actionUrl = $page;

		self::$path = empty(self::$ownerId)
			? self::GetServerPath().$page
			: self::GetPath(self::$type, self::$ownerId, true);

		self::$outerUrl = $APPLICATION->GetCurPageParam('', [
			"action",
			"bx_event_calendar_request",
			"clear_cache",
			"bitrix_include_areas",
			"bitrix_show_mode",
			"back_url_admin",
			"SEF_APPLICATION_CUR_PAGE_URL",
			"EVENT_ID",
			"EVENT_DATE",
			"CHOOSE_MR",
		], false);

		// *** Meeting room params ***
		$RMiblockId = self::$settings['rm_iblock_id'];
		self::$allowReserveMeeting = $params["allowResMeeting"] && $RMiblockId > 0;

		if(self::$allowReserveMeeting && !$USER->IsAdmin() && (CIBlock::GetPermission($RMiblockId) < "R"))
		{
			self::$allowReserveMeeting = false;
		}
	}

	public function Show($params = [])
	{
		global $APPLICATION;
		$arType = false;

		foreach(self::$arTypes as $type)
		{
			if(self::$type === $type['XML_ID'])
			{
				$arType = $type;
			}
		}

		if (!$arType)
		{
			$APPLICATION->ThrowException('[EC_WRONG_TYPE] '.Loc::getMessage('EC_WRONG_TYPE'), 'calendar_wrong_type');
			return false;
		}

		$typeModel = TypeModel::createFromXmlId(self::$type);
		if (!(new TypeAccessController(self::$userId))->check(ActionDictionary::ACTION_TYPE_VIEW,$typeModel, []))
		{
			$APPLICATION->ThrowException(Loc::getMessage("EC_ACCESS_DENIED"));
			return false;
		}

		$init_month = false;
		$init_year = false;
		$startupEvent = false;
		//Show new event dialog
		if (isset($_GET['EVENT_ID']))
		{
			if($this->doOpenEventInEditMode($_GET['EVENT_ID']))
			{
				$eventId = $this->getEditEventId($_GET['EVENT_ID']);
				$startupEvent = self::GetStartUpEvent($eventId);
				if ($startupEvent)
				{
					$startupEvent['EDIT'] = true;

					if ($startupEvent['DT_FROM'] ?? false)
					{
						$ts = self::Timestamp($startupEvent['DT_FROM']);
						$init_month = date('m', $ts);
						$init_year = date('Y', $ts);
					}
				}
			}
			// Show popup event at start
			else
			{
				$eventId = (int)$_GET['EVENT_ID'];
				$isSharing = (bool)($_GET['IS_SHARING'] ?? null) === true;
				$startupEvent = self::GetStartUpEvent($eventId, $isSharing);
				if ($startupEvent)
				{
					$eventFromTs = self::Timestamp($startupEvent['DATE_FROM']);
					$currentDateTs = self::Timestamp($_GET['EVENT_DATE'] ?? null);

					if ($currentDateTs > $eventFromTs)
					{
						$startupEvent['~CURRENT_DATE'] = self::Date($currentDateTs, false);
						$init_month = date('m', $currentDateTs);
						$init_year = date('Y', $currentDateTs);
					}
					else
					{
						$init_month = date('m', $eventFromTs);
						$init_year = date('Y', $eventFromTs);
					}
				}
			}
		}

		if (!$init_month && !$init_year && ($params["initDate"] ?? false) <> '' && mb_strpos($params["initDate"], '.') !== false)
		{
			$ts = self::Timestamp($params["initDate"]);
			$init_month = date('m', $ts);
			$init_year = date('Y', $ts);
		}

		if (!$init_month)
		{
			$init_month = date("m");
		}
		if (!$init_year)
		{
			$init_year = date("Y");
		}

		$id = $this->GetId();

		$weekHolidays = [];
		if (isset(self::$settings['week_holidays']))
		{
			$days = array('MO' => 0, 'TU' => 1, 'WE' => 2,'TH' => 3,'FR' => 4,'SA' => 5,'SU' => 6);
			foreach(self::$settings['week_holidays'] as $day)
			{
				$weekHolidays[] = $days[$day];
			}
		}
		else
		{
			$weekHolidays = array(5, 6);
		}

		$yearHolidays = [];
		if (isset(self::$settings['year_holidays']))
		{
			foreach(explode(',', self::$settings['year_holidays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) == 2 && $ardate[0] && $ardate[1])
				{
					$yearHolidays[] = (int)$ardate[0] . '.' . ((int)$ardate[1] - 1);
				}
			}
		}

		$yearWorkdays = [];
		if (isset(self::$settings['year_workdays']))
		{
			foreach(explode(',', self::$settings['year_workdays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) === 2 && $ardate[0] && $ardate[1])
				{
					$yearWorkdays[] = (int)$ardate[0] . '.' . ((int)$ardate[1] - 1);
				}
			}
		}

		$isPersonalCalendarContext = self::IsPersonal(self::$type, self::$ownerId, self::$userId);
		$bExchange = self::IsExchangeEnabled() && self::$type === 'user';
		$bExchangeConnected = $bExchange && CDavExchangeCalendar::IsExchangeEnabledForUser(self::$ownerId);
		$bWebservice = self::IsWebserviceEnabled();
		$bExtranet = self::IsExtranetEnabled();
		$isExtranetUser = Loader::includeModule('intranet') && !\Bitrix\Intranet\Util::isIntranetUser();

		$userTimezoneOffsetUTC = self::GetCurrentOffsetUTC(self::$userId);
		$userTimezoneName = self::GetUserTimezoneName(self::$userId, false);
		$userTimezoneDefault = '';

		// We don't have default timezone for this offset for this user
		// We will ask him but we should suggest some suitable for his offset
		if (!$userTimezoneName)
		{
			$userTimezoneDefault = self::GetGoodTimezoneForOffset($userTimezoneOffsetUTC);
		}

		$JSConfig = Array(
			'id' => $id,
			'type' => self::$type,
			'userId' => self::$userId,
			'userName' => self::GetUserName(self::$userId), // deprecated
			'ownerId' => self::$ownerId,
			'ownerName' => self::GetOwnerName(self::$type, self::$ownerId),
			'user' => [
				'id' => self::$userId,
				'name' => self::GetUserName(self::$userId),
				'url' => self::GetUserUrl(self::$userId),
				'avatar' => self::GetUserAvatarSrc(self::$userId),
				'smallAvatar' => self::GetUserAvatarSrc(self::$userId, array('AVATAR_SIZE' => 18)),
			],
			'perm' => $arType['PERM'], // Permissions from type
			'locationAccess' => Rooms\Util::getLocationAccess(self::$userId),
			'permEx' => self::$perm,
			'showTasks' => self::$showTasks,
			'sectionControlsDOMId' => self::$SectionsControlsDOMId,
			'week_holidays' => $weekHolidays,
			'year_holidays' => $yearHolidays,
			'year_workdays' => $yearWorkdays,
			'init_month' => $init_month,
			'init_year' => $init_year,
			'pathToUser' => self::$pathToUser,
			'path' => self::$path,
			'actionUrl' => self::$actionUrl,
			'settings' => self::$settings,
			'userSettings' => self::$userSettings,
			'bAnonym' => self::$bAnonym,
			'bIntranet' => self::$bIntranet,
			'bWebservice' => $bWebservice,
			'bExtranet' => $bExtranet,
			'bSocNet' => self::$bSocNet,
			'bExchange' => $bExchangeConnected,
			'startupEvent' => $startupEvent,
			'workTime' => [self::$settings['work_time_start'], self::$settings['work_time_end']], // Decrecated !!
			'userWorkTime' => [self::$settings['work_time_start'], self::$settings['work_time_end']],
			'meetingRooms' => Rooms\IBlockMeetingRoom::getMeetingRoomList([
				'RMiblockId' => self::$settings['rm_iblock_id'],
				'pathToMR' => self::$pathesForSite['path_to_rm'],
			]),
			'allowResMeeting' => self::$allowReserveMeeting,
			'bAMPM' => self::$bAMPM,
			'WDControllerCID' => 'UFWD' . $id,
			'userTimezoneOffsetUTC' => $userTimezoneOffsetUTC,
			'userTimezoneName' => $userTimezoneName,
			'userTimezoneDefault' => $userTimezoneDefault,
			'sectionCustomization' => UserSettings::getSectionCustomization(self::$userId),
			'locationFeatureEnabled' => Bitrix24Manager::isFeatureEnabled('calendar_location'),
			'plannerFeatureEnabled' => Bitrix24Manager::isPlannerFeatureEnabled(),
			'isSharingFeatureEnabled' => \Bitrix\Calendar\Sharing\SharingFeature::isEnabled(),
			'payAttentionToNewSharingFeature' => \Bitrix\Calendar\Sharing\Helper::payAttentionToNewSharingFeature(),
			'eventWithEmailGuestLimit'=> Bitrix24Manager::getEventWithEmailGuestLimit(),
			'countEventWithEmailGuestAmount'=> Bitrix24Manager::getCountEventWithEmailGuestAmount(),
			'showAfterSyncAccent' => isset($_GET['googleAuthSuccess']) && $_GET['googleAuthSuccess'] === 'y',
			'isExtranetUser' => $isExtranetUser,
			'sharingFeatureLimitEnable' => Bitrix24Manager::isFeatureEnabled('calendar_sharing'),
			'isGoogleApplicationRefused' => COption::GetOptionString('calendar', 'isGoogleApplicationRefused', 'N'),
			'showGoogleApplicationRefused' => CUserOptions::getOption('calendar', 'showGoogleApplicationRefused', 'Y'),
		);

		if (self::$type === 'user' && (int)self::$userId !== (int)self::$ownerId)
		{
			$JSConfig['ownerUser'] = array(
				'id' => self::$ownerId,
				'name' => self::GetUserName(self::$ownerId),
				'url' => self::GetUserUrl(self::$ownerId),
				'avatar' => self::GetUserAvatarSrc(self::$ownerId),
				'smallAvatar' => self::GetUserAvatarSrc(self::$ownerId, array('AVATAR_SIZE' => 18)),
			);
		}

		$JSConfig['dayOfWeekMonthFormat'] = (
			\Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getDayOfWeekMonthFormat()
		);

		$JSConfig['dayMonthFormat'] = (
			\Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getDayMonthFormat()
		);

		$JSConfig['longDateFormat'] = (
			\Bitrix\Main\Context::getCurrent()
				->getCulture()
				->getLongDateFormat()
		);

		$placementParams = false;
		if (Loader::includeModule('rest'))
		{
			$placementParams = [
				'gridPlacementCode' => \CCalendarRestService::PLACEMENT_GRID_VIEW,
				'gridPlacementList' => \Bitrix\Rest\PlacementTable::getHandlersList(\CCalendarRestService::PLACEMENT_GRID_VIEW),
				'serviceUrl' => '/bitrix/components/bitrix/app.layout/lazyload.ajax.php?&site='.SITE_ID.'&'.bitrix_sessid_get(),
			];
		}
		$JSConfig['placementParams'] = $placementParams;

		if (self::$type === 'user' && self::$userId === self::$ownerId)
		{
			$JSConfig['counters'] = CountersManager::getValues((int)self::$userId);
			$JSConfig['filterId'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilterId(
				self::$type,
				self::$ownerId,
				self::$userId
			);
		}

		else if (
			self::$type === 'company_calendar'
			|| self::$type === 'calendar_company'
			|| self::$type === 'company'
			|| self::$type === 'group'
		)
		{
			$JSConfig['filterId'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilterId(
				self::$type,
				self::$ownerId,
				self::$userId
			);
		}

		// Access permissions for type
		$typeModel = TypeModel::createFromXmlId(self::$type);
		$accessController = new TypeAccessController(self::$userId);
		if ($accessController->check(ActionDictionary::ACTION_TYPE_ACCESS, $typeModel, []))
		{
			$JSConfig['TYPE_ACCESS'] = $arType['ACCESS'];
		}

		if ($isPersonalCalendarContext)
		{
			$syncInfoParams = [
				'userId' => self::$userId,
				'type' => self::$type,
			];
			$JSConfig['syncInfo'] = CCalendarSync::GetSyncInfo($syncInfoParams);
			$JSConfig['syncLinks'] = CCalendarSync::getSyncLinks();
			$JSConfig['caldav_link_all'] = self::GetServerPath();
			$JSConfig['isRuZone'] = \Bitrix\Calendar\Util::checkRuZone();
			$JSConfig['isSetSyncGoogleSettings'] = self::IsCalDAVEnabled() && self::isGoogleApiEnabled();
			$JSConfig['isSetSyncOffice365Settings'] = self::IsCalDAVEnabled() && self::isOffice365ApiEnabled();
			$JSConfig['isIphoneConnected'] = self::isIphoneConnected();
			$JSConfig['isMacConnected'] = self::isMacConnected();
			$JSConfig['isIcloudConnected'] = ($JSConfig['syncInfo']['icloud'] ?? false)
				? $JSConfig['syncInfo']['icloud']['connected']
				: false
			;
			$JSConfig['isGoogleConnected'] = ($JSConfig['syncInfo']['google'] ?? false)
				? $JSConfig['syncInfo']['google']['connected']
				: false
			;
		}
		else
		{
			$JSConfig['caldav_link_all'] = self::GetServerPath();
			$JSConfig['isRuZone'] = \Bitrix\Calendar\Util::checkRuZone();
			$JSConfig['syncInfo'] = false;
			$JSConfig['isIphoneConnected'] = false;
			$JSConfig['isMacConnected'] = false;
			$JSConfig['isIcloudConnected'] = false;
			$JSConfig['isGoogleConnected'] = false;
		}

		self::$userMeetingSection = self::GetCurUserMeetingSection();

		$followedSectionList = UserSettings::getFollowedSectionIdList(self::$userId);
		$defaultHiddenSections = [];
		$sections = [];
		$roomsList = Rooms\Manager::getRoomsList();

		$categoryList = Rooms\Categories\Manager::getCategoryList();

		if(self::$type === 'location')
		{
			$sectionList = $roomsList ?? [];
		}
		else
		{
			$sectionList = self::getSectionList([
				'CAL_TYPE' => self::$type,
				'OWNER_ID' => self::$ownerId,
				'ACTIVE' => 'Y',
				'ADDITIONAL_IDS' => $followedSectionList,
				'checkPermissions' => true,
				'getPermissions' => true,
				'getImages' => true,
			]);
		}

		$sectionList = array_merge($sectionList, self::getSectionListAvailableForUser(self::$userId));
		$sectionIdList = [];
		foreach ($sectionList as $i => $section)
		{
			if (!in_array((int)$section['ID'], $sectionIdList))
			{
				$sections[] = $section;
				$sectionIdList[] = (int)$section['ID'];
			}

			if ($section['CAL_TYPE'] !== self::$type || self::$ownerId !== (int)$section['OWNER_ID'])
			{
				$defaultHiddenSections[] = (int)$section['ID'];
			}
		}

		$hiddenSections = UserSettings::getHiddenSections(
			self::$userId,
			[
				'type' => self::$type,
				'ownerId' => self::$ownerId,
				'isPersonalCalendarContext' => $isPersonalCalendarContext,
				'defaultHiddenSections' => $defaultHiddenSections,
			]
		);

		$readOnly = !self::$perm['edit'] && !self::$perm['section_edit'];

		if (self::$type === 'user' && self::$ownerId !== (int)self::$userId)
		{
			$readOnly = true;
		}

		if (self::$bAnonym)
		{
			$readOnly = true;
		}

		$bCreateDefault = !self::$bAnonym;

		if (self::$type === 'user')
		{
			$bCreateDefault = self::$ownerId === self::$userId;
		}

		$additionalMeetingsId = [];
		$groupOrUser = self::$type === 'user' || self::$type === 'group';
		if ($groupOrUser)
		{
			$noEditAccessedCalendars = true;
		}

		$trackingGroups = [];
		foreach ($sections as $i => $section)
		{
			$sections[$i]['~IS_MEETING_FOR_OWNER'] = $section['CAL_TYPE'] === 'user'
				&& $section['OWNER_ID'] !== self::$userId
				&& self::GetMeetingSection($section['OWNER_ID']) === $section['ID'];

			// It's superposed calendar of the other user and it's need to show user's meetings
			if (
				($section['ACTIVE'] ?? null) !== 'N'
				&& $sections[$i]['~IS_MEETING_FOR_OWNER']
				&& !in_array($section['ID'], $hiddenSections)
			)
			{
				$additionalMeetingsId[] = [
					'ID' => $section['OWNER_ID'],
					'SECTION_ID' => $section['ID'],
				];
			}

			$canEdit = $accessController->check(ActionDictionary::ACTION_TYPE_EDIT, $typeModel, []);
			// We check access only for main sections because we can't edit superposed section
			if (
				$groupOrUser
				&& $sections[$i]['CAL_TYPE'] === self::$type
				&& (int)$sections[$i]['OWNER_ID'] === (int)self::$ownerId
			)
			{
				if ($noEditAccessedCalendars && $section['PERM']['edit'])
				{
					$noEditAccessedCalendars = false;
				}

				if (
					$readOnly
					&& $canEdit
					&& ($section['PERM']['edit'] || $section['PERM']['edit_section'])
					&& !self::$isArchivedGroup
				)
				{
					$readOnly = false;
				}
			}

			if (in_array($section['ID'], $followedSectionList))
			{
				$sections[$i]['SUPERPOSED'] = true;
			}

			if (
				$bCreateDefault
				&& $section['CAL_TYPE'] === self::$type
				&& (int)$section['OWNER_ID'] === self::$ownerId
			)
			{
				$bCreateDefault = false;
			}

			$type = $sections[$i]['CAL_TYPE'];
			if ($type === 'user')
			{
				$path = CComponentEngine::MakePathFromTemplate(
					self::$pathesForSite['path_to_user_calendar'],
					["user_id" => $sections[$i]['OWNER_ID']]
				);
			}
			elseif($type === 'group')
			{
				$path = CComponentEngine::MakePathFromTemplate(
					self::$pathesForSite['path_to_group_calendar'],
					["group_id" => $sections[$i]['OWNER_ID']]
				);
				$trackingGroups[] = $sections[$i]['OWNER_ID'];
			}
			else
			{
				$path = self::$pathesForSite['path_to_type_'.$type];
			}
			$sections[$i]['LINK'] = $path;
		}

		if ($groupOrUser && $noEditAccessedCalendars && !$bCreateDefault)
		{
			$readOnly = true;
		}

		self::$readOnly = $readOnly;
		$JSConfig = array_merge(
			$JSConfig,
			[
				'trackingUsersList' => UserSettings::getTrackingUsers(self::$userId),
				'trackingGroupList' => UserSettings::getTrackingGroups(self::$userId, ['groupList' => $trackingGroups]),
			]
		);

		//  **** GET TASKS ****
		if (self::$showTasks)
		{
			$JSConfig['viewTaskPath'] = self::$viewTaskPath;
			$JSConfig['editTaskPath'] = self::$editTaskPath;
		}

		// We don't have any section
		if ($bCreateDefault)
		{
			$fullSectionsList = $groupOrUser
				? self::GetSectionList(['checkPermissions' => false, 'getPermissions' => false])
				: []
			;
			// Section exists but it closed to this user (Ref. mantis:#64037)
			if (
				!empty($fullSectionsList)
				&& self::GetOwnerId() !== self::GetUserId()
			)
			{
				$readOnly = true;
			}
			else
			{
				$defCalendar = CCalendarSect::CreateDefault([
					'type' => self::GetType(),
					'ownerId' => self::GetOwnerId(),
				]);
				$sections[] = $defCalendar;
				self::$userMeetingSection = $defCalendar['ID'];
			}
		}
		else if (!CCalendarSect::containsLocalSection($sectionList, self::$type))
		{
			$sectionsWithoutPermissions = CCalendarSect::GetList([
				'arFilter' => [
					'CAL_TYPE' => self::$type,
					'OWNER_ID' => self::$ownerId,
					'ACTIVE' => 'Y',
					'EXTERNAL_TYPE' => CCalendarSect::EXTERNAL_TYPE_LOCAL,
				],
				'limit' => 1,
				'checkPermissions' => false,
				'getPermissions' => false,
			]);

			if (empty($sectionsWithoutPermissions))
			{
				$defCalendar = CCalendarSect::CreateDefault([
					'type' => self::GetType(),
					'ownerId' => self::GetOwnerId(),
				]);
				$sections[] = $defCalendar;
				self::$userMeetingSection = $defCalendar['ID'];
			}
		}
		//check if we need to create default calendar for user when he opens location calendar
		if (self::$type === 'location')
		{
			$userSections = array_filter($sectionList, static function ($section) {
				return $section['CAL_TYPE'] === 'user' && (int)$section['OWNER_ID'] === self::$userId;
			});
			if (empty($userSections))
			{
				$defUserCalendar = CCalendarSect::CreateDefault([
					'type' => 'user',
					'ownerId' => self::$userId,
				]);
				if ($defUserCalendar)
				{
					$sections[] = $defUserCalendar;
					self::$userMeetingSection = $defUserCalendar['ID'];
				}
			}
		}
		$typeAccessController = new TypeAccessController(self::$userId);
		if ($typeAccessController->check(ActionDictionary::ACTION_TYPE_EDIT, TypeModel::createFromXmlId(self::$type)))
		{
			$JSConfig['new_section_access'] = CCalendarSect::GetDefaultAccess(self::$type, self::$ownerId);
		}

		$colors = ['#86B100','#0092CC','#00AFC7','#E89B06','#00B38C','#DE2B24','#BD7AC9','#838FA0','#C3612C','#E97090'];

		$JSConfig['hiddenSections'] = $hiddenSections;
		$JSConfig['readOnly'] = $readOnly;
		$JSConfig['hideSettingsHintLocation']  = CUserOptions::GetOption('calendar', 'hideSettingsHintLocation');
		// access
		$JSConfig['accessNames'] = self::GetAccessNames();
		$JSConfig['sectionAccessTasks'] = self::$type === 'location'
			? self::GetAccessTasks('calendar_section', 'location')
			: self::GetAccessTasks()
		;
		$JSConfig['typeAccessTasks'] = self::$type === 'location'
			? self::GetAccessTasks('calendar_type', 'location')
			: self::GetAccessTasks('calendar_type')
		;

		$JSConfig['bSuperpose'] = self::$bSuperpose;
		$JSConfig['additonalMeetingsId'] = $additionalMeetingsId;

		$sharing = new Sharing\Sharing(self::$userId);
		$JSConfig['sharing'] = $sharing->getLinkInfo();
		$JSConfig['sharingOptions'] = $sharing->getOptions();

		$userSettings = UserSettings::get(self::$ownerId);
		$meetSectionId = $userSettings['meetSection'];

		$meetSection = CCalendarSect::GetById($meetSectionId);
		$hasRightsForMeetSection = false;
		if (is_array($meetSection))
		{
			$sectionAccessController = new SectionAccessController(self::$userId);
			$sectionModel = SectionModel::createFromArray($meetSection);
			$action = ActionDictionary::ACTION_SECTION_EDIT;
			$hasRightsForMeetSection = $sectionAccessController->check($action, $sectionModel);
		}
		if ($meetSectionId && $hasRightsForMeetSection)
		{
			$JSConfig['meetSectionId'] = $meetSectionId;
		}

		$selectedUserCodes = array('U'.self::$userId);
		if (self::$type === 'user')
		{
			$selectedUserCodes[] = 'U'.self::$ownerId;
		}

		$additionalParams = array(
			'socnetDestination' => self::GetSocNetDestination(false, $selectedUserCodes),
			'locationList' => $roomsList,
			'timezoneList' => self::GetTimezoneList(),
			'defaultColorsList' => $colors,
			'formSettings' => array(
				'slider_main' => UserSettings::getFormSettings('slider_main'),
			),
		);

		// Append Javascript files and CSS files, and some base configs
		if (self::$type === 'location')
		{
			CCalendarSceleton::InitJS(
				$JSConfig,
				array(
					'sections' => $sections,
					'rooms' => $roomsList,
					'categories' => $categoryList,
				),
				$additionalParams
			);
		}
		else
		{
			CCalendarSceleton::InitJS(
				$JSConfig,
				array(
					'sections' => $sections,
				),
				$additionalParams
			);
		}
	}

	protected function doOpenEventInEditMode(string $id): bool
	{
		return mb_strpos($id, self::EDIT_PREFIX) === 0;
	}

	protected function getEditEventId(string $id): int
	{
		return (int)mb_substr($id, strlen(self::EDIT_PREFIX));
	}

	public static function SetDisplayedSuperposed($userId = false, $idList = [])
	{
		if (class_exists('CUserOptions') && $userId)
		{
			$idList = array_unique(array_map('intval', $idList));
			CUserOptions::SetOption("calendar", "superpose_displayed", serialize($idList));
			\Bitrix\Calendar\Util::addPullEvent(
				'change_section_subscription',
				$userId
			);
		}
	}

	public static function DeleteSection($id)
	{
		if (self::IsExchangeEnabled(self::GetCurUserId()) && self::$type === 'user')
		{
			$oSect = CCalendarSect::GetById($id);
			// For exchange we change only calendar name
			if ($oSect && $oSect['IS_EXCHANGE'] && $oSect['DAV_EXCH_CAL'])
			{
				$exchRes = CDavExchangeCalendar::DoDeleteCalendar($oSect['OWNER_ID'], $oSect['DAV_EXCH_CAL']);
				if ($exchRes !== true)
				{
					return self::CollectExchangeErrors($exchRes);
				}
			}
		}

		GoogleApiPush::stopChannel(GoogleApiPush::getPush(GoogleApiPush::TYPE_SECTION, $id));

		return CCalendarSect::Delete($id);
	}

	public static function CollectExchangeErrors($arErrors = [])
	{
		if (empty($arErrors) || !is_array($arErrors))
		{
			return '[EC_NO_EXCH] ' . Loc::getMessage('EC_NO_EXCHANGE_SERVER');
		}

		$str = "";
		foreach ($arErrors as $error)
		{
			$str .= "[" . $error[0] . "] " . $error[1] . "\n";
		}

		return $str;
	}

	/**
	 * @param $id
	 * @param bool $doExternalSync
	 * @param array $params
	 * params['sendNotification'] send notifications
	 * params['checkPermissions'] check permissions
	 * params['recursionMode'] check event recurrence (this|all)
	 * @return bool|string
	 */

	public static function DeleteEvent($id, $doExternalSync = true, $params = [])
	{
		global $CACHE_MANAGER;

		$id = (int)$id;
		$markDeleted = $params['markDeleted'] ?? true;
		$originalFrom = $params['originalFrom'] ?? null;
		if (!$id)
		{
			return false;
		}

		$checkPermissions = $params['checkPermissions'] ?? true;
		if (!isset(self::$userId))
		{
			self::$userId = self::GetCurUserId();
		}

		self::SetOffset();
		$res = CCalendarEvent::GetList([
			'arFilter' => ["ID" => $id],
			'parseRecursion' => false,
			'setDefaultLimit' => false,
			'fetchAttendees' => true,
			'checkPermissions' => $checkPermissions,
		]);

		if (!empty($res[0]) && $event = $res[0])
		{
			if (!isset(self::$type))
			{
				self::$type = $event['CAL_TYPE'];
			}

			if (!isset(self::$ownerId))
			{
				self::$ownerId = $event['OWNER_ID'];
			}

			$accessController = new EventAccessController(self::$userId);
			$eventModel = \CCalendarEvent::getEventModelForPermissionCheck((int)$event['ID'], $event);
			if ($checkPermissions && !$accessController->check(ActionDictionary::ACTION_EVENT_DELETE, $eventModel))
			{
				return Loc::getMessage('EC_ACCESS_DENIED');
			}

			CCalendarSect::UpdateModificationLabel($event['SECT_ID']);

			if ($doExternalSync !== false && $event['SECT_ID'])
			{
				$bGoogleApi = self::isGoogleApiEnabled() && $event['CAL_TYPE'] === 'user';
				$bCalDav = self::IsCalDAVEnabled() && $event['CAL_TYPE'] === 'user';
				$bExchangeEnabled = self::IsExchangeEnabled() && $event['CAL_TYPE'] === 'user';

				if ($bExchangeEnabled || $bCalDav || $bGoogleApi)
				{
					$res = CCalendarSync::DoDeleteToDav([
						'bCalDav' => $bCalDav,
						'bExchangeEnabled' => $bExchangeEnabled,
						'sectionId' => $event['SECT_ID'],
					], $event);

					if ($res !== true && self::$silentErrorMode)
					{
						self::ThrowError($res);
					}
				}
			}

			$sendNotification = $params['sendNotification'] ?? (($params['recursionMode'] ?? null) !== 'all');
			$userId = !empty($params['userId']) ? (int)$params['userId'] : self::$userId;

			$res = CCalendarEvent::Delete([
				'id' => $id,
				'Event' => $event,
				'bMarkDeleted' => $markDeleted,
				'originalFrom' => $originalFrom,
				'userId' => $userId,
				'sendNotification' => $sendNotification,
				'requestUid' => $params['requestUid'] ?? null,
			]);

			if (($params['recursionMode'] ?? null) !== 'this' && !empty($event['RECURRENCE_ID']))
			{
				self::DeleteEvent($event['RECURRENCE_ID'], $doExternalSync, [
					'sendNotification' => $sendNotification,
					'originalFrom' => $originalFrom,
				]);
			}

			if (CCalendarEvent::CheckRecurcion($event))
			{
				$events = CCalendarEvent::GetEventsByRecId($id);

				foreach($events as $ev)
				{
					self::DeleteEvent($ev['ID'], $doExternalSync, [
						'sendNotification' => $sendNotification,
						'originalFrom' => $originalFrom,
					]);
				}
			}

			if (isset($params['recursionMode']) && $params['recursionMode'] === 'all' && !empty($event['ATTENDEE_LIST']))
			{
				foreach($event['ATTENDEE_LIST'] as $attendee)
				{
					if ($attendee['status'] !== 'N')
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendee["id"]);
						CCalendarNotify::Send([
							"mode" => 'cancel_all',
							"name" => $event['NAME'],
							"from" => $event['DATE_FROM'],
							"guestId" => $attendee["id"],
							"eventId" => $event['PARENT_ID'],
							"userId" => $event['MEETING_HOST'],
							"fields" => $event,
						]);
					}
				}
			}

			return $res;
		}

		return false;
	}

	public static function SetOffset($userId = false, $value = 0)
	{
		if ($userId === false)
			self::$offset = $value;
		else
			self::$arTimezoneOffsets[$userId] = $value;
	}

	public static function CollectCalDAVErros($arErrors = [])
	{
		if (empty($arErrors) || !is_array($arErrors))
		{
			return '[EC_NO_EXCH] ' . Loc::getMessage('EC_NO_CAL_DAV');
		}

		$str = "";
		foreach ($arErrors as $error)
		{
			$str .= "[". $error[0]."] ". $error[1]."\n";
		}

		return $str;
	}

	public static function GetPathForCalendarEx($userId = 0)
	{
		$userId = (int)$userId;

		$cacheId = 'calendar_path_settings_'.$userId;
		$obCache = new CPHPCache;

		if($obCache->InitCache(3600 * 6, $cacheId, '/calendar/'.$cacheId))
		{
			$calendarUrl = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();

			$bExtranet = Loader::includeModule('extranet');
			// It's extranet user
			if ($bExtranet && self::IsExtranetUser($userId))
			{
				$siteId = CExtranet::GetExtranetSiteID();
			}
			else
			{
				$siteId = $bExtranet && !self::IsExtranetUser($userId)
					? CSite::GetDefSite()
					: self::GetSiteId();

				if (self::$siteId == $siteId
					&& isset(self::$pathesForSite)
					&& is_array(self::$pathesForSite))
				{
					self::$pathes[$siteId] = self::$pathesForSite;
				}
			}

			if (!isset(self::$pathes[$siteId]) || !is_array(self::$pathes[$siteId]))
			{
				self::$pathes[$siteId] = self::GetPathes($siteId);
			}

			$calendarUrl = self::$pathes[$siteId]['path_to_user_calendar'] ?? '';
			$calendarUrl = str_replace(array('#user_id#', '#USER_ID#'), $userId, $calendarUrl);
			$calendarUrl = self::GetServerPath().$calendarUrl;

			$obCache->EndDataCache($calendarUrl);
		}

		return $calendarUrl;
	}

	public static function IsExtranetUser($userId = 0)
	{
		if (!$userId)
		{
			return true;
		}

		$departments = self::GetUserDepartment($userId);
		if (
			!$departments
			|| empty($departments)
		)
		{
			return true;
		}

		return false;
	}

	public static function GetUserDepartment($userId = 0)
	{
		if (!isset(self::$arUserDepartment[$userId]))
		{
			$rsUser = CUser::GetByID($userId);
			if($arUser = $rsUser->Fetch())
			{
				self::SetUserDepartment($userId, $arUser["UF_DEPARTMENT"]);
			}
		}

		return self::$arUserDepartment[$userId];
	}

	public static function SetUserDepartment($userId = 0, $dep = [])
	{
		if (!is_array($dep))
			$dep = [];
		self::$arUserDepartment[$userId] = $dep;
	}

	public static function HandleImCallback($module, $tag, $value, $arNotify)
	{
		$userId = self::GetCurUserId();
		if ($module === "calendar" && $userId)
		{
			$arTag = explode("|", $tag);
			$eventId = (int)$arTag[2];
			if ($arTag[0] === "CALENDAR" && $arTag[1] === "INVITE" && $eventId && $userId)
			{
				CCalendarEvent::SetMeetingStatus([
					'userId' => $userId,
					'eventId' => $eventId,
					'status' => $value === 'Y' ? 'Y' : 'N',
					'personalNotification' => true,
				]);

				return $value === 'Y' ? Loc::getMessage('EC_PROP_CONFIRMED_TEXT_Y') : Loc::getMessage('EC_PROP_CONFIRMED_TEXT_N');
			}
		}
	}

	public static function SetSettings($settings = [], $clearOptions = false)
	{
		$arPathes = self::GetPathesList();
		$optionNames = [
			'work_time_start',
			'work_time_end',
			'year_holidays',
			'year_workdays',
			'week_holidays',
			'week_start',
			'user_name_template',
			'sync_by_push',
			'user_show_login',
			'rm_iblock_type',
			'rm_iblock_id',
			'denied_superpose_types',
			'pathes_for_sites',
			'pathes',
			'dep_manager_sub',
			'forum_id',
			'rm_for_sites',
		];

		$optionNames = array_merge($optionNames, $arPathes);
		if (isset($settings['rm_iblock_ids']) && !$settings['rm_for_sites'])
		{
			foreach($settings['rm_iblock_ids'] as $site => $value)
			{
				COption::SetOptionString("calendar", 'rm_iblock_id', $value, false, $site);
			}
		}

		foreach($optionNames as $opt)
		{
			if ($clearOptions)
			{
				COption::RemoveOption("calendar", $opt);
			}
			else if (isset($settings[$opt]))
			{
				if ($opt === 'rm_iblock_id' && !$settings['rm_for_sites'])
				{
					continue;
				}

				if ($opt === 'sync_by_push')
				{
					if (self::isOffice365ApiEnabled() || self::isGoogleApiEnabled())
					{
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\Managers\\DataExchangeManager::importAgent();", 'calendar');
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\Managers\\PushWatchingManager::renewWatchChannels();", 'calendar');

						if ($settings[$opt])
						{
							// legacy
							\CAgent::RemoveAgent(
								"\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();",
								"calendar"
							);
							// actual
							\CAgent::AddAgent(
								"\\Bitrix\\Calendar\\Sync\\Managers\\PushWatchingManager::renewWatchChannels();",
								'calendar',
								'N',
								3600
							);
						}
						else
						{
							global $DB;
							// legacy
							\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();", "calendar");
							\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");
							$DB->Query("DELETE FROM b_agent WHERE NAME LIKE '%GoogleApiPush::checkPushChannel%'");
							// actual
							\CAgent::AddAgent(
								"\\Bitrix\\Calendar\\Sync\\Managers\\DataExchangeManager::importAgent();",
								'calendar',
								'N',
								180
							);
						}
					}
				}

				if ($opt === 'pathes' && is_array($settings[$opt]))
				{
					$sitesPathes = $settings[$opt];

					$ar = [];
					$arAffectedSites = [];
					foreach($sitesPathes as $s => $pathes)
					{
						$affect = false;
						foreach($arPathes as $path)
						{
							if ($pathes[$path] != $settings[$path])
							{
								$ar[$path] = $pathes[$path] ?? $settings[$path];
								$affect = true;
							}
						}

						if ($affect && !in_array($s, $arAffectedSites))
						{
							$arAffectedSites[] = $s;
							COption::SetOptionString("calendar", 'pathes_'.$s, serialize($ar));
						}
						else
						{
							COption::RemoveOption("calendar", 'pathes_'.$s);
						}
					}
					COption::SetOptionString("calendar", 'pathes_sites', serialize($arAffectedSites));
					continue;
				}

				else if ($opt === 'denied_superpose_types' && is_array($settings[$opt]))
				{
					$settings[$opt] = serialize($settings[$opt]);
				}

				else if ($opt === 'week_holidays' && is_array($settings[$opt]))
				{
					$settings[$opt] = implode(
						'|',
						array_intersect(array_unique($settings[$opt]), ['SU','MO','TU','WE','TH','FR','SA'])
					);
				}

				COption::SetOptionString("calendar", $opt, $settings[$opt]);
			}
		}
	}

	public static function IsBitrix24()
	{
		return \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
	}

	public static function SearchAttendees($name = '', $Params = [])
	{
		if (!isset($Params['arFoundUsers']))
		{
			$Params['arFoundUsers'] = CSocNetUser::SearchUser($name);
		}

		$arUsers = [];
		if (!is_array($Params['arFoundUsers']) || empty($Params['arFoundUsers']))
		{
			if ($Params['addExternal'] !== false)
			{
				if (check_email($name, true))
				{
					$arUsers[] = array(
						'type' => 'ext',
						'email' => htmlspecialcharsex($name),
					);
				}
				else
				{
					$arUsers[] = array(
						'type' => 'ext',
						'name' => htmlspecialcharsex($name),
					);
				}
			}
		}
		else
		{
			foreach ($Params['arFoundUsers'] as $userId => $userName)
			{
				$userId = (int)$userId;

				$r = CUser::GetList('id', 'asc', array("ID_EQUAL_EXACT" => $userId, "ACTIVE" => "Y"));

				if (!$User = $r->Fetch())
				{
					continue;
				}
				$name = trim($User['NAME'].' '.$User['LAST_NAME']);
				if (!$name)
				{
					$name = trim($User['LOGIN']);
				}

				$arUsers[] = array(
					'type' => 'int',
					'id' => $userId,
					'name' => $name,
					'status' => 'Q',
					'busy' => 'free',
				);
			}
		}
		return $arUsers;
	}

	public static function GetGroupMembers($groupId)
	{
		$dbMembers = CSocNetUserToGroup::GetList(
			array("RAND" => "ASC"),
			array(
				"GROUP_ID" => $groupId,
				"<=ROLE" => SONET_ROLES_USER,
				"USER_ACTIVE" => "Y",
			),
			false,
			false,
			array("USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN")
		);

		$arMembers = [];
		if ($dbMembers)
		{
			while ($Member = $dbMembers->GetNext())
			{
				$name = trim($Member['USER_NAME'].' '.$Member['USER_LAST_NAME']);
				if ($name == '')
					$name = trim($Member['USER_LOGIN']);
				$arMembers[] = array('id' => $Member["USER_ID"],'name' => $name);
			}
		}
		return $arMembers;
	}

	public static function ReminderAgent($eventId = 0, $userId = 0, $viewPath = '', $calendarType = '', $ownerId = 0, $index = 0)
	{
		CCalendarReminder::ReminderAgent($eventId, $userId, $viewPath, $calendarType, $ownerId, $index);
	}

	public static function GetMaxTimestamp()
	{
		return self::CALENDAR_MAX_TIMESTAMP;
	}

	public static function GetOwnerName($type = '', $ownerId = '')
	{
		$type = mb_strtolower($type);
		$key = $type.'_'.$ownerId;

		if (isset(self::$ownerNames[$key]))
		{
			return self::$ownerNames[$key];
		}

		$ownerName = '';
		if($type === 'user')
		{
			$ownerName = self::GetUserName($ownerId);
		}
		elseif($type === 'group')
		{
			// Get group name
			if (!Loader::includeModule("socialnetwork"))
			{
				return $ownerName;
			}

			if ($arGroup = CSocNetGroup::GetByID($ownerId))
			{
				$ownerName = $arGroup["~NAME"];
			}
		}
		else
		{
			// Get type name
			$arTypes = CCalendarType::GetList(array("arFilter" => array("XML_ID" => $type)));
			$ownerName = $arTypes[0]['NAME'];
		}
		self::$ownerNames[$key] = $ownerName;
		$ownerName = is_string($ownerName) ? trim($ownerName) : '';

		return $ownerName;
	}

	public static function GetTimezoneOffset($timezoneId, $dateTimestamp = false)
	{
		$offset = 0;
		if ($timezoneId)
		{
			try
			{
				$oTz = new DateTimeZone($timezoneId);
				if ($oTz)
				{
					$offset = $oTz->getOffset(new DateTime($dateTimestamp ? "@$dateTimestamp" : "now", $oTz));
				}
			}
			catch(Exception $e){}
		}
		return $offset;
	}

	public static function GetAbsentEvents($params)
	{
		if (!isset($params['arUserIds']))
			return false;

		return CCalendarEvent::GetAbsent($params['arUserIds'], $params);
	}

	/**
	 * @deprecated
	 */
	public static function GetAccessibilityForUsers($params)
	{
		if (!isset($params['checkPermissions']))
		{
			$params['checkPermissions'] = true;
		}

		$res = CCalendarEvent::GetAccessibilityForUsers([
			'users' => $params['users'],
			'from' => $params['from'],
			'to' => $params['to'],
			'curEventId' => $params['curEventId'] ?? null,
			'checkPermissions' => $params['checkPermissions'],
		]);

		// Fetch absence from intranet
		if (isset($params['getFromHR']) && self::IsIntranetEnabled())
		{
			$resHR = CIntranetUtils::GetAbsenceData(
				array(
					'DATE_START' => $params['from'],
					'DATE_FINISH' => $params['to'],
					'USERS' => $params['users'],
					'PER_USER' => true,
					'SELECT' => array('ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO'),
				),
				BX_INTRANET_ABSENCE_HR
			);

			foreach($resHR as $userId => $forUser)
			{
				if (!isset($res[$userId]) || !is_array($res[$userId]))
				{
					$res[$userId] = [];
				}

				foreach($forUser as $event)
				{
					$res[$userId][] = [
						'FROM_HR' => true,
						'ID' => $event['ID'],
						'DT_FROM' => $event['DATE_ACTIVE_FROM'],
						'DT_TO' => $event['DATE_ACTIVE_TO'],
						'ACCESSIBILITY' => 'absent',
						'IMPORTANCE' => 'normal',
						"FROM" => self::Timestamp($event['DATE_ACTIVE_FROM']),
						"TO" => self::Timestamp($event['DATE_ACTIVE_TO']),
					];
				}
			}
		}

		return $res;
	}

	public static function GetNearestEventsList($params = [])
	{
		$type = $params['bCurUserList'] ? 'user' : $params['type'];
		$isFromRest = ($params['fromRest'] ?? false) === true;

		// Get current user id
		if (!isset($params['userId']) || $params['userId'] <= 0)
		{
			$curUserId = self::GetCurUserId();
		}
		else
		{
			$curUserId = (int)$params['userId'];
		}

		$accessController = new TypeAccessController($curUserId);
		if (!$accessController->check(ActionDictionary::ACTION_TYPE_VIEW, TypeModel::createFromXmlId($type)))
		{
			return 'access_denied';
		}

		if (
			$params['bCurUserList']
			&& (
				$curUserId <= 0
				|| (
					class_exists('CSocNetFeatures')
					&& !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $curUserId, "calendar")
				)
			)
		)
		{
			return 'inactive_feature';
		}

		$maxAmount = isset($params['maxAmount']) && (int)$params['maxAmount'] > 0
			? (int)$params['maxAmount']
			: 75
		;

		$arFilter = array(
			'CAL_TYPE' => $type,
			'FROM_LIMIT' => $params['fromLimit'],
			'TO_LIMIT' => $params['toLimit'],
			'DELETED' => 'N',
			'ACTIVE_SECTION' => 'Y',
		);

		if ($params['bCurUserList'])
		{
			$arFilter['OWNER_ID'] = $curUserId;
		}

		if (isset($params['sectionId']) && $params['sectionId'])
		{
			$arFilter["SECTION"] = $params['sectionId'];
		}

		if ($type === 'user')
		{
			unset($arFilter['CAL_TYPE']);
		}

		$selectFields = [
			'ID',
			'PARENT_ID',
			'NAME',
			'OWNER_ID',
			'RRULE',
			'EXDATE',
			'DATE_FROM',
			'DATE_TO',
			'TZ_FROM',
			'TZ_TO',
			'TZ_OFFSET_FROM',
			'TZ_OFFSET_TO',
			'IS_MEETING',
			'MEETING_STATUS',
			'CAL_TYPE',
			'DT_LENGTH',
			'DT_SKIP_TIME',
			'SECTION_ID',
			'DATE_FROM_TS_UTC',
			'DATE_TO_TS_UTC',
		];

		if ($isFromRest)
		{
			$selectFields = ['*'];
		}

		$eventsList = CCalendarEvent::GetList(
			[
				'arFilter' => $arFilter,
				'arSelect' => $selectFields,
				'parseRecursion' => true,
				'fetchAttendees' => $isFromRest,
				'userId' => $curUserId,
				'fetchMeetings' => $type === 'user',
				'preciseLimits' => true,
				'skipDeclined' => true,
				'getUserfields' => $isFromRest,
			]
		);

		$pathToCalendar = self::GetPathForCalendarEx($curUserId);

		if (self::Date(time(), false) === $params['fromLimit'])
		{
			$limitTime = time();
		}
		else
		{
			$limitTime = self::Timestamp($params['fromLimit']);
		}

		$limitTime -= (int)date("Z", $limitTime);
		$entryList = [];

		foreach ($eventsList as $event)
		{
			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] === 'N')
			{
				continue;
			}

			if ($type === 'user' && !$event['IS_MEETING'] && $event['CAL_TYPE'] !== 'user')
			{
				continue;
			}

			$fromTs = self::Timestamp($event['DATE_FROM']);
			$toTs = $fromTs + $event['DT_LENGTH'];

			$toTsUtc = $toTs - $event['TZ_OFFSET_FROM'];

			if ($toTsUtc >= $limitTime)
			{
				if ($event['DT_SKIP_TIME'] !== "Y")
				{
					$fromTs -= $event['~USER_OFFSET_FROM'];
					$toTs -= $event['~USER_OFFSET_TO'];
				}
				$event['DATE_FROM'] = self::Date($fromTs, $event['DT_SKIP_TIME'] !== 'Y');
				$event['DATE_TO'] = self::Date($toTs, $event['DT_SKIP_TIME'] !== 'Y');
				unset($event['TZ_FROM'], $event['TZ_TO'], $event['TZ_OFFSET_FROM'], $event['TZ_OFFSET_TO']);
				$event['DT_FROM_TS'] = $fromTs;
				$event['DT_TO_TS'] = $toTs;

				$event['~URL'] = \CHTTP::urlAddParams($pathToCalendar, [
					'EVENT_ID' => $event['ID'],
					'EVENT_DATE' => self::Date($fromTs, false),
				]);

				$event['~WEEK_DAY'] = FormatDate("D", $fromTs);

				$event['~FROM_TO_HTML'] = self::GetFromToHtml(
					$fromTs,
					$toTs,
					$event['DT_SKIP_TIME'] === 'Y',
					$event['DT_LENGTH']
				);

				$entryList[] = $event;
			}
		}

		// Sort by DATE_FROM_TS
		usort($entryList, static function($a, $b){
			if ($a['DT_FROM_TS'] === $b['DT_FROM_TS'])
			{
				return 0;
			}
			return $a['DT_FROM_TS'] < $b['DT_FROM_TS'] ? -1 : 1;
		});
		array_splice($entryList, $maxAmount);

		return $entryList;
	}

	public static function GetAccessibilityForMeetingRoom($params)
	{
		return Rooms\IBlockMeetingRoom::getAccessibilityForMeetingRoom($params);
	}

	public static function GetMeetingRoomById($params)
	{
		return Rooms\IBlockMeetingRoom::getMeetingRoomById($params);
	}

	public static function ReleaseLocation($loc)
	{
		Rooms\Util::releaseLocation($loc);
	}

	public static function ReleaseMeetingRoom($params)
	{
		Rooms\IBlockMeetingRoom::releaseMeetingRoom($params);
	}

	/*
	 * $params['from'], $params['from'] - datetime in UTC
	 * */

	public static function GetDavCalendarEventsList($calendarId, $arFilter = [])
	{
		[$sectionId, $entityType, $entityId] = $calendarId;

		self::SetOffset(false, 0);
		$arFilter1 = array(
			'OWNER_ID' => $entityId,
			'DELETED' => 'N',
		);

		if (isset($arFilter['DAV_XML_ID']))
		{
			unset($arFilter['DATE_START'], $arFilter['FROM_LIMIT'], $arFilter['DATE_END'], $arFilter['TO_LIMIT']);
		}
		else
		{
			if (isset($arFilter['DATE_START']))
			{
				$arFilter['FROM_LIMIT'] = $arFilter['DATE_START'];
				unset($arFilter['DATE_START']);
			}
			if (isset($arFilter['DATE_END']))
			{
				$arFilter['TO_LIMIT'] = $arFilter['DATE_END'];
				unset($arFilter['DATE_END']);
			}
		}

		$fetchMeetings = true;
		if ($sectionId > 0)
		{
			$arFilter['SECTION'] = $sectionId;
			$fetchMeetings = false;
			if ($entityType === 'user')
			{
				$fetchMeetings = self::GetMeetingSection($entityId) == $sectionId;
			}
		}
		$arFilter = array_merge($arFilter1, $arFilter);

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'getUserfields' => false,
				'parseRecursion' => false,
				'fetchAttendees' => false,
				'fetchMeetings' => $fetchMeetings,
				'userId' => self::GetCurUserId(),
			)
		);

		$result = [];
		foreach ($arEvents as $event)
		{
			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] === 'N')
			{
				continue;
			}

			// Skip events from where owner is host of the meeting and it's meeting from other section
			if (
				$entityType === 'user'
				&& $event['IS_MEETING']
				&& (int)$event['MEETING_HOST'] === (int)$entityId
				&& (int)$event['SECT_ID'] !== (int)$sectionId
			)
			{
				continue;
			}

			$event['XML_ID'] = $event['DAV_XML_ID'];
			if ($event['LOCATION'] !== '')
			{
				$event['LOCATION'] = self::GetTextLocation($event['LOCATION']);
			}
			$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
			$result[] = $event;
		}

		return $result;
	}

	public static function GetTextLocation($loc = '')
	{
		return Rooms\Util::getTextLocation($loc);
	}

	public static function ParseLocation($location = '')
	{
		return Rooms\Util::parseLocation($location);
	}

	/* * * * RESERVE MEETING ROOMS  * * * */

	public static function GetUserPermissionsForCalendar($calendarId, $userId)
	{
		[$sectionId, $entityType, $entityId] = $calendarId;
		$entityType = mb_strtolower($entityType);

		$accessController = new SectionAccessController((int)$userId);
		$sectionModel =
			SectionModel::createFromId((int)$sectionId)
				->setType($entityType)
				->setOwnerId((int)$entityId)
		;
		$request = [
			ActionDictionary::ACTION_SECTION_EDIT => [],
			ActionDictionary::ACTION_SECTION_EVENT_VIEW_FULL => [],
			ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME => [],
			ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE => [],
		];

		$result = $accessController->batchCheck($request, $sectionModel);
		$res = [
			'bAccess' => $result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME],
			'bReadOnly' => !$result[ActionDictionary::ACTION_SECTION_EDIT],
		];

		if ($res['bReadOnly'])
		{
			if ($result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TIME])
			{
				$res['privateStatus'] = 'time';
			}
			if ($result[ActionDictionary::ACTION_SECTION_EVENT_VIEW_TITLE])
			{
				$res['privateStatus'] = 'title';
			}
		}

		return $res;
	}

	public static function GetDayLen()
	{
		return self::DAY_LENGTH;
	}

	public static function UnParseTextLocation($loc = '')
	{
		return Rooms\Util::unParseTextLocation($loc);
	}

	public static function ClearExchangeHtml($html = "")
	{
		// Echange in chrome puts chr(13) instead of \n
		$html = str_replace(chr(13), "\n", trim($html, chr(13)));
		$html = preg_replace("/(\s|\S)*<a\s*name=\"bm_begin\"><\/a>/isu","", $html);
		$html = preg_replace("/<br>(\n|\r)+/isu","<br>", $html);
		return self::ParseHTMLToBB($html);
	}

	public static function ParseHTMLToBB($html = "")
	{
		$id = AddEventHandler("main", "TextParserBeforeTags", Array("CCalendar", "_ParseHack"));

		$TextParser = new CTextParser();
		$TextParser->allow = array("HTML" => "N", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "N", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "Y", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "Y", "ALIGN" => "Y");
		$html = $TextParser->convertText($html);

		$html = htmlspecialcharsback($html);
		// Replace BR
		$html = preg_replace("/\<br\s*\/*\>/isu","\n", $html);
		//replace /p && /div to \n
		$html = preg_replace("/\<\/(p|div)\>/isu","\n", $html);
		// Kill &nbsp;
		$html = preg_replace("/&nbsp;/isu","", $html);
		// For images in Office 365
		$html = preg_replace(
			"#<img[^>]+src\\s*=[\\s'\"]*((cid):[.\\-_:a-z0-9@]+)*[\\s'\"]*[^>]*>#isu",
			"[img]\\1[/img]", $html
		);
		// Kill tags
		$html = preg_replace("/\<([^>]*?)>/isu","", $html);
		// Clean multiple \n symbols
		$html = preg_replace("/\n[\s\n]+\n/", "\n" , $html);

		$html = htmlspecialcharsbx($html);

		RemoveEventHandler("main", "TextParserBeforeTags", $id);

		return $html;
	}

	public static function WeekDayByInd($i, $binv = true)
	{
		if ($binv)
		{
			$arDays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
		}
		else
		{
			$arDays = ['MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU'];
		}
		return $arDays[$i] ?? false;
	}

	public static function IndByWeekDay(string $weekday): int
	{
		$weekdays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
		$weekdays = array_combine($weekdays, array_keys(array_values($weekdays)));
		return $weekdays[$weekday] ?? 0;
	}

	public static function SaveEvent($params = [])
	{
		$res = self::SaveEventEx($params);

		if (is_array($res) && isset($res['originalDavXmlId']))
		{
			return $res;
		}

		if (is_array($res) && isset($res['id']))
		{
			return $res['id'];
		}

		return $res;
	}

	/**
	 * @throws Main\ObjectException
	 */
	public static function SaveEventEx($params = [])
	{
		$arFields = $params['arFields'];
		if (self::$type && !isset($arFields['CAL_TYPE']))
		{
			$arFields['CAL_TYPE'] = self::$type;
		}
		elseif (isset($arFields['SECTION_CAL_TYPE']) && !isset($arFields['CAL_TYPE']))
		{
			$arFields['CAL_TYPE'] = $arFields['SECTION_CAL_TYPE'];
		}
		if (self::$bOwner && !isset($arFields['OWNER_ID']))
		{
			$arFields['OWNER_ID'] = self::$ownerId;
		}
		elseif (isset($arFields['SECTION_OWNER_ID']) && !isset($arFields['OWNER_ID']))
		{
			$arFields['OWNER_ID'] = $arFields['SECTION_OWNER_ID'];
		}

		if (!isset($arFields['SKIP_TIME']) && isset($arFields['DT_SKIP_TIME']))
		{
			$arFields['SKIP_TIME'] = $arFields['DT_SKIP_TIME'] === 'Y';
		}
		else if(!isset($arFields['SKIP_TIME']))
		{
			$arFields['SKIP_TIME'] = false;
		}

		//flags for synchronize the instance of a recurring event
		//modeSync - edit mode instance for avoid unnecessary request (patch)
		//editParentEvents - editing the parent event of the following
		$params['modeSync'] = true;
		$params['editInstance'] = $params['editInstance'] ?? false;
		$params['editNextEvents'] = $params['editNextEvents'] ?? false;
		$params['editParentEvents'] = $params['editParentEvents'] ?? false;
		$params['editEntryUntil'] = $params['editEntryUntil'] ?? false;
		$params['originalDavXmlId'] = $params['originalDavXmlId'] ?? null;
		$params['originalFrom'] = $params['originalFrom'] ?? null;
		$params['instanceTz'] = $params['instanceTz'] ?? null;
		$params['syncCaldav'] = $params['syncCaldav'] ?? false;
		$params['sendInvitesToDeclined'] = $params['sendInvitesToDeclined'] ?? false;
		$params['autoDetectSection'] = $params['autoDetectSection'] ?? false;
		$userId = $params['userId'] ?? self::getCurUserId();
		$accessController = new EventAccessController($userId);

		$result = [];
		$sectionId =
			(!empty($arFields['SECTIONS']) && is_array($arFields['SECTIONS']))
				? $arFields['SECTIONS'][0]
				: (int)($arFields['SECTIONS'] ?? null);
		$bPersonal = self::IsPersonal($arFields['CAL_TYPE'] ?? null, $arFields['OWNER_ID'] ?? null, $userId);
		$checkPermission = !isset($params['checkPermission']) || $params['checkPermission'] !== false;
		$silentErrorModePrev = self::$silentErrorMode;
		self::SetSilentErrorMode();

		if (
			isset($arFields['DT_FROM'], $arFields['DT_TO'])
			&& !isset($arFields['DATE_FROM'])
			&& !isset($arFields['DATE_TO'])
		)
		{
			$arFields['DATE_FROM'] = $arFields['DT_FROM'];
			$arFields['DATE_TO'] = $arFields['DT_TO'];
			unset($arFields['DT_FROM'], $arFields['DT_TO']);
		}

		// Fetch current event
		$curEvent = false;
		$bNew = !isset($arFields['ID']) || !$arFields['ID'];
		if (!$bNew)
		{
			$curEvent = CCalendarEvent::GetList(
				[
					'arFilter' => [
						"ID" => (int)$arFields['ID'],
						"DELETED" => 'N',
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'fetchMeetings' => false,
					'userId' => $userId,
					'checkPermissions' => $checkPermission,
					'loadOriginalRecursion' => true,
				]
			);
			if ($curEvent)
			{
				$curEvent = $curEvent[0];
			}

			if (in_array($curEvent['EVENT_TYPE'] ?? '', Sharing\SharingEventManager::getSharingEventTypes()))
			{
				unset(
					$arFields['SECTIONS'],
					$arFields['DATE_FROM'],
					$arFields['DATE_TO'],
					$arFields['SKIP_TIME'],
					$arFields['TZ_FROM'],
					$arFields['TZ_TO'],
				);
				$arFields['RRULE'] = [
					'FREQ' => 'NONE',
					'INTERVAL' => 1,
				];
			}

			$canChangeDateRecurrenceEvent = isset($params['recursionEditMode'])
				&& in_array($params['recursionEditMode'], ['all', ''], true)
				&& (($arFields['DATE_FROM'] ?? null) !== ($curEvent['DATE_FROM'] ?? null))
				&& ($arFields['RRULE']['FREQ'] ?? null) !== 'NONE'
			;

			if ($canChangeDateRecurrenceEvent)
			{
				$arFields['DATE_FROM'] = self::GetOriginalDate(
					$arFields['DATE_FROM'],
					$curEvent['DATE_FROM'],
					$arFields['TZ_FROM'] ?? null
				);
				$arFields['DATE_TO'] = self::GetOriginalDate(
					$arFields['DATE_TO'],
					$curEvent['DATE_TO'],
					$arFields['TZ_TO'] ?? null
				);
			}

			$bPersonal = $bPersonal && self::IsPersonal($curEvent['CAL_TYPE'], $curEvent['OWNER_ID'], $userId);

			$arFields['CAL_TYPE'] = $curEvent['CAL_TYPE'];
			$arFields['OWNER_ID'] = $curEvent['OWNER_ID'];
			$arFields['CREATED_BY'] = $curEvent['CREATED_BY'];
			$arFields['ACTIVE'] = $curEvent['ACTIVE'] ?? null;

			$eventModel = CCalendarEvent::getEventModelForPermissionCheck((int)($curEvent['ID'] ?? 0), $curEvent, $userId);

			$accessCheckResult = $accessController->check(ActionDictionary::ACTION_EVENT_EDIT, $eventModel);
			$bChangeMeeting = !$checkPermission || $accessCheckResult;

			if (!$bChangeMeeting)
			{
				return Loc::getMessage('EC_ACCESS_DENIED');
			}

			if (!isset($arFields['NAME']))
			{
				$arFields['NAME'] = $curEvent['NAME'];
			}
			if (!isset($arFields['DESCRIPTION']))
			{
				$arFields['DESCRIPTION'] = $curEvent['DESCRIPTION'];
			}
			if (!isset($arFields['COLOR']) && $curEvent['COLOR'])
			{
				$arFields['COLOR'] = $curEvent['COLOR'];
			}
			if (!isset($arFields['TEXT_COLOR']) && !empty($curEvent['TEXT_COLOR']))
			{
				$arFields['TEXT_COLOR'] = $curEvent['TEXT_COLOR'];
			}
			if (!isset($arFields['SECTIONS']))
			{
				$arFields['SECTIONS'] = [$curEvent['SECT_ID']];
				$sectionId = !empty($arFields['SECTIONS']) ? $arFields['SECTIONS'][0] : 0;
			}
			if (!isset($arFields['IS_MEETING']))
			{
				$arFields['IS_MEETING'] = $curEvent['IS_MEETING'];
			}
			if (!isset($arFields['MEETING_HOST']))
			{
				$arFields['MEETING_HOST'] = $curEvent['MEETING_HOST'];
			}
			if (!isset($arFields['MEETING_STATUS']))
			{
				$arFields['MEETING_STATUS'] = $curEvent['MEETING_STATUS'];
			}
			if (!isset($arFields['ACTIVE']) && isset($curEvent['ACTIVE']))
			{
				$arFields['ACTIVE'] = $curEvent['ACTIVE'];
			}
			if (!isset($arFields['PRIVATE_EVENT']))
			{
				$arFields['PRIVATE_EVENT'] = $curEvent['PRIVATE_EVENT'];
			}
			if (!isset($arFields['ACCESSIBILITY']))
			{
				$arFields['ACCESSIBILITY'] = $curEvent['ACCESSIBILITY'];
			}
			if (!isset($arFields['IMPORTANCE']))
			{
				$arFields['IMPORTANCE'] = $curEvent['IMPORTANCE'];
			}
			if (!isset($arFields['SKIP_TIME']))
			{
				$arFields['SKIP_TIME'] = $curEvent['DT_SKIP_TIME'] === 'Y';
			}
			if (!isset($arFields['DATE_FROM']) && isset($curEvent['DATE_FROM']))
			{
				$arFields['DATE_FROM'] = $curEvent['DATE_FROM'];
			}
			if (!isset($arFields['DATE_TO']) && isset($curEvent['DATE_TO']))
			{
				$arFields['DATE_TO'] = $curEvent['DATE_TO'];
			}
			if (!isset($arFields['TZ_FROM']))
			{
				$arFields['TZ_FROM'] = $curEvent['TZ_FROM'];
			}
			if (!isset($arFields['TZ_TO']))
			{
				$arFields['TZ_TO'] = $curEvent['TZ_TO'];
			}
			if (!isset($arFields['RELATIONS']))
			{
				$arFields['RELATIONS'] = $curEvent['RELATIONS'];
			}
			if (!isset($arFields['MEETING']))
			{
				$arFields['MEETING'] = $curEvent['MEETING'];
			}
			if (!isset($arFields['SYNC_STATUS']) && $curEvent['SYNC_STATUS'])
			{
				$arFields['SYNC_STATUS'] = $curEvent['SYNC_STATUS'];
			}
			if (!isset($arFields['EVENT_TYPE']) && $curEvent['EVENT_TYPE'])
			{
				$arFields['EVENT_TYPE'] = $curEvent['EVENT_TYPE'];
			}
			$arFields['MEETING']['LANGUAGE_ID'] = self::getUserLanguageId((int)$userId);

			if (
				!isset($arFields['ATTENDEES']) && !isset($arFields['ATTENDEES_CODES'])
				&& $arFields['IS_MEETING']
				&& !empty($curEvent['ATTENDEE_LIST'])
				&& is_array($curEvent['ATTENDEE_LIST'])
			)
			{
				$arFields['ATTENDEES'] = [];
				foreach ($curEvent['ATTENDEE_LIST'] as $attendee)
				{
					$arFields['ATTENDEES'][] = $attendee['id'];
				}
			}
			if (!isset($arFields['ATTENDEES_CODES']) && $arFields['IS_MEETING'])
			{
				$arFields['ATTENDEES_CODES'] = $curEvent['ATTENDEES_CODES'];
			}

			if (!isset($arFields['LOCATION']) && $curEvent['LOCATION'] !== "")
			{
				$arFields['LOCATION'] = [
					'OLD' => $curEvent['LOCATION'],
					'NEW' => $curEvent['LOCATION'],
				];

				//if location wasn't change when updating event
				$parsedLoc = Bitrix\Calendar\Rooms\Util::parseLocation($curEvent['LOCATION']);
				if ($parsedLoc['room_event_id'])
				{
					$arFields['LOCATION']['NEW'] = 'calendar_' . $parsedLoc['room_id'];
				}
			}

			if (!$bChangeMeeting)
			{
				$arFields['IS_MEETING'] = $curEvent['IS_MEETING'];
			}

			if ($arFields['IS_MEETING'] && !$bPersonal && $arFields['CAL_TYPE'] === 'user')
			{
				$arFields['SECTIONS'] = [$curEvent['SECT_ID']];
			}

			// If it's attendee but modifying called from CalDav methods
			if (
				(!empty($params['bSilentAccessMeeting'])
					|| (isset($params['fromWebservice']) && $params['fromWebservice'] === true)
				)
				&& !empty($curEvent['IS_MEETING'])
				&& ($curEvent['PARENT_ID'] !== $curEvent['ID'])
			)
			{
				// TODO: It called when changes caused in google/webservise side but can't be
				// TODO: implemented because user is only attendee, not the owner of the event
				//Todo: we have to update such events back to revert changes from google
				return true; // CalDav will return 204
			}

			if (!isset($arFields["RRULE"]) && $curEvent["RRULE"] !== '' && ($params['fromWebservice'] ?? null) !== true)
			{
				$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent["RRULE"]);
			}

			if (
				(($params['fromWebservice'] ?? null) === true)
				&& $arFields["RRULE"] === -1
				&& CCalendarEvent::CheckRecurcion($curEvent)
			)
			{
				$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent['RRULE']);
			}

			if (!isset($arFields['EXDATE']) && !empty($arFields["RRULE"]))
			{
				$arFields['EXDATE'] = $curEvent['EXDATE'];
			}

			else if (
				isset($arFields['EXDATE'], $curEvent['EXDATE'])
				&& $arFields['EXDATE']
				&& $curEvent['EXDATE']
				&& !empty($arFields["RRULE"])
			)
			{
				$arFields['EXDATE'] = self::mergeExcludedDates($curEvent['EXDATE'], $arFields['EXDATE']);
			}

			if ($curEvent)
			{
				$params['currentEvent'] = $curEvent;
			}
		}
		elseif ($checkPermission && $sectionId > 0 && !$bPersonal)
		{
			$section = CCalendarSect::GetList(['arFilter' => ['ID' => $sectionId],
				'checkPermissions' => false,
				'getPermissions' => false
			])[0] ?? null;

			if ($section)
			{
				$arFields['CAL_TYPE'] = $section['CAL_TYPE'];
			}
			else
			{
				return self::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
			}

			$newEventModel =
				EventModel::createNew()
					->setOwnerId((int)$arFields['OWNER_ID'])
					->setSectionId((int)$sectionId)
					->setSectionType($arFields['CAL_TYPE'])
			;

			if (!$accessController->check(ActionDictionary::ACTION_EVENT_ADD, $newEventModel))
			{
				return self::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
			}
		}

		if ($params['autoDetectSection'] && $sectionId <= 0)
		{
			$sectionId = false;
			if ($arFields['CAL_TYPE'] === 'user')
			{
				$sectionId = self::GetMeetingSection($arFields['OWNER_ID'], true);
				if ($sectionId)
				{
					$res = CCalendarSect::GetList(
						[
							'arFilter' => [
								'CAL_TYPE' => $arFields['CAL_TYPE'],
								'OWNER_ID' => $arFields['OWNER_ID'],
								'ID' => $sectionId,
							],
						]
					);


					if (!$res || !$res[0] || CCalendarSect::CheckGoogleVirtualSection($res[0]['GAPI_CALENDAR_ID']))
					{
						$sectionId = false;
					}
				}
				else
				{
					$sectionId = false;
				}

				if ($sectionId)
				{
					$arFields['SECTIONS'] = [$sectionId];
				}
			}

			if (!$sectionId)
			{
				if (empty($arFields['CAL_TYPE']) || empty($arFields['OWNER_ID']))
				{
					return false;
				}

				$sectRes = CCalendarSect::GetSectionForOwner(
					$arFields['CAL_TYPE'],
					$arFields['OWNER_ID'],
					$params['autoCreateSection']
				);
				if ($sectRes['sectionId'] > 0)
				{
					$sectionId = $sectRes['sectionId'];
					$arFields['SECTIONS'] = [$sectionId];
					if ($sectRes['autoCreated'])
					{
						$params['bAffectToDav'] = false;
					}
				}
				else
				{
					return false;
				}
			}
		}

		if (isset($arFields["RRULE"]))
		{
			$arFields["RRULE"] = CCalendarEvent::CheckRRULE($arFields["RRULE"]);
		}

		if (!empty($arFields['TZ_FROM']) && is_string($arFields['TZ_FROM']))
		{
			$tzFrom = Util::prepareTimezone($arFields['TZ_FROM']);

			if (
				!empty($arFields['TZ_TO'])
				&& is_string($arFields['TZ_TO'])
				&& $arFields['TZ_TO'] !== $arFields['TZ_FROM']
			)
			{
				$tzTo = Util::prepareTimezone($arFields['TZ_TO']);
				$arFields['TZ_TO'] = $tzTo->getName();
			}
			else
			{
				$arFields['TZ_TO'] = $tzFrom->getName();
			}

			$arFields['TZ_FROM'] = $tzFrom->getName();
		}

		if ($bNew && !$params['editInstance'] && !($arFields['DAV_XML_ID'] ?? null))
		{
			$arFields['DAV_XML_ID'] = UidGenerator::createInstance()
				->setDate(
					new Date(
						Util::getDateObject(
							$arFields['DATE_FROM'],
							$arFields['SKIP_TIME'] ?? null,
							$arFields['TZ_FROM'] ?? null,
						)
					)
				)
				->setPortalName(Util::getServerName())
				->setUserId((int)$arFields['OWNER_ID'])
				->getUidWithDate();
		}
		elseif ($params['editInstance'])
		{
			$arFields['DAV_XML_ID'] = $params['currentEvent']['DAV_XML_ID'];
		}

		// Set version
		if (!isset($arFields['VERSION']) || ($arFields['VERSION'] <= ($curEvent['VERSION'] ?? null)))
		{
			$arFields['VERSION'] = ($curEvent['VERSION'] ?? null)
				? $curEvent['VERSION'] + 1
				: 1
			;
		}

		if ($params['autoDetectSection'] && $sectionId <= 0 && $arFields['OWNER_ID'] > 0)
		{
			$res = CCalendarSect::GetList(
				[
					'arFilter' => [
						'CAL_TYPE' => $arFields['CAL_TYPE'],
						'OWNER_ID' => $arFields['OWNER_ID'],
					],
					'checkPermissions' => false,
				]
			);
			if ($res && is_array($res) && isset($res[0]))
			{
				$sectionId = $res[0]['ID'];
			}
			else
			{
				$defCalendar = CCalendarSect::CreateDefault(array(
					'type' => $arFields['CAL_TYPE'],
					'ownerId' => $arFields['OWNER_ID'],
				));
				$sectionId = $defCalendar['ID'];
				self::SetCurUserMeetingSection($defCalendar['ID']);

				$params['bAffectToDav'] = false;
			}
			if ($sectionId > 0)
			{
				$arFields['SECTIONS'] = [$sectionId];
			}
			else
			{
				return false;
			}
		}

		$bExchange = self::IsExchangeEnabled() && $arFields['CAL_TYPE'] === 'user';
		$bCalDav = self::IsCalDAVEnabled() && $arFields['CAL_TYPE'] === 'user';

		if (
			(($params['editNextEvents'] ?? null) === false && ($params['recursionEditMode'] ?? null) === 'next')
			|| (in_array($params['recursionEditMode'] ?? null, ['this', 'skip'])
				&& ($params['editInstance'] ?? null) === false)
		)
		{
			$params['modeSync'] = false;

			if (($params['editParentEvents'] ?? null) === true)
			{
				$params['modeSync'] = true;
			}
		}

		if (
			(
				($params['bAffectToDav'] ?? null) !== false
				&& ($bExchange || $bCalDav)
				&& $sectionId > 0
				&& !(isset($params['dontSyncParent']) && $params['dontSyncParent'])
				&& ($params['overSaving'] ?? false) !== true
			)
			|| $params['syncCaldav']
		)
		{
			$davParams = [
				'bCalDav' => $bCalDav,
				'bExchange' => $bExchange,
				'sectionId' => $sectionId,
				'modeSync' => $params['modeSync'],
				'editInstance' => $params['editInstance'],
				'originalDavXmlId' => $params['originalDavXmlId'],
				'instanceTz' => $params['instanceTz'],
				'editParentEvents' => $params['editParentEvents'],
				'editNextEvents' => $params['editNextEvents'],
				'syncCaldav' => $params['syncCaldav'],
				'parentDateFrom' => ($params['parentDateFrom'] ?? null),
				'parentDateTo' => ($params['parentDateTo'] ?? null),
			];

			$res = CCalendarSync::DoSaveToDav( $arFields, $davParams, $curEvent);

			if ($res !== true && self::$silentErrorMode === true)
			{
				self::ThrowError($res);
			}
		}

		$params['arFields'] = $arFields;
		$params['userId'] = $userId;
		$params['path'] = self::GetPath($arFields['CAL_TYPE'], $arFields['OWNER_ID'], 1);

		$isSharingEvent =
			isset($curEvent['EVENT_TYPE'])
			&& in_array($curEvent['EVENT_TYPE'], SharingEventManager::getSharingEventTypes(), true)
		;

		$params['isSharingEvent'] = $isSharingEvent;

		if (!empty($arFields['ID']) && $isSharingEvent)
		{
			SharingEventManager::onSharingEventEdit($arFields);
		}

		if (
			$curEvent
			&& in_array(($params['recursionEditMode'] ?? null), ['this', 'next'], true)
			&& CCalendarEvent::CheckRecurcion($curEvent)
		)
		{
			// Edit only current instance of the set of recurrent events
			if ($params['recursionEditMode'] === 'this')
			{
				// 1. Edit current reccurent event: exclude current date
				$excludeDates = CCalendarEvent::GetExDate($curEvent['EXDATE']);
				$excludeDate = self::Date(
					self::Timestamp($params['currentEventDateFrom'] ?? $arFields['DATE_FROM']),
					false
				);
				$excludeDates[] = $excludeDate;

				$saveEventData = [
					'recursionEditMode' => 'skip',
					'silentErrorMode' => $params['silentErrorMode'],
					'sendInvitesToDeclined' => $params['sendInvitesToDeclined'],
					'sendInvitations' => false,
					'sendEditNotification' => false,
					'userId' => $userId,
					'requestUid' => $params['requestUid'] ?? null,
				];

				$arFieldsCurrent = [
					'ID' => $curEvent["ID"],
					'EXDATE' => CCalendarEvent::SetExDate($excludeDates),
				];

				if (
					!empty($params['arFields']['SECTIONS'][0])
					&& (int)$curEvent['SECTION_ID'] !== (int)$params['arFields']['SECTIONS'][0]
				)
				{
					$arFieldsCurrent['SECTIONS'] = $params['arFields']['SECTIONS'];
					$arFieldsCurrent['CAL_TYPE'] = $params['arFields']['CAL_TYPE'];
					$arFieldsCurrent['OWNER_ID'] = $userId;
				}

				$saveEventData['arFields'] = $arFieldsCurrent;

				// Save current event
				$id = self::SaveEvent($saveEventData);

				// 2. Copy event with new changes, but without recursion
				$newParams = $params;
				$newParams['sendEditNotification'] = false;

				if (!($newParams['arFields']['MEETING']['REINVITE'] ?? null))
				{
					$newParams['saveAttendeesStatus'] = true;
				}

				$newParams['arFields']['RECURRENCE_ID'] = $curEvent['RECURRENCE_ID'] ?: $newParams['arFields']['ID'];
				$newParams['arFields']['ORIGINAL_RECURSION_ID'] = (int)($curEvent['ORIGINAL_RECURSION_ID'] ?: $newParams['arFields']['ID']);

				unset(
					$newParams['arFields']['ID'],
					$newParams['arFields']['DAV_XML_ID'],
					$newParams['arFields']['G_EVENT_ID'],
					$newParams['arFields']['SYNC_STATUS'],
					$newParams['arFields']['CAL_DAV_LABEL'],
					$newParams['arFields']['RRULE'],
					$newParams['arFields']['EXDATE'],
					$newParams['recursionEditMode'],
				);

				$newParams['arFields']['REMIND'] = $params['currentEvent']['REMIND'];

				$fromTs = self::Timestamp($newParams['currentEventDateFrom']);
				$currentFromTs = self::Timestamp($newParams['arFields']['DATE_FROM']);
				$length = self::Timestamp($newParams['arFields']['DATE_TO']) - self::Timestamp($newParams['arFields']['DATE_FROM']);

				if (!isset($newParams['arFields']['DATE_FROM'], $newParams['arFields']['DATE_TO']))
				{
					$length = $curEvent['DT_LENGTH'];
					$currentFromTs = self::Timestamp($curEvent['DATE_FROM']);
				}

				$instanceDate = !isset($newParams['arFields']['DATE_FROM'])
					||self::Date(self::Timestamp($curEvent['DATE_FROM']), false) === self::Date($currentFromTs, false);

				if ($newParams['arFields']['SKIP_TIME'])
				{
					if ($instanceDate)
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($fromTs, false);
						$newParams['arFields']['DATE_TO'] = self::Date($fromTs + $length - self::GetDayLen(), false);
					}
					else
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($currentFromTs, false);
						$newParams['arFields']['DATE_TO'] = self::Date($currentFromTs + $length - self::GetDayLen(), false);
					}
				}
				elseif ($instanceDate)
				{
					$newFromTs = self::DateWithNewTime($currentFromTs, $fromTs);
					$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
					$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
				}

				$eventMod = $curEvent;
				if (!isset($eventMod['~DATE_FROM']))
				{
					$eventMod['~DATE_FROM'] = $eventMod['DATE_FROM'];
				}

				$eventMod['DATE_FROM'] = $newParams['currentEventDateFrom'];
				$commentXmlId = CCalendarEvent::GetEventCommentXmlId($eventMod);
				$newParams['arFields']['RELATIONS'] = array('COMMENT_XML_ID' => $commentXmlId);
				$newParams['editInstance'] = true;
				//original instance date start
				$newParams['arFields']['ORIGINAL_DATE_FROM'] = self::GetOriginalDate(
					$params['currentEvent']['DATE_FROM'],
					$eventMod['DATE_FROM'] ?? $newParams['currentEventDateFrom'],
					$newParams['arFields']['TZ_FROM']
				);
				$newParams['originalDavXmlId'] = $params['currentEvent']['G_EVENT_ID'];
				$newParams['instanceTz'] = $params['currentEvent']['TZ_FROM'];
				$newParams['parentDateFrom'] = $params['currentEvent']['DATE_FROM'];
				$newParams['parentDateTo'] = $params['currentEvent']['DATE_TO'];
				$newParams['requestUid'] = $params['requestUid'] ?? null;
				$newParams['sendInvitesToDeclined'] = $params['sendInvitesToDeclined'] ?? null;

				$result['recEventId'] = self::SaveEvent($newParams);
				(new Core\Managers\EventOriginalRecursion())->add(
					$result['recEventId'] ?? 0,
					$newParams['arFields']['ORIGINAL_RECURSION_ID'] ?? 0,
				);
			}
			// Edit all next instances of the set of recurrent events
			elseif(($params['recursionEditMode']) === 'next')
			{
				$currentDateTimestamp = self::Timestamp($params['currentEventDateFrom'] ?? null);

				// Copy event with new changes
				$newParams = $params;
				$recId = $curEvent['RECURRENCE_ID'] ?: $newParams['arFields']['ID'];

				$newParams['arFields']['RELATIONS'] ??= [];
				$newParams['arFields']['RELATIONS'] = [
					'ORIGINAL_RECURSION_ID' => $curEvent['RELATIONS']['ORIGINAL_RECURSION_ID'] ?? $recId,
				];
				$newParams['arFields']['ORIGINAL_RECURSION_ID'] = (int)($curEvent['ORIGINAL_RECURSION_ID'] ?? $recId);

				if (empty($newParams['arFields']['MEETING']['REINVITE']))
				{
					$newParams['saveAttendeesStatus'] = true;
				}

				$currentFromTs = self::Timestamp($newParams['arFields']['DATE_FROM'] ?? null);
				$length = self::Timestamp($newParams['arFields']['DATE_TO']) - self::Timestamp($newParams['arFields']['DATE_FROM']);

				if (!isset($newParams['arFields']['DATE_FROM']) || !isset($newParams['arFields']['DATE_TO']))
				{
					$length = $curEvent['DT_LENGTH'];
					$currentFromTs = self::Timestamp($curEvent['DATE_FROM']);
				}

				$instanceDate = !isset($newParams['arFields']['DATE_FROM'])
					||self::Date(self::Timestamp($curEvent['DATE_FROM']), false) === self::Date($currentFromTs, false);

				if ($newParams['arFields']['SKIP_TIME'])
				{
					if ($instanceDate)
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($currentDateTimestamp, false);
						$newParams['arFields']['DATE_TO'] = self::Date($currentDateTimestamp + $length, false);
					}
					else
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($currentFromTs, false);
						$newParams['arFields']['DATE_TO'] = self::Date($currentFromTs + $length, false);
					}
				}
				elseif ($instanceDate)
				{
					$newFromTs = self::DateWithNewTime($currentFromTs, $currentDateTimestamp);
					$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
					$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
				}

				if (isset($curEvent['EXDATE']) && $curEvent['EXDATE'] !== '')
				{
					$newParams['arFields']['EXDATE'] = $curEvent['EXDATE'];
				}

				if (isset($newParams['arFields']['RRULE']['COUNT']) && $newParams['arFields']['RRULE']['COUNT'] > 0)
				{
					$countParams = [
						'rrule' => $newParams['arFields']['RRULE'],
						'dateFrom' => $curEvent['DATE_FROM'],
						'dateTo' => $newParams['arFields']['DATE_FROM'],
						'timeZone' => $curEvent['TZ_FROM'],
					];

					$newParams['arFields']['RRULE']['COUNT'] = self::CountNumberFollowEvents($countParams);
					unset($newParams['arFields']['RRULE']['UNTIL'], $newParams['arFields']['RRULE']['~UNTIL']);
				}

				if (
					isset($newParams['arFields']['RRULE']['FREQ'])
					&& $newParams['arFields']['RRULE']['FREQ'] === 'WEEKLY'
					&& isset($curEvent['RRULE']['FREQ'])
					&& $curEvent['RRULE']['FREQ'] === 'WEEKLY'
					&& $newParams['arFields']['RRULE']['BYDAY'] === $curEvent['RRULE']['BYDAY']
				)
				{
					$currentDate = new Type\Date($params['currentEventDateFrom']);
					$currentFromDate = new Type\Date($newParams['arFields']['DATE_FROM']);
					$currentDateWeekday = self::WeekDayByInd($currentDate->format('N'));
					$currentFromDateWeekday = self::WeekDayByInd($currentFromDate->format('N'));

					if (isset($newParams['arFields']['RRULE']['BYDAY'][$currentDateWeekday]))
					{
						unset($newParams['arFields']['RRULE']['BYDAY'][$currentDateWeekday]);
					}

					$newParams['arFields']['RRULE']['BYDAY'][$currentFromDateWeekday] = $currentFromDateWeekday;
				}

				// Check if it's first instance of the series, so we shouldn't create another event
				if (self::Date(self::Timestamp($curEvent['DATE_FROM']), false) === self::Date($currentDateTimestamp, false))
				{
					$newParams['recursionEditMode'] = 'skip';
				}
				else
				{
					// 1. Edit current recurrent event: set finish date with date of current instance
					$arFieldsCurrent = [
						"ID" => $curEvent["ID"],
						"RRULE" => CCalendarEvent::ParseRRULE($curEvent['RRULE']),
					];
					$arFieldsCurrent['RRULE']['UNTIL'] = self::Date($currentDateTimestamp - self::GetDayLen(), false);
					unset($arFieldsCurrent['RRULE']['~UNTIL'], $arFieldsCurrent['RRULE']['COUNT']);

					if (
						!empty($params['arFields']['SECTIONS'][0])
						&& (int)$curEvent['SECTION_ID'] !== (int)$params['arFields']['SECTIONS'][0]
					)
					{
						$arFieldsCurrent['SECTIONS'] = $params['arFields']['SECTIONS'];
					}

					// Save current event
					$id = self::SaveEvent([
						'arFields' => $arFieldsCurrent,
						'silentErrorMode' => $params['silentErrorMode'] ?? null,
						'recursionEditMode' => 'skip',
						'sendInvitations' => false,
						'sendEditNotification' => false,
						'sendInvitesToDeclined' => $params['sendInvitesToDeclined'] ?? null,
						'userId' => $userId,
						'editNextEvents' => true,
						'editParentEvents' => true,
						'checkPermission' => $checkPermission,
						'requestUid' => $params['requestUid'] ?? null,
						'checkLocationOccupancyFields' => $newParams['arFields'],
						'checkLocationOccupancy' => $params['checkLocationOccupancy'] ?? false,
					]);

					unset($newParams['arFields']['ID'],
						$newParams['arFields']['DAV_XML_ID'],
						$newParams['arFields']['G_EVENT_ID'],
						$newParams['recursionEditMode']
					);
				}

				if (empty($newParams['arFields']['DAV_XML_ID']))
				{
					$newParams['arFields']['DAV_XML_ID'] = UidGenerator::createInstance()
						->setPortalName(Util::getServerName())
						->setDate(new Date(
							Util::getDateObject(
								$newParams['arFields']['ORIGINAL_DATE_FROM'] ?? null,
								$newParams['arFields']['SKIP_TIME'] ?? null,
								$newParams['arFields']['TZ_FROM'] ?? null
							)
						))
						->setUserId((int)($newParams['arFields']['OWNER_ID'] ?? null))
						->getUidWithDate()
					;
				}

				$newParams['sendInvitesToDeclined'] = $params['sendInvitesToDeclined'];
				$newParams['editNextEvents'] = true;
				$result = self::SaveEvent($newParams);
				if (!is_array($result))
				{
					$result = [
						'id' => $result,
						'recEventId' => $result,
					];
				}
				(new Core\Managers\EventOriginalRecursion())->add(
					$result['id'] ?? 0,
					$newParams['arFields']['ORIGINAL_RECURSION_ID'] ?? 0,
				);

				if ($recId)
				{
					$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recId, false);

					foreach($recRelatedEvents as $ev)
					{
						if ($ev['ID'] === $result['id'])
						{
							continue;
						}

						$evFromTs = self::Timestamp($ev['DATE_FROM']);

						if ($evFromTs > $currentDateTimestamp)
						{
							$newParams['arFields']['ID'] = $ev['ID'];
							$newParams['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($ev['RRULE']);

							if ($newParams['arFields']['SKIP_TIME'])
							{
								$newParams['arFields']['DATE_FROM'] = self::Date($evFromTs, false);
								$newParams['arFields']['DATE_TO'] = self::Date(self::Timestamp($ev['DATE_TO']), false);
							}
							else
							{
								$newFromTs = self::DateWithNewTime($currentFromTs, $evFromTs);
								$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
								$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
							}


							$newParams['arFields']['RECURRENCE_ID'] = $result['id'];
							$newParams['originalDavXmlId'] = $result['originalDavXmlId'];
							$newParams['arFields']['ORIGINAL_DATE_FROM'] = self::GetOriginalDate(
								$result['originalDateFrom'] ?? null,
								$ev['ORIGINAL_DATE_FROM'] ?? $newParams['currentEventDateFrom'],
								$result['instanceTz']
							);
							$newParams['instanceTz'] = $result['instanceTz'];
							$newParams['editInstance'] = true;

							unset($newParams['arFields']['EXDATE']);

							if (isset($newParams['arFields']['RELATIONS']['ORIGINAL_RECURSION_ID']))
							{
								unset($newParams['arFields']['RELATIONS']);
							}

							self::SaveEvent($newParams);
						}
					}
				}
			}
		}
		else
		{
			if (($params['recursionEditMode'] ?? null) !== 'all')
			{
				$params['recursionEditMode'] = 'skip';
			}
			else
			{
				$params['recursionEditMode'] = '';
			}

			$id = CCalendarEvent::Edit($params);

			if ($id)
			{
				$UFs = $params['UF'] ?? null;
				if(!empty($UFs) && is_array($UFs))
				{
					CCalendarEvent::UpdateUserFields($id, $UFs);

					if (!empty($arFields['IS_MEETING']) && !empty($UFs['UF_WEBDAV_CAL_EVENT']))
					{
						$UF = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("CALENDAR_EVENT", $id, LANGUAGE_ID);
						self::UpdateUFRights(
							$UFs['UF_WEBDAV_CAL_EVENT'],
							$arFields['ATTENDEES_CODES'] ?? null,
							$UF['UF_WEBDAV_CAL_EVENT'] ?? null
						);
					}
				}
			}

			if ($params['editNextEvents'] === true && $params['editParentEvents'] === false)
			{
				$result['originalDate'] = $params['arFields']['DATE_FROM'];
				$result['originalDavXmlId'] = $params['arFields']['DAV_XML_ID'];
				$result['instanceTz'] = $params['arFields']['TZ_FROM'];
				$result['recEventId'] = $id;
			}

			// Here we should select all events connected with edited via RECURRENCE_ID:
			// It could be original source event (without RECURRENCE_ID) or sibling events
			if (
				$curEvent
				&& !$params['recursionEditMode']
				&& !($params['arFields']['RECURRENCE_ID'] ?? null)
				&& CCalendarEvent::CheckRecurcion($curEvent)
			)
			{
				$events = [];
				$recId = $curEvent['RECURRENCE_ID'] ?: $curEvent['ID'];
				if ($curEvent['RECURRENCE_ID'] && $curEvent['RECURRENCE_ID'] !== $curEvent['ID'])
				{
					$masterEvent = CCalendarEvent::GetById($curEvent['RECURRENCE_ID']);
					if ($masterEvent)
					{
						$events[] = $masterEvent;
					}
				}

				if ($recId)
				{
					$instances = CCalendarEvent::GetList([
						'arFilter' => [
							'RECURRENCE_ID' => $recId,
						],
						'parseRecursion' => false,
						'setDefaultLimit' => false,
					]);

					if ($instances)
					{
						$events = array_merge($events, $instances);
					}
				}

				foreach($events as $ev)
				{
					if ($ev['ID'] !== $curEvent['ID'])
					{
						$newParams = $params;

						$newParams['arFields']['ID'] = $ev['ID'];
						$newParams['arFields']['RECURRENCE_ID'] = $ev['RECURRENCE_ID'];
						$newParams['arFields']['DAV_XML_ID'] = $ev['DAV_XML_ID'];
						$newParams['arFields']['G_EVENT_ID'] = $ev['G_EVENT_ID'];
						$newParams['arFields']['ORIGINAL_DATE_FROM'] = self::GetOriginalDate($arFields['DATE_FROM'], $ev['ORIGINAL_DATE_FROM'], $arFields['TZ_FROM']);
						$newParams['arFields']['CAL_DAV_LABEL'] = $ev['CAL_DAV_LABEL'];
						$newParams['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($ev['RRULE']);
						$newParams['recursionEditMode'] = 'skip';
						$newParams['currentEvent'] = $ev;

						$eventFromTs = self::Timestamp($ev['DATE_FROM']);
						$currentFromTs = self::Timestamp($newParams['arFields']['DATE_FROM']);
						$length = self::Timestamp($newParams['arFields']['DATE_TO']) - self::Timestamp($newParams['arFields']['DATE_FROM']);

						if ($newParams['arFields']['SKIP_TIME'])
						{
							$newParams['arFields']['DATE_FROM'] = $ev['DATE_FROM'];
							$newParams['arFields']['DATE_TO'] = self::Date($eventFromTs + $length, false);
						}
						else
						{
							$newFromTs = self::DateWithNewTime($currentFromTs, $eventFromTs);
							$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
							$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
						}

						if (isset($ev['EXDATE']) && $ev['EXDATE'])
						{
							$newParams['arFields']['EXDATE'] = $ev['EXDATE'];
						}

						if (isset($newParams['arFields']['RELATIONS']['ORIGINAL_RECURSION_ID']))
						{
							unset($newParams['arFields']['RELATIONS']);
						}

						self::SaveEvent($newParams);
					}
				}
			}

			if ($id)
			{
				self::syncChange($id, $arFields, $params, $curEvent ?: null);
			}

			$arFields['ID'] = $id;
			if (($params['overSaving'] ?? false) !== true)
			{
				foreach(GetModuleEvents("calendar", "OnAfterCalendarEventEdit", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array($arFields, $bNew, $userId));
				}
			}
		}

		self::SetSilentErrorMode($silentErrorModePrev);

		$result['id'] = $id ?? null;

		return $result;
	}

	private static function CountNumberFollowEvents($params)
	{
		$curCount = self::CountPastEvents($params);

		$count = (int)$params['rrule']['COUNT'] - $curCount;

		return (string)$count;
	}

	public static function getUserLanguageId(?int $userId): string
	{
		if (!$userId)
		{
			return LANGUAGE_ID;
		}

		if (isset(self::$userLanguageId[$userId]))
		{
			return self::$userLanguageId[$userId];
		}

		$user = Main\UserTable::query()
			->where('ID', $userId)
			->setSelect(['NOTIFICATION_LANGUAGE_ID'])
			->exec()
			->fetch()
		;

		self::$userLanguageId[$userId] = $user['NOTIFICATION_LANGUAGE_ID'] ?? LANGUAGE_ID;

		return self::$userLanguageId[$userId];
	}

	public static function CountPastEvents($params)
	{
		$curCount = 0;

		$dateFromTz = !empty($params['timeZone']) ? new \DateTimeZone($params['timeZone']) : new \DateTimeZone("UTC");
		$dateToTz = !empty($params['timeZone']) ? new \DateTimeZone($params['timeZone']) : new \DateTimeZone("UTC");
		$dateFrom = new Main\Type\DateTime(date('Ymd His',self::Timestamp($params['dateFrom'])), 'Ymd His', $dateFromTz);
		$dateTo = new Main\Type\DateTime(date('Ymd His',self::Timestamp($params['dateTo'])), 'Ymd His', $dateToTz);

		$parentInfoDate = getdate($dateFrom->getTimestamp());
		$dateTo->setTime($parentInfoDate['hours'], $parentInfoDate['minutes']);

		$diff = $dateFrom->getDiff($dateTo);

		if ($params['rrule']['FREQ'] === 'DAILY')
		{
			$diff = (int)$diff->format('%a');
			$curCount = $diff / (int)$params['rrule']['INTERVAL'];
		}

		if ($params['rrule']['FREQ'] === 'WEEKLY')
		{
			$diff = (int)$diff->format('%a');

			for ($i = 0; $i < $diff; $i++)
			{
				$timestamp = $dateFrom->getTimestamp();
				$date = getdate($timestamp);
				$weekday = mb_strtoupper(mb_substr($date['weekday'], 0, 2));

				if (in_array($weekday, $params['rrule']['BYDAY'], true))
				{
					$curCount++;
				}

				$dateFrom = $dateFrom->add('+1 day');
			}
		}

		if ($params['rrule']['FREQ'] === 'MONTHLY')
		{
			$diff = (int)$diff->format('%m');
			$curCount = $diff / (int)$params['rrule']['INTERVAL'];
		}

		if ($params['rrule']['FREQ'] === 'YEARLY')
		{
			$diff = (int)$diff->format('%y');
			$curCount = $diff / (int)$params['rrule']['INTERVAL'];
		}

		return $curCount;
	}

	public static function ThrowError($str)
	{
		if (self::$silentErrorMode)
		{
			self::$errors[] = $str;
			return false;
		}

		global $APPLICATION;
		echo '<!-- BX_EVENT_CALENDAR_ACTION_ERROR:'.$str.'-->';
		return $APPLICATION->ThrowException($str);
	}

	public static function GetErrors()
	{
		return self::$errors;
	}

	private static ?array $tasksForUpdateUFRights = null;

	private static function getTasksForUpdateUFRights(): array
	{
		if (!Loader::includeModule('webdav'))
		{
			return [];
		}

		if (self::$tasksForUpdateUFRights === null)
		{
			self::$tasksForUpdateUFRights = CWebDavIblock::GetTasks() ?? [];
		}

		return self::$tasksForUpdateUFRights;
	}

	public static function UpdateUFRights($files, $rights, $ufEntity = [])
	{
		global $USER;
		$arTasks = self::getTasksForUpdateUFRights();

		if (!is_array($rights) || sizeof($rights) <= 0)
			return false;
		if ($files===null || $files===false)
			return false;
		if (!is_array($files))
			$files = array($files);
		if (sizeof($files) <= 0)
			return false;
		if (!Loader::includeModule('iblock') || !Loader::includeModule('webdav'))
			return false;

		$arFiles = [];
		foreach($files as $id)
		{
			$id = (int)$id;
			if ($id)
			{
				$arFiles[] = $id;
			}
		}

		if (empty($arFiles))
		{
			return false;
		}

		$arCodes = [];
		foreach($rights as $value)
		{
			if (mb_strpos($value, 'SG') === 0)
			{
				$arCodes[] = $value . '_K';
			}
			$arCodes[] = $value;
		}
		$arCodes = array_unique($arCodes);

		$i = 0;
		$arViewRights = $arEditRights = [];
		$curUserID = 'U'.$USER->GetID();
		foreach($arCodes as $right)
		{
			if ($curUserID == $right) // do not override owner's rights
				continue;
			$key = 'n' . $i++;
			$arViewRights[$key] = array(
				'GROUP_CODE' => $right,
				'TASK_ID' => $arTasks['R'],
			);
		}

		$ibe = new CIBlockElement();
		$dbWDFile = $ibe->GetList([], array('ID' => $arFiles, 'SHOW_NEW' => 'Y'), false, false, array('ID', 'NAME', 'SECTION_ID', 'IBLOCK_ID', 'WF_NEW'));
		$iblockIds = [];
		if ($dbWDFile)
		{
			while ($arWDFile = $dbWDFile->Fetch())
			{
				$id = $arWDFile['ID'];

				if ($arWDFile['WF_NEW'] === 'Y')
				{
					$ibe->Update($id, ['BP_PUBLISHED' => 'Y']);
				}

				if (CIBlock::GetArrayByID($arWDFile['IBLOCK_ID'], "RIGHTS_MODE") === "E")
				{
					$ibRights = CWebDavIblock::_get_ib_rights_object('ELEMENT', $id, $arWDFile['IBLOCK_ID']);
					$ibRights->SetRights(CWebDavTools::appendRights($ibRights, $arViewRights, $arTasks));
					if(empty($iblockIds[$arWDFile['IBLOCK_ID']]))
						$iblockIds[$arWDFile['IBLOCK_ID']] = $arWDFile['IBLOCK_ID'];
				}
			}

			global $CACHE_MANAGER;

			foreach ($iblockIds as $iblockId)
			{
				$CACHE_MANAGER->ClearByTag('iblock_id_' . $iblockId);
			}

			unset($iblockId);
		}
	}

	public static function TempUser($TmpUser = false, $create = true, $ID = false)
	{
		global $USER;
		if ($create && $TmpUser === false && (!$USER || !is_object($USER)))
		{
			$USER = new CUser;
			if ($ID && (int)$ID > 0)
			{
				$USER->Authorize((int)$ID);
			}

			return $USER;
		}

		if (!$create && $USER && is_object($USER))
		{
			unset($USER);
			return false;
		}
		return false;
	}

	public static function SaveSection($params)
	{
		$type = $params['arFields']['CAL_TYPE'] ?? self::$type;

		// Exchange
		if (($params['bAffectToDav'] ?? null) !== false && $type === 'user' && self::IsExchangeEnabled(self::$ownerId))
		{
			$exchRes = true;
			$ownerId = $params['arFields']['OWNER_ID'] ?? self::$ownerId;

			if ($params['arFields']['IS_EXCHANGE'])
			{
				$exchRes = CDavExchangeCalendar::DoAddCalendar($ownerId, $params['arFields']);
			}

			if ($exchRes !== true)
			{
				if (!is_array($exchRes) || !isset($exchRes['XML_ID']))
				{
					return self::ThrowError(self::CollectExchangeErrors($exchRes));
				}

				// // It's ok, we successfuly save event to exchange calendar - and save it to DB
				$params['arFields']['DAV_EXCH_CAL'] = $exchRes['XML_ID'];
				$params['arFields']['DAV_EXCH_MOD'] = $exchRes['MODIFICATION_LABEL'];
			}
		}

		// Save here
		$id = (int)CCalendarSect::Edit($params);
		self::ClearCache(['section_list', 'event_list']);

		return $id;
	}

	public static function ClearCache($arPath = [])
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->ClearByTag("CALENDAR_EVENT_LIST");

		if (empty($arPath))
		{
			$arPath = [
				'access_tasks',
				'type_list',
				'section_list',
				'attendees_list',
				'event_list',
			];
		}
		elseif (!is_array($arPath))
		{
			$arPath = [$arPath];
		}

		if (!empty($arPath))
		{
			$cache = new CPHPCache;
			foreach($arPath as $path)
			{
				if ($path)
				{
					$cache->CleanDir(self::CachePath() . $path);
				}
			}
		}
	}

	public static function CachePath()
	{
		return self::$cachePath;
	}

	// * * * * * * * * * * * * CalDAV + Exchange * * * * * * * * * * * * * * * *

	public static function SyncCalendarItems($connectionType, $calendarId, $arCalendarItems): array
	{
		$arResult = [];
		self::$silentErrorMode = true;

		[$sectionId, $entityType, $entityId] = $calendarId;
		$entityType = mb_strtolower($entityType);

		if ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::EXCHANGE_TYPE)
		{
			$xmlIdField = 'DAV_EXCH_LABEL';
		}
		elseif ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE)
		{
			$xmlIdField = 'CAL_DAV_LABEL';
		}
		else
		{
			return [];
		}

		$eventsList = CCalendarEvent::GetList([
			'arSelect' => [
				'ID',
				'PARENT_ID',
				'RECURRENCE_ID',
				'DATE_TO',
				'DATE_FROM',
				'TZ_FROM',
				'DATE_TO_TS_UTC',
				'DATE_FROM_TS_UTC',
				'DAV_XML_ID',
				'DAV_EXCH_LABEL',
				'CAL_DAV_LABEL'
			],
			'arFilter' => [
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId,
				'SECTION' => $sectionId,
			],
			'getUserfields' => false,
			'parseRecursion' => false,
			'fetchAttendees' => false,
			'fetchMeetings' => false,
			'userId' => $entityType === 'user' ? $entityId : 0,
		]);

		foreach ($eventsList as $event)
		{
			$eventXmlId = $event['DAV_XML_ID'];
			if ($event['RECURRENCE_ID'] && $instanceChangeKey = self::FindSyncInstance($event))
			{
				$arCalendarItems[$eventXmlId] = $instanceChangeKey;
			}

			if (isset($arCalendarItems[$eventXmlId]))
			{
				if ($event[$xmlIdField] !== $arCalendarItems[$eventXmlId])
				{
					$arResult[] = [
						'XML_ID' => $eventXmlId,
						'ID' => $event['ID'],
					];
				}

				unset($arCalendarItems[$eventXmlId]);
			}
			elseif ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::EXCHANGE_TYPE)
			{
				if ((int)$event['ID'] === (int)$event['PARENT_ID'])
				{
					self::DeleteCalendarEvent($event["ID"], self::$userId);
				}
			}
			else
			{
				self::DeleteCalendarEvent($event["ID"], self::$userId);
			}
		}

		foreach ($arCalendarItems as $key => $value)
		{
			$arResult[] = [
				'XML_ID' => $key,
				'ID' => 0,
			];
		}

		self::$silentErrorMode = false;

		return $arResult;
	}

	private static function FindSyncInstance($event)
	{
		$exchangeScheme = COption::GetOptionString('dav', 'exchange_scheme', 'http');
		$exchangeServer = COption::GetOptionString('dav', 'exchange_server', '');
		$exchangePort = COption::GetOptionString('dav', 'exchange_port', '80');
		$exchangeUsername = COption::GetOptionString('dav', 'exchange_username', '');
		$exchangePassword = COption::GetOptionString('dav', 'exchange_password', '');

		if (empty($exchangeServer))
		{
			return '';
		}

		$exchange = new CDavExchangeCalendar($exchangeScheme, $exchangeServer, $exchangePort, $exchangeUsername, $exchangePassword);

		$params = [
			'dateTo' => $event['DATE_TO'],
			'parentDateTo' => $event['DATE_TO'],
			'dateFrom' => $event['DATE_FROM'],
			'parentDateFrom' => $event['DATE_FROM'],
			'parentTz' => $event['TZ_FROM'],
			'changekey' => $event['DAV_EXCH_LABEL'],
		];

		[ , $changeKey] = $exchange->FindInstance($params);

		return $changeKey;
	}

	public static function DeleteCalendarEvent($eventId, $userId, $oEvent = false)
	{
		return CCalendarEvent::Delete(array(
			'id' => $eventId,
			'userId' => $userId,
			'bMarkDeleted' => true,
			'Event' => $oEvent,
		));
	}

	public static function Color($color = '', $defaultColor = true)
	{
		if ((string)$color !== '')
		{
			$color = ltrim(trim(preg_replace('/\W/', '', $color)), "#");
			if (mb_strlen($color) > 6)
			{
				$color = mb_substr($color, 0, 6);
			}
			elseif(mb_strlen($color) < 6)
			{
				$color = '';
			}
		}
		$color = '#'.$color;

		// Default color
		$DEFAULT_COLOR = '#9dcf00';
		if ($color === '#')
		{
			if ($defaultColor === true)
			{
				$color = $DEFAULT_COLOR;
			}
			elseif($defaultColor)
			{
				$color = $defaultColor;
			}
			else
			{
				$color = '';
			}
		}

		return $color;
	}

	public static function ConvertDayInd($i)
	{
		return $i == 0 ? 6 : $i - 1;
	}

	// return array('bAccess' => true/false, 'bReadOnly' => true/false, 'privateStatus' => 'time'/'title');

	public static function _fixTimestamp($timestamp)
	{
		if (date("Z") !== date("Z", $timestamp))
		{
			$timestamp += (date("Z") - date("Z", $timestamp));
		}
		return $timestamp;
	}

	// Called from CalDav, Exchange methods

	public static function FormatTime($h = 0, $m = 0)
	{
		$m = (int)$m;

		if ($m > 59)
		{
			$m = 59;
		}
		elseif ($m < 0)
		{
			$m = 0;
		}

		if ($m < 10)
		{
			$m = '0' . $m;
		}

		$h = (int)$h;
		if ($h > 24)
		{
			$h = 24;
		}
		if ($h < 0)
		{
			$h = 0;
		}

		if (IsAmPmMode())
		{
			$ampm = 'am';

			if ($h == 0)
			{
				$h = 12;
			}
			else if ($h == 12)
			{
				$ampm = 'pm';
			}
			else if ($h > 12)
			{
				$ampm = 'pm';
				$h -= 12;
			}

			$res = $h.':'.$m.' '.$ampm;
		}
		else
		{
			$res = (($h < 10) ? '0' : '').$h.':'.$m;
		}
		return $res;
	}

	// Called from SaveEvent: try to save event in Exchange or to Dav Server and if it's Ok, return true
	public static function GetUserId()
	{
		if (!self::$userId)
		{
			self::$userId = self::GetCurUserId();
		}
		return self::$userId;
	}

	public static function GetReadonlyMode()
	{
		return self::$readOnly;
	}

	// Called from CalDav sync methods

	public static function GetUserAvatarSrc($user = [], $params = [])
	{
		if (!is_array($user) && (int)$user > 0)
		{
			$user = self::GetUser($user);
		}

		$avatar_src = self::GetUserAvatar($user, $params);
		if ($avatar_src === false)
		{
			$avatar_src = (isset($params['fillAvatar']) && $params['fillAvatar'] === false ? '' : '/bitrix/images/1.gif');
		}

		return $avatar_src;
	}

	public static function GetUserAvatar($user = [], $params = [])
	{
		if (!is_array($user) && (int)$user > 0)
		{
			$user = self::GetUser($user);
		}

		if (!empty($user["PERSONAL_PHOTO"]))
		{
			if (empty($params['AVATAR_SIZE']))
			{
				$params['AVATAR_SIZE'] = 42;
			}
			$arFileTmp = CFile::ResizeImageGet(
				$user["PERSONAL_PHOTO"],
				array('width' => $params['AVATAR_SIZE'], 'height' => $params['AVATAR_SIZE']),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			$avatar_src = $arFileTmp['src'];
		}
		else
		{
			$avatar_src = false;
		}
		return $avatar_src;
	}

	public static function GetUserUrl($userId = 0, $pathToUser = "")
	{
		if ($pathToUser == '')
		{
			if (self::$pathToUser == '')
			{
				if (empty(self::$pathesForSite))
				{
					self::$pathesForSite = self::GetPathes(SITE_ID);
				}
				self::$pathToUser = self::$pathesForSite['path_to_user'];
			}
			$pathToUser = self::$pathToUser;
		}

		return CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($pathToUser, array("user_id" => $userId, "USER_ID" => $userId)));
	}

	public static function GetAccessTasksByName($binging = 'calendar_section', $name = 'calendar_denied')
	{
		$arTasks = self::GetAccessTasks($binging);

		foreach($arTasks as $id => $task)
		{
			if ($task['name'] == $name)
			{
				return $id;
			}
		}

		return false;
	}

	public static function GetAccessTasks($binging = 'calendar_section', $type = '')
	{
		\Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/admin/task_description.php");

		if (isset(self::$arAccessTask[$binging]) && is_array(self::$arAccessTask[$binging]))
		{
			return self::$arAccessTask[$binging];
		}

		$bIntranet = self::IsIntranetEnabled();
		$arTasks = [];
		$res = CTask::GetList(Array('ID' => 'asc'), Array('MODULE_ID' => 'calendar', 'BINDING' => $binging));
		while($arRes = $res->Fetch())
		{
			if($type === 'location')
			{
				if (
					mb_strtolower($arRes['NAME']) === 'calendar_view_time'
					|| mb_strtolower($arRes['NAME']) === 'calendar_view_title'
				)
				{
					continue;
				}
				$name = '';
				if ($arRes['SYS'])
				{
					if (
						mb_strtolower($arRes['NAME']) === 'calendar_edit'
						|| mb_strtolower($arRes['NAME']) === 'calendar_view'
						|| mb_strtolower($arRes['NAME']) === 'calendar_type_edit'
						|| mb_strtolower($arRes['NAME']) === 'calendar_type_view')
					{
						$name = Loc::getMessage('TASK_NAME_LOCATION_'.mb_strtoupper($arRes['NAME']));
					}
					else
					{
						$name = Loc::getMessage('TASK_NAME_'.mb_strtoupper($arRes['NAME']));
					}
				}
			}
			else
			{
				if (
					!$bIntranet
					&& (
						mb_strtolower($arRes['NAME']) === 'calendar_view_time'
						|| mb_strtolower($arRes['NAME']) === 'calendar_view_title'
					)
				)
					continue;

				$name = '';
				if ($arRes['SYS'])
					$name = Loc::getMessage('TASK_NAME_'.mb_strtoupper($arRes['NAME']));

			}
			if ($name == '')
				$name = $arRes['NAME'];

			$arTasks[$arRes['ID']] = array(
				'name' => $arRes['NAME'],
				'title' => $name,
			);
		}

		// self::$arAccessTask[$binging] = $arTasks;

		return $arTasks;
	}

	public static function PushAccessNames($arCodes = [])
	{
		foreach($arCodes as $code)
		{
			if (!array_key_exists($code, self::$accessNames))
			{
				self::$accessNames[$code] = null;
			}
		}
	}

	public static function SetLocation($old = '', $new = '', $params = [])
	{
		return Rooms\Util::setLocation($old, $new, $params);
	}

	public static function ReserveMeetingRoom($params)
	{
		return Rooms\IBlockMeetingRoom::reserveMeetingRoom($params);
	}

	public static function CheckMeetingRoom($params)
	{
		return Rooms\IBlockMeetingRoom::checkMeetingRoom($params);
	}

	public static function GetOuterUrl()
	{
		return self::$outerUrl;
	}

	public static function AddConnection($connection, $type = 'caldav')
	{
		if((!self::CheckCalDavUrl($connection['link'], $connection['user_name'], $connection['pass'])))
		{
			return Loc::getMessage('EC_CAL_OPERATION_CANNOT_BE_PERFORMED');
		}

		$arFields = [
			'ENTITY_TYPE' => 'user',
			'ENTITY_ID' => $connection['user_id'],
			'ACCOUNT_TYPE' =>  Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
			'NAME' => $connection['name'],
			'SERVER' => $connection['link'],
			'SERVER_USERNAME' => $connection['user_name'],
			'SERVER_PASSWORD' => $connection['pass'],
		];

		\CDavConnection::ParseFields($arFields);

		$davConnection = \CDavConnection::getList(
			['ID' => 'ASC'],
			[
				'SERVER_HOST' => $arFields['SERVER_HOST'],
				'SERVER_PATH' => $arFields['SERVER_PATH'],
				'ENTITY_ID' => $arFields['ENTITY_ID'],
			],
			false,
			['nTopCount' => 1]
		);

		if ($con = $davConnection->fetch())
		{
			return Loc::getMessage('EC_CAL_SERVER_ALREADY_CONNECTED');
		}

		\CDavConnection::Add($arFields);

		return true;
	}

	public static function CheckCalDavUrl($url, $username, $password)
	{
		$arServer = [
			'host' => null,
			'scheme' => null,
			'port' => null,
			'path' => null,
		];
		$parsedUrl = parse_url($url);
		$arServer = array_merge($arServer, $parsedUrl);

		// Mantis #71074
		if (
			mb_strpos(mb_strtolower($_SERVER['SERVER_NAME']), mb_strtolower($arServer['host'])) !== false
			|| mb_strpos(mb_strtolower($_SERVER['HTTP_HOST']), mb_strtolower($arServer['host'])) !== false
		)
		{
			return false;
		}

		if (Loader::includeModule("dav"))
		{
			return \CDavGroupdavClientCalendar::DoCheckCalDAVServer($arServer["scheme"], $arServer["host"], $arServer["port"], $username, $password, $arServer["path"]);
		}

		return false;
	}

	public static function RemoveConnection(array $params = [])
	{
		if (Loader::includeModule('dav'))
		{
			$sections = self::getSectionsByConnectionId($params['id']);
			$connection = CDavConnection::GetList(
				["ID" => "ASC"],
				["ID" => $params['id']]
			);

			if (is_array($sections))
			{
				foreach ($sections as $section)
				{
					if ($params['del_calendars'] /*&& $section['IS_LOCAL'] !== 'Y'*/)
					{
						CCalendarSect::Delete($section['ID'], false);
					}
					else
					{
						self::markSectionLikeDelete($section['ID']);
					}
				}
			}

			\CDavConnection::Delete($params['id']);

			if (is_array($connection))
			{
				/** @var Google\Helper $googleHelper */
				$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
				$caldavHelper = ServiceLocator::getInstance()->get('calendar.service.caldav.helper');
				$connectionType = $caldavHelper->isYandex($connection['SERVER_HOST'])
					? Bitrix\Calendar\Sync\Caldav\Helper::YANDEX_TYPE
					: Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE
				;
				$connectionName = $googleHelper->isGoogleConnection($connection['ACCOUNT_TYPE'])
					? 'google'
					: $connectionType . $connection['ID']
				;
				Util::addPullEvent(
					'delete_sync_connection',
					$connection['ENTITY_ID'],
					[
						'syncInfo' => [
							$connectionName => [
								'type' => $connectionType,
							],
						],
						'requestUid' => Util::getRequestUid(),
					]
				);
			}
		}
	}

	public static function GetTypeByExternalId($externalId = false)
	{
		if ($externalId)
		{
			$res = CCalendarType::GetList(array('arFilter' => array('EXTERNAL_ID' => $externalId)));
			if ($res && $res[0])
				return $res[0]['XML_ID'];
		}
		return false;
	}

	public static function SetCurUserMeetingSection($userMeetingSection)
	{
		self::$userMeetingSection = $userMeetingSection;
	}

	public static function CacheTime($time = false)
	{
		if ($time !== false)
		{
			self::$cacheTime = $time;
		}
		return self::$cacheTime;
	}

	public static function _ParseHack(&$text, &$TextParser)
	{
		$text = preg_replace(array("/\&lt;/isu", "/\&gt;/isu"),array('<', '>'),$text);

		$text = preg_replace("/\<br\s*\/*\>/isu","", $text);
		$text = preg_replace("/\<(\w+)[^>]*\>(.+?)\<\/\\1[^>]*\>/isu","\\2",$text);
		$text = preg_replace("/\<*\/li\>/isu","", $text);

		$text = str_replace(array("<", ">"),array("&lt;", "&gt;"),$text);

		$TextParser->allow = [];
		return true;
	}

	public static function IsSocnetAdmin()
	{
		if (!isset(self::$bCurUserSocNetAdmin))
		{
			self::$bCurUserSocNetAdmin = self::IsSocNet() && CSocNetUser::IsCurrentUserModuleAdmin();
		}

		return self::$bCurUserSocNetAdmin;
	}

	public static function GetMaxDate()
	{
		if (!self::$CALENDAR_MAX_DATE)
		{
			$date = new DateTime();
			$date->setDate(2038, 1, 1);
			self::$CALENDAR_MAX_DATE = self::Date($date->getTimestamp(), false);
		}
		return self::$CALENDAR_MAX_DATE;
	}
	public static function GetMinDate()
	{
		if (!self::$CALENDAR_MIN_DATE)
		{
			$date = new DateTime();
			$date->setDate(1970, 1, 1);
			self::$CALENDAR_MIN_DATE = self::Date($date->getTimestamp(), false);
		}
		return self::$CALENDAR_MIN_DATE;
	}

	public static function GetDestinationUsers($codes, $fetchUsers = false)
	{
		if (!Main\Loader::includeModule('socialnetwork'))
		{
			return [];
		}
		$users = \CSocNetLogDestination::getDestinationUsers($codes, $fetchUsers);

		if ($fetchUsers)
		{
			foreach ($users as $i => $user)
			{
				$users[$i]['FORMATTED_NAME'] = self::GetUserName($user);
				$users[$i]['ID'] = (int)$user['ID'];
			}
		}
		else
		{
			foreach ($users as &$user)
			{
				if(is_numeric($user))
				{
					$user = (int)$user;
				}
			}
		}

		return $users;
	}

	public static function GetAttendeesMessage($cnt = 0)
	{
		if (
			($cnt % 100) > 10
			&& ($cnt % 100) < 20
		)
			$suffix = 5;
		else
			$suffix = $cnt % 10;

		return Loc::getMessage("EC_ATTENDEE_".$suffix, Array("#NUM#" => $cnt));
	}

	public static function GetMoreAttendeesMessage($cnt = 0)
	{
		if (
			($cnt % 100) > 10
			&& ($cnt % 100) < 20
		)
			$suffix = 5;
		else
			$suffix = $cnt % 10;

		return Loc::getMessage("EC_ATTENDEE_MORE_".$suffix, Array("#NUM#" => $cnt));
	}

	public static function GetFormatedDestination($codes = [])
	{
		$ac = CSocNetLogTools::FormatDestinationFromRights($codes, array(
			"CHECK_PERMISSIONS_DEST" => "Y",
			"DESTINATION_LIMIT" => 100000,
			"NAME_TEMPLATE" => "#NAME# #LAST_NAME#",
			"PATH_TO_USER" => "/company/personal/user/#user_id#/",
		));

		return $ac;
	}

	public static function GetFromToHtml(
		$fromTs = false,
		$toTs = false,
		$skipTime = false,
		$dtLength = 0,
		$forRrule = false,
		$languageId = null
	)
	{
		if ((int)$fromTs != $fromTs)
		{
			$fromTs = self::Timestamp($fromTs);
		}
		if ((int)$toTs != $toTs)
		{
			$toTs = self::Timestamp($toTs);
		}
		if ($toTs < $fromTs)
		{
			$toTs = $fromTs;
		}

		// Formats
		$formatShort = self::DFormat(false);
		$formatFull = self::DFormat(true);
		$formatTime = str_replace($formatShort, '', $formatFull);
		$formatTime = $formatTime == $formatFull ? "H:i" : str_replace(':s', '', $formatTime);
		$html = '';

		$formatFull = str_replace(':s', '', $formatFull);

		if ($skipTime)
		{
			if ((int)$dtLength === self::DAY_LENGTH || !$dtLength) // One full day event
			{
				if (!$forRrule)
				{
					$html = FormatDate([
						"tomorrow" => "tomorrow",
						"today" => "today",
						"yesterday" => "yesterday",
						"-" => $formatShort,
						"" => $formatShort,
					], $fromTs, time() + CTimeZone::GetOffset());
					$html .= ', ';
				}

				$html .= Loc::getMessage('EC_VIEW_FULL_DAY', null, $languageId);
			}
			else // Event for several days
			{
				$from = FormatDate([
					"tomorrow" => "tomorrow",
					"today" => "today",
					"yesterday" => "yesterday",
					"-" => $formatShort,
					"" => $formatShort,
				], $fromTs, time() + CTimeZone::GetOffset());

				$to = FormatDate([
					"tomorrow" => "tomorrow",
					"today" => "today",
					"yesterday" => "yesterday",
					"-" => $formatShort,
					"" => $formatShort,
				], $toTs - self::DAY_LENGTH, time() + CTimeZone::GetOffset());

				$html = Loc::getMessage(
					'EC_VIEW_DATE_FROM_TO',
					['#DATE_FROM#' => $from, '#DATE_TO#' => $to],
					$languageId
				);
			}
		}
		else
		{
			// Event during one day
			if(date('dmY', $fromTs) == date('dmY', $toTs))
			{
				if (!$forRrule)
				{
					$html = FormatDate([
						"tomorrow" => "tomorrow",
						"today" => "today",
						"yesterday" => "yesterday",
						"-" => $formatShort,
						"" => $formatShort,
					], $fromTs, time() + CTimeZone::GetOffset());
					$html .= ', ';
				}

				$html .= Loc::getMessage(
					'EC_VIEW_TIME_FROM_TO_TIME',
					[
						'#TIME_FROM#' => FormatDate($formatTime, $fromTs, false),
						'#TIME_TO#' => FormatDate($formatTime, $toTs, false)
					],
					$languageId
				);
			}
			else
			{
				$html = Loc::getMessage(
					'EC_VIEW_DATE_FROM_TO',
					[
						'#DATE_FROM#' => FormatDate($formatFull, $fromTs, time() + CTimeZone::GetOffset()),
						'#DATE_TO#' => FormatDate($formatFull, $toTs, time() + CTimeZone::GetOffset())
					],
					$languageId
				);
			}
		}

		return $html;
	}

	public static function GetSocNetDestination($user_id = false, $selected = [], $userList = [])
	{
		if (!Loader::includeModule("socialnetwork"))
		{
			return false;
		}

		global $CACHE_MANAGER;

		if (!is_array($selected))
		{
			$selected = [];
		}

		if (method_exists('CSocNetLogDestination','GetDestinationSort'))
		{
			$DESTINATION = array(
				'LAST' => [],
				'DEST_SORT' => CSocNetLogDestination::GetDestinationSort(array("DEST_CONTEXT" => \Bitrix\Calendar\Util::getUserSelectorContext())),
			);

			CSocNetLogDestination::fillLastDestination($DESTINATION['DEST_SORT'], $DESTINATION['LAST']);
		}
		else
		{
			$DESTINATION = array(
				'LAST' => array(
					'SONETGROUPS' => CSocNetLogDestination::GetLastSocnetGroup(),
					'DEPARTMENT' => CSocNetLogDestination::GetLastDepartment(),
					'USERS' => CSocNetLogDestination::GetLastUser(),
				),
			);
		}

		if (!$user_id)
		{
			$user_id = self::GetCurUserId();
		}

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'calendar_dest_'.$user_id;
		$cacheDir = '/calendar/socnet_destination/'.SITE_ID.'/'.$user_id;

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$DESTINATION['SONETGROUPS'] = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			$DESTINATION['SONETGROUPS'] = CSocNetLogDestination::GetSocnetGroup(Array('features' => array("calendar", array("view"))));

			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($cacheDir);
				foreach($DESTINATION['SONETGROUPS'] as $val)
				{
					$CACHE_MANAGER->RegisterTag("sonet_features_G_".$val["entityId"]);
					$CACHE_MANAGER->RegisterTag("sonet_group_".$val["entityId"]);
				}
				$CACHE_MANAGER->RegisterTag("sonet_user2group_U".$user_id);
				$CACHE_MANAGER->EndTagCache();
			}
			$obCache->EndDataCache($DESTINATION['SONETGROUPS']);
		}

		$destinationUserList = [];
		$DESTINATION['SELECTED'] = [];

		if (!empty($userList))
		{
			foreach ($userList as $userId)
			{
				$DESTINATION['SELECTED']['U'.$userId] = "users";
				$DESTINATION['LAST']['USERS']['U'.$userId] = 'U'.$userId;
			}
		}

		foreach ($selected as $ind => $code)
		{
			if (mb_substr($code, 0, 2) === 'DR')
			{
				$DESTINATION['SELECTED'][$code] = "department";
			}
			elseif (mb_substr($code, 0, 2) === 'UA')
			{
				$DESTINATION['SELECTED'][$code] = "groups";
			}
			elseif (mb_substr($code, 0, 2) === 'SG')
			{
				$DESTINATION['SELECTED'][$code] = "sonetgroups";
			}
			elseif (mb_substr($code, 0, 1) === 'U')
			{
				$DESTINATION['SELECTED'][$code] = "users";
				$destinationUserList[] = (int)str_replace('U', '', $code);
			}
		}

		// intranet structure
		$arStructure = CSocNetLogDestination::GetStucture();
		$DESTINATION['DEPARTMENT'] = $arStructure['department'];
		$DESTINATION['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$DESTINATION['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		if (Loader::includeModule('extranet') && !CExtranet::IsIntranetUser(SITE_ID, $user_id))
		{
			$DESTINATION['EXTRANET_USER'] = 'Y';
			$DESTINATION['USERS'] = CSocNetLogDestination::GetExtranetUser();
			$DESTINATION['USERS'] = array_merge($DESTINATION['USERS'], CSocNetLogDestination::GetUsers(['id' => [$user_id]]));
		}
		else
		{
			if (is_array($DESTINATION['LAST']['USERS']))
			{
				foreach ($DESTINATION['LAST']['USERS'] as $value)
				{
					$destinationUserList[] = (int)str_replace('U', '', $value);
				}
			}

			if (!empty($userList))
			{
				$destinationUserList = array_unique(array_merge($destinationUserList, $userList));
			}

			$DESTINATION['EXTRANET_USER'] = 'N';
			$DESTINATION['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $destinationUserList));
		}

		$users = [];
		foreach ($DESTINATION['USERS'] as $key => $entry)
		{
			if ($entry['isExtranet'] === 'N')
			{
				$users[$key] = $entry;
			}
		}
		$DESTINATION['USERS'] = $users;

		return $DESTINATION;
	}

	public static function SaveUserTimezoneName($user, $tzName = '')
	{
		if (!is_array($user) && (int)$user > 0)
		{
			$user = self::GetUser($user, true);
		}

		CUserOptions::SetOption("calendar", "timezone".self::GetCurrentOffsetUTC($user['ID']), $tzName, false, $user['ID']);
	}

	public static function CheckOffsetForTimezone($timezone, $offset, $date = false)
	{
		return true;
	}

	public static function GetOffsetUTC($userId = false, $dateTimestamp = null)
	{
		if (!$userId && self::$userId)
			$userId = self::$userId;

		$tzName = self::GetUserTimezoneName($userId);
		if ($tzName)
		{
			$offset = self::GetTimezoneOffset($tzName, $dateTimestamp);
		}
		else
		{
			$offset = date("Z", $dateTimestamp) + self::GetOffset($userId);
		}
		return (int)$offset;
	}

	public static function OnSocNetGroupDelete($groupId)
	{
		$groupId = (int)$groupId;
		if ($groupId > 0)
		{
			$res = CCalendarSect::GetList(
				array(
					'arFilter' => array(
						'CAL_TYPE' => 'group',
						'OWNER_ID' => $groupId,
					),
					'checkPermissions' => false,
				)
			);

			foreach($res as $sect)
			{
				CCalendarSect::Delete($sect['ID'], false);
			}
		}
		return true;
	}

	/**
	 * Handles last caldav activity from mobile devices
	 *
	 * @param \Bitrix\Main\Event $event Event.
	 * @return null
	 */
	public static function OnDavCalendarSync(\Bitrix\Main\Event $event)
	{
		$calendarId = $event->getParameter('id');
		$userAgent = mb_strtolower($event->getParameter('agent'));
		$agent = false;
		[$sectionId, $entityType, $entityId] = $calendarId;

		$arAgentsMap = [
			'android' => 'android', // Android/iOS CardDavBitrix24
			'iphone' => 'iphone', // Apple iPhone iCal
			'davkit' => 'mac', // Apple iCal
			'mac os' => 'mac', // Apple iCal (Mac Os X > 10.8)
			'mac_os_x' => 'mac', // Apple iCal (Mac Os X > 10.8)
			'mac+os+x' => 'mac', // Apple iCal (Mac Os X > 10.10)
			'macos' => 'mac', // Apple iCal (Mac Os X > 11)
			'dataaccess' => 'iphone', // Apple addressbook iPhone
			//'sunbird' => 'sunbird', // Mozilla Sunbird
			'ios' => 'iphone',
		];

		foreach ($arAgentsMap as $pattern => $name)
		{
			if (mb_strpos($userAgent, $pattern) !== false)
			{
				$agent = $name;
				break;
			}
		}

		if ($entityType === 'user' && $agent)
		{
			self::SaveSyncDate($entityId, $agent);
		}
	}

	/**
	 * Saves date of last successful sync
	 *
	 * @param int $userId User Id
	 * @param string $syncType Type of synchronization.
	 * @return null
	 */
	public static function SaveSyncDate($userId, $syncType)
	{
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook');
		if (in_array($syncType, $syncTypes))
		{
			if (!CUserOptions::GetOption('calendar', 'last_sync_'.$syncType, false, $userId))
			{
				AddEventToStatFile('calendar', 'sync_connection_connected', $syncType, '', 'client_connection');
			}

			CUserOptions::SetOption("calendar", "last_sync_".$syncType, self::Date(time()), false, $userId);

			Util::addPullEvent('refresh_sync_status', $userId, [
				'syncInfo' => [
					$syncType => [
						'status' => true,
						'type' => $syncType,
						'connected' => true,
						'syncOffset' => 0,
					],
				],
				'requestUid' => Util::getRequestUid(),
			]);
		}
	}

	/**
	 * @param $userId
	 * @param $syncType
	 * @param $sectionId
	 */
	public static function SaveMultipleSyncDate($userId, $syncType, $sectionId): void
	{
		$syncTypes = ['outlook'];
		if (in_array($syncType, $syncTypes, true))
		{
			if (!CUserOptions::GetOption('calendar', 'last_sync_'.$syncType, false, $userId))
			{
				AddEventToStatFile('calendar', 'sync_connection_connected', $syncType, '', 'client_connection');
			}

			$options = CUserOptions::GetOption("calendar", "last_sync_".$syncType, false, $userId);

			if (!is_array($options))
			{
				unset($options);
			}

			$options[$sectionId] = self::Date(time());
			CUserOptions::SetOption("calendar", "last_sync_".$syncType, $options, false, $userId);

			Util::addPullEvent('refresh_sync_status', $userId, [
				'syncInfo' => [
					$syncType => [
						'status' => true,
						'type' => $syncType,
						'connected' => true,
						'syncOffset' => 0,
					],
				],
				'requestUid' => Util::getRequestUid(),
			]);
		}
	}

	public static function OnExchangeCalendarSync(\Bitrix\Main\Event $event)
	{
		self::SaveSyncDate($event->getParameter('userId'), 'exchange');
	}

	public static function ClearSyncInfo($userId, $syncType)
	{
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook', 'office365', 'icloud');
		if (in_array($syncType, $syncTypes))
		{
			CUserOptions::DeleteOption("calendar", "last_sync_".$syncType, false, $userId);
		}
	}

	/**
	 * Updates counter in left menu in b24, sets amount of requests for meeting for current user or
	 * set of users
	 *
	 * @param int|array $users array of user's ids or user id as an int
	 * @return null
	 */
	public static function UpdateCounter($users = false)
	{
		if (!$users)
		{
			$users = array(self::GetCurUserId());
		}
		elseif(!is_array($users))
		{
			$users = array($users);
		}

		$ids = [];
		foreach($users as $user)
		{
			if ((int)$user)
			{
				$ids[] = (int)$user;
			}
		}
		$users = $ids;

		if (!empty($users))
		{
			$events = CCalendarEvent::GetList([
				'arFilter' => [
					'CAL_TYPE' => 'user',
					'OWNER_ID' => $users,
					'FROM_LIMIT' => self::Date(time(), false),
					'TO_LIMIT' => self::Date(time() + self::DAY_LENGTH * 90, false),
					'IS_MEETING' => 1,
					'MEETING_STATUS' => 'Q',
					'DELETED' => 'N',
				],
				'parseRecursion' => false,
				'checkPermissions' => false
			]);

			$counters = [];
			foreach($events as $event)
			{
				if(!isset($counters[$event['OWNER_ID']]))
				{
					$counters[$event['OWNER_ID']] = 0;
				}

				$counters[$event['OWNER_ID']]++;
			}

			foreach($users as $user)
			{
				if($user > 0)
				{
					if(isset($counters[$user]) && $counters[$user] > 0)
					{
						CUserCounter::Set($user, 'calendar', $counters[$user], '**', '', false);
					}
					else
					{
						CUserCounter::Set($user, 'calendar', 0, '**', '', false);
					}
				}
			}
		}
	}

	private static function GetInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}


	public static function IsIntranetEnabled()
	{
		if (!isset(self::$bIntranet))
		{
			self::$bIntranet = IsModuleInstalled('intranet');
		}
		return self::$bIntranet;
	}

	public static function IsSocNet()
	{
		if (!isset(self::$bSocNet))
		{
			Loader::includeModule("socialnetwork");
			self::$bSocNet = class_exists('CSocNetUserToGroup') && CBXFeatures::IsFeatureEnabled("Calendar") && self::IsIntranetEnabled();
		}

		return self::$bSocNet;
	}

	public static function GetCurUserId($refresh = false): int
	{
		global $USER;

		if (!isset(self::$curUserId)
			|| !is_numeric(self::$curUserId)
			|| $refresh
		)
		{
			self::$curUserId =
				(is_object($USER) && $USER->IsAuthorized())
					? (int)$USER->GetId()
					: 0
			;
		}

		return self::$curUserId;
	}

	public static function GetSettings($params = [])
	{
		if (!is_array($params))
		{
			$params = [];
		}
		if (
			isset(self::$settings)
			&& !empty(self::$settings)
			&& ($params['request'] ?? '') === false
		)
		{
			return self::$settings;
		}

		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if (($params['forseGetSitePathes'] ?? false) || !$pathes_for_sites)
		{
			$pathes = self::GetPathes($params['site'] ?? false);
		}
		else
		{
			$pathes = [];
		}

		if (!isset($params['getDefaultForEmpty']) || $params['getDefaultForEmpty'] !== false)
		{
			$params['getDefaultForEmpty'] = true;
		}

		$siteId = $params['site'] ?? SITE_ID;
		$resMeetingCommonForSites = COption::GetOptionString('calendar', 'rm_for_sites', true);
		$siteIdForResMeet = !$resMeetingCommonForSites && $siteId ? $siteId : false;

		self::$settings = [
			'work_time_start' => COption::GetOptionString('calendar', 'work_time_start', 9),
			'work_time_end' => COption::GetOptionString('calendar', 'work_time_end', 19),
			'year_holidays' => COption::GetOptionString('calendar', 'year_holidays', Loc::getMessage('EC_YEAR_HOLIDAYS_DEFAULT')),
			'year_workdays' => COption::GetOptionString('calendar', 'year_workdays', Loc::getMessage('EC_YEAR_WORKDAYS_DEFAULT')),
			'week_holidays' => explode('|', COption::GetOptionString('calendar', 'week_holidays', 'SA|SU')),
			'week_start' => COption::GetOptionString('calendar', 'week_start', 'MO'),
			'user_name_template' => self::GetUserNameTemplate($params['getDefaultForEmpty']),
			'sync_by_push' => COption::GetOptionString('calendar', 'sync_by_push', false),
			'user_show_login' => COption::GetOptionString('calendar', 'user_show_login', true),
			'path_to_user' => COption::GetOptionString('calendar', 'path_to_user', "/company/personal/user/#user_id#/"),
			'path_to_user_calendar' => COption::GetOptionString('calendar', 'path_to_user_calendar', "/company/personal/user/#user_id#/calendar/"),
			'path_to_group' => COption::GetOptionString('calendar', 'path_to_group', "/workgroups/group/#group_id#/"),
			'path_to_group_calendar' => COption::GetOptionString('calendar', 'path_to_group_calendar', "/workgroups/group/#group_id#/calendar/"),
			'path_to_vr' => COption::GetOptionString('calendar', 'path_to_vr', ""),
			'path_to_rm' => COption::GetOptionString('calendar', 'path_to_rm', ""),
			'rm_iblock_type' => COption::GetOptionString('calendar', 'rm_iblock_type', ""),
			'rm_iblock_id' => COption::GetOptionString('calendar', 'rm_iblock_id', "", $siteIdForResMeet, !!$siteIdForResMeet),
			'dep_manager_sub' => COption::GetOptionString('calendar', 'dep_manager_sub', true),
			'denied_superpose_types' => unserialize(COption::GetOptionString('calendar', 'denied_superpose_types', serialize([])), ['allowed_classes' => false]),
			'pathes_for_sites' => $pathes_for_sites,
			'pathes' => $pathes,
			'forum_id' => COption::GetOptionString('calendar', 'forum_id', ""),
			'rm_for_sites' => COption::GetOptionString('calendar', 'rm_for_sites', true),
		];

		$arPathes = self::GetPathesList();
		foreach($arPathes as $pathName)
		{
			if (!isset(self::$settings[$pathName]))
			{
				self::$settings[$pathName] = COption::GetOptionString('calendar', $pathName, "");
			}
		}

		if(self::$settings['work_time_start'] > 23)
		{
			self::$settings['work_time_start'] = 23;
		}
		if (self::$settings['work_time_end'] <= self::$settings['work_time_start'])
		{
			self::$settings['work_time_end'] = self::$settings['work_time_start'] + 1;
		}
		if (self::$settings['work_time_end'] > 23.30)
		{
			self::$settings['work_time_end'] = 23.30;
		}

		if (empty(self::$settings['forum_id']))
		{
			self::$settings['forum_id'] = COption::GetOptionString("tasks", "task_forum_id", "");
			if (empty(self::$settings['forum_id']) && Loader::includeModule("forum"))
			{
				$db = CForumNew::GetListEx();
				if ($ar = $db->GetNext())
				{
					self::$settings['forum_id'] = $ar["ID"];
				}
			}
			COption::SetOptionString("calendar", "forum_id", self::$settings['forum_id']);
		}

		return self::$settings;
	}

	public static function GetPathes($forSite = null)
	{
		$pathes = [];
		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if ($forSite === null)
		{
			$arAffectedSites = COption::GetOptionString('calendar', 'pathes_sites', false);

			if ($arAffectedSites && CheckSerializedData($arAffectedSites))
			{
				$arAffectedSites = unserialize($arAffectedSites, ['allowed_classes' => false]);
			}
		}
		elseif (is_array($forSite))
		{
			$arAffectedSites = $forSite;
		}
		else
		{
			$arAffectedSites = [$forSite];
		}

		if(is_array($arAffectedSites) && !empty($arAffectedSites))
		{
			foreach($arAffectedSites as $s)
			{
				$ar = COption::GetOptionString("calendar", 'pathes_'.$s, false);
				if ($ar && CheckSerializedData($ar))
				{
					$ar = unserialize($ar, ['allowed_classes' => false]);
					if(is_array($ar))
					{
						$pathes[$s] = $ar;
					}
				}
			}
		}

		if ($forSite !== false)
		{
			$result = [];
			if (isset($pathes[$forSite]) && is_array($pathes[$forSite]))
			{
				$result = $pathes[$forSite];
			}

			$arPathes = self::GetPathesList();
			foreach($arPathes as $pathName)
			{
				$val = $result[$pathName] ?? '';
				if (empty($val) || $pathes_for_sites)
				{
					if (!isset($SET))
					{
						$SET = self::GetSettings();
					}
					$val = $SET[$pathName] ?? null;
					$result[$pathName] = $val;
				}
			}
			return $result;
		}
		return $pathes;
	}

	public static function GetPathesList()
	{
		if (!self::$pathesListEx)
		{
			self::$pathesListEx = self::$pathesList;
			$arTypes = CCalendarType::GetList(array('checkPermissions' => false));
			foreach ($arTypes as $type)
			{
				if ($type['XML_ID'] !== 'user' && $type['XML_ID'] !== 'group')
				{
					self::$pathesList[] = 'path_to_type_'. $type['XML_ID'];
				}
			}
		}
		return self::$pathesList;
	}

	public static function GetUserNameTemplate($fromSite = true)
	{
		$user_name_template = COption::GetOptionString('calendar', 'user_name_template', '');
		if ($fromSite && empty($user_name_template))
		{
			$user_name_template = CSite::GetNameFormat(false);
		}
		return $user_name_template;
	}

	public static function SetUserSettings($settings = [], $userId = false)
	{
		UserSettings::set($settings, $userId);
	}

	public static function GetUserSettings($userId = false)
	{
		return UserSettings::get($userId);
	}

	public static function GetPermissions($Params = [])
	{
		global $USER;
		$type = $Params['type'] ?? self::$type;
		$ownerId = (int)($Params['ownerId'] ?? self::$ownerId);
		$userId = (int)($Params['userId'] ?? self::$userId);

		$bView = true;
		$bEdit = true;
		$bEditSection = true;

		$accessController = new TypeAccessController($userId);
		$typeModel = TypeModel::createFromXmlId($type);
		$request = [
			ActionDictionary::ACTION_TYPE_VIEW => [],
			ActionDictionary::ACTION_TYPE_EDIT => [],
		];

		$result = $accessController->batchCheck($request, $typeModel);

		if ($type === 'user' && (int)$ownerId !== (int)$userId)
		{
			$bEdit = false;
			$bEditSection = false;
		}
		else
		{
			$bView = $result[ActionDictionary::ACTION_TYPE_VIEW];
			$bEdit = $result[ActionDictionary::ACTION_TYPE_EDIT];
			$bEditSection = $result[ActionDictionary::ACTION_TYPE_EDIT];
		}

		if (($type === 'group') && !$USER->CanDoOperation('edit_php'))
		{
			$keyOwner = 'SG'.$ownerId.'_A';
			$keyMod = 'SG'.$ownerId.'_E';
			$keyMember = 'SG'.$ownerId.'_K';

			$codes = Util::getUserAccessCodes($userId);

			if (Loader::includeModule("socialnetwork"))
			{
				$group = CSocNetGroup::getByID($ownerId);
				if(!empty($group['CLOSED']) && $group['CLOSED'] === 'Y' &&
					\Bitrix\Main\Config\Option::get('socialnetwork', 'work_with_closed_groups', 'N') === 'N')
				{
					self::$isArchivedGroup = true;
				}
			}

			if (in_array($keyOwner, $codes, true))// Is owner
			{
				$bEdit = true;
				$bEditSection = true;
			}
			elseif(!self::$isArchivedGroup && in_array($keyMod, $codes, true))// Is moderator
			{
				$bEdit = true;
				$bEditSection = true;
			}
			elseif(!self::$isArchivedGroup && in_array($keyMember, $codes, true))// Is member
			{
				$bEdit = true;
				$bEditSection = false;
			}
			else
			{
				$bEdit = false;
				$bEditSection = false;
			}
		}

		if (($Params['setProperties'] ?? '') !== false)
		{
			self::$perm['view'] = $bView;
			self::$perm['edit'] = $bEdit;
			self::$perm['section_edit'] = $bEditSection;
		}

		return array(
			'view' => $bView,
			'edit' => $bEdit,
			'section_edit' => $bEditSection,
		);
	}

	public static function GetPath($type = '', $ownerId = '', $hard = false)
	{
		return self::GetServerPath().Util::getPathToCalendar((int)$ownerId, $type);
	}

	public static function GetSiteId()
	{
		if (!self::$siteId)
		{
			self::$siteId = SITE_ID;
		}
		return self::$siteId;
	}

	public static function GetServerPath()
	{
		if (!isset(self::$serverPath))
		{
			self::$serverPath = (CMain::IsHTTPS() ? "https://" : "http://").self::GetServerName();
		}

		return self::$serverPath;
	}

	public static function GetServerName()
	{
		$server_name = '';
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
		{
			$server_name = SITE_SERVER_NAME;
		}
		if (!$server_name)
		{
			$server_name = COption::GetOptionString("main", "server_name", "");
		}
		if (!$server_name)
		{
			$server_name = $_SERVER['HTTP_HOST'];
		}
		$server_name = rtrim($server_name, '/');
		if (!preg_match('/^[a-z0-9\.\-]+$/i', $server_name)) // cyrillic domain hack
		{
			$converter = new CBXPunycode(defined('BX_UTF') && BX_UTF === true ? 'UTF-8' : 'windows-1251');
			$host = $converter->Encode($server_name);
			if (!preg_match('#--p1ai$#', $host)) // trying to guess
			{
				$host = $converter->Encode(CharsetConverter::ConvertCharset($server_name, 'utf-8', 'windows-1251'));
			}
			$server_name = $host;
		}

		return $server_name;
	}

	public static function GetStartUpEvent($eventId = false, $isSharing = false)
	{
		if ($eventId)
		{
			if ($isSharing)
			{
				$res = [self::getDeletedSharedEvent($eventId)];
			}
			else
			{
				$res = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"PARENT_ID" => $eventId,
							"OWNER_ID" => self::$userId,
							"IS_MEETING" => 1,
							"DELETED" => "N",
						),
						'parseRecursion' => false,
						'fetchAttendees' => true,
						'fetchMeetings' => true,
						'checkPermissions' => true,
						'setDefaultLimit' => false,
					)
				);
			}

			if (!$res || !is_array($res[0]))
			{
				$res = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"DELETED" => "N",
						),
						'parseRecursion' => false,
						'userId' => self::$userId,
						'fetchAttendees' => false,
						'fetchMeetings' => true,
					)
				);
			}

			if ($res && isset($res[0]) && ($event = $res[0]))
			{
				if (
					$event['MEETING_STATUS'] === 'Y'
					|| $event['MEETING_STATUS'] === 'N'
					|| $event['MEETING_STATUS'] === 'Q'
				)
				{
					$_GET['CONFIRM'] ??= null;
					if (
						$event['IS_MEETING']
						&& (int)self::$userId === (int)self::$ownerId
						&& self::$type === 'user'
						&& ($_GET['CONFIRM'] === 'Y' || $_GET['CONFIRM'] === 'N')
					)
					{
						CCalendarEvent::SetMeetingStatus(array(
							'userId' => self::$userId,
							'eventId' => $event['ID'],
							'status' => $_GET['CONFIRM'] === 'Y' ? 'Y' : 'N',
							'personalNotification' => true,
						));
					}
				}

				if ($event['RRULE'])
				{
					$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
				}

				$event['~userIndex'] = CCalendarEvent::getUserIndex();

				return $event;
			}

			CCalendarNotify::ClearNotifications($eventId);
		}

		return false;
	}

	public static function getDeletedSharedEvent(int $entryId): ?array
	{
		/** @var Sharing\Link\EventLink $eventLink */
		$eventLink = (new Sharing\Link\Factory)->getDeletedEventLinkByEventId($entryId);
		if (!$eventLink)
		{
			return null;
		}

		$result = EventTable::query()
			->setSelect(['*'])
			->where('OWNER_ID', $eventLink->getOwnerId())
			->where(Query::filter()
				->logic('or')
				->where([
					['DELETED', 'Y'],
					['MEETING_STATUS', Core\Event\Tools\Dictionary::MEETING_STATUS['No']],
				])
			)
			->where(Query::filter()
				->logic('or')
				->where([
					['ID', $entryId],
					['PARENT_ID', $entryId],
				])
			)
			->whereIn('EVENT_TYPE', SharingEventManager::getSharingEventTypes())
			->exec()
		;
		$event = $result->fetch() ?: null;

		if ($event)
		{
			$canceledUserId = (int)$event["MEETING_HOST"];
			if ($event['MEETING_STATUS'] === Core\Event\Tools\Dictionary::MEETING_STATUS['No'])
			{
				$canceledUserId = (int)$event["OWNER_ID"];
				$event['canceledByManager'] = true;
			}
			$host = Sharing\Helper::getOwnerInfo($canceledUserId);

			$event['HOST_NAME'] = trim($host['name'] . ' ' . $host['lastName']);
			$event['timestampFromUTC'] = Sharing\Helper::getEventTimestampUTC($event['DATE_FROM'], $event['TZ_FROM']);
			$event['timestampToUTC'] = Sharing\Helper::getEventTimestampUTC($event['DATE_TO'], $event['TZ_TO']);
			$event['canceledUserId'] = $canceledUserId;
		}

		return $event;
	}

	public static function Timestamp($date, $bRound = true, $bTime = true)
	{
		$timestamp = MakeTimeStamp($date, self::TSFormat($bTime ? "FULL" : "SHORT"));
		if ($bRound)
		{
			$timestamp = self::RoundTimestamp($timestamp);
		}

		return $timestamp;
	}

	public static function TimestampUTC(string $date): int
	{
		$dateTime = self::createDateTimeObjectFromString($date, 'UTC');

		return (int)$dateTime->format('U');
	}

	public static function createDateTimeObjectFromString(string $date, ?string $timezone = null)
	{
		try
		{
			$parsedDateTime = ParseDateTime($date);
			$hours = (int)($parsedDateTime['HH'] ?? $parsedDateTime['H'] ?? 0);
			if (isset($parsedDateTime['TT']) || isset($parsedDateTime['T']))
			{
				$amPm = $parsedDateTime['TT'] ?? $parsedDateTime['T'];
				if (strcasecmp('pm', $amPm) === 0)
				{
					if ($hours < 12)
					{
						$hours += 12;
					}
				}
				else
				{
					$hours %= 12;
				}
			}

			$dateTime = (new \DateTime('now', $timezone ? new \DateTimeZone($timezone) : null))
				->setDate($parsedDateTime['YYYY'], $parsedDateTime['MM'], $parsedDateTime['DD'])
				->setTime($hours, $parsedDateTime['MI'] ?? 0);
		}
		catch (\TypeError)
		{
			$dateTime = new \DateTime($date, $timezone ? new \DateTimeZone($timezone) : null );
		}
		finally
		{
			return $dateTime;
		}
	}

	public static function TSFormat($format = "FULL")
	{
		return CSite::GetDateFormat($format);
	}

	public static function RoundTimestamp($ts)
	{
		return round($ts / 60) * 60; // We don't need for seconds here
	}

	public static function IsPersonal($type = false, $ownerId = false, $userId = false)
	{
		if (!$type)
		{
			$type = self::$type;
		}
		if(!$ownerId)
		{
			$ownerId = self::$ownerId;
		}
		if(!$userId)
		{
			$userId = self::$userId;
		}

		return $type === 'user' && $ownerId == $userId;
	}

	public static function IsExchangeEnabled($userId = false)
	{
		if (isset(self::$arExchEnabledCache[$userId]))
		{
			return self::$arExchEnabledCache[$userId];
		}

		if (!IsModuleInstalled('dav') || COption::GetOptionString("dav", "agent_calendar") !== "Y")
		{
			$res = false;
		}
		elseif (!Loader::includeModule('dav'))
		{
			$res = false;
		}
		elseif ($userId === false)
		{
			$res = CDavExchangeCalendar::IsExchangeEnabled();
		}
		else
		{
			$res = CDavExchangeCalendar::IsExchangeEnabled() && CDavExchangeCalendar::IsExchangeEnabledForUser($userId);
		}

		self::$arExchEnabledCache[$userId] = $res;

		return $res;
	}

	public static function isGoogleApiEnabled()
	{
		if (!isset(self::$isGoogleApiEnabled))
		{
			self::$isGoogleApiEnabled = \Bitrix\Main\ModuleManager::isModuleInstalled('dav')
				&& \Bitrix\Main\ModuleManager::isModuleInstalled('socialservices')
				&& (
					is_null(\Bitrix\Main\Config\Configuration::getValue("calendar_integration"))
					|| \Bitrix\Main\Config\Configuration::getValue("calendar_integration") === self::INTEGRATION_GOOGLE_API
				);

			if (self::$isGoogleApiEnabled
				&& !self::IsBitrix24()
				&& Loader::includeModule('socialservices'))
			{
				self::$isGoogleApiEnabled =
					(
						CSocServGoogleOAuth::GetOption('google_appid') !== ''
						&& CSocServGoogleOAuth::GetOption('google_appsecret') !== ''
					)
					|| CSocServGoogleOAuth::GetOption('google_sync_proxy') === 'Y'
				;
			}
		}

		return self::$isGoogleApiEnabled;
	}

	public static function IsCalDAVEnabled()
	{
		if (!IsModuleInstalled('dav') || COption::GetOptionString("dav", "agent_calendar_caldav") !== "Y")
		{
			return false;
		}
		return Loader::includeModule('dav') && CDavGroupdavClientCalendar::IsCalDAVEnabled();
	}

	public static function isICloudEnabled()
	{
		return self::IsCalDAVEnabled();
	}

	public static function isIphoneConnected()
	{
		$info = CCalendarSync::GetSyncInfoItem(self::$userId, 'iphone');

		return $info['connected'];
	}

	public static function isMacConnected()
	{
		$info = CCalendarSync::GetSyncInfoItem(self::$userId, 'mac');

		return $info['connected'];
	}

	public static function IsWebserviceEnabled()
	{
		if (!isset(self::$bWebservice))
		{
			self::$bWebservice = IsModuleInstalled('webservice');
		}
		return self::$bWebservice;
	}

	public static function IsExtranetEnabled()
	{
		if (!isset(self::$bExtranet))
		{
			self::$bExtranet = Loader::includeModule('extranet') && CExtranet::IsExtranetSite();
		}
		return self::$bExtranet;
	}

	public static function GetMeetingRoomList($params = [])
	{
		return Rooms\IBlockMeetingRoom::getMeetingRoomList($params);
	}

	public static function GetCurrentOffsetUTC($userId = false)
	{
		if (!$userId && self::$userId)
		{
			$userId = self::$userId;
		}

		return (int)(date("Z") + self::GetOffset($userId));
	}

	public static function GetOffset($userId = false)
	{
		if ($userId > 0)
		{
			if (!isset(self::$arTimezoneOffsets[$userId]))
			{
				$offset = CTimeZone::GetOffset($userId, true);
				self::$arTimezoneOffsets[$userId] = $offset;
			}
			else
			{
				$offset = self::$arTimezoneOffsets[$userId];
			}
		}
		else if (!isset(self::$offset))
		{
			$offset = CTimeZone::GetOffset(null, true);
			self::$offset = $offset;
		}
		else
		{
			$offset = self::$offset;
		}

		return $offset;
	}

	public static function GetUserTimezoneName($user, $getDefault = true)
	{
		if (isset(self::$userTimezoneList[$user]) && !is_array($user) && (int)$user > 0)
		{
			return self::$userTimezoneList[$user];
		}

		if (is_array($user) && (int)$user['ID'] > 0 && isset(self::$userTimezoneList[$user['ID']]))
		{
			return self::$userTimezoneList[$user['ID']];
		}
		else
		{
			if (!is_array($user) && (int)$user > 0)
			{
				$user = self::GetUser((int)$user, true);
			}

			if (\CTimezone::OptionEnabled() && $user && is_array($user))
			{
				$offset = isset($user['TIME_ZONE_OFFSET'])
					? (int)(date('Z') + $user['TIME_ZONE_OFFSET'])
					: self::GetCurrentOffsetUTC($user['ID']);

				$tzName = CUserOptions::GetOption(
					"calendar",
					"timezone" . $offset,
					false,
					$user['ID']
				);

				if ($tzName === 'undefined' || $tzName === 'false')
				{
					$tzName = false;
				}
				if (!$tzName && $user['AUTO_TIME_ZONE'] !== 'Y' && $user['TIME_ZONE'])
				{
					$tzName = $user['TIME_ZONE'];
				}
			}
			else
			{
				$offset = date('Z');
				$tzName = date_default_timezone_get();
			}

			try
			{
				new DateTimeZone($tzName);
			}
			catch (\Throwable)
			{
				$tzName = false;
			}

			if (!$tzName && $getDefault)
			{
				$tzName = self::GetGoodTimezoneForOffset($offset);
			}

			if ($user && is_array($user) && $user['ID'])
			{
				self::$userTimezoneList[$user['ID']] = $tzName;
			}
		}

		return $tzName;
	}

	public static function GetUser($userId, $bPhoto = false)
	{
		global $USER;
		$user = false;

		if (is_object($USER) && (int)$userId === (int)$USER->GetId() && !$bPhoto)
		{
			$user = [
				'ID' => $USER->GetId(),
				'NAME' => $USER->GetFirstName(),
				'LAST_NAME' => $USER->GetLastName(),
				'SECOND_NAME' => $USER->GetParam('SECOND_NAME'),
				'LOGIN' => $USER->GetLogin(),
				'PERSONAL_PHOTO' => $USER->GetParam('PERSONAL_PHOTO'),
			];
		}
		else if ($rsUser = CUser::GetByID((int)$userId)->Fetch())
		{
			$user = [
				'ID' => (int)$rsUser['ID'],
				'NAME' => $rsUser['NAME'],
				'LAST_NAME' => $rsUser['LAST_NAME'],
				'SECOND_NAME' => $rsUser['SECOND_NAME'],
				'LOGIN' => $rsUser['LOGIN'],
				'PERSONAL_PHOTO' => $rsUser['PERSONAL_PHOTO'],
				'AUTO_TIME_ZONE' => $rsUser['AUTO_TIME_ZONE'],
				'TIME_ZONE' => $rsUser['TIME_ZONE'],
			];
		}

		return $user;
	}

	public static function GetGoodTimezoneForOffset($offset)
	{
		$timezones = self::GetTimezoneList();
		$goodTz = [];
		$result = false;

		foreach($timezones as $tz)
		{
			if ($tz['offset'] == $offset)
			{
				$goodTz[] = $tz;
				if (LANGUAGE_ID == 'ru')
				{
					if (preg_match('/(kaliningrad|moscow|samara|yekaterinburg|novosibirsk|krasnoyarsk|irkutsk|yakutsk|vladivostok)/i', $tz['timezone_id']))
					{

						$result = $tz['timezone_id'];
						break;
					}
				}
				elseif (mb_strpos($tz['timezone_id'], 'Europe') !== false)
				{
					$result = $tz['timezone_id'];
					break;
				}
			}
		}

		if (!$result && !empty($goodTz))
		{
			$result = $goodTz[0]['timezone_id'];
		}

		if (!$result)
		{
			$result = date_default_timezone_get();
		}

		return $result;
	}

	public static function GetTimezoneList()
	{
		if (empty(self::$timezones))
		{
			self::$timezones = [];
			$aExcept = ["Etc/", "GMT", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU"];
			foreach(DateTimeZone::listIdentifiers() as $tz)
			{
				foreach($aExcept as $ex)
				{
					if(str_starts_with($tz, $ex))
					{
						continue 2;
					}
				}
				try
				{
					$oTz = new DateTimeZone($tz);
					self::$timezones[$tz] = [
						'timezone_id' => $tz,
						'offset' => $oTz->getOffset(new DateTime("now", $oTz))
					];
				}
				catch(Exception $e)
				{
				}
			}
			uasort(self::$timezones, static function($a, $b){
				if($a['offset'] === $b['offset'])
				{
					return strcmp($a['timezone_id'], $b['timezone_id']);
				}
				return $a['offset'] < $b['offset'] ? -1 : 1;
			});

			foreach(self::$timezones as $k => $z)
			{
				$offset = $z['offset'];
				$hours = floor(abs($offset) / 3600);

				if ($z['timezone_id'] === 'UTC')
				{
					self::$timezones[$k]['title'] = $z['timezone_id'];
				}
				else
				{
					self::$timezones[$k]['title'] =
						'(UTC'
						. ($offset !== 0
							? ' ' . ($offset < 0 ? '-' : '+')
							.sprintf("%02d", $hours)
							. ':' . sprintf("%02d", abs($offset)/60 - $hours * 60)
							: ''
						) . ') ' . $z['timezone_id'];
				}
			}
		}
		return self::$timezones;
	}

	public static function GetUserName($user)
	{
		if (!is_array($user) && (int)$user > 0)
		{
			$user = self::GetUser($user);
		}
		if (!$user || !is_array($user))
		{
			return '';
		}

		return CUser::FormatName(self::$userNameTemplate, $user, true, false);
	}

	public static function IsAdmin(): bool
	{
		GLOBAL $USER;

		return $USER->IsAdmin();
	}

	public static function GetWeekStart()
	{
		if (!isset(self::$weekStart))
		{
			$days = ['1' => 'MO', '2' => 'TU', '3' => 'WE', '4' => 'TH', '5' => 'FR', '6' => 'SA', '0' => 'SU'];
			$cultureWeekStart = \Bitrix\Main\Context::getCurrent()->getCulture()->getWeekStart();
			self::$weekStart = $days[$cultureWeekStart];

			if (!in_array(self::$weekStart, $days))
			{
				self::$weekStart = 'MO';
			}
		}

		return self::$weekStart;
	}

	public static function Date($timestamp, $bTime = true, $bRound = true, $bCutSeconds = false)
	{
		if ($bRound)
		{
			$timestamp = self::RoundTimestamp($timestamp);
		}

		$format = self::DFormat($bTime);
		if ($bTime && $bCutSeconds)
		{
			$format = str_replace(':s', '', $format);
		}

		return FormatDate($format, $timestamp);
	}

	public static function DFormat($bTime = true)
	{
		return CDatabase::DateFormatToPHP(CSite::GetDateFormat($bTime ? "FULL" : "SHORT", SITE_ID));
	}

	public static function DateWithNewTime($timestampTime, $timestampDate)
	{
		return mktime(date("H", $timestampTime), date("i", $timestampTime), 0, date("m", $timestampDate), date("d", $timestampDate), date("Y", $timestampDate));
	}

	public static function GetCurUserMeetingSection($bCreate = false)
	{
		if (!isset(self::$userMeetingSection) || !self::$userMeetingSection)
		{
			self::$userMeetingSection = self::GetMeetingSection(self::$userId, $bCreate);
		}

		return self::$userMeetingSection;
	}

	public static function GetMeetingSection($userId, $autoCreate = false)
	{
		if (isset(self::$meetingSections[$userId]))
		{
			return self::$meetingSections[$userId];
		}

		$result = false;
		$meetingSectionId = false;
		if ($userId > 0)
		{
			$set = UserSettings::get($userId);

			$result = $set['meetSection'];
			$meetingSectionId = $result;
			$section = false;

			if ($result)
			{
				$section = CCalendarSect::GetList([
					'arFilter' => [
						'ID' => $result,
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId,
						'ACTIVE' => 'Y',
					],
					'checkPermissions' => false,
					'getPermissions' => false,
				]);
				if($section && is_array($section) && is_array($section[0]))
				{
					$section = $section[0];
				}
			}

			if($result && !$section)
			{
				$result = false;
			}

			if (!$result)
			{
				$res = CCalendarSect::GetList([
					'arFilter' => [
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId,
						'ACTIVE' => 'Y',
					],
					'checkPermissions' => false,
					'getPermissions' => false,
				]);
				if ($res && !empty($res) && $res[0]['ID'])
				{
					$result = $res[0]['ID'];
				}

				if (!$result && $autoCreate)
				{
					$defCalendar = CCalendarSect::CreateDefault([
						'type' => 'user',
						'ownerId' => $userId,
					]);
					if ($defCalendar && $defCalendar['ID'] > 0)
					{
						$result = $defCalendar['ID'];
					}
				}
			}
		}

		foreach(\Bitrix\Main\EventManager::getInstance()->findEventHandlers("calendar", "OnGetMeetingSectionForUser") as $event)
		{
			ExecuteModuleEventEx($event, array($userId, &$result));
		}

		if ($meetingSectionId != $result)
		{
			$set['meetSection'] = $result;
			UserSettings::set($set, $userId);
		}

		self::$meetingSections[$userId] = $result;
		return $result;
	}

	public static function GetCrmSection($userId, $autoCreate = false)
	{
		if (isset(self::$crmSections[$userId]))
		{
			return self::$crmSections[$userId];
		}

		$result = false;
		if ($userId > 0)
		{
			$set = UserSettings::get($userId);

			$result = $set['crmSection'];
			$section = false;

			if ((int)$result)
			{
				$sectionQueryResult = \Bitrix\Calendar\Internals\SectionTable::query()
					->setSelect(['ID', 'CAL_TYPE', 'OWNER_ID'])
					->where('ID', (int)$result)
					->where('CAL_TYPE', 'user')
					->where('OWNER_ID', (int)$userId)
					->exec()->fetch()
				;
				$section = !empty($sectionQueryResult) ? $sectionQueryResult['ID'] : false;
			}

			if ($result && !$section)
			{
				$result = false;
			}

			if (!$result)
			{
				$sectionQueryResult = \Bitrix\Calendar\Internals\SectionTable::query()
					->setSelect(['ID', 'CAL_TYPE', 'OWNER_ID'])
					->where('CAL_TYPE', 'user')
					->where('OWNER_ID', (int)$userId)
					->exec()->fetch()
				;

				if (!empty($sectionQueryResult) && $sectionQueryResult['ID'])
				{
					$result = $sectionQueryResult['ID'];
				}

				if (!$result && $autoCreate)
				{
					$defCalendar = CCalendarSect::CreateDefault(array(
						'type' => 'user',
						'ownerId' => $userId,
					));
					if ($defCalendar && $defCalendar['ID'] > 0)
					{
						$result = $defCalendar['ID'];
					}
				}

				if($result)
				{
					$set['crmSection'] = $result;
					UserSettings::set($set, $userId);
				}
			}
		}

		self::$crmSections[$userId] = $result;
		return $result;
	}

	public static function GetSectionList($params = [])
	{
		$type = $params['CAL_TYPE'] ?? self::$type;

		$arFilter = [
			'CAL_TYPE' => $type,
		];

		if (isset($params['OWNER_ID']))
		{
			$arFilter['OWNER_ID'] = $params['OWNER_ID'];
		}
		elseif ($type === 'user' || $type === 'group')
		{
			$arFilter['OWNER_ID'] = self::GetOwnerId();
		}

		if (isset($params['ACTIVE']))
		{
			$arFilter['ACTIVE'] = $params['ACTIVE'];
		}

		if (isset($params['ADDITIONAL_IDS']) && !empty($params['ADDITIONAL_IDS']))
		{
			$arFilter['ADDITIONAL_IDS'] = $params['ADDITIONAL_IDS'];
		}

		$sectionList = CCalendarSect::GetList([
			'arFilter' => $arFilter,
			'checkPermissions' => ($params['checkPermissions'] ?? null),
			'getPermissions' => ($params['getPermissions'] ?? null),
		]);

		if ($type === 'user')
		{
			$sectionIdList = [];
			foreach ($sectionList as $section)
			{
				$sectionIdList[] = (int)$section['ID'];
			}

			$sectionLinkList = SectionConnectionTable::getList([
				'select' => [
					'SECTION_ID',
					'CONNECTION_ID',
					'ACTIVE',
					'IS_PRIMARY',
				],
				'filter' => [
					'=SECTION_ID' => $sectionIdList,
				],
			])->fetchAll();

			if (!empty($sectionLinkList))
			{
				foreach ($sectionList as $i => $section)
				{
					$sectionList[$i]['connectionLinks'] = [];
					foreach ($sectionLinkList as $sectionLink)
					{
						if ((int)$sectionLink['SECTION_ID'] === (int)$section['ID'])
						{
							$sectionList[$i]['connectionLinks'][] = [
								'id' => $sectionLink['CONNECTION_ID'],
								'active' => $sectionLink['ACTIVE'],
								'isPrimary' => $sectionLink['IS_PRIMARY'],
							];
						}
					};
				}
			}
		}

		if (($params['getImages'] ?? null))
		{
			$sectionList = self::fetchIconsForSectionList($sectionList);
		}

		return $sectionList;
	}

	public static function fetchIconsForSectionList($sectionList)
	{
		$SECTION_IMG_SIZE = 28;
		$userIdList = [];
		$groupIdList = [];
		$userIndexList = [];
		$groupListIndex = [];

		foreach ($sectionList as $section)
		{
			$ownerId = (int)$section['OWNER_ID'];
			if (
				$section['CAL_TYPE'] === 'user'
				&& !in_array($ownerId, $userIdList)
			)
			{
				$userIdList[] = $ownerId;
			}
			elseif ($section['CAL_TYPE'] === 'group'
				&& !in_array($ownerId, $groupIdList))
			{
				$groupIdList[] = $ownerId;
			}
		}

		if (!empty($userIdList))
		{
			$userIndexList = \CCalendarEvent::getUsersDetails($userIdList);
		}

		if (!empty($groupIdList) && Loader::includeModule("socialnetwork"))
		{
			$res = Bitrix\Socialnetwork\WorkgroupTable::getList([
				'filter' => [
					'=ACTIVE' => 'Y',
					'@ID' => $groupIdList,
				],
				'select' => ['ID', 'IMAGE_ID'],
			]);
			while ($workgroupFields = $res->fetch())
			{
				if (!empty($workgroupFields["IMAGE_ID"]))
				{
					$arFileTmp = CFile::ResizeImageGet(
						$workgroupFields["IMAGE_ID"],
						['width' => $SECTION_IMG_SIZE, 'height' => $SECTION_IMG_SIZE],
						BX_RESIZE_IMAGE_EXACT,
						false,
						false,
						true
					);
					$workgroupFields['IMAGE'] = $arFileTmp['src'];
				}
				$groupListIndex[$workgroupFields['ID']] = $workgroupFields;
			}
		}

		foreach ($sectionList as $k => $section)
		{
			$ownerId = (int)$section['OWNER_ID'];
			if ($section['CAL_TYPE'] === 'user'
				&& isset($userIndexList[$ownerId])
				&& !empty($userIndexList[$ownerId]['AVATAR'])
				&& $userIndexList[$ownerId]['AVATAR'] !== '/bitrix/images/1.gif'
			)
			{
				$sectionList[$k]['IMAGE'] = $userIndexList[$ownerId]['AVATAR'];
			}
			elseif (
				$section['CAL_TYPE'] === 'group'
				&& isset($groupListIndex[$ownerId])
				&& !empty($groupListIndex[$ownerId]['IMAGE'])
			)
			{
				$sectionList[$k]['IMAGE'] = $groupListIndex[$ownerId]['IMAGE'];
			}

			$pathesForSite = self::getPathes(SITE_ID);
			if ($section['CAL_TYPE'] === 'user')
			{
				$sectionList[$k]['LINK'] = str_replace(
					['#user_id#', '#USER_ID#'],
					$section['OWNER_ID'],
					$pathesForSite['path_to_user_calendar']
				);
			}
			else if($section['CAL_TYPE'] === 'group')
			{
				$sectionList[$k]['LINK'] = str_replace(
					['#group_id#', '#GROUP_ID#'],
					$section['OWNER_ID'],
					$pathesForSite['path_to_user_calendar']
				);
			}
			else
			{
				$path = $pathesForSite['path_to_type_'.$section['CAL_TYPE']];
				$sectionList[$k]['LINK'] = $path;
			}
		}
		return $sectionList;
	}

	public static function GetOwnerId()
	{
		return self::$ownerId;
	}

	/**
	 * @param array $params
	 * @param array $arAttendees
	 *
	 * @return array|null
	 */
	public static function GetEventList($params, &$arAttendees)
	{
		$type = isset($params['type']) ? $params['type'] : self::$type;
		$ownerId = isset($params['ownerId']) ? (int)$params['ownerId'] : self::$ownerId;
		$userId = isset($params['userId']) ? (int)$params['userId'] : self::$userId;

		if (empty($params['section']))
		{
			return [];
		}

		$arFilter = [];
		if (isset($params['fromLimit']))
		{
			$arFilter["FROM_LIMIT"] = $params['fromLimit'];
		}
		if (isset($params['toLimit']))
		{
			$arFilter["TO_LIMIT"] = $params['toLimit'];
		}

		$arFilter["OWNER_ID"] = $ownerId;

		if ($type === 'user')
		{
			$fetchMeetings = in_array(self::GetMeetingSection($ownerId), $params['section']);
		}
		else
		{
			$fetchMeetings = in_array(self::GetCurUserMeetingSection(), $params['section']);
			if ($type)
			{
				$arFilter['CAL_TYPE'] = $type;
			}
		}

		$res = CCalendarEvent::GetList([
			'arFilter' => $arFilter,
			'parseRecursion' => true,
			'fetchAttendees' => true,
			'userId' => $userId,
			'fetchMeetings' => $fetchMeetings,
			'setDefaultLimit' => false,
			'limit' => $params['limit'],
			'getUserfields' => true,
		]);

		if (!empty($params['section']))
		{
			$NewRes = [];
			foreach($res as $event)
			{
				if (in_array($event['SECT_ID'], $params['section']))
				{
					unset($event['DESCRIPTION'], $event['~DESCRIPTION']);
					$NewRes[] = $event;
				}
			}
			$res = $NewRes;
		}

		//$arAttendees = CCalendarEvent::GetLastAttendees();
		return $res;
	}

	public static function getTaskList(TaskQueryParameter $parameter)
	{
		if (!Loader::includeModule('tasks'))
		{
			return [];
		}

		$res = [];
		$userSettings = Bitrix\Calendar\UserSettings::get();

		$filter = [
			'!STATUS' => [
				Status::DEFERRED,
			],
			'CHECK_PERMISSIONS' => 'Y',
		];

		if ($userSettings['showCompletedTasks'] === 'N')
		{
			$filter['!STATUS'][] = Status::COMPLETED;
		}
		if ($parameter->isUserType())
		{
			$filter['DOER'] = $parameter->getOwnerId();
		}
		elseif ($parameter->isGroupType())
		{
			$filter['GROUP_ID'] = $parameter->getOwnerId();
		}

		$tzEnabled = CTimeZone::Enabled();
		if ($tzEnabled)
		{
			CTimeZone::Disable();
		}

		$query = (new TaskQuery($parameter->getUserId()))
			->setSelect([
				'ID',
				'TITLE',
				'DESCRIPTION',
				'CREATED_DATE',
				'DEADLINE',
				'START_DATE_PLAN',
				'END_DATE_PLAN',
				'DATE_START',
				'CLOSED_DATE',
				'STATUS_CHANGED_DATE',
				'STATUS',
				'REAL_STATUS',
				'CREATED_BY',
				'GROUP_ID',
			])
			->setOrder(['START_DATE_PLAN' => 'ASC'])
			->setWhere($filter);

		$tasks = (new TaskList())->getList($query);

		$offset = self::GetOffset();
		foreach ($tasks as $task)
		{
			$dtFrom = null;
			$dtTo = null;

			$skipFromOffset = false;
			$skipToOffset = false;

			if (isset($task["START_DATE_PLAN"]) && $task["START_DATE_PLAN"])
			{
				$dtFrom = self::CutZeroTime($task["START_DATE_PLAN"]);
			}

			if (isset($task["END_DATE_PLAN"]) && $task["END_DATE_PLAN"])
			{
				$dtTo = self::CutZeroTime($task["END_DATE_PLAN"]);
			}

			if (!isset($dtFrom) && isset($task["DATE_START"]))
			{
				$dtFrom = self::CutZeroTime($task["DATE_START"]);
			}

			if (!isset($dtTo) && isset($task["CLOSED_DATE"]))
			{
				$dtTo = self::CutZeroTime($task["CLOSED_DATE"]);
			}

			if (
				!isset($dtTo) && isset($task["STATUS_CHANGED_DATE"])
				&& in_array(
					(int)$task["REAL_STATUS"],
					[Status::SUPPOSEDLY_COMPLETED, Status::COMPLETED, Status::DEFERRED, Status::DECLINED],
					true
				)
			)
			{
				$dtTo = self::CutZeroTime($task["STATUS_CHANGED_DATE"]);
			}

			if (isset($dtTo))
			{
				$ts = self::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
				if (date("H:i", $ts) == '00:00')
				{
					$dtTo = self::Date($ts - 24 * 60 * 60);
				}
			}
			elseif (isset($task["DEADLINE"]))
			{
				$dtTo = self::CutZeroTime($task["DEADLINE"]);
				$ts = self::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
				if (date("H:i", $ts) == '00:00')
				{
					$dtTo = self::Date($ts - 24 * 60 * 60);
				}

				if (!isset($dtFrom))
				{
					$skipFromOffset = true;
					$dtFrom = self::Date(time(), false);
				}
			}

			if (!isset($dtTo))
			{
				$dtTo = self::Date(time(), false);
			}

			if (!isset($dtFrom))
			{
				$dtFrom = $dtTo;
			}

			$dtFromTS = self::Timestamp($dtFrom);
			$dtToTS = self::Timestamp($dtTo);

			if ($dtToTS < $dtFromTS)
			{
				$dtToTS = $dtFromTS;
				$dtTo = self::Date($dtToTS, true);
			}

			$skipTime = date("H:i", $dtFromTS) == '00:00' && date("H:i", $dtToTS) == '00:00';
			if (!$skipTime && $offset != 0)
			{
				if (!$skipFromOffset)
				{
					$dtFromTS += $offset;
					$dtFrom = self::Date($dtFromTS, true);
				}

				if (!$skipToOffset)
				{
					$dtToTS += $offset;
					$dtTo = self::Date($dtToTS, true);
				}
			}

			$res[] = [
				"ID" => $task["ID"],
				"~TYPE" => "tasks",
				"NAME" => $task["TITLE"],
				"DATE_FROM" => $dtFrom,
				"DATE_TO" => $dtTo,
				"DT_SKIP_TIME" => $skipTime ? 'Y' : 'N',
				"CAN_EDIT" => CTasks::CanCurrentUserEdit($task),
			];
		}

		if ($tzEnabled)
		{
			CTimeZone::Enable();
		}

		return $res;
	}

	public static function CutZeroTime($date)
	{
		if (preg_match('/.*\s\d\d:\d\d:\d\d/i', $date))
		{
			$date = trim($date);
			if (mb_substr($date, -9) == ' 00:00:00')
				return mb_substr($date, 0, -9);
			if (mb_substr($date, -3) == ':00')
				return mb_substr($date, 0, -3);
		}
		return $date;
	}

	public static function GetType()
	{
		return self::$type;
	}

	public static function GetAccessNames()
	{
		$codes = [];
		foreach (self::$accessNames as $code => $name)
		{
			if ($name === null)
			{
				$codes[] = $code;
			}
		}

		if ($codes)
		{
			$access = new CAccess();
			$names = $access->GetNames($codes);

			foreach($names as $code => $name)
			{
				self::$accessNames[$code] = trim(htmlspecialcharsbx($name['name']));
			}
			self::$accessNames['UA'] = Loc::getMessage('EC_ENTITY_SELECTOR_ALL_EMPLOYEES');
		}

		return self::$accessNames;
	}

	public static function SetSilentErrorMode($silentErrorMode = true)
	{
		self::$silentErrorMode = $silentErrorMode;
	}

	public function GetId()
	{
		return self::$id ? self::$id : 'EC'.rand();
	}

	/**
	 * @param $parentDateTime
	 * @param $instanceDateTime
	 * @param $timeZone
	 * @param $format
	 * @param $originalInstanceDate
	 *
	 * @return string
	 * @throws Main\ObjectException
	 */
	public static function GetOriginalDate(
		$parentDateTime,
		$instanceDateTime,
		$timeZone = null,
		$format = null
	): string
	{
		CTimeZone::Disable();
		$format = $format ?? Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME);
		$parentTimestamp = Util::getDateObject($parentDateTime, false, $timeZone)->getTimestamp();
		$baseTimeZone = date_default_timezone_get();
		if ($timeZone)
		{
			date_default_timezone_set($timeZone);
		}
		$parentInfoDate = getdate($parentTimestamp);
		/** @var Type\DateTime $instanceDateTime */
		$instanceDateTime = Util::getDateObject($instanceDateTime, false, $timeZone);
		$eventDate = $instanceDateTime->setTime($parentInfoDate['hours'], $parentInfoDate['minutes'])->format($format);
		if ($baseTimeZone)
		{
			date_default_timezone_set($baseTimeZone);
		}
		CTimeZone::Enable();

		return $eventDate;
	}

	public static function getSectionListAvailableForUser($userId, $additionalSectionIdList = [])
	{
		return self::GetSectionList([
			'CAL_TYPE' => 'user',
			'OWNER_ID' => $userId,
			'ACTIVE' => 'Y',
			'ADDITIONAL_IDS' => array_merge($additionalSectionIdList, UserSettings::getFollowedSectionIdList($userId)),
		]);
	}

	public static function getSectionListForContext(array $params = []): array
	{
		$userId = isset($params['userId']) ? (int)$params['userId'] : self::getCurUserId();
		$sections = [];
		$followedSectionList = UserSettings::getFollowedSectionIdList($userId);
		$hiddenSections = UserSettings::getHiddenSections($userId);

		self::$userMeetingSection = self::GetCurUserMeetingSection();

		$sectionList = self::GetSectionList(
			[
				'ADDITIONAL_IDS' => $followedSectionList,
				'checkPermissions' => true,
				'getPermissions' => true,
				'getImages' => true,
			]
		);

		$sectionList = array_merge($sectionList, self::getSectionListAvailableForUser($userId));

		$sectionIdList = [];
		foreach ($sectionList as $i => $section)
		{
			if (!in_array((int)$section['ID'], $sectionIdList))
			{
				$sections[] = $section;
				$sectionIdList[] = (int)$section['ID'];
			}
		}

		$readOnly = !self::$perm['edit'] && !self::$perm['section_edit'];

		if (self::$type === 'user' && self::$ownerId != self::$userId)
			$readOnly = true;

		if (self::$bAnonym)
			$readOnly = true;

		$bCreateDefault = !self::$bAnonym;

		if (self::$type === 'user')
		{
			$bCreateDefault = self::$ownerId == self::$userId;
		}

		$additionalMeetingsId = [];
		$groupOrUser = self::$type === 'user' || self::$type === 'group';
		if ($groupOrUser)
		{
			$noEditAccessedCalendars = true;
		}

		$trackingUsers = [];
		$trackingGroups = [];

		foreach ($sections as $i => $section)
		{
			$sections[$i]['~IS_MEETING_FOR_OWNER'] = $section['CAL_TYPE'] === 'user' && $section['OWNER_ID'] !== self::$userId && self::GetMeetingSection($section['OWNER_ID']) === $section['ID'];

			if (!in_array($section['ID'], $hiddenSections, true) && $section['ACTIVE'] !== 'N')
			{
				// It's superposed calendar of the other user and it's need to show user's meetings
				if ($sections[$i]['~IS_MEETING_FOR_OWNER'])
				{
					$additionalMeetingsId[] = array('ID' => $section['OWNER_ID'], 'SECTION_ID' => $section['ID']);
				}
			}

			// We check access only for main sections because we can't edit superposed section
			if ($groupOrUser && $sections[$i]['CAL_TYPE'] == self::$type &&
				$sections[$i]['OWNER_ID'] == self::$ownerId)
			{
				if ($noEditAccessedCalendars && $section['PERM']['edit'])
					$noEditAccessedCalendars = false;

				if ($readOnly && ($section['PERM']['edit'] || $section['PERM']['edit_section']) && !self::$isArchivedGroup)
					$readOnly = false;
			}

			if (in_array($section['ID'], $followedSectionList))
			{
				$sections[$i]['SUPERPOSED'] = true;
			}

			if ($bCreateDefault && $section['CAL_TYPE'] == self::$type && $section['OWNER_ID'] == self::$ownerId)
			{
				$bCreateDefault = false;
			}

			if ($sections[$i]['SUPERPOSED'])
			{
				$type = $sections[$i]['CAL_TYPE'];
				if ($type === 'user')
				{
					$path = self::$pathesForSite['path_to_user_calendar'];
					$path = CComponentEngine::MakePathFromTemplate($path, array("user_id" => $sections[$i]['OWNER_ID']));
					$trackingUsers[] = $sections[$i]['OWNER_ID'];
				}
				elseif($type === 'group')
				{
					$path = self::$pathesForSite['path_to_group_calendar'];
					$path = CComponentEngine::MakePathFromTemplate($path, array("group_id" => $sections[$i]['OWNER_ID']));
					$trackingGroups[] = $sections[$i]['OWNER_ID'];
				}
				else
				{
					$path = self::$pathesForSite['path_to_type_'.$type];
				}
				$sections[$i]['LINK'] = $path;
			}
		}

		if ($groupOrUser && $noEditAccessedCalendars && !$bCreateDefault)
		{
			$readOnly = true;
		}

		self::$readOnly = $readOnly;

		return $sections;
	}

	public static function setOwnerId($userId)
	{
		self::$ownerId = $userId;
	}

	public static function isOffice365ApiEnabled(): ?bool
	{
		if (!isset(self::$isOffice365ApiEnabled))
		{
			self::$isOffice365ApiEnabled = \Bitrix\Main\ModuleManager::isModuleInstalled('dav')
				&& \Bitrix\Main\ModuleManager::isModuleInstalled('socialservices')
			;

			if (self::$isOffice365ApiEnabled
				&& !self::IsBitrix24()
				&& Loader::includeModule('socialservices'))
			{
				self::$isOffice365ApiEnabled = CSocServGoogleOAuth::GetOption('office365_appid') !== '' && CSocServGoogleOAuth::GetOption('office365_appid') !== '';
			}
		}

		return self::$isOffice365ApiEnabled;
	}

	/**
	 * @param int $id
	 * @param array $arFields
	 * @param array $params
	 * @param array|null $curEvent
	 *
	 * @return Sync\Util\Result|null
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectNotFoundException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 *
	 * @todo temporary resolve. This method will change when we change a common design.
	 */
	public static function syncChange(int $id, array $arFields, array $params, ?array $curEvent): ?Sync\Util\Result
	{
		/** @var Bitrix\Calendar\Core\Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		/** @var Core\Event\Event $event */
		$event = $mapperFactory->getEvent()->resetCacheById($id)->getById($id);
		if (!$event)
		{
			return null;
		}

		if (
			$curEvent
			&& !empty($curEvent['SECT_ID'])
			&& $event->getSection()
			&& (int)$curEvent['SECT_ID'] !== $event->getSection()->getId()
		)
		{
			return self::changeCalendarSync($event, $curEvent, $params);
		}

		$factories = FactoriesCollection::createBySection(
			$event->getSection()
		);

		if ($factories->count() === 0)
		{
			return null;
		}

		$syncManager = new Synchronization($factories);
		$context = new Context([]);

		if (
			$params['originalFrom']
			&& (int)$arFields['OWNER_ID'] === (int)$params['userId']
		)
		{
			$context->add('sync', 'originalFrom', $params['originalFrom']);

			$connection = (new Bitrix\Calendar\Core\Mappers\Connection())->getMap([
				'=ACCOUNT_TYPE' => $params['originalFrom'],
				'=ENTITY_TYPE' => $event->getCalendarType(),
				'=ENTITY_ID' => $event->getOwner()->getId(),
			])->fetch();

			if ($connection)
			{
				$syncManager->upEventVersion(
					$event,
					$connection,
					$arFields['VERSION'] ?? 1
				);
			}
		}

		$pushManager = new Sync\Managers\PushManager();
		try
		{
			/** @var Sync\Factories\FactoryBase $factory */
			foreach ($factories as $factory)
			{
				$pushManager->unLockConnection($factory->getConnection());
				$pushManager->lockConnection($factory->getConnection(), 30);
			}
			if (($params['recursionEditMode'] ?? null) === 'skip')
			{
				if ($event->isInstance())
				{
					$params['editInstance'] = $event->isInstance();
					$params['modeSync'] = true;
				}

				if (!empty($params['modeSync']))
				{
					$recurrenceSyncMode = ($params['editInstance'] << 2)
						| ($params['editNextEvents'] << 1)
						| ($params['editEntryUntil'] << 1)
						| $params['editParentEvents']
					;
					switch ($recurrenceSyncMode)
					{
						case Sync\Dictionary::RECURRENCE_SYNC_MODE['exception']:
							if (
								(
									$event->getMeetingStatus() === 'H'
									&& !empty($params['editMeetingStatus'])
									&& $params['editMeetingStatus']['status'] === 'N'
								)
								||
								(
									$event->getMeetingStatus() !== 'N'
									&& (empty($params['editMeetingStatus']) || $params['editMeetingStatus']['status'] !== 'N')
								)
							)
							{
								$result = empty($curEvent)
									? $syncManager->createInstance($event, $context)
									: $syncManager->updateInstance($event, $context)
								;
							}
							break;
						case Sync\Dictionary::RECURRENCE_SYNC_MODE['deleteInstance']:
							$context->add('diff', 'EXDATE', $curEvent['EXDATE']);
							$result = $syncManager->deleteInstance($event, $context);
							break;
						default:
							if ($event->getMeetingStatus() !== 'N')
							{
								$result = empty($curEvent)
									? $syncManager->createEvent($event, $context)
									: $syncManager->updateEvent($event, $context)
								;
							}
					}
				}
			}
			elseif (empty($curEvent))
			{
				if ($event->isInstance())
				{
					$attendeeMasterEvent = $mapperFactory->getEvent()->getMap([
						'=PARENT_ID' => $event->getRecurrenceId(),
						'=OWNER_ID' => $event->getOwner()->getId(),
						'=CAL_TYPE' => 'user'
					])->fetch();

					if ($attendeeMasterEvent)
					{
						$result = $syncManager->reCreateRecurrence($attendeeMasterEvent, $context);
					}
					else
					{
						$result =  (new Sync\Util\Result())
							->addError(new Main\Error("Master event not found", 404));
					}
				}
				else
				{
					$result = $syncManager->createEvent($event, $context);
				}
			}
			else
			{
				$syncManager->updateEvent($event, $context);
			}
		}
		catch(Throwable $e)
		{
			throw $e;
		}
		finally
		{
			/** @var Sync\Factories\FactoryBase $factory */
			foreach ($factories as $factory)
			{
				// TODO: try to use it
				// $pushManager->unLockConnection($factory->getConnection());
			}
		}

		return $result ?? null;
	}

	public static function changeCalendarSync(Core\Event\Event $event, array $currentEvent, array $params)
	{
		$result = null;
		/** @var Bitrix\Calendar\Core\Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$pushManager = new Sync\Managers\PushManager();
		/** @var Core\Section\Section $oldSection */
		$oldSection = $mapperFactory->getSection()->getById($currentEvent['SECTION_ID']);

		$oldFactories = FactoriesCollection::createBySection($oldSection);
		if ($oldFactories->count() > 0)
		{
			$syncManager = new Synchronization($oldFactories);
			$context = new Context();
			foreach ($oldFactories as $factory)
			{
				$pushManager->unLockConnection($factory->getConnection());
				$pushManager->lockConnection($factory->getConnection());
			}

			$eventId = (int)$currentEvent['ID'];
			if ($currentEvent['RECURRENCE_ID'] && $currentEvent['ORIGINAL_DATE_FROM'])
			{
				$masterEvent = \Bitrix\Calendar\Internals\EventTable::query()
					->setSelect(['ID'])
					->where('DELETED', 'N')
					->where('PARENT_ID', $currentEvent['RECURRENCE_ID'])
					->where('OWNER_ID', $currentEvent['OWNER_ID'])
					->exec()->fetch()
				;

				if (!empty($masterEvent['ID']))
				{
					$eventId = (int)$masterEvent['ID'];
				}
			}

			/** @var Core\Event\Event $eventToDelete */
			$eventToDelete = $mapperFactory->getEvent()->getById($eventId);

			if ($eventToDelete)
			{
				$clonedEvent = (new Core\Builders\EventCloner($event))->build();
				$clonedEvent->setSection($oldSection);
				$syncManager->deleteEvent($clonedEvent, $context);
			}
		}

		$newFactories = FactoriesCollection::createBySection($event->getSection());
		if ($newFactories->count() > 0)
		{
			foreach ($newFactories as $factory)
			{
				$pushManager->unLockConnection($factory->getConnection());
				$pushManager->lockConnection($factory->getConnection());
			}

			$syncManager = new Synchronization($newFactories);
			$context = new Context();

			if ($event->isInstance())
			{
				$masterEvent = $mapperFactory->getEvent()->getMap([
					'=PARENT_ID' => $event->getRecurrenceId(),
					'=OWNER_ID' => $event->getOwner()->getId(),
				])->fetch();

				$result = $syncManager->createRecurrence($masterEvent, $context);
			}
			else if ($event->getRecurringRule())
			{
				$result = $syncManager->createRecurrence($event, $context);
			}
			else
			{
				$result = $syncManager->createEvent($event, $context);
			}
		}

		return $result;
	}

	/**
	 * @param $id
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function stopGoogleConnectionChannels(int $connectionId): void
	{
		GoogleApiPush::stopChannel(
			GoogleApiPush::getPush(GoogleApiPush::TYPE_CONNECTION, $connectionId),
			self::$ownerId
		);
	}

	/**
	 * @param $connectionId
	 * @return array|false|mixed
	 */
	private static function getSectionsByConnectionId($connectionId)
	{
		return \CCalendarSect::GetList([
			'arFilter' => [
				'CAL_TYPE' => 'user',
				'OWNER_ID' => self::$ownerId,
				'CAL_DAV_CON' => $connectionId,
			],
		]);
	}

	private static function deleteGoogleConnectionSections(array $sections)
	{
		foreach ($sections as $section)
		{
			self::stopGoogleSectionChannels($section);
			if (in_array(
				$section['EXTERNAL_TYPE'],
				Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE,
				true
			))
			{
				CCalendarSect::Delete($section['ID'], false);
			}
			else
			{
				\CCalendarSect::CleanFieldsValueById((int)$section['ID'], [
					'CAL_DAV_CON',
					'SYNC_TOKEN',
					'PAGE_TOKEN',
				]);
				\CCalendarEvent::cleanFieldsValueBySectionId((int)$section['ID'], [
					'SYNC_STATUS',
				]);
			}
		}
	}

	private static function editGoogleConnectionsSections(array $sections)
	{
		foreach ($sections as $section)
		{
			self::stopGoogleSectionChannels($section);
			self::markSectionLikeDelete((int)$section['ID']);
		}
	}

	/**
	 * @param $section
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function stopGoogleSectionChannels(array $section): void
	{
		GoogleApiPush::stopChannel(
			GoogleApiPush::getPush(GoogleApiPush::TYPE_SECTION, (int)$section['ID']),
			(int)$section['OWNER_ID']
		);
	}

	/**
	 * @param $sectionId
	 */
	private static function markSectionLikeDelete(int $sectionId): void
	{
		CCalendarSect::Edit([
			'arFields' => [
				"ID" => $sectionId,
				"CAL_DAV_CON" => '',
				'CAL_DAV_CAL' => '',
				'CAL_DAV_MOD' => '',
			],
		]);
	}

	private static function mergeExcludedDates($currentExDates, $newExDates)
	{
		if (is_string($currentExDates))
		{
			$currentExDates = explode(';', $currentExDates);
		}
		if (is_string($newExDates))
		{
			$newExDates = explode(';', $newExDates);
		}

		return implode(';', array_unique(array_merge($currentExDates, $newExDates)));
	}
}
