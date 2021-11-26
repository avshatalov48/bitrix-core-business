<?php

/** var CMain $APPLICATION */

use Bitrix\Calendar\Sync\Google;
use Bitrix\Calendar\Util;
use Bitrix\Main;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\PushTable;
use Bitrix\Calendar\Sync\GoogleApiSync;
use Bitrix\Main\Loader;
use Bitrix\Calendar\UserSettings;
use Bitrix\Main\Type;
use Bitrix\Calendar\Integration\Bitrix24Manager;
use Bitrix\Calendar\Ui\CountersManager;

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
		$errors = [],
		$timezones = [];

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
		self::$bIntranet = CCalendar::IsIntranetEnabled();
		self::$bSocNet = self::IsSocNet();
		self::$userId = (isset($params['userId']) && $params['userId'] > 0) ? intval($params['userId']) : CCalendar::GetCurUserId(true);
		self::$bOwner = self::$type == 'user' || self::$type == 'group';
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
			self::$SectionsControlsDOMId = $params['SectionControlsDOMId'];

		if (self::$bOwner && isset($params['ownerId']) && $params['ownerId'] > 0)
			self::$ownerId = intval($params['ownerId']);

		self::$showTasks = (self::$type == 'user' || self::$type == 'group')
			&& $params['showTasks'] !== false
			&& $params['viewTaskPath']
			&& Loader::includeModule('tasks')
			&& self::$userSettings['showTasks'] != 'N';

		if (self::$showTasks)
		{
			self::$viewTaskPath = $params['viewTaskPath'];
			self::$editTaskPath = $params['editTaskPath'];
		}

		self::GetPermissions(array(
			'type' => self::$type,
			'bOwner' => self::$bOwner,
			'userId' => self::$userId,
			'ownerId' => self::$ownerId
		));

		// Cache params
		if (isset($params['cachePath']))
			self::$cachePath = $params['cachePath'];
		if (isset($params['cacheTime']))
			self::$cacheTime = $params['cacheTime'];
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
				"/IFRAME_TYPE=.*?\&/i"
			),
			"", $params['pageUrl'].'&'
		);
		$page = preg_replace(array("/^(.*?)\&$/i","/^(.*?)\?$/i"), "\$1", $page);
		self::$actionUrl = $page;

		if (self::$bOwner && !empty(self::$ownerId))
			self::$path = self::GetPath(self::$type, self::$ownerId, true);
		else
			self::$path = CCalendar::GetServerPath().$page;

		self::$outerUrl = $APPLICATION->GetCurPageParam('', array("action", "bx_event_calendar_request", "clear_cache", "bitrix_include_areas", "bitrix_show_mode", "back_url_admin", "SEF_APPLICATION_CUR_PAGE_URL", "EVENT_ID", "EVENT_DATE", "CHOOSE_MR"), false);

		// *** Meeting room params ***
		$RMiblockId = self::$settings['rm_iblock_id'];
		self::$allowReserveMeeting = $params["allowResMeeting"] && $RMiblockId > 0;

		if(self::$allowReserveMeeting && !$USER->IsAdmin() && (CIBlock::GetPermission($RMiblockId) < "R"))
			self::$allowReserveMeeting = false;
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

		if (!CCalendarType::CanDo('calendar_type_view', self::$type))
		{
			$APPLICATION->ThrowException(Loc::getMessage("EC_ACCESS_DENIED"));
			return false;
		}

		$startupEvent = false;
		//Show new event dialog
		if (isset($_GET['EVENT_ID']))
		{
			if(mb_substr($_GET['EVENT_ID'], 0, 4) == 'EDIT')
			{
				$startupEvent = self::GetStartUpEvent(intval(mb_substr($_GET['EVENT_ID'], 4)));
				if ($startupEvent)
					$startupEvent['EDIT'] = true;
				if ($startupEvent['DT_FROM'])
				{
					$ts = self::Timestamp($startupEvent['DT_FROM']);
					$init_month = date('m', $ts);
					$init_year = date('Y', $ts);
				}
			}
			// Show popup event at start
			elseif ($startupEvent = self::GetStartUpEvent($_GET['EVENT_ID']))
			{
				$eventFromTs = self::Timestamp($startupEvent['DATE_FROM']);
				$currentDateTs = self::Timestamp($_GET['EVENT_DATE']);

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

		if (!$init_month && !$init_year && $params["initDate"] <> '' && mb_strpos($params["initDate"], '.') !== false)
		{
			$ts = self::Timestamp($params["initDate"]);
			$init_month = date('m', $ts);
			$init_year = date('Y', $ts);
		}

		if (!isset($init_month))
			$init_month = date("m");
		if (!isset($init_year))
			$init_year = date("Y");

		$id = $this->GetId();

		$weekHolidays = [];
		if (isset(self::$settings['week_holidays']))
		{
			$days = array('MO' => 0, 'TU' => 1, 'WE' => 2,'TH' => 3,'FR' => 4,'SA' => 5,'SU' => 6);
			foreach(self::$settings['week_holidays'] as $day)
				$weekHolidays[] = $days[$day];
		}
		else
			$weekHolidays = array(5, 6);

		$yearHolidays = [];
		if (isset(self::$settings['year_holidays']))
		{
			foreach(explode(',', self::$settings['year_holidays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) == 2 && $ardate[0] && $ardate[1])
					$yearHolidays[] = intval($ardate[0]).'.'.(intval($ardate[1]) - 1);
			}
		}

		$yearWorkdays = [];
		if (isset(self::$settings['year_workdays']))
		{
			foreach(explode(',', self::$settings['year_workdays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) == 2 && $ardate[0] && $ardate[1])
					$yearWorkdays[] = intval($ardate[0]).'.'.(intval($ardate[1]) - 1);
			}
		}

		$isPersonalCalendarContext = self::IsPersonal(self::$type, self::$ownerId, self::$userId);
		$bExchange = CCalendar::IsExchangeEnabled() && self::$type == 'user';
		$bExchangeConnected = $bExchange && CDavExchangeCalendar::IsExchangeEnabledForUser(self::$ownerId);
		$bCalDAV = CCalendar::IsCalDAVEnabled() && self::$type == "user";
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && self::$type == "user";
		$bWebservice = CCalendar::IsWebserviceEnabled();
		$bExtranet = CCalendar::IsExtranetEnabled();

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
			'user' => array(
				'id' => self::$userId,
				'name' => self::GetUserName(self::$userId),
				'url' => self::GetUserUrl(self::$userId),
				'avatar' => self::GetUserAvatarSrc(self::$userId),
				'smallAvatar' => self::GetUserAvatarSrc(self::$userId, array('AVATAR_SIZE' => 18))
			),
			'perm' => $arType['PERM'], // Permissions from type
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
			'workTime' => array(self::$settings['work_time_start'], self::$settings['work_time_end']), // Decrecated !!
			'userWorkTime' => array(self::$settings['work_time_start'], self::$settings['work_time_end']),
			'meetingRooms' => self::GetMeetingRoomList(array(
				'RMiblockId' => self::$settings['rm_iblock_id'],
				'pathToMR' => self::$pathesForSite['path_to_rm']
			)),
			'allowResMeeting' => self::$allowReserveMeeting,
			'bAMPM' => self::$bAMPM,
			'WDControllerCID' => 'UFWD'.$id,
			'userTimezoneOffsetUTC' => $userTimezoneOffsetUTC,
			'userTimezoneName' => $userTimezoneName,
			'userTimezoneDefault' => $userTimezoneDefault,
			'sectionCustomization' => UserSettings::getSectionCustomization(self::$userId),
			'locationFeatureEnabled' => Bitrix24Manager::isFeatureEnabled("calendar_location"),
			'plannerFeatureEnabled' => Bitrix24Manager::isPlannerFeatureEnabled(),
			'eventWithEmailGuestLimit'=> Bitrix24Manager::getEventWithEmailGuestLimit(),
			'countEventWithEmailGuestAmount'=> Bitrix24Manager::getCountEventWithEmailGuestAmount()
		);

		if(self::$type == 'user' && self::$userId != self::$ownerId)
		{
			$JSConfig['ownerUser'] = array(
				'id' => self::$ownerId,
				'name' => self::GetUserName(self::$ownerId),
				'url' => self::GetUserUrl(self::$ownerId),
				'avatar' => self::GetUserAvatarSrc(self::$ownerId),
				'smallAvatar' => self::GetUserAvatarSrc(self::$ownerId, array('AVATAR_SIZE' => 18))
			);
		}

		$placementParams = false;
		if (Loader::includeModule('rest'))
		{
			$placementParams = [
				'gridPlacementCode' => \CCalendarRestService::PLACEMENT_GRID_VIEW,
				'gridPlacementList' => \Bitrix\Rest\PlacementTable::getHandlersList(\CCalendarRestService::PLACEMENT_GRID_VIEW),
				'serviceUrl' => '/bitrix/components/bitrix/app.layout/lazyload.ajax.php?&site='.SITE_ID.'&'.bitrix_sessid_get()
			];
		}
		$JSConfig['placementParams'] = $placementParams;

		if(self::$type == 'user' && self::$userId == self::$ownerId)
		{
			$JSConfig['counters'] = CountersManager::getValues((int)self::$userId);
			$JSConfig['filterId'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilterId(self::$type, self::$ownerId, self::$userId);
		}

		// Access permissons for type
		if (CCalendarType::CanDo('calendar_type_edit_access', self::$type))
		{
			$JSConfig['TYPE_ACCESS'] = $arType['ACCESS'];
		}

		if ($bCalDAV || $bGoogleApi)
		{
			self::InitExternalCalendarsSyncParams($JSConfig);
		}

		if ($isPersonalCalendarContext)
		{
			$syncInfoParams = [
				'userId' => self::$userId,
				'type' => self::$type,
			];
			$JSConfig['syncInfo'] = CCalendarSync::GetSyncInfo($syncInfoParams);
			$JSConfig['syncLinks'] = CCalendarSync::GetSyncLinks($syncInfoParams);
			$JSConfig['isSetSyncCaldavSettings'] = CCalendarSync::isSetSyncCaldavSettings($syncInfoParams['type']);

			$JSConfig['displayMobileBanner'] = CCalendarSync::checkMobileBannerDisplay()
				&& !$JSConfig['syncInfo']['iphone']['connected']
				&& !$JSConfig['syncInfo']['android']['connected'];
		}
		else
		{
			$JSConfig['syncInfo'] = false;
		}

		self::$userMeetingSection = CCalendar::GetCurUserMeetingSection();

		$followedSectionList = UserSettings::getFollowedSectionIdList(self::$userId);
		$defaultHiddenSections = [];
		$sections = [];
		$sectionList = self::getSectionList([
			'CAL_TYPE' => self::$type,
			'OWNER_ID' => self::$ownerId,
			'ADDITIONAL_IDS' => $followedSectionList,
			'checkPermissions' => true,
			'getPermissions' => true,
			'getImages' => true
		]);

		$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser(self::$userId));
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
				'defaultHiddenSections' => $defaultHiddenSections
			]
		);

		$readOnly = !self::$perm['edit'] && !self::$perm['section_edit'];

		if (self::$type === 'user' && self::$ownerId != self::$userId)
			$readOnly = true;

		if (self::$bAnonym)
			$readOnly = true;

		$bCreateDefault = !self::$bAnonym;

		if (self::$type === 'user')
		{
			$bCreateDefault = self::$ownerId === self::$userId;
		}

		$additonalMeetingsId = [];
		$groupOrUser = self::$type === 'user' || self::$type === 'group';
		if ($groupOrUser)
		{
			$noEditAccessedCalendars = true;
		}

		$trackingUsers = [];
		$trackingGroups = [];

		foreach ($sections as $i => $section)
		{
			$sections[$i]['~IS_MEETING_FOR_OWNER'] = $section['CAL_TYPE'] === 'user' && $section['OWNER_ID'] !== self::$userId && CCalendar::GetMeetingSection($section['OWNER_ID']) === $section['ID'];

			if (!in_array($section['ID'], $hiddenSections, true) && $section['ACTIVE'] !== 'N')
			{
				// It's superposed calendar of the other user and it's need to show user's meetings
				if ($sections[$i]['~IS_MEETING_FOR_OWNER'])
				{
					$additonalMeetingsId[] = [
						'ID' => $section['OWNER_ID'],
						'SECTION_ID' => $section['ID']
					];
				}
			}

			// We check access only for main sections because we can't edit superposed section
			if ($groupOrUser && $sections[$i]['CAL_TYPE'] == self::$type &&
				$sections[$i]['OWNER_ID'] == self::$ownerId)
			{
				if ($noEditAccessedCalendars && $section['PERM']['edit'])
				{
					$noEditAccessedCalendars = false;
				}

				if ($readOnly && ($section['PERM']['edit'] || $section['PERM']['edit_section']) && !self::$isArchivedGroup)
				{
					$readOnly = false;
				}
			}

			if (self::$bSuperpose && in_array($section['ID'], $followedSectionList))
			{
				$sections[$i]['SUPERPOSED'] = true;
			}

			if ($bCreateDefault && $section['CAL_TYPE'] == self::$type && $section['OWNER_ID'] == self::$ownerId)
			{
				$bCreateDefault = false;
			}

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

		if ($groupOrUser && $noEditAccessedCalendars && !$bCreateDefault)
		{
			$readOnly = true;
		}

		if ($type === 'location')
		{
			$readOnly = false;
		}

		self::$readOnly = $readOnly;

		$JSConfig = array_merge(
			$JSConfig,
			[
				'trackingUsersList' => UserSettings::getTrackingUsers(false, ['userList' => $trackingUsers]),
				'trackingGroupList' => UserSettings::getTrackingGroups(false, ['groupList' => $trackingGroups])
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
			$fullSectionsList = $groupOrUser ? self::GetSectionList(array('checkPermissions' => false, 'getPermissions' => false)) : [];
			// Section exists but it closed to this user (Ref. mantis:#64037)
			if (count($fullSectionsList) > 0)
			{
				$readOnly = true;
			}
			else
			{
				$defCalendar = CCalendarSect::CreateDefault(array(
					'type' => CCalendar::GetType(),
					'ownerId' => CCalendar::GetOwnerId()
				));
				$sections[] = $defCalendar;
				self::$userMeetingSection = $defCalendar['ID'];
			}
		}

		if (CCalendarType::CanDo('calendar_type_edit', self::$type))
			$JSConfig['new_section_access'] = CCalendarSect::GetDefaultAccess(self::$type, self::$ownerId);

		$colors = ['#86B100','#0092CC','#00AFC7','#DA9100','#00B38C','#DE2B24','#BD7AC9','#838FA0','#AB7917','#E97090'];

		$JSConfig['hiddenSections'] = $hiddenSections;
		$JSConfig['readOnly'] = $readOnly;

		// access
		$JSConfig['accessNames'] = self::GetAccessNames();
		$JSConfig['sectionAccessTasks'] = self::GetAccessTasks('calendar_section');
		$JSConfig['typeAccessTasks'] = self::GetAccessTasks('calendar_type');

		$JSConfig['bSuperpose'] = self::$bSuperpose;
		$JSConfig['additonalMeetingsId'] = $additonalMeetingsId;

		$selectedUserCodes = array('U'.self::$userId);
		if (self::$type === 'user')
		{
			$selectedUserCodes[] = 'U'.self::$ownerId;
		}

		$additionalParams = array(
			'socnetDestination' => CCalendar::GetSocNetDestination(false, $selectedUserCodes),
			'locationList' => CCalendarLocation::GetList(),
			'timezoneList' => CCalendar::GetTimezoneList(),
			'defaultColorsList' => $colors,
			'formSettings' => array(
				'slider_main' => UserSettings::getFormSettings('slider_main')
			)
		);

		// Append Javascript files and CSS files, and some base configs
		CCalendarSceleton::InitJS(
			$JSConfig,
			array(
				'sections' => $sections
			),
			$additionalParams
		);
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
		if (CCalendar::IsExchangeEnabled(self::GetCurUserId()) && self::$type == 'user')
		{
			$oSect = CCalendarSect::GetById($id);
			// For exchange we change only calendar name
			if ($oSect && $oSect['IS_EXCHANGE'] && $oSect['DAV_EXCH_CAL'])
			{
				$exchRes = CDavExchangeCalendar::DoDeleteCalendar($oSect['OWNER_ID'], $oSect['DAV_EXCH_CAL']);
				if ($exchRes !== true)
					return CCalendar::CollectExchangeErrors($exchRes);
			}
		}
		$pushChannels = PushTable::getById(array('ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $id));
		if ($row = $pushChannels->fetch())
		{
			\Bitrix\Calendar\Sync\GoogleApiPush::stopChannel($row);
		}
		return CCalendarSect::Delete($id);
	}

	public static function CollectExchangeErrors($arErrors = [])
	{
		if (count($arErrors) == 0 || !is_array($arErrors))
			return '[EC_NO_EXCH] '.Loc::getMessage('EC_NO_EXCHANGE_SERVER');

		$str = "";
		$errorCount = count($arErrors);
		for($i = 0; $i < $errorCount; $i++)
			$str .= "[".$arErrors[$i][0]."] ".$arErrors[$i][1]."\n";

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
		if (!$id)
		{
			return false;
		}

		$checkPermissions = isset($params['checkPermissions']) ? $params['checkPermissions'] : true;
		if (!isset(self::$userId))
		{
			self::$userId = CCalendar::GetCurUserId();
		}

		CCalendar::SetOffset(false, 0);
		$res = CCalendarEvent::GetList(
			array(
				'arFilter' => ["ID" => $id],
				'parseRecursion' => false,
				'setDefaultLimit' => false,
				'fetchAttendees' => true,
				'checkPermissions' => $checkPermissions
			)
		);

		if ($event = $res[0])
		{
			if (!isset(self::$type))
			{
				self::$type = $event['CAL_TYPE'];
			}

			if (!isset(self::$ownerId))
			{
				self::$ownerId = $event['OWNER_ID'];
			}

			if ($checkPermissions && !self::IsPersonal($event['CAL_TYPE'], $event['OWNER_ID'], self::$userId) && !CCalendarSect::CanDo('calendar_edit', $event['SECT_ID'], self::$userId))
			{
				CCalendarSect::UpdateModificationLabel($event['SECT_ID']);
				return Loc::getMessage('EC_ACCESS_DENIED');
			}

			if ($doExternalSync !== false && $event['SECT_ID'])
			{
				$bGoogleApi = CCalendar::isGoogleApiEnabled() && $event['CAL_TYPE'] == 'user';
				$bCalDav = CCalendar::IsCalDAVEnabled() && $event['CAL_TYPE'] == 'user';
				$bExchangeEnabled = CCalendar::IsExchangeEnabled() && $event['CAL_TYPE'] == 'user';

				if ($bExchangeEnabled || $bCalDav || $bGoogleApi)
				{
					$res = CCalendarSync::DoDeleteToDav(array(
						'bCalDav' => $bCalDav,
						'bExchangeEnabled' => $bExchangeEnabled,
						'sectionId' => $event['SECT_ID']
					), $event);

					if ($res !== true)
						return $res;
				}
			}

			$sendNotification = isset($params['sendNotification']) ? $params['sendNotification'] : ($params['recursionMode'] !== 'all');

			$res = CCalendarEvent::Delete(array(
				'id' => $id,
				'Event' => $event,
				'bMarkDeleted' => true,
				'userId' => self::$userId,
				'sendNotification' => $sendNotification,
				'requestUid' => $params['requestUid']
			));

			if ($params['recursionMode'] != 'this' && $event['RECURRENCE_ID'])
			{
				self::DeleteEvent($event['RECURRENCE_ID'], $doExternalSync, array('sendNotification' => $sendNotification));
			}

			if (CCalendarEvent::CheckRecurcion($event))
			{
				$events = CCalendarEvent::GetEventsByRecId($id);

				foreach($events as $ev)
				{
					self::DeleteEvent($ev['ID'], $doExternalSync, array('sendNotification' => $sendNotification));
				}
			}

			if($params['recursionMode'] === 'all')
			{
				foreach($event['ATTENDEE_LIST'] as $attendee)
				{
					if ($attendee['status'] !== 'N')
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendee["id"]);
						CCalendarNotify::Send(array(
							"mode" => 'cancel_all',
							"name" => $event['NAME'],
							"from" => $event['DATE_FROM'],
							"guestId" => $attendee["id"],
							"eventId" => $event['PARENT_ID'],
							"userId" => $event['MEETING_HOST'],
							"fields" => $event
						));
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
		if (count($arErrors) == 0 || !is_array($arErrors))
			return '[EC_NO_EXCH] '.Loc::getMessage('EC_NO_CAL_DAV');

		$str = "";
		$errorCount = count($arErrors);
		for($i = 0; $i < $errorCount; $i++)
		{
			$str .= "[".$arErrors[$i][0]."] ".$arErrors[$i][1]."\n";
		}

		return $str;
	}

	public static function GetPathForCalendarEx($userId = 0)
	{
		$bExtranet = Loader::includeModule('extranet');
		// It's extranet user
		if ($bExtranet && self::IsExtranetUser($userId))
		{
			$siteId = CExtranet::GetExtranetSiteID();
		}
		else
		{
			if ($bExtranet && !self::IsExtranetUser($userId))
				$siteId = CSite::GetDefSite();
			else
				$siteId = self::GetSiteId();

			if (self::$siteId == $siteId
				&& isset(self::$pathesForSite)
				&& is_array(self::$pathesForSite))
			{
				self::$pathes[$siteId] = self::$pathesForSite;
			}
		}

		if (!isset(self::$pathes[$siteId]) || !is_array(self::$pathes[$siteId]))
			self::$pathes[$siteId] = self::GetPathes($siteId);

		$calendarUrl = self::$pathes[$siteId]['path_to_user_calendar'];
		$calendarUrl = str_replace(array('#user_id#', '#USER_ID#'), $userId, $calendarUrl);
		$calendarUrl = CCalendar::GetServerPath().$calendarUrl;

		return $calendarUrl;
	}

	public static function IsExtranetUser($userId = 0)
	{
		return !count(self::GetUserDepartment($userId));
	}

	public static function GetUserDepartment($userId = 0)
	{
		if (!isset(self::$arUserDepartment[$userId]))
		{
			$rsUser = CUser::GetByID($userId);
			if($arUser = $rsUser->Fetch())
				self::SetUserDepartment($userId, $arUser["UF_DEPARTMENT"]);
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
		$userId = CCalendar::GetCurUserId();
		if ($module == "calendar" && $userId)
		{
			$arTag = explode("|", $tag);
			$eventId = intval($arTag[2]);
			if ($arTag[0] == "CALENDAR" && $arTag[1] == "INVITE" && $eventId > 0 && $userId)
			{
				CCalendarEvent::SetMeetingStatus([
					'userId' => $userId,
					'eventId' => $eventId,
					'status' => $value == 'Y' ? 'Y' : 'N',
					'personalNotification' => true
				]);

				return $value == 'Y' ? Loc::getMessage('EC_PROP_CONFIRMED_TEXT_Y') : Loc::getMessage('EC_PROP_CONFIRMED_TEXT_N');
			}
		}
	}

	public static function ClearSettings()
	{
		self::SetSettings([], true);
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
			'rm_for_sites'
		];

		$optionNames = array_merge($optionNames, $arPathes);
		if ($settings['rm_iblock_ids'] && !$settings['rm_for_sites'])
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
			elseif (isset($settings[$opt]))
			{
				if ($opt === 'rm_iblock_id' && !$settings['rm_for_sites'])
				{
					continue;
				}
				elseif ($opt === 'sync_by_push' && self::isGoogleApiEnabled())
				{
					if ($settings[$opt])
					{
						// start push agents
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();", "calendar");
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar", "N", 60);
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();", "calendar", "N", 180);
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar", "N", \Bitrix\Calendar\Sync\GoogleApiPush::RENEW_INTERVAL_CHANNEL);
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::checkPushChannel();", "calendar", "N", \Bitrix\Calendar\Sync\GoogleApiPush::CHECK_INTERVAL_CHANNEL);
					}
					else
					{
						global $DB;
						// start clear push channels agent
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();", "calendar", "N", 60);
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();", "calendar");
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");
						$DB->Query("DELETE FROM b_agent WHERE `NAME` LIKE '%GoogleApiPush::checkPushChannel%'");
					}
				}
				elseif ($opt === 'pathes' && is_array($settings[$opt]))
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
								$ar[$path] = $pathes[$path];
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
				elseif ($opt === 'denied_superpose_types' && is_array($settings[$opt]))
				{
					$settings[$opt] = serialize($settings[$opt]);
				}
				elseif ($opt === 'week_holidays' && is_array($settings[$opt]))
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
			$Params['arFoundUsers'] = CSocNetUser::SearchUser($name);

		$arUsers = [];
		if (!is_array($Params['arFoundUsers']) || count($Params['arFoundUsers']) <= 0)
		{
			if ($Params['addExternal'] !== false)
			{
				if (check_email($name, true))
				{
					$arUsers[] = array(
						'type' => 'ext',
						'email' => htmlspecialcharsex($name)
					);
				}
				else
				{
					$arUsers[] = array(
						'type' => 'ext',
						'name' => htmlspecialcharsex($name)
					);
				}
			}
		}
		else
		{
			foreach ($Params['arFoundUsers'] as $userId => $userName)
			{
				$userId = intval($userId);

				$r = CUser::GetList('id', 'asc', array("ID_EQUAL_EXACT" => $userId, "ACTIVE" => "Y"));

				if (!$User = $r->Fetch())
					continue;
				$name = trim($User['NAME'].' '.$User['LAST_NAME']);
				if ($name == '')
					$name = trim($User['LOGIN']);

				$arUsers[] = array(
					'type' => 'int',
					'id' => $userId,
					'name' => $name,
					'status' => 'Q',
					'busy' => 'free'
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
				"USER_ACTIVE" => "Y"
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
			return self::$ownerNames[$key];

		$ownerName = '';
		if($type == 'user')
		{
			$ownerName = CCalendar::GetUserName($ownerId);
		}
		elseif($type == 'group')
		{
			// Get group name
			if (!Loader::includeModule("socialnetwork"))
				return $ownerName;

			if ($arGroup = CSocNetGroup::GetByID($ownerId))
				$ownerName = $arGroup["~NAME"];
		}
		else
		{
			// Get type name
			$arTypes = CCalendarType::GetList(array("arFilter" => array("XML_ID" => $type)));
			$ownerName = $arTypes[0]['NAME'];
		}
		self::$ownerNames[$key] = $ownerName;
		$ownerName = trim($ownerName);

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

	public static function GetAccessibilityForUsers($params)
	{
		if (!isset($params['checkPermissions']))
			$params['checkPermissions'] = true;

		$res = CCalendarEvent::GetAccessibilityForUsers(array(
			'users' => $params['users'],
			'from' => $params['from'],
			'to' => $params['to'],
			'curEventId' => $params['curEventId'],
			'checkPermissions' => $params['checkPermissions']
		));

		// Fetch absence from intranet
		if ($params['getFromHR'] && CCalendar::IsIntranetEnabled())
		{
			$resHR = CIntranetUtils::GetAbsenceData(
				array(
					'DATE_START' => $params['from'],
					'DATE_FINISH' => $params['to'],
					'USERS' => $params['users'],
					'PER_USER' => true,
					'SELECT' => array('ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO')
				),
				BX_INTRANET_ABSENCE_HR
			);

			foreach($resHR as $userId => $forUser)
			{
				if (!isset($res[$userId]) || !is_array($res[$userId]))
					$res[$userId] = [];

				foreach($forUser as $event)
				{
					$res[$userId][] = array(
						'FROM_HR' => true,
						'ID' => $event['ID'],
						'DT_FROM' => $event['DATE_ACTIVE_FROM'],
						'DT_TO' => $event['DATE_ACTIVE_TO'],
						'ACCESSIBILITY' => 'absent',
						'IMPORTANCE' => 'normal',
						"FROM" => CCalendar::Timestamp($event['DATE_ACTIVE_FROM']),
						"TO" => CCalendar::Timestamp($event['DATE_ACTIVE_TO'])
					);
				}
			}
		}

		return $res;
	}

	public static function GetNearestEventsList($params = [])
	{
		$type = $params['bCurUserList'] ? 'user' : $params['type'];

		// Get current user id
		if (!isset($params['userId']) || $params['userId'] <= 0)
			$curUserId = CCalendar::GetCurUserId();
		else
			$curUserId = intval($params['userId']);

		if (!CCalendarType::CanDo('calendar_type_view', $type, $curUserId))
			return 'access_denied';

		if ($params['bCurUserList'] && ($curUserId <= 0 || (class_exists('CSocNetFeatures') && !CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $curUserId, "calendar"))))
			return 'inactive_feature';

		$maxAmount = isset($params['maxAmount']) && (int)$params['maxAmount'] > 0
			? (int)$params['maxAmount']
			: 75;

		$arFilter = array(
			'CAL_TYPE' => $type,
			'FROM_LIMIT' => $params['fromLimit'],
			'TO_LIMIT' => $params['toLimit'],
			'DELETED' => 'N',
			'ACTIVE_SECTION' => 'Y'
		);

		if ($params['bCurUserList'])
			$arFilter['OWNER_ID'] = $curUserId;

		if (isset($params['sectionId']) && $params['sectionId'])
			$arFilter["SECTION"] = $params['sectionId'];

		if ($type == 'user')
			unset($arFilter['CAL_TYPE']);

		$eventsList = CCalendarEvent::GetList(
			[
				'arFilter' => $arFilter,
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'userId' => $curUserId,
				'fetchMeetings' => $type == 'user',
				'preciseLimits' => true,
				'skipDeclined' => true
			]
		);

		$pathToCalendar = \CCalendar::GetPathForCalendarEx($curUserId);

		if (CCalendar::Date(time(), false) == $params['fromLimit'])
			$limitTime = time();
		else
			$limitTime = CCalendar::Timestamp($params['fromLimit']);

		$limitTime -= (int)date("Z", $limitTime);
		$entryList = [];

		foreach($eventsList as $event)
		{
			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] == 'N')
			{
				continue;
			}

			if ($type === 'user' && !$event['IS_MEETING'] && $event['CAL_TYPE'] != 'user')
			{
				continue;
			}

			$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
			$toTs = $fromTs + $event['DT_LENGTH'];

			$toTsUtc = $toTs - $event['TZ_OFFSET_FROM'];

			if ($toTsUtc >= $limitTime)
			{
				if ($event['DT_SKIP_TIME'] !== "Y")
				{
					$fromTs -= $event['~USER_OFFSET_FROM'];
					$toTs -= $event['~USER_OFFSET_TO'];
				}
				$event['DATE_FROM'] = CCalendar::Date($fromTs, $event['DT_SKIP_TIME'] != 'Y');
				$event['DATE_TO'] = CCalendar::Date($toTs, $event['DT_SKIP_TIME'] != 'Y');
				unset($event['TZ_FROM'], $event['TZ_TO'], $event['TZ_OFFSET_FROM'], $event['TZ_OFFSET_TO']);
				$event['DT_FROM_TS'] = $fromTs;
				$event['DT_TO_TS'] = $toTs;

				$event['~URL'] = \CHTTP::urlAddParams($pathToCalendar, [
					'EVENT_ID' => $event['ID'],
					'EVENT_DATE' => CCalendar::Date($fromTs, false)
				]);

				$event['~WEEK_DAY'] = FormatDate("D", $fromTs);

				$event['~FROM_TO_HTML'] = CCalendar::GetFromToHtml(
					$fromTs,
					$toTs,
					$event['DT_SKIP_TIME'] === 'Y',
					$event['DT_LENGTH']
				);

				$entryList[] = $event;
			}
		}

		// Sort by DATE_FROM_TS
		usort($entryList, array('CCalendar', '_NearestSort'));
		array_splice($entryList, $maxAmount);

		return $entryList;
	}

	public static function _NearestSort($a, $b)
	{
		if ($a['DT_FROM_TS'] == $b['DT_FROM_TS'])
			return 0;
		if ($a['DT_FROM_TS'] < $b['DT_FROM_TS'])
			return -1;
		return 1;
	}

	public static function GetAccessibilityForMeetingRoom($params)
	{
		$allowReserveMeeting = isset($params['allowReserveMeeting']) ? $params['allowReserveMeeting'] : self::$allowReserveMeeting;
		$RMiblockId = isset($params['RMiblockId']) ? $params['RMiblockId'] : self::$settings['rm_iblock_id'];
		$curEventId = $params['curEventId'] > 0 ? $params['curEventId'] : false;
		$arResult = [];
		$offset = CCalendar::GetOffset();

		if ($allowReserveMeeting)
		{
			$arSelect = array("ID", "NAME", "IBLOCK_SECTION_ID", "IBLOCK_ID", "ACTIVE_FROM", "ACTIVE_TO");
			$arFilter = array(
				"IBLOCK_ID" => $RMiblockId,
				"SECTION_ID" => $params['id'],
				"INCLUDE_SUBSECTIONS" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => 'N',
				">=DATE_ACTIVE_TO" => $params['from'],
				"<=DATE_ACTIVE_FROM" => $params['to']
			);
			if(intval($curEventId) > 0)
			{
				$arFilter["!ID"] = intval($curEventId);
			}

			$rsElement = CIBlockElement::GetList(Array('ACTIVE_FROM' => 'ASC'), $arFilter, false, false, $arSelect);
			while($obElement = $rsElement->GetNextElement())
			{
				$arItem = $obElement->GetFields();
				$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat(self::DFormat(true), MakeTimeStamp($arItem["ACTIVE_FROM"]));
				$arItem["DISPLAY_ACTIVE_TO"] = CIBlockFormatProperties::DateFormat(self::DFormat(true), MakeTimeStamp($arItem["ACTIVE_TO"]));

				$arResult[] = array(
					"ID" => intval($arItem['ID']),
					"NAME" => $arItem['~NAME'],
					"DT_FROM" => CCalendar::CutZeroTime($arItem['DISPLAY_ACTIVE_FROM']),
					"DT_TO" => CCalendar::CutZeroTime($arItem['DISPLAY_ACTIVE_TO']),
					"DT_FROM_TS" => (CCalendar::Timestamp($arItem['DISPLAY_ACTIVE_FROM']) - $offset) * 1000,
					"DT_TO_TS" => (CCalendar::Timestamp($arItem['DISPLAY_ACTIVE_TO']) - $offset) * 1000
				);
			}
		}

		return $arResult;
	}

	public static function GetMeetingRoomById($Params)
	{
		if (intval($Params['RMiblockId']) > 0 && CIBlock::GetPermission($Params['RMiblockId']) >= "R")
		{
			$arFilter = array("IBLOCK_ID" => $Params['RMiblockId'], "ACTIVE" => "Y", "ID" => $Params['id']);
			$arSelectFields = array("NAME");
			$res = CIBlockSection::GetList([], $arFilter, false, array("NAME"));
			if ($arMeeting = $res->GetNext())
				return $arMeeting;
		}

		if(intval($Params['VMiblockId']) > 0 && CIBlock::GetPermission($Params['VMiblockId']) >= "R")
		{
			$arFilter = array("IBLOCK_ID" => $Params['VMiblockId'], "ACTIVE" => "Y");
			$arSelectFields = array("ID", "NAME", "DESCRIPTION", "IBLOCK_ID");
			$res = CIBlockSection::GetList([], $arFilter, false, $arSelectFields);
			if($arMeeting = $res->GetNext())
			{
				return array(
					'ID' => $Params['VMiblockId'],
					'NAME' => $arMeeting["NAME"],
					'DESCRIPTION' => $arMeeting['DESCRIPTION'],
				);
			}
		}
		return false;
	}

	public static function ReleaseLocation($loc)
	{
		$set = CCalendar::GetSettings(array('request' => false));
		if($set['rm_iblock_id'])
		{
			CCalendar::ReleaseMeetingRoom(array(
				'mrevid' => $loc['mrevid'],
				'mrid' => $loc['mrid'],
				'RMiblockId' => $set['rm_iblock_id']
			));
		}

		if ($loc['room_id'] && $loc['room_event_id'] !== false)
		{
			CCalendarLocation::releaseRoom(array(
				'room_id' => $loc['room_id'],
				'room_event_id' => $loc['room_event_id']
			));
		}
	}

	public static function ReleaseMeetingRoom($Params)
	{
		$Params['RMiblockId'] = isset($Params['RMiblockId']) ? $Params['RMiblockId'] : self::$settings['rm_iblock_id'];
		$arFilter = array(
			"ID" => $Params['mrevid'],
			"IBLOCK_ID" => $Params['RMiblockId'],
			"IBLOCK_SECTION_ID" => $Params['mrid'],
			"SECTION_ID" => array($Params['mrid'])
		);

		$res = CIBlockElement::GetList([], $arFilter, false, false, array("ID"));
		if($arElement = $res->Fetch())
		{
			$obElement = new CIBlockElement;
			$obElement->Delete($Params['mrevid']);
		}

		// Hack: reserve meeting calendar based on old calendar's cache
		$cache = new CPHPCache;
		$cache->CleanDir('event_calendar/');
		$cache->CleanDir('event_calendar/events/');
		$cache->CleanDir('event_calendar/events/'.$Params['RMiblockId']);
	}

	public static function GetCalendarList($calendarId, $params = [])
	{
		$TASK_ID = '1_tasks';

		self::SetSilentErrorMode();
		[$sectionId, $entityType, $entityId] = $calendarId;

		if ($sectionId !== $TASK_ID)
		{
			$arFilter = array(
				'CAL_TYPE' => $entityType,
				'OWNER_ID' => $entityId
			);

			if (!is_array($params))
				$params = [];

			if ($sectionId > 0)
				$arFilter['ID'] = $sectionId;

			if (isset($params['active']))
			{
				$arFilter['ACTIVE'] = $params['active'] ? 'Y' : 'N';
			}
			$res = CCalendarSect::GetList(array('arFilter' => $arFilter));

			$arCalendars = [];
			foreach($res as $calendar)
			{
				if ($params['skipExchange'] == true && $calendar['DAV_EXCH_CAL'] <> '')
				{
					continue;
				}

				$arCalendars[] = array(
					'ID' => $calendar['ID'],
					'~NAME' => $calendar['NAME'],
					'NAME' => htmlspecialcharsbx($calendar['NAME']),
					'COLOR' => htmlspecialcharsbx($calendar['COLOR'])
				);
			}
		}

		if (CCalendarSync::isTaskListSyncEnabled() && $entityType == 'user' && ($sectionId === $TASK_ID || !$sectionId))
		{
			$arCalendars[] = array(
				'ID' => $TASK_ID,
				'~NAME' => 'My tasks',
				'NAME' => 'My tasks',
				'COLOR' => self::DEFAULT_TASK_COLOR
			);
		}

		self::SetSilentErrorMode(false);
		return $arCalendars;
	}

	/*
	 * $params['from'], $params['from'] - datetime in UTC
	 * */

	public static function GetDavCalendarEventsList($calendarId, $arFilter = [])
	{
		[$sectionId, $entityType, $entityId] = $calendarId;

		CCalendar::SetOffset(false, 0);
		$arFilter1 = array(
			'OWNER_ID' => $entityId,
			'DELETED' => 'N'
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
			if ($entityType == 'user')
				$fetchMeetings = self::GetMeetingSection($entityId) == $sectionId;
		}
		$arFilter = array_merge($arFilter1, $arFilter);

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'getUserfields' => false,
				'parseRecursion' => false,
				'fetchAttendees' => false,
				'fetchMeetings' => $fetchMeetings,
				'userId' => CCalendar::GetCurUserId()
			)
		);

		$result = [];
		foreach ($arEvents as $event)
		{
			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] == 'N')
				continue;

			// Skip events from where owner is host of the meeting and it's meeting from other section
			if ($entityType == 'user' && $event['IS_MEETING']  && $event['MEETING_HOST'] == $entityId && $event['SECT_ID'] != $sectionId)
				continue;

			$event['XML_ID'] = $event['DAV_XML_ID'];
			if ($event['LOCATION'] !== '')
				$event['LOCATION'] = CCalendar::GetTextLocation($event['LOCATION']);
			$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);
			$result[] = $event;
		}

		return $result;
	}

	public static function GetTextLocation($loc = '')
	{
		$result = $loc;
		if ($loc !== '')
		{
			$location = self::ParseLocation($loc);

			if($location['mrid'] === false && $location['room_id'] === false)
			{
				$result = $location['str'];
			}
			elseif($location['room_id'] > 0)
			{
				$room = CCalendarLocation::getById($location['room_id']);
				$result = $room ? $room['NAME'] : '';
			}
			else
			{
				$MRList = CCalendar::GetMeetingRoomList();
				foreach($MRList as $MR)
				{
					if($MR['ID'] == $location['mrid'])
					{
						$result = $MR['NAME'];
						break;
					}
				}
			}
		}

		return $result;
	}

	public static function ParseLocation($location = '')
	{
		$res = array(
			'mrid' => false,
			'mrevid' => false,
			'room_id' => false,
			'room_event_id' => false,
			'str' => $location
		);

		if (mb_strlen($location) > 5 && mb_substr($location, 0, 5) == 'ECMR_')
		{
			$location = explode('_', $location);
			if (count($location) >= 2)
			{
				if (intval($location[1]) > 0)
					$res['mrid'] = intval($location[1]);
				if (intval($location[2]) > 0)
					$res['mrevid'] = intval($location[2]);
			}
		}
		elseif (mb_strlen($location) > 9 && mb_substr($location, 0, 9) == 'calendar_')
		{
			$location = explode('_', $location);
			if (count($location) >= 2)
			{
				if (intval($location[1]) > 0)
					$res['room_id'] = intval($location[1]);
				if (intval($location[2]) > 0)
					$res['room_event_id'] = intval($location[2]);
			}
		}
		return $res;
	}

	/* * * * RESERVE MEETING ROOMS  * * * */

	public static function GetUserPermissionsForCalendar($calendarId, $userId)
	{
		[$sectionId, $entityType, $entityId] = $calendarId;
		$entityType = mb_strtolower($entityType);

		if ($sectionId == 0)
		{
			$res = array(
				'bAccess' => CCalendarType::CanDo('calendar_type_view', $entityType, $userId),
				'bReadOnly' => !CCalendarType::CanDo('calendar_type_edit', $entityType, $userId)
			);
		}

		$bOwner = $entityType == 'user' && $entityId == $userId;
		$res = array(
			'bAccess' => $bOwner || CCalendarSect::CanDo('calendar_view_time', $sectionId, $userId),
			'bReadOnly' => !$bOwner && !CCalendarSect::CanDo('calendar_edit', $sectionId, $userId)
		);

		if ($res['bReadOnly'] && !$bOwner)
		{
			if (CCalendarSect::CanDo('calendar_view_time', $sectionId, $userId))
				$res['privateStatus'] = 'time';
			if (CCalendarSect::CanDo('calendar_view_title', $sectionId, $userId))
				$res['privateStatus'] = 'title';
		}

		return $res;
	}

	public static function GetDayLen()
	{
		return self::DAY_LENGTH;
	}

	public static function UnParseTextLocation($loc = '')
	{
		$result = array('NEW' => $loc);
		if ($loc != "")
		{
			$location = self::ParseLocation($loc);
			if ($location['mrid'] === false && $location['room_id'] === false)
			{
				$MRList = CCalendar::GetMeetingRoomList();
				$loc_ = trim(mb_strtolower($loc));
				foreach($MRList as $MR)
				{
					if (trim(mb_strtolower($MR['NAME'])) == $loc_)
					{
						$result['NEW'] = 'ECMR_'.$MR['ID'];
						break;
					}
				}

				if (Bitrix24Manager::isFeatureEnabled("calendar_location"))
				{
					$locationList = CCalendarLocation::GetList();
					foreach($locationList as $room)
					{
						if (trim(mb_strtolower($room['NAME'])) == $loc_)
						{
							$result['NEW'] = 'calendar_'.$room['ID'];
						}
					}
				}

			}
		}
		return $result;
	}

	public static function ClearExchangeHtml($html = "")
	{
		// Echange in chrome puts chr(13) instead of \n
		$html = str_replace(chr(13), "\n", trim($html, chr(13)));
		$html = preg_replace("/(\s|\S)*<a\s*name=\"bm_begin\"><\/a>/is".BX_UTF_PCRE_MODIFIER,"", $html);
		$html = preg_replace("/<br>(\n|\r)+/is".BX_UTF_PCRE_MODIFIER,"<br>", $html);
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
		$html = preg_replace("/\<br\s*\/*\>/is".BX_UTF_PCRE_MODIFIER,"\n", $html);
		//replace /p && /div to \n
		$html = preg_replace("/\<\/(p|div)\>/is".BX_UTF_PCRE_MODIFIER,"\n", $html);
		// Kill &nbsp;
		$html = preg_replace("/&nbsp;/is".BX_UTF_PCRE_MODIFIER,"", $html);
		// Kill tags
		$html = preg_replace("/\<([^>]*?)>/is".BX_UTF_PCRE_MODIFIER,"", $html);
		$html = htmlspecialcharsbx($html);

		RemoveEventHandler("main", "TextParserBeforeTags", $id);

		return $html;
	}

	public static function WeekDayByInd($i, $binv = true)
	{
		if ($binv)
			$arDays = ['SU','MO','TU','WE','TH','FR','SA'];
		else
			$arDays = ['MO','TU','WE','TH','FR','SA','SU'];
		return isset($arDays[$i]) ? $arDays[$i] : false;
	}

	public static function SaveEvent($params = [])
	{
		$res = self::SaveEventEx($params);

		if (is_array($res) && isset($res['originalDavXmlId']))
		{
			return $res;
		}
		elseif (is_array($res) && isset($res['id']))
		{
			return $res['id'];
		}
		else
		{
			return $res;
		}
	}

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
			$arFields['SKIP_TIME'] = $arFields['DT_SKIP_TIME'] == 'Y';
		}

		//flags for synchronize the instance of a recurring event
		//modeSync - edit mode instance for avoid unnecessary request (patch)
		//editParentEvents - editing the parent event of the following
		$modeSync = true;
		$editInstance = $params['editInstance'] ?: false;
		$editNextEvents = $params['editNextEvents']?: false;
		$editParentEvents = $params['editParentEvents'] ?: false;
		$originalDavXmlId = $params['originalDavXmlId'] ?: null;
		$instanceTz = $params['instanceTz'] ?: null;
		$syncCaldav = $params['syncCaldav'] ?: false;
		$userId = isset($params['userId']) ? $params['userId'] : self::getCurUserId();

		$result = [];
		$sectionId = (is_array($arFields['SECTIONS']) && count($arFields['SECTIONS']) > 0) ? $arFields['SECTIONS'][0] : intval($arFields['SECTIONS']);
		$bPersonal = self::IsPersonal($arFields['CAL_TYPE'], $arFields['OWNER_ID'], $userId);
		$checkPermission = !isset($params['checkPermission']) || $params['checkPermission'] !== false;
		$silentErrorModePrev = self::$silentErrorMode;
		self::SetSilentErrorMode();

		if (!isset($arFields['DATE_FROM']) &&
			!isset($arFields['DATE_TO']) &&
			isset($arFields['DT_FROM']) &&
			isset($arFields['DT_TO']))
		{
			$arFields['DATE_FROM'] = $arFields['DT_FROM'];
			$arFields['DATE_TO'] = $arFields['DT_TO'];
			unset($arFields['DT_FROM']);
			unset($arFields['DT_TO']);
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
						"DELETED" => "N"
					],
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'fetchMeetings' => false,
					'userId' => $userId
				]
			);

			if ($curEvent)
				$curEvent = $curEvent[0];

			$canChangeDateRecurrenceEvent = ($params['recursionEditMode'] === 'all' || $params['recursionEditMode'] === '')
				&& ($arFields['DATE_FROM'] !== $curEvent['DATE_FROM']) && $arFields['RRULE']['FREQ'] != 'NONE';

			if ($canChangeDateRecurrenceEvent)
			{
				$arFields['DATE_FROM'] = self::GetOriginalDate($arFields['DATE_FROM'], $curEvent['DATE_FROM'], $arFields['TZ_FROM']);
				$arFields['DATE_TO'] = self::GetOriginalDate($arFields['DATE_TO'], $curEvent['DATE_TO'], $arFields['TZ_TO']);
			}

			$bPersonal = $bPersonal && self::IsPersonal($curEvent['CAL_TYPE'], $curEvent['OWNER_ID'], $userId);

			$arFields['CAL_TYPE'] = $curEvent['CAL_TYPE'];
			$arFields['OWNER_ID'] = $curEvent['OWNER_ID'];
			$arFields['CREATED_BY'] = $curEvent['CREATED_BY'];
			$arFields['ACTIVE'] = $curEvent['ACTIVE'];

			$bChangeMeeting = !$checkPermission || CCalendarSect::CanDo('calendar_edit', $curEvent['SECT_ID'], $userId);

			if (!isset($arFields['NAME']))
				$arFields['NAME'] = $curEvent['NAME'];
			if (!isset($arFields['DESCRIPTION']))
				$arFields['DESCRIPTION'] = $curEvent['DESCRIPTION'];
			if (!isset($arFields['COLOR']) && $curEvent['COLOR'])
				$arFields['COLOR'] = $curEvent['COLOR'];
			if (!isset($arFields['TEXT_COLOR']) && $curEvent['TEXT_COLOR'])
				$arFields['TEXT_COLOR'] = $curEvent['TEXT_COLOR'];
			if (!isset($arFields['SECTIONS']))
			{
				$arFields['SECTIONS'] = array($curEvent['SECT_ID']);
				$sectionId = (is_array($arFields['SECTIONS']) && count($arFields['SECTIONS']) > 0) ? $arFields['SECTIONS'][0] : 0;
			}
			if (!isset($arFields['IS_MEETING']))
				$arFields['IS_MEETING'] = $curEvent['IS_MEETING'];
			if (!isset($arFields['ACTIVE']))
				$arFields['ACTIVE'] = $curEvent['ACTIVE'];
			if (!isset($arFields['PRIVATE_EVENT']))
				$arFields['PRIVATE_EVENT'] = $curEvent['PRIVATE_EVENT'];
			if (!isset($arFields['ACCESSIBILITY']))
				$arFields['ACCESSIBILITY'] = $curEvent['ACCESSIBILITY'];
			if (!isset($arFields['IMPORTANCE']))
				$arFields['IMPORTANCE'] = $curEvent['IMPORTANCE'];
			if (!isset($arFields['SKIP_TIME']))
				$arFields['SKIP_TIME'] = $curEvent['DT_SKIP_TIME'] == 'Y';
			if (!isset($arFields['DATE_FROM']) && isset($curEvent['DATE_FROM']))
				$arFields['DATE_FROM'] = $curEvent['DATE_FROM'];
			if (!isset($arFields['DATE_TO']) && isset($curEvent['DATE_TO']))
				$arFields['DATE_TO'] = $curEvent['DATE_TO'];
			if (!isset($arFields['TZ_FROM']))
				$arFields['TZ_FROM'] = $curEvent['TZ_FROM'];
			if (!isset($arFields['TZ_TO']))
				$arFields['TZ_TO'] = $curEvent['TZ_TO'];
			if (!isset($arFields['MEETING']) && $arFields['IS_MEETING'])
				$arFields['MEETING'] = $curEvent['MEETING'];
			if (!isset($arFields['MEETING']) && $arFields['IS_MEETING'])
				$arFields['MEETING'] = $curEvent['MEETING'];

			if (!isset($arFields['ATTENDEES']) && !isset($arFields['ATTENDEES_CODES'])
				&& $arFields['IS_MEETING'] && is_array($curEvent['ATTENDEE_LIST']))
			{
				$arFields['ATTENDEES'] = [];
				foreach($curEvent['ATTENDEE_LIST'] as $attendee)
				{
					$arFields['ATTENDEES'][] = $attendee['id'];
				}
			}
			if (!isset($arFields['ATTENDEES_CODES']) && $arFields['IS_MEETING'])
			{
				$arFields['ATTENDEES_CODES'] = $curEvent['ATTENDEES_CODES'];
			}

			if (!isset($arFields['LOCATION']) && $curEvent['LOCATION'] != "")
			{
				$arFields['LOCATION'] = [
					"OLD" => $curEvent['LOCATION'],
					"NEW" => $curEvent['LOCATION']
				];
			}

			if (!$bChangeMeeting)
			{
				$arFields['IS_MEETING'] = $curEvent['IS_MEETING'];
			}

			if ($arFields['IS_MEETING'] && !$bPersonal && $arFields['CAL_TYPE'] == 'user')
			{
				$arFields['SECTIONS'] = array($curEvent['SECT_ID']);
			}

			if ($curEvent['IS_MEETING'])
			{
				$arFields['MEETING_HOST'] = $curEvent['MEETING_HOST'];
			}

			// If it's attendee but modifying called from CalDav methods
			if (($params['bSilentAccessMeeting'] || $params['fromWebservice'] === true)
				&& $curEvent['IS_MEETING'] && $curEvent['PARENT_ID'] != $curEvent['ID'])
			{
				// TODO: It called when changes caused in google/webservise side but can't be
				// TODO: implemented because user is only attendee, not the owner of the event
				//Todo: we have to update such events back to revert changes from google
//				$params['recursionEditMode'] = 'skip';
//				$params['sendInvitations'] = false;
//				$params['sendEditNotification'] = false;
//				$params['significantChanges'] = false;
//				$params['arFields'] = array(
//					"ID" => $arFields["ID"],
//					"PARENT_ID" => $arFields["PARENT_ID"],
//					"OWNER_ID" => $arFields["OWNER_ID"],
//					"DAV_XML_ID" => $arFields['DAV_XML_ID'],
//					"CAL_DAV_LABEL" => $arFields['CAL_DAV_LABEL'],
//					"DAV_EXCH_LABEL" => $arFields['DAV_EXCH_LABEL'],
//					"RRULE" => $arFields['RRULE'],
//					"EXDATE" => $arFields['EXDATE']
//				);
//				$params['userId'] = $userId;
//				$params['sync'] = $sync;
//
//				CCalendarEvent::Edit($params);
				return true; // CalDav will return 204
			}

			if (!isset($arFields["RRULE"]) && $curEvent["RRULE"] != '' && $params['fromWebservice'] !== true)
			{
				$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent["RRULE"]);
			}

			if ($params['fromWebservice'] === true)
			{
				if ($arFields["RRULE"] == -1 && CCalendarEvent::CheckRecurcion($curEvent))
				{
					$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent['RRULE']);
				}
			}

			if (!isset($arFields['EXDATE']) && $arFields["RRULE"])
			{
				$arFields['EXDATE'] = $curEvent['EXDATE'];
			}

			if ($curEvent)
				$params['currentEvent'] = $curEvent;

			if ($checkPermission && !$bPersonal
				&& !CCalendarSect::CanDo('calendar_edit', $curEvent['SECT_ID'], $userId))
			{
				return Loc::getMessage('EC_ACCESS_DENIED');
			}
		}
		elseif ($sectionId > 0 && $checkPermission && !$bPersonal
			&& !CCalendarSect::CanDo('calendar_add', $sectionId, $userId))
		{
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
		}

		if ($params['autoDetectSection'] && $sectionId <= 0)
		{
			$sectionId = false;
			if ($arFields['CAL_TYPE'] == 'user')
			{
				$sectionId = CCalendar::GetMeetingSection($arFields['OWNER_ID'], true);
				//$sectionId = CCalendarSect::GetLastUsedSection('user', $arFields['OWNER_ID'], $userId);
				if ($sectionId)
				{
					$res = CCalendarSect::GetList(
						array(
							'arFilter' => array(
								'CAL_TYPE' => $arFields['CAL_TYPE'],
								'OWNER_ID' => $arFields['OWNER_ID'],
								'ID'=> $sectionId
							)));


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
					$arFields['SECTIONS'] = array($sectionId);
			}

			if (!$sectionId)
			{
				$sectRes = self::GetSectionForOwner($arFields['CAL_TYPE'], $arFields['OWNER_ID'], $params['autoCreateSection']);
				if ($sectRes['sectionId'] > 0)
				{
					$sectionId = $sectRes['sectionId'];
					$arFields['SECTIONS'] = array($sectionId);
					if ($sectRes['autoCreated'])
						$params['bAffectToDav'] = false;
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

		// Set version
		if (!isset($arFields['VERSION']) || $arFields['VERSION'] <= $curEvent['VERSION'])
			$arFields['VERSION'] = $curEvent['VERSION'] ? $curEvent['VERSION'] + 1 : 1;

		if ($params['autoDetectSection'] && $sectionId <= 0 && $arFields['OWNER_ID'] > 0)
		{
			$res = CCalendarSect::GetList(
				array('arFilter' => array(
					'CAL_TYPE' => $arFields['CAL_TYPE'],
					'OWNER_ID' => $arFields['OWNER_ID']),
					'checkPermissions' => false
				)
			);
			if ($res && is_array($res) && isset($res[0]))
			{
				$sectionId = $res[0]['ID'];
			}
			elseif ($params['autoCreateSection'])
			{
				$defCalendar = CCalendarSect::CreateDefault(array(
					'type' => $arFields['CAL_TYPE'],
					'ownerId' => $arFields['OWNER_ID']
				));
				$sectionId = $defCalendar['ID'];

				$params['bAffectToDav'] = false;
			}
			if ($sectionId > 0)
				$arFields['SECTIONS'] = array($sectionId);
			else
				return false;
		}

		$bExchange = CCalendar::IsExchangeEnabled() && $arFields['CAL_TYPE'] == 'user';
		$bCalDav = CCalendar::IsCalDAVEnabled() && $arFields['CAL_TYPE'] == 'user';
		$bGoogleApi = CCalendar::isGoogleApiEnabled();

		if ((in_array($params['recursionEditMode'], ['this', 'skip']) && $editInstance === false)
			|| ($editNextEvents === false && ($params['recursionEditMode'] === 'next')))
		{
			$modeSync = false;

			if ($editParentEvents === true)
			{
				$modeSync = true;
			}
		}

		if (($params['bAffectToDav'] !== false
				&& ($bExchange || $bCalDav || $bGoogleApi) && $sectionId > 0)
			|| $syncCaldav)
		{
			$res = CCalendarSync::DoSaveToDav(array(
				'bCalDav' => $bCalDav,
				'bExchange' => $bExchange,
				'sectionId' => $sectionId,
				'modeSync' => $modeSync,
				'editInstance' => $editInstance,
				'originalDavXmlId' => $originalDavXmlId,
				'instanceTz' => $instanceTz,
				'editParentEvents' => $editParentEvents,
				'editNextEvents' => $editNextEvents,
				'syncCaldav' => $syncCaldav,
				'parentDateFrom' => $params['parentDateFrom'],
				'parentDateTo' => $params['parentDateTo'],
			), $arFields, $curEvent);

			if ($res !== true)
			{
				return CCalendar::ThrowError($res);
			}
		}

		$params['arFields'] = $arFields;
		$params['userId'] = $userId;

		if (self::$ownerId != $arFields['OWNER_ID'] && self::$type != $arFields['CAL_TYPE'])
			$params['path'] = self::GetPath($arFields['CAL_TYPE'], $arFields['OWNER_ID'], 1);
		else
			$params['path'] = self::$path;

		if (
			$curEvent
			&& in_array($params['recursionEditMode'], ['this', 'next'])
			&& CCalendarEvent::CheckRecurcion($curEvent)
		)
		{
			// Edit only current instance of the set of reccurent events
			if ($params['recursionEditMode'] == 'this')
			{
				// 1. Edit current reccurent event: exclude current date
				$excludeDates = CCalendarEvent::GetExDate($curEvent['EXDATE']);
				$excludeDate = self::Date(self::Timestamp(isset($params['currentEventDateFrom']) ? $params['currentEventDateFrom'] : $arFields['DATE_FROM']), false);
				$excludeDates[] = $excludeDate;

				// Save current event
				$id = CCalendar::SaveEvent(array(
					'arFields' => array(
						"ID" => $curEvent["ID"],
						'EXDATE' => CCalendarEvent::SetExDate($excludeDates),
					),
					'recursionEditMode' => 'skip',
					'silentErrorMode' => $params['silentErrorMode'],
					'sendInvitations' => false,
					'sendEditNotification' => false,
					'userId' => $userId,
					'requestUid' => $params['requestUid']
				));

				// 2. Copy event with new changes, but without reccursion
				$newParams = $params;
				$newParams['sendEditNotification'] = false;

				if (!$newParams['arFields']['MEETING']['REINVITE'])
				{
					$newParams['saveAttendeesStatus'] = true;
				}

				$newParams['arFields']['RECURRENCE_ID'] = $curEvent['RECURRENCE_ID'] ?: $newParams['arFields']['ID'];

				unset($newParams['arFields']['ID']);
				unset($newParams['arFields']['DAV_XML_ID']);
				unset($newParams['arFields']['RRULE']);
				unset($newParams['recursionEditMode']);
				$newParams['arFields']['REMIND'] = $params['currentEvent']['REMIND'];

				$fromTs = self::Timestamp($newParams['currentEventDateFrom']);
				$currentFromTs = self::Timestamp($newParams['arFields']['DATE_FROM']);
				$length = self::Timestamp($newParams['arFields']['DATE_TO']) - self::Timestamp($newParams['arFields']['DATE_FROM']);

				if (!isset($newParams['arFields']['DATE_FROM']) || !isset($newParams['arFields']['DATE_TO']))
				{
					$length = $curEvent['DT_LENGTH'];
					$currentFromTs = self::Timestamp($curEvent['DATE_FROM']);
				}

				$instanceDate = !isset($newParams['arFields']['DATE_FROM']) ||self::Date(self::Timestamp($curEvent['DATE_FROM']), false) == self::Date($currentFromTs, false);

				if ($newParams['arFields']['SKIP_TIME'])
				{
					if ($instanceDate)
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($fromTs, false);
						$newParams['arFields']['DATE_TO'] = self::Date($fromTs + $length - CCalendar::GetDayLen(), false);
					}
					else
					{
						$newParams['arFields']['DATE_FROM'] = self::Date($currentFromTs, false);
						$newParams['arFields']['DATE_TO'] = self::Date($currentFromTs + $length - CCalendar::GetDayLen(), false);
					}
				}
				else
				{
					if ($instanceDate)
					{
						$newFromTs = self::DateWithNewTime($currentFromTs, $fromTs);
						$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
						$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
					}
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
				$newParams['arFields']['ORIGINAL_DATE_FROM'] = self::GetOriginalDate($params['currentEvent']['DATE_FROM'], $arFields['DATE_FROM'], $newParams['arFields']['TZ_FROM']);
				$newParams['originalDavXmlId'] = $params['currentEvent']['DAV_XML_ID'];
				$newParams['instanceTz'] = $params['currentEvent']['TZ_FROM'];
				$newParams['parentDateFrom'] = $params['currentEvent']['DATE_FROM'];
				$newParams['parentDateTo'] = $params['currentEvent']['DATE_TO'];
				$newParams['requestUid'] = $params['requestUid'];

				$result['recEventId'] = CCalendar::SaveEvent($newParams);
			}
			// Edit all next instances of the set of reccurent events
			elseif($params['recursionEditMode'] == 'next')
			{
				$currentDateTimestamp = self::Timestamp($params['currentEventDateFrom']);

				// Copy event with new changes
				$newParams = $params;
				$recId = $curEvent['RECURRENCE_ID'] ?: $newParams['arFields']['ID'];

				// Check if it's first instance of the series, so we shoudn't create another event
				if (CCalendar::Date(self::Timestamp($curEvent['DATE_FROM']), false) == CCalendar::Date($currentDateTimestamp, false))
				{
					$newParams['recursionEditMode'] = 'skip';
				}
				else
				{
					// 1. Edit current reccurent event: set finish date with date of current instance
					$arFieldsCurrent = [
						"ID" => $curEvent["ID"],
						"RRULE" => CCalendarEvent::ParseRRULE($curEvent['RRULE'])
					];
					$arFieldsCurrent['RRULE']['UNTIL'] = self::Date($currentDateTimestamp - self::GetDayLen(), false);
					unset($arFieldsCurrent['RRULE']['~UNTIL']);
					unset($arFieldsCurrent['RRULE']['COUNT']);

					// Save current event
					$id = CCalendar::SaveEvent(array(
						'arFields' => $arFieldsCurrent,
						'silentErrorMode' => $params['silentErrorMode'],
						'recursionEditMode' => 'skip',
						'sendInvitations' => false,
						'sendEditNotification' => false,
						'userId' => $userId,
						'editNextEvents' => true,
						'editParentEvents' => true,
						'checkPermission' => $checkPermission,
						'requestUid' => $params['requestUid']
					));

					unset($newParams['arFields']['ID']);
					unset($newParams['arFields']['DAV_XML_ID']);
					unset($newParams['arFields']['G_EVENT_ID']);
					unset($newParams['recursionEditMode']);
				}

				if (!$newParams['arFields']['MEETING']['REINVITE'])
				{
					$newParams['saveAttendeesStatus'] = true;
				}

				$currentFromTs = self::Timestamp($newParams['arFields']['DATE_FROM']);
				$length = self::Timestamp($newParams['arFields']['DATE_TO']) - self::Timestamp($newParams['arFields']['DATE_FROM']);

				if (!isset($newParams['arFields']['DATE_FROM']) || !isset($newParams['arFields']['DATE_TO']))
				{
					$length = $curEvent['DT_LENGTH'];
					$currentFromTs = self::Timestamp($curEvent['DATE_FROM']);
				}

				$instanceDate = !isset($newParams['arFields']['DATE_FROM'])
					||self::Date(self::Timestamp($curEvent['DATE_FROM']), false) == self::Date($currentFromTs, false);

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
				else
				{
					if ($instanceDate)
					{
						$newFromTs = self::DateWithNewTime($currentFromTs, $currentDateTimestamp);
						$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
						$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
					}
				}

				if (isset($curEvent['EXDATE']) && $curEvent['EXDATE'] != '')
					$newParams['arFields']['EXDATE'] = $curEvent['EXDATE'];

				if (isset($newParams['arFields']['RRULE']['COUNT']) && $newParams['arFields']['RRULE']['COUNT'] > 0)
				{
					$countParams['rrule'] = $newParams['arFields']['RRULE'];
					$countParams['dateFrom'] = $curEvent['DATE_FROM'];
					$countParams['dateTo'] = $newParams['arFields']['DATE_FROM'];
					$countParams['timeZone'] = $curEvent['TZ_FROM'];
					$newParams['arFields']['RRULE']['COUNT'] = CCalendar::CountNumberFollowEvents($countParams);
					unset($newParams['arFields']['RRULE']['UNTIL']);
					unset($newParams['arFields']['RRULE']['~UNTIL']);
				}

				$newParams['editNextEvents'] = true;
				$result = CCalendar::SaveEvent($newParams);
				if (!is_array($result))
				{
					$result = [
						'id' => $result,
						'recEventId' => $result,
					];
				}

				if ($recId)
				{
					$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recId, false);

					foreach($recRelatedEvents as $ev)
					{
						if ($ev['ID'] == $result['id'])
						{
							continue;
						}

						$evFromTs = CCalendar::Timestamp($ev['DATE_FROM']);

						if ($evFromTs > $currentDateTimestamp)
						{
							$newParams['arFields']['ID'] = $ev['ID'];
							$newParams['arFields']['RRULE'] = CCalendarEvent::ParseRRULE($ev['RRULE']);

							if ($newParams['arFields']['SKIP_TIME'])
							{
								$newParams['arFields']['DATE_FROM'] = self::Date($evFromTs, false);
								$newParams['arFields']['DATE_TO'] = self::Date(CCalendar::Timestamp($ev['DATE_TO']), false);
							}
							else
							{
								$newFromTs = self::DateWithNewTime($currentFromTs, $evFromTs);
								$newParams['arFields']['DATE_FROM'] = self::Date($newFromTs);
								$newParams['arFields']['DATE_TO'] = self::Date($newFromTs + $length);
							}

							$newParams['arFields']['RECURRENCE_ID'] = $result['id'];
							$newParams['originalDavXmlId'] = $result['originalDavXmlId'];
							$newParams['arFields']['ORIGINAL_DATE_FROM'] = self::GetOriginalDate($result['originalDateFrom'], $ev['ORIGINAL_DATE_FROM'], $result['instanceTz']);
							$newParams['instanceTz'] = $result['instanceTz'];
							$newParams['editInstance'] = true;

							unset($newParams['arFields']['EXDATE']);

							CCalendar::SaveEvent($newParams);
						}
					}
				}
			}
		}
		else
		{
			if ($params['recursionEditMode'] !== 'all')
				$params['recursionEditMode'] = 'skip';
			else
				$params['recursionEditMode'] = '';

			$id = CCalendarEvent::Edit($params);

			if ($id)
			{
				$UFs = $params['UF'];
				if(isset($UFs) && count($UFs) > 0)
				{
					CCalendarEvent::UpdateUserFields($id, $UFs);

					if($arFields['IS_MEETING'])
					{
						if(!empty($UFs['UF_WEBDAV_CAL_EVENT']))
						{
							$UF = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("CALENDAR_EVENT", $id, LANGUAGE_ID);
							CCalendar::UpdateUFRights(
								$UFs['UF_WEBDAV_CAL_EVENT'],
								$arFields['ATTENDEES_CODES'],
								$UF['UF_WEBDAV_CAL_EVENT']
							);
						}
					}
				}
			}

			if ($editNextEvents === true && $editParentEvents === false)
			{
				$result['originalDate'] = $params['arFields']['DATE_FROM'];
				$result['originalDavXmlId'] = $params['arFields']['DAV_XML_ID'];
				$result['instanceTz'] = $params['arFields']['TZ_FROM'];
			}

			// Here we should select all events connected with edited via RECURRENCE_ID:
			// It could be original source event (without RECURRENCE_ID) or sibling events
			if ($curEvent && CCalendarEvent::CheckRecurcion($curEvent)
				&& !$params['recursionEditMode']
				&& !$params['arFields']['RECURRENCE_ID']
			)
			{
				$events = [];
				$recId = $curEvent['RECURRENCE_ID'] ? $curEvent['RECURRENCE_ID'] : $curEvent['ID'];
				if ($curEvent['RECURRENCE_ID'] && $curEvent['RECURRENCE_ID'] !== $curEvent['ID'])
				{
					$topEvent = CCalendarEvent::GetById($curEvent['RECURRENCE_ID']);
					if ($topEvent)
					{
						$events[] = $topEvent;
					}
				}

				if ($recId)
				{
					$events_1 = CCalendarEvent::GetList(array('arFilter' => array('RECURRENCE_ID' => $recId), 'parseRecursion' => false, 'setDefaultLimit' => false));

					if ($events_1)
						$events = array_merge($events, $events_1);
				}

				foreach($events as $ev)
				{
					if ($ev['ID'] !== $curEvent['ID'])
					{
						$newParams = $params;

						$newParams['arFields']['ID'] = $ev['ID'];
						$newParams['arFields']['RECURRENCE_ID'] = $ev['RECURRENCE_ID'];
						$newParams['arFields']['DAV_XML_ID'] = $ev['DAV_XML_ID'];
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

						if (isset($ev['EXDATE']) && $ev['EXDATE'] != '')
							$newParams['arFields']['EXDATE'] = $ev['EXDATE'];

						CCalendar::SaveEvent($newParams);
					}
				}
			}

			$arFields['ID'] = $id;
			foreach(GetModuleEvents("calendar", "OnAfterCalendarEventEdit", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields, $bNew, $userId));
		}

		self::SetSilentErrorMode($silentErrorModePrev);

		$result['id'] = $id;

		return $result;
	}

	private static function CountNumberFollowEvents($params)
	{
		$curCount = self::CountPastEvents($params);

		$count = (int)$params['rrule']['COUNT'] - $curCount;

		return (string)$count;
	}

	public static function CountPastEvents($params)
	{
		$curCount = 0;

		$dateFromTz = !empty($params['timeZone']) ? new \DateTimeZone($params['timeZone']) : new \DateTimeZone("UTC");
		$dateToTz = !empty($params['timeZone']) ? new \DateTimeZone($params['timeZone']) : new \DateTimeZone("UTC");
		$dateFrom = new Main\Type\DateTime($params['dateFrom'], Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME), $dateFromTz);
		$dateTo = new Main\Type\DateTime($params['dateTo'], Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME), $dateToTz);
		$diff = $dateFrom->getDiff($dateTo);

		if ($params['rrule']['FREQ'] == 'DAILY')
		{
			$diff = (int)$diff->format('%a');
			$curCount = $diff / (int)$params['rrule']['INTERVAL'];
		}

		if ($params['rrule']['FREQ'] == 'WEEKLY')
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

		if ($params['rrule']['FREQ'] == 'MONTHLY')
		{
			$diff = (int)$diff->format('%m');
			$curCount = $diff / (int)$params['rrule']['INTERVAL'];
		}

		if ($params['rrule']['FREQ'] == 'YEARLY')
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

	public static function GetSectionForOwner($calType, $ownerId, $autoCreate = true)
	{
		return CCalendarSect::GetSectionForOwner($calType, $ownerId, $autoCreate);
	}

	public static function UpdateUFRights($files, $rights, $ufEntity = [])
	{
		global $USER;
		static $arTasks = null;

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
			$id = intval($id);
			if (intval($id) > 0)
				$arFiles[] = $id;
		}

		if (sizeof($arFiles) <= 0)
			return false;

		if ($arTasks == null)
			$arTasks = CWebDavIblock::GetTasks();

		$arCodes = [];
		foreach($rights as $value)
		{
			if (mb_substr($value, 0, 2) === 'SG')
				$arCodes[] = $value.'_K';
			$arCodes[] = $value;
		}
		$arCodes = array_unique($arCodes);

		$i=0;
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

				if ($arWDFile['WF_NEW'] == 'Y')
					$ibe->Update($id, array('BP_PUBLISHED' => 'Y'));

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
				$CACHE_MANAGER->ClearByTag('iblock_id_' . $iblockId);

			unset($iblockId);
		}
	}

	public static function TempUser($TmpUser = false, $create = true, $ID = false)
	{
		global $USER;
		if ($create && $TmpUser === false && (!$USER || !is_object($USER)))
		{
			$USER = new CUser;
			if ($ID && intval($ID) > 0)
				$USER->Authorize(intval($ID));
			return $USER;
		}
		elseif (!$create && $USER && is_object($USER))
		{
			unset($USER);
			return false;
		}
		return false;
	}

	public static function SaveSection($Params)
	{
		$type = isset($Params['arFields']['CAL_TYPE']) ? $Params['arFields']['CAL_TYPE'] : self::$type;

		// Exchange
		if ($Params['bAffectToDav'] !== false && CCalendar::IsExchangeEnabled(self::$ownerId) && $type == 'user')
		{
			$exchRes = true;
			$ownerId = isset($Params['arFields']['OWNER_ID']) ? $Params['arFields']['OWNER_ID'] : self::$ownerId;

			if(isset($Params['arFields']['ID']) && $Params['arFields']['ID'] > 0)
			{
				// Fetch section
				//$oSect = CCalendarSect::GetById($Params['arFields']['ID']);
				// For exchange we change only calendar name
				//if ($oSect && $oSect['IS_EXCHANGE'] && $oSect['DAV_EXCH_CAL'] && $oSect["NAME"] != $Params['arFields']['NAME'])
				//	$exchRes = CDavExchangeCalendar::DoUpdateCalendar($ownerId, $oSect['DAV_EXCH_CAL'], $oSect['DAV_EXCH_MOD'], $Params['arFields']);
			}
			elseif($Params['arFields']['IS_EXCHANGE'])
			{
				$exchRes = CDavExchangeCalendar::DoAddCalendar($ownerId, $Params['arFields']);
			}

			if ($exchRes !== true)
			{
				if (!is_array($exchRes) || !array_key_exists("XML_ID", $exchRes))
					return CCalendar::ThrowError(CCalendar::CollectExchangeErrors($exchRes));

				// // It's ok, we successfuly save event to exchange calendar - and save it to DB
				$Params['arFields']['DAV_EXCH_CAL'] = $exchRes['XML_ID'];
				$Params['arFields']['DAV_EXCH_MOD'] = $exchRes['MODIFICATION_LABEL'];
			}
		}

		// Save here
		$ID = CCalendarSect::Edit($Params);
		CCalendar::ClearCache(array('section_list', 'event_list'));
		return $ID;
	}

	public static function ClearCache($arPath = false)
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->ClearByTag("CALENDAR_EVENT_LIST");

		if ($arPath === false)
		{
			$arPath = array(
				'access_tasks',
				'type_list',
				'section_list',
				'attendees_list',
				'event_list'
			);
		}
		elseif (!is_array($arPath))
		{
			$arPath = array($arPath);
		}

		if (is_array($arPath) && count($arPath) > 0)
		{
			$cache = new CPHPCache;
			foreach($arPath as $path)
				if ($path != '')
					$cache->CleanDir(CCalendar::CachePath().$path);
		}
	}

	public static function CachePath()
	{
		return self::$cachePath;
	}

	// * * * * * * * * * * * * CalDAV + Exchange * * * * * * * * * * * * * * * *

	public static function SyncCalendarItems($connectionType, $calendarId, $arCalendarItems): array
	{
		self::$silentErrorMode = true;
		// $arCalendarItems:
		//Array(
		//	[0] => Array(
		//		[XML_ID] => AAATAGFudGlfYn...
		//		[MODIFICATION_LABEL] => DwAAABYAAA...
		//	)
		//	[1] => Array(
		//		[XML_ID] => AAATAGFudGlfYnVn...
		//		[MODIFICATION_LABEL] => DwAAABYAAAAQ...
		//	)
		//)

		[$sectionId, $entityType, $entityId] = $calendarId;
		$entityType = mb_strtolower($entityType);

		if ($connectionType === 'exchange')
		{
			$xmlIdField = "DAV_EXCH_LABEL";
		}
		elseif ($connectionType === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE)
		{
			$xmlIdField = "CAL_DAV_LABEL";
		}
		else
		{
			return [];
		}

		$arCalendarItemsMap = [];
		foreach ($arCalendarItems as $value)
		{
			$arCalendarItemsMap[$value["XML_ID"]] = $value["MODIFICATION_LABEL"];
		}

		$arModified = [];
		$eventsList = CCalendarEvent::GetList(
			[
				'arFilter' => [
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId,
					'SECTION' => $sectionId
				],
				'getUserfields' => false,
				'parseRecursion' => false,
				'fetchAttendees' => false,
				'fetchMeetings' => false,
				'userId' => $entityType === 'user' ? $entityId : '0'
			]
		);

		foreach ($eventsList as $event)
		{
			if ($event['RECURRENCE_ID'] && $instanceChangeKey = self::FindSyncInstance($event))
			{
				$arCalendarItemsMap[$event['DAV_XML_ID']] = $instanceChangeKey;
			}

			if (isset($arCalendarItemsMap[$event["DAV_XML_ID"]]))
			{
				if ($event[$xmlIdField] !== $arCalendarItemsMap[$event["DAV_XML_ID"]])
				{
					$arModified[$event["DAV_XML_ID"]] = $event["ID"];
				}

				unset($arCalendarItemsMap[$event["DAV_XML_ID"]]);
			}
			elseif ($connectionType === 'exchange')
			{
				if ((int)$event['ID'] === (int)$event['PARENT_ID'])
				{
					self::DeleteCalendarEvent($calendarId, $event["ID"], self::$userId, $event);
				}
			}
			else
			{
				self::DeleteCalendarEvent($calendarId, $event["ID"], self::$userId, $event);
			}
		}

		$arResult = [];
		foreach ($arCalendarItems as $value)
		{
			if (array_key_exists($value["XML_ID"], $arModified))
			{
				$arResult[] = [
					"XML_ID" => $value["XML_ID"],
					"ID" => $arModified[$value["XML_ID"]]
				];
			}
		}

		foreach ($arCalendarItemsMap as $key => $value)
		{
			$arResult[] = [
				"XML_ID" => $key,
				"ID" => 0
			];
		}

		self::$silentErrorMode = false;
		return $arResult;
	}

	private static function FindSyncInstance($event)
	{
		$exchangeScheme = COption::GetOptionString("dav", "exchange_scheme", "http");
		$exchangeServer = COption::GetOptionString("dav", "exchange_server", "");
		$exchangePort = COption::GetOptionString("dav", "exchange_port", "80");
		$exchangeUsername = COption::GetOptionString("dav", "exchange_username", "");
		$exchangePassword = COption::GetOptionString("dav", "exchange_password", "");

		if (empty($exchangeServer))
			return "";

		$exchange = new CDavExchangeCalendar($exchangeScheme, $exchangeServer, $exchangePort, $exchangeUsername, $exchangePassword);

		$params = [
			'dateTo' => $event['DATE_TO'],
			'parentDateTo' => $event['DATE_TO'],
			'dateFrom' => $event['DATE_FROM'],
			'parentDateFrom' => $event['DATE_FROM'],
			'parentTz' => $event['TZ_FROM'],
			'changekey' => $event['DAV_EXCH_LABEL']
		];

		[ , $changeKey] = $exchange->FindInstance($params);

		return $changeKey;
	}

	public static function DeleteCalendarEvent($calendarId, $eventId, $userId, $oEvent = false)
	{
		[$sectionId, $entityType, $entityId] = $calendarId;
		$res = CCalendarEvent::Delete(array(
			'id' => $eventId,
			'userId' => $userId,
			'bMarkDeleted' => true,
			'Event' => $oEvent
		));
		return $res;
	}

	// Called from CalDav sync functions and from  CCalendar::SyncCalendarItems

	public static function SyncClearCache()
	{
	}

	public static function Color($color = '', $defaultColor = true)
	{
		if ($color != '')
		{
			$color = ltrim(trim(preg_replace('/[^\d|\w]/', '', $color)), "#");
			if (mb_strlen($color) > 6)
				$color = mb_substr($color, 0, 6);
			elseif(mb_strlen($color) < 6)
				$color = '';
		}
		$color = '#'.$color;

		// Default color
		$DEFAULT_COLOR = '#9dcf00';
		if ($color == '#')
		{
			if ($defaultColor === true)
				$color = $DEFAULT_COLOR;
			elseif($defaultColor)
				$color = $defaultColor;
			else
				$color = '';
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
		$m = intval($m);

		if ($m > 59)
			$m = 59;
		elseif ($m < 0)
			$m = 0;

		if ($m < 10)
			$m = '0'.$m;

		$h = intval($h);
		if ($h > 24)
			$h = 24;
		if ($h < 0)
			$h = 0;

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
			self::$userId = self::GetCurUserId();
		return self::$userId;
	}

	public static function GetReadonlyMode()
	{
		return self::$readOnly;
	}

	// Called from CalDav sync methods

	public static function GetUserAvatarSrc($user = [], $params = [])
	{
		if (!is_array($user) && intval($user) > 0)
			$user = self::GetUser($user);

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
					self::$pathesForSite = self::GetPathes(SITE_ID);
				self::$pathToUser = self::$pathesForSite['path_to_user'];
			}
			$pathToUser = self::$pathToUser;
		}

		return CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($pathToUser, array("user_id" => $userId, "USER_ID" => $userId)));
	}

	public static function GetAccessTasksByName($binging = 'calendar_section', $name = 'calendar_denied')
	{
		$arTasks = CCalendar::GetAccessTasks($binging);

		foreach($arTasks as $id => $task)
			if ($task['name'] == $name)
				return $id;

		return false;
	}

	public static function GetAccessTasks($binging = 'calendar_section')
	{
		\Bitrix\Main\Localization\Loc::loadLanguageFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/admin/task_description.php");

		if (is_array(self::$arAccessTask[$binging]))
			return self::$arAccessTask[$binging];

		$bIntranet = self::IsIntranetEnabled();
		$arTasks = [];
		$res = CTask::GetList(Array('ID' => 'asc'), Array('MODULE_ID' => 'calendar', 'BINDING' => $binging));
		while($arRes = $res->Fetch())
		{
			if (!$bIntranet && (mb_strtolower($arRes['NAME']) == 'calendar_view_time' || mb_strtolower($arRes['NAME']) == 'calendar_view_title'))
				continue;

			$name = '';
			if ($arRes['SYS'])
				$name = Loc::getMessage('TASK_NAME_'.mb_strtoupper($arRes['NAME']));
			if ($name == '')
				$name = $arRes['NAME'];

			$arTasks[$arRes['ID']] = array(
				'name' => $arRes['NAME'],
				'title' => $name
			);
		}

		self::$arAccessTask[$binging] = $arTasks;

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
		$tzEnabled = CTimeZone::Enabled();
		if ($tzEnabled)
		{
			CTimeZone::Disable();
		}

		// *** ADD MEETING ROOM ***
		$locOld = CCalendar::ParseLocation($old);
		$locNew = CCalendar::ParseLocation($new);
		CCalendar::GetSettings(array('request' => false));
		$res = $locNew['mrid'] ? $locNew['str'] : $new;
		$RMiblockId = isset($params['RMiblockId']) ? $params['RMiblockId'] : self::$settings['rm_iblock_id'];

		// If not allowed
		if ($RMiblockId && $locOld['mrid'] !== false && $locOld['mrevid'] !== false) // Release MR
		{
			CCalendar::ReleaseMeetingRoom(array(
				'mrevid' => $locOld['mrevid'],
				'mrid' => $locOld['mrid'],
				'RMiblockId' => $RMiblockId
			));
		}

		if ($locOld['room_id'] !== false && $locOld['room_event_id'] !== false) // Release MR
		{
			CCalendar::ReleaseLocation($locOld);
		}

		if ($locNew['mrid'] !== false) // Reserve MR
		{
			$mrevid = false;
			if ($params['bRecreateReserveMeetings'])
			{
				$mrevid = CCalendar::ReserveMeetingRoom(array(
					'RMiblockId' => $RMiblockId,
					'mrid' => $locNew['mrid'],
					'dateFrom' => $params['dateFrom'],
					'dateTo' => $params['dateTo'],
					'name' => $params['name'],
					'description' => Loc::getMessage('EC_RESERVE_FOR_EVENT').': '.$params['name'],
					'persons' => $params['persons'],
					'members' => $params['attendees']
				));
			}
			elseif(is_array($locNew) && $locNew['mrevid'] !== false)
			{
				$mrevid = $locNew['mrevid'];
			}

			$locNew = ($mrevid && $mrevid != 'reserved' && $mrevid != 'expire' && $mrevid > 0) ? 'ECMR_'.$locNew['mrid'].'_'.$mrevid : '';
		}
		elseif($locNew['room_id'])
		{
			$roomEventId = \CCalendarLocation::reserveRoom([
				'room_id' => $locNew['room_id'],
				'room_event_id' => false,
				'parentParams' => $params['parentParams']
			]);

			$locNew = $roomEventId ? 'calendar_'.$locNew['room_id'].'_'.$roomEventId : '';
		}
		else
		{
			$locNew = $locNew['str'];
		}

		if ($locNew)
		{
			$res = $locNew;
		}

		if ($tzEnabled)
		{
			CTimeZone::Enable();
		}

		return $res;
	}

	public static function ReserveMeetingRoom($params)
	{
		$tst = MakeTimeStamp($params['dateTo']);
		if (date("H:i", $tst) == '00:00')
			$params['dateTo'] = CIBlockFormatProperties::DateFormat(self::DFormat(true), $tst + (23 * 60 + 59) * 60);

		CCalendar::GetSettings(array('request' => false));
		$params['RMiblockId'] = (isset($params['RMiblockId']) && $params['RMiblockId']) ? $params['RMiblockId'] : self::$settings['rm_iblock_id'];

		$check = CCalendar::CheckMeetingRoom($params);
		if ($check !== true)
			return $check;

		$arFields = array(
			"IBLOCK_ID" => $params['RMiblockId'],
			"IBLOCK_SECTION_ID" => $params['mrid'],
			"NAME" => $params['name'],
			"DATE_ACTIVE_FROM" => $params['dateFrom'],
			"DATE_ACTIVE_TO" => $params['dateTo'],
			"CREATED_BY" => CCalendar::GetCurUserId(),
			"DETAIL_TEXT" => $params['description'],
			"PROPERTY_VALUES" => array(
				"UF_PERSONS" => $params['persons'],
				"PERIOD_TYPE" => 'NONE'
			),
			"ACTIVE" => "Y"
		);

		$bs = new CIBlockElement;
		$id = $bs->Add($arFields);

		// Hack: reserve meeting calendar based on old calendar's cache
		$cache = new CPHPCache;
		$cache->CleanDir('event_calendar/');
		$cache->CleanDir('event_calendar/events/');
		$cache->CleanDir('event_calendar/events/'.$params['RMiblockId']);

		return $id;
	}

	public static function CheckMeetingRoom($Params)
	{
		$fromDateTime = MakeTimeStamp($Params['dateFrom']);
		$toDateTime = MakeTimeStamp($Params['dateTo']);
		$arFilter = array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $Params['RMiblockId'],
			"SECTION_ID" => $Params['mrid'],
			"<DATE_ACTIVE_FROM" => $Params['dateTo'],
			">DATE_ACTIVE_TO" => $Params['dateFrom'],
			"PROPERTY_PERIOD_TYPE" => "NONE",
		);

		if ($Params['mrevid_old'] > 0)
			$arFilter["!=ID"] = $Params['mrevid_old'];

		$dbElements = CIBlockElement::GetList(array("DATE_ACTIVE_FROM" => "ASC"), $arFilter, false, false, array('ID'));
		if ($arElements = $dbElements->GetNext())
			return 'reserved';

		include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/intranet.reserve_meeting/init.php");
		$arPeriodicElements = __IRM_SearchPeriodic($fromDateTime, $toDateTime, $Params['RMiblockId'], $Params['mrid']);

		for ($i = 0, $l = count($arPeriodicElements); $i < $l; $i++)
			if (!$Params['mrevid_old'] || $arPeriodicElements[$i]['ID'] != $Params['mrevid_old'])
				return 'reserved';

		return true;
	}

	public static function GetOuterUrl()
	{
		return self::$outerUrl;
	}

	public static function ManageConnections($arConnections = [])
	{
		global $APPLICATION;
		$bSync = false;
		$l = count($arConnections);

		for ($i = 0; $i < $l; $i++)
		{
			$con = $arConnections[$i];
			$conId = (int)$con['id'];
			if ($conId <= 0) // It's new connection
			{
				if ($con['del'] !== 'Y')
				{
					if(!CCalendar::CheckCalDavUrl($con['link'], $con['user_name'], $con['pass']))
					{
						return Loc::getMessage("EC_CALDAV_URL_ERROR");
					}

					CDavConnection::Add(array(
						"ENTITY_TYPE" => 'user',
						"ENTITY_ID" => self::$ownerId,
						"ACCOUNT_TYPE" => Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
						"NAME" => $con['name'],
						"SERVER" => $con['link'],
						"SERVER_USERNAME" => $con['user_name'],
						"SERVER_PASSWORD" => $con['pass'])
					);
					$bSync = true;
				}
			}
			elseif ($con['del'] !== 'Y') // Edit connection
			{
				$arFields = [
					"NAME" => $con['name'],
					"SERVER" => $con['link'],
					"SERVER_USERNAME" => $con['user_name']
				];
				if ($con['pass'] !== 'bxec_not_modify_pass')
				{
					$arFields["SERVER_PASSWORD"] = $con['pass'];
				}

				$resCon = CDavConnection::GetList(array("ID" => "ASC"), array("ID" => $conId));
				if ($arCon = $resCon->Fetch())
				{
					/** @var Google\Helper $googleHelper */
					$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
					if(!$googleHelper->isGoogleConnection($arCon['ACCOUNT_TYPE']))
					{
						CDavConnection::Update($conId, $arFields);
					}
				}

				if (is_array($con['sections']))
				{
					foreach ($con['sections'] as $sectId => $active)
					{
						$sectId = (int)$sectId;

						if(CCalendar::IsPersonal() || CCalendarSect::CanDo('calendar_edit_section', $sectId, self::$userId))
						{
							CCalendarSect::Edit(array('arFields' => array("ID" => $sectId, "ACTIVE" => $active == "Y" ? "Y" : "N")));
						}
					}
				}

				$bSync = true;
			}
			else
			{
				CCalendar::RemoveConnection(array('id' => $conId, 'del_calendars' => $con['del_calendars']));
			}
		}

		if($err = $APPLICATION->GetException())
		{
			return $err->GetString();
		}

		if ($bSync)
		{
			CDavGroupdavClientCalendar::DataSync("user", self::$ownerId);
		}

		$res = CDavConnection::GetList(
			["ID" => "DESC"],
			[
				"ENTITY_TYPE" => "user",
				"ENTITY_ID" => self::$ownerId
			],
			false,
			false
		);

		while($arCon = $res->Fetch())
		{
			if (
				$arCon['ACCOUNT_TYPE'] === Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV
				|| $arCon['ACCOUNT_TYPE'] === Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE
			)
			{
				if (mb_strpos($arCon['LAST_RESULT'], "[200]") === false)
				{
					return Loc::getMessage('EC_CALDAV_CONNECTION_ERROR',
						[
							'#CONNECTION_NAME#' => $arCon['NAME'],
							'#ERROR_STR#' => $arCon['LAST_RESULT']
						]
					);
				}
			}
		}

		return true;
	}

	public static function AddConnection($connection)
	{
		if((!\CCalendar::CheckCalDavUrl($connection['link'], $connection['user_name'], $connection['pass'])))
		{
			return Loc::getMessage('EC_CAL_OPERATION_CANNOT_BE_PERFORMED');
		}

		$arFields = [
			'ENTITY_TYPE' => 'user',
			'ENTITY_ID' => $connection['user_id'],
			'ACCOUNT_TYPE' => Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
			'NAME' => $connection['name'],
			'SERVER' => $connection['link'],
			'SERVER_USERNAME' => $connection['user_name'],
			'SERVER_PASSWORD' => $connection['pass']
		];

		\CDavConnection::ParseFields($arFields);

		$davConnection = \CDavConnection::getList(
			['ID' => 'ASC'],
			[
				'SERVER_HOST' => $arFields['SERVER_HOST'],
				'SERVER_PATH' => $arFields['SERVER_PATH'],
				'ENTITY_ID' => $arFields['ENTITY_ID']
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

	public static function EditConnection()
	{

	}

	public static function CheckCalDavUrl($url, $username, $password)
	{
		$arServer = parse_url($url);

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
			$connection = (\CDavConnection::GetList(["ID" => "ASC"], ["ID" => $params['id']]))->Fetch();
			/** @var Google\Helper $googleHelper */
			$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
			if (
				is_array($connection)
				&& isset($connection['ACCOUNT_TYPE'])
				&& $googleHelper->isGoogleConnection($connection['ACCOUNT_TYPE'])
			)
			{
				self::stopGoogleConnectionChannels($params['id']);
				if(is_array($sections))
				{
					$params['del_calendars']
						? self::deleteGoogleConnectionSections($sections)
						: self::editGoogleConnectionsSections($sections);
				}
				self::removeGoogleAuthToken($connection);
			}
			elseif (is_array($sections))
			{
				foreach ($sections as $section)
				{
					if ($params['del_calendars'] === 'Y' /*&& $section['IS_LOCAL'] !== 'Y'*/)
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
								'type' => $connectionType
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
		$text = preg_replace(array("/\&lt;/is".BX_UTF_PCRE_MODIFIER, "/\&gt;/is".BX_UTF_PCRE_MODIFIER),array('<', '>'),$text);

		$text = preg_replace("/\<br\s*\/*\>/is".BX_UTF_PCRE_MODIFIER,"", $text);
		$text = preg_replace("/\<(\w+)[^>]*\>(.+?)\<\/\\1[^>]*\>/is".BX_UTF_PCRE_MODIFIER,"\\2",$text);
		$text = preg_replace("/\<*\/li\>/is".BX_UTF_PCRE_MODIFIER,"", $text);

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
			for ($i = 0, $l = count($users); $i < $l; $i++)
			{
				$users[$i]['FORMATTED_NAME'] = CCalendar::GetUserName($users[$i]);
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

	public static function GetFromToHtml($fromTs = false, $toTs = false, $skipTime = false, $dtLength = 0, $forRrule = false)
	{
		if (intval($fromTs) != $fromTs)
		{
			$fromTs = self::Timestamp($fromTs);
		}
		if (intval($toTs) != $toTs)
		{
			$toTs = self::Timestamp($toTs);
		}
		if ($toTs < $fromTs)
		{
			$toTs = $fromTs;
		}

		// Formats
		$formatShort = CCalendar::DFormat(false);
		$formatFull = CCalendar::DFormat(true);
		$formatTime = str_replace($formatShort, '', $formatFull);
		$formatTime = $formatTime == $formatFull ? "H:i" : str_replace(':s', '', $formatTime);
		$html = '';

		$formatFull = str_replace(':s', '', $formatFull);

		if ($skipTime)
		{
			if ($dtLength == self::DAY_LENGTH || !$dtLength) // One full day event
			{
				if (!$forRrule)
				{
					$html = FormatDate(array(
						"tommorow" => "tommorow",
						"today" => "today",
						"yesterday" => "yesterday",
						"-" => $formatShort,
						"" => $formatShort,
					), $fromTs, time() + CTimeZone::GetOffset());
					$html .= ', ';
				}

				$html .= Loc::getMessage('EC_VIEW_FULL_DAY');
			}
			else // Event for several days
			{
				$from = FormatDate(array(
					"tommorow" => "tommorow",
					"today" => "today",
					"yesterday" => "yesterday",
					"-" => $formatShort,
					"" => $formatShort,
				), $fromTs, time() + CTimeZone::GetOffset());

				$to = FormatDate(array(
					"tommorow" => "tommorow",
					"today" => "today",
					"yesterday" => "yesterday",
					"-" => $formatShort,
					"" => $formatShort,
				), $toTs - self::DAY_LENGTH, time() + CTimeZone::GetOffset());

				$html = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => $from, '#DATE_TO#' => $to));
			}
		}
		else
		{
			// Event during one day
			if(date('dmY', $fromTs) == date('dmY', $toTs))
			{
				if (!$forRrule)
				{
					$html = FormatDate(array(
						"tommorow" => "tommorow",
						"today" => "today",
						"yesterday" => "yesterday",
						"-" => $formatShort,
						"" => $formatShort,
					), $fromTs, time() + CTimeZone::GetOffset());
					$html .= ', ';
				}

				$html .= Loc::getMessage('EC_VIEW_TIME_FROM_TO_TIME', array('#TIME_FROM#' => FormatDate($formatTime, $fromTs), '#TIME_TO#' => FormatDate($formatTime, $toTs)));
			}
			else
			{
				$html = Loc::getMessage('EC_VIEW_DATE_FROM_TO', array('#DATE_FROM#' => FormatDate($formatFull, $fromTs, time() + CTimeZone::GetOffset()), '#DATE_TO#' => FormatDate($formatFull, $toTs, time() + CTimeZone::GetOffset())));
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
				'DEST_SORT' => CSocNetLogDestination::GetDestinationSort(array("DEST_CONTEXT" => \Bitrix\Calendar\Util::getUserSelectorContext()))
			);

			CSocNetLogDestination::fillLastDestination($DESTINATION['DEST_SORT'], $DESTINATION['LAST']);
		}
		else
		{
			$DESTINATION = array(
				'LAST' => array(
					'SONETGROUPS' => CSocNetLogDestination::GetLastSocnetGroup(),
					'DEPARTMENT' => CSocNetLogDestination::GetLastDepartment(),
					'USERS' => CSocNetLogDestination::GetLastUser()
				)
			);
		}

		if (!$user_id)
			$user_id = CCalendar::GetCurUserId();

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
			if (mb_substr($code, 0, 2) == 'DR')
			{
				$DESTINATION['SELECTED'][$code] = "department";
			}
			elseif (mb_substr($code, 0, 2) == 'UA')
			{
				$DESTINATION['SELECTED'][$code] = "groups";
			}
			elseif (mb_substr($code, 0, 2) == 'SG')
			{
				$DESTINATION['SELECTED'][$code] = "sonetgroups";
			}
			elseif (mb_substr($code, 0, 1) == 'U')
			{
				$DESTINATION['SELECTED'][$code] = "users";
				$destinationUserList[] = intval(str_replace('U', '', $code));
			}
		}

		// intranet structure
		$arStructure = CSocNetLogDestination::GetStucture();
		$DESTINATION['DEPARTMENT'] = $arStructure['department'];
		$DESTINATION['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$DESTINATION['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		if (Loader::includeModule('extranet') && !CExtranet::IsIntranetUser(SITE_ID, $userId))
		{
			$DESTINATION['EXTRANET_USER'] = 'Y';
			$DESTINATION['USERS'] = CSocNetLogDestination::GetExtranetUser();
			$DESTINATION['USERS'] = array_merge($DESTINATION['USERS'], CSocNetLogDestination::GetUsers(['id' => [$userId]]));
		}
		else
		{
			if (is_array($DESTINATION['LAST']['USERS']))
			{
				foreach ($DESTINATION['LAST']['USERS'] as $value)
				{
					$destinationUserList[] = intval(str_replace('U', '', $value));
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
			if ($entry['isExtranet'] == 'N')
				$users[$key] = $entry;
		}
		$DESTINATION['USERS'] = $users;

		return $DESTINATION;
	}

	public static function SaveUserTimezoneName($user, $tzName = '')
	{
		if (!is_array($user) && intval($user) > 0)
			$user = self::GetUser($user, true);

		CUserOptions::SetOption("calendar", "timezone".self::GetCurrentOffsetUTC($user['ID']), $tzName, false, $user['ID']);
	}

	public static function CheckOffsetForTimezone($timezone, $offset, $date = false)
	{
		return true;
	}

	public static function GetOffsetUTC($userId = false, $dateTimestamp)
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
			$offset = date("Z", $dateTimestamp) + CCalendar::GetOffset($userId);
		}
		return intval($offset);
	}

	public static function OnSocNetGroupDelete($groupId)
	{
		$groupId = intval($groupId);
		if ($groupId > 0)
		{
			$res = CCalendarSect::GetList(
				array(
					'arFilter' => array(
						'CAL_TYPE' => 'group',
						'OWNER_ID' => $groupId
					),
					'checkPermissions' => false
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

		static $arAgentsMap = array(
				'android' => 'android', // Android/iOS CardDavBitrix24
				'iphone' => 'iphone', // Apple iPhone iCal
				'davkit' => 'mac', // Apple iCal
				'mac os' => 'mac', // Apple iCal (Mac Os X > 10.8)
				'mac_os_x' => 'mac', // Apple iCal (Mac Os X > 10.8)
				'mac+os+x' => 'mac', // Apple iCal (Mac Os X > 10.10)
				'macos' => 'mac', // Apple iCal (Mac Os X > 11)
				'dataaccess' => 'iphone', // Apple addressbook iPhone
				//'sunbird' => 'sunbird', // Mozilla Sunbird
				'ios' => 'iphone'
		);

		foreach ($arAgentsMap as $pattern => $name)
		{
			if (mb_strpos($userAgent, $pattern) !== false)
			{
				$agent = $name;
				break;
			}
		}

		if ($entityType == 'user' && $agent)
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
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook', 'office365');
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
						'syncTimestamp' => time(),
						'status' => true,
						'type' => $syncType,
						'connected' => true,
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
						'syncTimestamp' => time(),
						'status' => true,
						'type' => $syncType,
						'connected' => true,
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
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook', 'office365');
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
		if ($users == false)
			$users = array(self::GetCurUserId());
		elseif(!is_array($users))
			$users = array($users);

		$ids = [];
		foreach($users as $user)
		{
			if (intval($user) > 0)
				$ids[] = intval($user);
		}
		$users = $ids;

		if (count($users) > 0)
		{
			$events = CCalendarEvent::GetList(array(
				'arFilter' => array(
					'CAL_TYPE' => 'user',
					'OWNER_ID' => $users,
					'FROM_LIMIT' => self::Date(time(), false),
					'TO_LIMIT' => self::Date(time() + self::DAY_LENGTH * 90, false),
					'IS_MEETING' => 1,
					'MEETING_STATUS' => 'Q',
					'DELETED' => 'N'
				),
				'parseRecursion' => false,
				'checkPermissions' => false)
			);

			$counters = [];
			foreach($events as $event)
			{
				if(!isset($counters[$event['OWNER_ID']]))
					$counters[$event['OWNER_ID']] = 0;

				$counters[$event['OWNER_ID']]++;
			}

			foreach($users as $user)
			{
				if($user > 0)
				{
					if(isset($counters[$user]) && $counters[$user] > 0)
						CUserCounter::Set($user, 'calendar', $counters[$user], '**', '', false);
					else
						CUserCounter::Set($user, 'calendar', 0, '**', '', false);
				}
			}
		}
	}

	// TODO: cache it!!!!!!
	private static function __tzsort($a, $b)
	{
		if($a['offset'] == $b['offset'])
			return strcmp($a['timezone_id'], $b['timezone_id']);
		return ($a['offset'] < $b['offset']? -1 : 1);
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
			self::$curUserId = (is_object($USER) && $USER->IsAuthorized())
					? (int)$USER->GetId()
					: 0
			;
		}

		return (int)self::$curUserId;
	}

	public static function GetSettings($params = [])
	{
		if (!is_array($params))
			$params = [];
		if (isset(self::$settings) && count(self::$settings) > 0 && $params['request'] === false)
			return self::$settings;

		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if ($params['forseGetSitePathes'] || !$pathes_for_sites)
			$pathes = self::GetPathes(isset($params['site']) ? $params['site'] : false);
		else
			$pathes = [];

		if (!isset($params['getDefaultForEmpty']) || $params['getDefaultForEmpty'] !== false)
			$params['getDefaultForEmpty'] = true;

		$siteId = isset($params['site']) ? $params['site'] : SITE_ID;
		$resMeetingCommonForSites = COption::GetOptionString('calendar', 'rm_for_sites', true);
		$siteIdForResMeet = !$resMeetingCommonForSites && $siteId ? $siteId : false;

		self::$settings = array(
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
			'rm_for_sites' => COption::GetOptionString('calendar', 'rm_for_sites', true)
		);

		$arPathes = self::GetPathesList();
		foreach($arPathes as $pathName)
		{
			if (!isset(self::$settings[$pathName]))
				self::$settings[$pathName] = COption::GetOptionString('calendar', $pathName, "");
		}

		if(self::$settings['work_time_start'] > 23)
			self::$settings['work_time_start'] = 23;
		if (self::$settings['work_time_end'] <= self::$settings['work_time_start'])
			self::$settings['work_time_end'] = self::$settings['work_time_start'] + 1;
		if (self::$settings['work_time_end'] > 23.30)
			self::$settings['work_time_end'] = 23.30;

		if (self::$settings['forum_id'] == "")
		{
			self::$settings['forum_id'] = COption::GetOptionString("tasks", "task_forum_id", "");
			if (self::$settings['forum_id'] == "" && Loader::includeModule("forum"))
			{
				$db = CForumNew::GetListEx();
				if ($ar = $db->GetNext())
					self::$settings['forum_id'] = $ar["ID"];
			}
			COption::SetOptionString("calendar", "forum_id", self::$settings['forum_id']);
		}

		return self::$settings;
	}

	public static function GetPathes($forSite = false)
	{
		$pathes = [];
		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if ($forSite === false)
		{
			$arAffectedSites = COption::GetOptionString('calendar', 'pathes_sites', false);

			if ($arAffectedSites != false && CheckSerializedData($arAffectedSites))
				$arAffectedSites = unserialize($arAffectedSites, ['allowed_classes' => false]);
		}
		else
		{
			if (is_array($forSite))
				$arAffectedSites = $forSite;
			else
				$arAffectedSites = array($forSite);
		}

		if(is_array($arAffectedSites) && count($arAffectedSites) > 0)
		{
			foreach($arAffectedSites as $s)
			{
				$ar = COption::GetOptionString("calendar", 'pathes_'.$s, false);
				if ($ar != false && CheckSerializedData($ar))
				{
					$ar = unserialize($ar, ['allowed_classes' => false]);
					if(is_array($ar))
						$pathes[$s] = $ar;
				}
			}
		}

		if ($forSite !== false)
		{
			$result = [];
			if (isset($pathes[$forSite]) && is_array($pathes[$forSite]))
				$result = $pathes[$forSite];

			$arPathes = self::GetPathesList();
			foreach($arPathes as $pathName)
			{
				$val = $result[$pathName];
				if (!isset($val) || empty($val) || $pathes_for_sites)
				{
					if (!isset($SET))
						$SET = self::GetSettings();
					$val = $SET[$pathName];
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
			for ($i = 0, $l = count($arTypes); $i < $l; $i++)
			{
				if ($arTypes[$i]['XML_ID'] !== 'user' && $arTypes[$i]['XML_ID'] !== 'group')
				{
					self::$pathesList[] = 'path_to_type_'.$arTypes[$i]['XML_ID'];
				}
			}
		}
		return self::$pathesList;
	}

	public static function GetUserNameTemplate($fromSite = true)
	{
		$user_name_template = COption::GetOptionString('calendar', 'user_name_template', '');
		if ($fromSite && empty($user_name_template))
			$user_name_template = CSite::GetNameFormat(false);
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
		$type = isset($Params['type']) ? $Params['type'] : self::$type;
		$ownerId = isset($Params['ownerId']) ? $Params['ownerId'] : self::$ownerId;
		$userId = isset($Params['userId']) ? $Params['userId'] : self::$userId;

		$bView = true;
		$bEdit = true;
		$bEditSection = true;

		if ($type == 'user' && $ownerId != $userId)
		{
			$bEdit = false;
			$bEditSection = false;
		}

		if ($type == 'group')
		{
			if (!$USER->CanDoOperation('edit_php'))
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

				if (in_array($keyOwner, $codes))// Is owner
				{
					$bEdit = true;
					$bEditSection = true;
				}
				elseif(in_array($keyMod, $codes) && !self::$isArchivedGroup)// Is moderator
				{
					$bEdit = true;
					$bEditSection = true;
				}
				elseif(in_array($keyMember, $codes) && !self::$isArchivedGroup)// Is member
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
		}

		if ($type != 'user' && $type != 'group')
		{
			$bView = CCalendarType::CanDo('calendar_type_view', $type);
			$bEdit = CCalendarType::CanDo('calendar_type_edit', $type);
			$bEditSection = CCalendarType::CanDo('calendar_type_edit_section', $type);
		}

		if ($Params['setProperties'] !== false)
		{
			self::$perm['view'] = $bView;
			self::$perm['edit'] = $bEdit;
			self::$perm['section_edit'] = $bEditSection;
		}

		return array(
			'view' => $bView,
			'edit' => $bEdit,
			'section_edit' => $bEditSection
		);
	}

	public static function GetPath($type = '', $ownerId = '', $hard = false)
	{
		if (self::$path == '' || $hard)
		{
			$path = '';
			if (empty($type))
				$type = self::$type;
			if (!empty($type))
			{
				if ($type === 'user')
				{
					$path = COption::GetOptionString(
						'calendar',
						'path_to_user_calendar',
						COption::getOptionString('socialnetwork', 'user_page', "/company/personal/")."user/#user_id#/calendar/"
					);
				}
				elseif($type === 'group')
				{
					$path = COption::GetOptionString(
						'calendar',
						'path_to_group_calendar',
						COption::getOptionString('socialnetwork', 'workgroups_page', "/workgroups/")."group/#group_id#/calendar/"
					);
				}

				if (!COption::GetOptionString('calendar', 'pathes_for_sites', true))
				{
					$siteId = self::GetSiteId();
					$pathes = self::GetPathes();
					if (isset($pathes[$siteId]))
					{
						if ($type == 'user' && isset($pathes[$siteId]['path_to_user_calendar']))
							$path = $pathes[$siteId]['path_to_user_calendar'];
						elseif($type == 'group' && isset($pathes[$siteId]['path_to_group_calendar']))
							$path = $pathes[$siteId]['path_to_group_calendar'];
					}
				}

				if (empty($ownerId))
					$ownerId = self::$ownerId;

				if (!empty($path) && !empty($ownerId))
				{
					if ($type == 'user')
						$path = str_replace(array('#user_id#', '#USER_ID#'), $ownerId, $path);
					elseif($type == 'group')
						$path = str_replace(array('#group_id#', '#GROUP_ID#'), $ownerId, $path);
				}

				$path = CCalendar::GetServerPath().$path;
			}
		}
		else
		{
			$path = self::$path;
		}

		return $path;
	}

	public static function GetSiteId()
	{
		if (!self::$siteId)
			self::$siteId = SITE_ID;
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
		if (defined("SITE_SERVER_NAME") && SITE_SERVER_NAME <> '')
			$server_name = SITE_SERVER_NAME;
		if (!$server_name)
			$server_name = COption::GetOptionString("main", "server_name", "");
		if (!$server_name)
			$server_name = $_SERVER['HTTP_HOST'];
		$server_name = rtrim($server_name, '/');
		if (!preg_match('/^[a-z0-9\.\-]+$/i', $server_name)) // cyrillic domain hack
		{
			$converter = new CBXPunycode(defined('BX_UTF') && BX_UTF === true ? 'UTF-8' : 'windows-1251');
			$host = $converter->Encode($server_name);
			if (!preg_match('#--p1ai$#', $host)) // trying to guess
				$host = $converter->Encode(CharsetConverter::ConvertCharset($server_name, 'utf-8', 'windows-1251'));
			$server_name = $host;
		}

		return $server_name;
	}

	public static function GetStartUpEvent($eventId = false)
	{
		if ($eventId)
		{
			$res = CCalendarEvent::GetList(
				array(
					'arFilter' => array(
						"PARENT_ID" => $eventId,
						"OWNER_ID" => self::$userId,
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

			if (!$res || !is_array($res[0]))
			{
				$res = CCalendarEvent::GetList(
					array(
						'arFilter' => array(
							"ID" => $eventId,
							"DELETED" => "N"
						),
						'parseRecursion' => false,
						'userId' => self::$userId,
						'fetchAttendees' => false,
						'fetchMeetings' => true
					)
				);
			}

			if ($res && isset($res[0]) && ($event = $res[0]))
			{
				if ($event['MEETING_STATUS'] == 'Y' || $event['MEETING_STATUS'] == 'N' || $event['MEETING_STATUS'] == 'Q')
				{
					if ($event['IS_MEETING'] && self::$userId == self::$ownerId && self::$type == 'user' && ($_GET['CONFIRM'] == 'Y' || $_GET['CONFIRM'] == 'N'))
					{
						CCalendarEvent::SetMeetingStatus(array(
							'userId' => self::$userId,
							'eventId' => $event['ID'],
							'status' => $_GET['CONFIRM'] == 'Y' ? 'Y' : 'N',
							'personalNotification' => true
						));
					}
				}

				if ($event['RRULE'])
					$event['RRULE'] = CCalendarEvent::ParseRRULE($event['RRULE']);

				$event['~userIndex'] = CCalendarEvent::getUserIndex();

				return $event;
			}
			else
			{
				CCalendarNotify::ClearNotifications($eventId);
			}
		}

		return false;
	}

	public static function Timestamp($date, $bRound = true, $bTime = true)
	{
		$timestamp = MakeTimeStamp($date, self::TSFormat($bTime ? "FULL" : "SHORT"));
		if ($bRound)
			$timestamp = self::RoundTimestamp($timestamp);
		return $timestamp;
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
			$type = self::$type;
		if(!$ownerId)
			$ownerId = self::$ownerId;
		if(!$userId)
			$userId = self::$userId;

		return $type == 'user' && $ownerId == $userId;
	}

	public static function IsExchangeEnabled($userId = false)
	{
		if (isset(self::$arExchEnabledCache[$userId]))
			return self::$arExchEnabledCache[$userId];

		if (!IsModuleInstalled('dav') || COption::GetOptionString("dav", "agent_calendar") != "Y")
			$res = false;
		elseif (!Loader::includeModule('dav'))
			$res = false;
		elseif ($userId === false)
			$res = CDavExchangeCalendar::IsExchangeEnabled();
		else
			$res = CDavExchangeCalendar::IsExchangeEnabled() && CDavExchangeCalendar::IsExchangeEnabledForUser($userId);

		self::$arExchEnabledCache[$userId] = $res;
		return $res;
	}

	public static function isGoogleApiEnabled()
	{
		if (!isset(self::$isGoogleApiEnabled))
		{
			self::$isGoogleApiEnabled = \Bitrix\Main\ModuleManager::isModuleInstalled('socialservices') &&
				(is_null(\Bitrix\Main\Config\Configuration::getValue("calendar_integration")) || \Bitrix\Main\Config\Configuration::getValue("calendar_integration") === self::INTEGRATION_GOOGLE_API);

			if (self::$isGoogleApiEnabled
				&& !self::IsBitrix24()
				&& Loader::includeModule('socialservices'))
			{
				self::$isGoogleApiEnabled = CSocServGoogleOAuth::GetOption('google_appid') !== '' && CSocServGoogleOAuth::GetOption('google_appsecret') !== '';
			}
		}

		return self::$isGoogleApiEnabled;
	}

	public static function IsCalDAVEnabled()
	{
		if (!IsModuleInstalled('dav') || COption::GetOptionString("dav", "agent_calendar_caldav") != "Y")
			return false;
		return Loader::includeModule('dav') && CDavGroupdavClientCalendar::IsCalDAVEnabled();
	}

	public static function IsWebserviceEnabled()
	{
		if (!isset(self::$bWebservice))
			self::$bWebservice = IsModuleInstalled('webservice');
		return self::$bWebservice;
	}

	public static function IsExtranetEnabled()
	{
		if (!isset(self::$bExtranet))
			self::$bExtranet = Loader::includeModule('extranet') && CExtranet::IsExtranetSite();
		return self::$bExtranet;
	}

	public static function GetMeetingRoomList($params = [])
	{
		if (isset(self::$meetingRoomList))
		{
			$meetingRoomList = self::$meetingRoomList;
		}
		else
		{
			$meetingRoomList = [];
			if (!self::IsBitrix24())
			{
				if (!isset($params['RMiblockId']) && !isset($params['VMiblockId']))
				{
					$settings = self::GetSettings();
					if (!self::$pathesForSite)
					{
						self::$pathesForSite = self::GetSettings(array('forseGetSitePathes' => true,'site' =>self::GetSiteId()));
					}
					$RMiblockId = $settings['rm_iblock_id'];
					$pathToMR = self::$pathesForSite['path_to_rm'];
				}
				else
				{
					$RMiblockId = $params['RMiblockId'];
					$pathToMR = $params['pathToMR'];
				}

				if (intval($RMiblockId) > 0 && CIBlock::GetPermission($RMiblockId) >= "R" && self::$allowReserveMeeting)
				{
					$arOrderBy = array("NAME" => "ASC", "ID" => "DESC");
					$arFilter = array("IBLOCK_ID" => $RMiblockId, "ACTIVE" => "Y");
					$arSelectFields = array("IBLOCK_ID","ID","NAME","DESCRIPTION","UF_FLOOR","UF_PLACE","UF_PHONE");
					$res = CIBlockSection::GetList($arOrderBy, $arFilter, false, $arSelectFields );
					while ($arMeeting = $res->GetNext())
					{
						$meetingRoomList[] = array(
							'ID' => $arMeeting['ID'],
							'NAME' => $arMeeting['~NAME'],
							'DESCRIPTION' => $arMeeting['~DESCRIPTION'],
							'UF_PLACE' => $arMeeting['UF_PLACE'],
							'UF_PHONE' => $arMeeting['UF_PHONE'],
							'URL' => str_replace(array("#id#", "#ID#"), $arMeeting['ID'], $pathToMR)
						);
					}
				}
			}

			self::$meetingRoomList = $meetingRoomList;
		}

		return $meetingRoomList;
	}

	public static function GetCurrentOffsetUTC($userId = false)
	{
		if (!$userId && self::$userId)
			$userId = self::$userId;
		return intval(date("Z") + self::GetOffset($userId));
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
		else
		{
			if (!isset(self::$offset))
			{
				$offset = CTimeZone::GetOffset(null, true);
				self::$offset = $offset;
			}
			else
			{
				$offset = self::$offset;
			}
		}
		return $offset;
	}

	public static function GetUserTimezoneName($user, $getDefault = true)
	{
		if (isset(self::$userTimezoneList[$user]) && !is_array($user) && intval($user) > 0)
		{
			return self::$userTimezoneList[$user];
		}
		elseif(is_array($user) && (int)$user['ID'] > 0 && isset(self::$userTimezoneList[$user['ID']]))
		{
			return self::$userTimezoneList[$user['ID']];
		}
		else
		{
			if (!is_array($user) && intval($user) > 0)
			{
				$user = self::GetUser($user, true);
			}

			$offset = self::GetCurrentOffsetUTC($user['ID']);
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

			try
			{
				new DateTimeZone($tzName);
			}
			catch (Exception $e)
			{
				$tzName = false;
			}

			if (!$tzName && $getDefault)
			{
				$tzName = self::GetGoodTimezoneForOffset($offset);
			}

			self::$userTimezoneList[$user['ID']] = $tzName;
		}

		return $tzName;
	}

	public static function GetUser($userId, $bPhoto = false)
	{
		global $USER;
		if (is_object($USER) && (int)$userId === $USER->GetId() && !$bPhoto)
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
		else
		{
			$rsUser = CUser::GetByID((int)$userId);
			$user = $rsUser->Fetch();
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

		if (!$result && count($goodTz) > 0)
		{
			$result = $goodTz[0]['timezone_id'];
		}

		return $result;
	}

	public static function GetTimezoneList()
	{
		if (empty(self::$timezones))
		{
			self::$timezones = [];
			static $aExcept = array("Etc/", "GMT", "UTC", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU");
			foreach(DateTimeZone::listIdentifiers() as $tz)
			{
				foreach($aExcept as $ex)
				{
					if(mb_strpos($tz, $ex) === 0)
					{
						continue 2;
					}
				}
				try
				{
					$oTz = new DateTimeZone($tz);
					self::$timezones[$tz] = array('timezone_id' => $tz, 'offset' => $oTz->getOffset(new DateTime("now", $oTz)));
				}
				catch(Exception $e){}
			}
			uasort(self::$timezones, array('CCalendar', '__tzsort'));

			foreach(self::$timezones as $k => $z)
			{
				self::$timezones[$k]['title'] = '(UTC'.($z['offset'] <> 0? ' '.($z['offset'] < 0? '-':'+').sprintf("%02d", ($h = floor(abs($z['offset'])/3600))).':'.sprintf("%02d", abs($z['offset'])/60 - $h*60) : '').') '.$z['timezone_id'];
			}
		}
		return self::$timezones;
	}

	public static function GetUserName($user)
	{
		if (!is_array($user) && intval($user) > 0)
			$user = self::GetUser($user);
		if(!$user || !is_array($user))
			return '';

		return CUser::FormatName(self::$userNameTemplate, $user, true, false);
	}

	public static function GetWeekStart()
	{
		if (!isset(self::$weekStart))
		{
			$days = array('1' => 'MO', '2' => 'TU', '3' => 'WE', '4' => 'TH', '5' => 'FR', '6' => 'SA', '0' => 'SU');
			self::$weekStart = $days[CSite::GetWeekStart()];

			if (!in_array(self::$weekStart, $days))
				self::$weekStart = 'MO';
		}

		return self::$weekStart;
	}

	public static function InitExternalCalendarsSyncParams(&$JSConfig)
	{
		$isGoogleApiEnabled = CCalendar::isGoogleApiEnabled() && self::$type == "user";
		$googleCalDavStatus = [];
		$googleApiStatus = [];
		$JSConfig['bCalDAV'] = true;
		$JSConfig['caldav_link_all'] = CCalendar::GetServerPath();
		$JSConfig['isRuZone'] = \Bitrix\Calendar\Util::checkRuZone();

		if (self::$type == 'user' && self::IsPersonal() && Loader::includeModule('dav'))
		{
			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
				CTimeZone::Disable();

			$connectionList = [];
			$res = CDavConnection::GetList(
				["ID" => "DESC"],
				[
					"ENTITY_TYPE" => "user",
					"ENTITY_ID" => self::$ownerId,
					'ACCOUNT_TYPE' => [
						Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV,
						Google\Helper::GOOGLE_ACCOUNT_TYPE_API,
						Bitrix\Calendar\Sync\Caldav\Helper::CALDAV_TYPE,
					],
				], false, false);

			if ($isGoogleApiEnabled)
			{
				$googleApiConnection = new GoogleApiSync(self::getCurUserId());
				$transportErrors = $googleApiConnection->getTransportErrors();
				if (!$transportErrors)
				{
					$googleApiStatus['googleCalendarPrimaryId'] = $googleApiConnection->getPrimaryId();

					$curPath = CCalendar::GetPath();
					if($curPath)
						$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
					$client = new CSocServGoogleOAuth(self::$ownerId);
					$client->getEntityOAuth()->addScope(array(
						'https://www.googleapis.com/auth/calendar',
						'https://www.googleapis.com/auth/calendar.readonly'
					));
					$JSConfig['googleAuthLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
				}
			}
			else
			{
				$googleCalDavStatus = CCalendarSync::GetGoogleCalendarConnection();
			}

			$googleApiConnections = [];
			while($connection = $res->Fetch())
			{
				$connectionListItem = array(
					'id' => $connection['ID'],
					'server_host' => $connection['SERVER_HOST'],
					'account_type' => $connection['ACCOUNT_TYPE'],
					'name' => $connection['NAME'],
					'link' => $connection['SERVER'],
					'user_name' => $connection['SERVER_USERNAME'] ? $connection['SERVER_USERNAME'] : $googleApiStatus['googleCalendarPrimaryId'],
					'last_result' => $connection['LAST_RESULT'],
					'sync_date' => $connection['SYNCHRONIZED']
				);

				$connectionList[] = $connectionListItem;
				/** @var Google\Helper $googleHelper */
				$googleHelper = ServiceLocator::getInstance()->get('calendar.service.google.helper');
				if ($googleHelper->isGoogleConnection($connection['ACCOUNT_TYPE']))
				{
					$googleApiConnections[] = $connectionListItem;
				}
				else
				{
					$JSConfig['caldav'][$connection['ID']] = [
						'active' => true,
						'connected' => $connection['LAST_RESULT'] == '[200] OK' ? true : false,
						'syncDate' => $connection['SYNCHRONIZED'],
						'syncTimestamp' => (new Type\DateTime($JSConfig['googleCalDavStatus']['sync_date'], Type\Date::convertFormatToPhp(FORMAT_DATETIME)))->getTimestamp(),
						'name' => $connection['NAME'],
						'user_name' => $connection['SERVER_USERNAME'],
					];
				}
			}

			$addConnection = true;

			if ($isGoogleApiEnabled)
			{
				if (empty($googleApiConnections))
				{
					$googleApiConnection = new GoogleApiSync(self::getCurUserId());
					$transportErrors = $googleApiConnection->getTransportErrors();
					if (!$transportErrors)
					{
						$googleApiStatus['googleCalendarPrimaryId'] = $googleApiConnection->getPrimaryId();

						$curPath = CCalendar::GetPath();
						if($curPath)
							$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
						$client = new CSocServGoogleOAuth(self::$ownerId);
						$client->getEntityOAuth()->addScope(array(
							'https://www.googleapis.com/auth/calendar',
							'https://www.googleapis.com/auth/calendar.readonly'
						));
						$JSConfig['googleAuthLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
					}
				}

				if ($googleApiStatus['googleCalendarPrimaryId'] || !empty($googleApiConnections))
				{
					$serverPath = Google\Helper::GOOGLE_SERVER_PATH_V3;
					foreach ($googleApiConnections as $connection)
					{
						if (($connection['link'] == $serverPath
								|| $connection['account_type'] === Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV)
							|| $connection['account_type'] === Google\Helper::GOOGLE_ACCOUNT_TYPE_API)
						{
							$googleApiStatus['last_result'] = $connection['last_result'];
							$googleApiStatus['sync_date'] = CCalendar::Date(self::Timestamp($connection['sync_date']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
							$googleApiStatus['connection_id'] = $connection['id'];
							$addConnection = false;
							break;
						}
					}

					if ($addConnection)
					{
						$sAccountType = Bitrix\Calendar\Sync\Google\Helper::GOOGLE_ACCOUNT_TYPE_API;
						$sServer = Google\Helper::GOOGLE_SERVER_PATH_V3;
						$connectionData = array(
							"ENTITY_TYPE" => 'user',
							"ENTITY_ID" => self::$ownerId,
							"ACCOUNT_TYPE" => $sAccountType,
							"NAME" => 'Google Calendar ('.$googleApiStatus['googleCalendarPrimaryId'].')',
							"SERVER" => $sServer,
							"SYNC_TOKEN" => NULL,
						);
						$conId = CDavConnection::Add($connectionData);

						if ($conId)
						{
							CCalendarSync::dataSync(array_merge($connectionData, ['ID' => $conId]));

							$pushOptionEnabled = COption::GetOptionString('calendar', 'sync_by_push', false);
							if ($pushOptionEnabled || CCalendar::IsBitrix24())
							{
								$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID'], $conId);
								$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
								if ($channelInfo)
								{
									PushTable::delete(array("ENTITY_TYPE" => 'CONNECTION', 'ENTITY_ID' => $conId));
									PushTable::add(array(
										'ENTITY_TYPE' => 'CONNECTION',
										'ENTITY_ID' => $conId,
										'CHANNEL_ID' => $channelInfo['id'],
										'RESOURCE_ID' => $channelInfo['resourceId'],
										'EXPIRES' => $channelInfo['expiration'],
										'NOT_PROCESSED' => 'N'
									));
								}
							}
							$res = CDavConnection::GetList(["ID" => "DESC"], ["ID" => $conId], false, false);
							if ($connection = $res->Fetch())
							{
								$connectionList[] = array(
									'id' => $connection['ID'],
									'server_host' => $connection['SERVER_HOST'],
									'account_type' => $connection['ACCOUNT_TYPE'],
									'name' => $connection['NAME'],
									'link' => $connection['SERVER'],
									'user_name' => $connection['SERVER_USERNAME'] ? $connection['SERVER_USERNAME'] : $googleCalDavStatus['googleCalendarPrimaryId'],
									'last_result' => $connection['LAST_RESULT'],
									'sync_date' => $connection['SYNCHRONIZED']
								);
								$googleApiStatus['connection_id'] = $connection['ID'];
								$googleApiStatus['last_result'] = $googleApiConnections['LAST_RESULT'];
								$googleApiStatus['sync_date'] = CCalendar::Date(self::Timestamp($connection['SYNCHRONIZED']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
							}
						}
					}
				}

				$JSConfig['googleCalDavStatus'] = $googleApiStatus;
			}
			else if ($googleCalDavStatus && $googleCalDavStatus['googleCalendarPrimaryId'])
			{
				$serverPath = Google\Helper::GOOGLE_SERVER_PATH_V2.$googleCalDavStatus['googleCalendarPrimaryId'].'/user';

				foreach($connectionList as $connection)
				{
					if ($connection['link'] == $serverPath)
					{
						$googleCalDavStatus['last_result'] = $connection['last_result'];
						$googleCalDavStatus['sync_date'] = CCalendar::Date(self::Timestamp($connection['sync_date']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
						$googleCalDavStatus['connection_id'] = $connection['id'];

						$addConnection = false;
						break;
					}
				}

				if ($addConnection)
				{
					$conId = CDavConnection::Add(array(
						"ENTITY_TYPE" => 'user',
						"ENTITY_ID" => self::$ownerId,
						"ACCOUNT_TYPE" => Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV,
						"NAME" => 'Google Calendar ('.$googleCalDavStatus['googleCalendarPrimaryId'].')',
						"SERVER" => Google\Helper::GOOGLE_SERVER_PATH_V2.$googleCalDavStatus['googleCalendarPrimaryId'].'/user'
					));

					if ($conId)
					{
						CDavGroupdavClientCalendar::DataSync("user", self::$ownerId);
						$res = CDavConnection::GetList(array("ID" => "DESC"), array("ID" => $conId), false, false);
						if($connection = $res->Fetch())
						{
							$connectionList[] = array(
								'id' => $connection['ID'],
								'server_host' => $connection['SERVER_HOST'],
								'account_type' => $connection['ACCOUNT_TYPE'],
								'name' => $connection['NAME'],
								'link' => $connection['SERVER'],
								'user_name' => $connection['SERVER_USERNAME'] ? $connection['SERVER_USERNAME'] : $googleCalDavStatus['googleCalendarPrimaryId'],
								'last_result' => $connection['LAST_RESULT'],
								'sync_date' => $connection['SYNCHRONIZED']
							);
							$googleCalDavStatus['connection_id'] = $connection['ID'];
							$googleCalDavStatus['last_result'] = $connection['LAST_RESULT'];
							$googleCalDavStatus['sync_date'] = CCalendar::Date(self::Timestamp($googleApiConnections['SYNCHRONIZED']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
						}
					}
				}

				$JSConfig['googleCalDavStatus'] = $googleCalDavStatus;
			}
			$JSConfig['connections'] = $connectionList;

			if ($tzEnabled)
				CTimeZone::Enable();
		}
	}

	public static function Date($timestamp, $bTime = true, $bRound = true, $bCutSeconds = false)
	{
		if ($bRound)
			$timestamp = self::RoundTimestamp($timestamp);

		$format = self::DFormat($bTime);
		if ($bTime && $bCutSeconds)
			$format = str_replace(':s', '', $format);
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

	public static function GetSyncInfo($userId, $syncType)
	{
		$activeSyncPeriod = 604800; // 3600 * 24 * 7 - one week
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook', 'office365');
		$result = array('connected' => false);

		if (in_array($syncType, $syncTypes))
		{
			$result['date'] = CUserOptions::GetOption("calendar", "last_sync_".$syncType, false, $userId);
		}

		if ($result['date'])
		{
			$result['date'] = CCalendar::Date(self::Timestamp($result['date']) + CCalendar::GetOffset($userId), true, true, true);

			$period = time() - self::Timestamp($result['date']);
			if ($period <= $activeSyncPeriod)
			{
				$result['connected'] = true;
			}
		}

		return $result;
	}

	public static function GetCurUserMeetingSection($bCreate = false)
	{
		if (!isset(self::$userMeetingSection) || !self::$userMeetingSection)
			self::$userMeetingSection = CCalendar::GetMeetingSection(self::$userId, $bCreate);
		return self::$userMeetingSection;
	}

	public static function GetMeetingSection($userId, $autoCreate = false)
	{
		if (isset(self::$meetingSections[$userId]))
			return self::$meetingSections[$userId];

		$result = false;
		if ($userId > 0)
		{
			$set = UserSettings::get($userId);

			$result = $set['meetSection'];
			$meetingSectionId = $result;
			$section = false;

			if ($result)
			{
				$section = CCalendarSect::GetList(
					['arFilter' => [
						'ID' => $result,
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId,
						'ACTIVE' => 'Y'
					],
					'checkPermissions' => false,
					'getPermissions' => false
				]);
				if($section && is_array($section) && is_array($section[0]))
				{
					$section = $section[0];
				}
			}

			if($result && !$section)
				$result = false;

			if (!$result)
			{
				$res = CCalendarSect::GetList([
					'arFilter' => [
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId,
						'ACTIVE' => 'Y'
					],
					'checkPermissions' => false,
					'getPermissions' => false
				]);
				if ($res && count($res) > 0 && $res[0]['ID'])
					$result = $res[0]['ID'];

				if (!$result && $autoCreate)
				{
					$defCalendar = CCalendarSect::CreateDefault(array(
						'type' => 'user',
						'ownerId' => $userId
					));
					if ($defCalendar && $defCalendar['ID'] > 0)
						$result = $defCalendar['ID'];
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
			return self::$crmSections[$userId];

		$result = false;
		if ($userId > 0)
		{
			$set = UserSettings::get($userId);

			$result = $set['crmSection'];
			$section = false;

			if ($result)
			{
				$section = CCalendarSect::GetList([
					'arFilter' => [
						'ID' => $result,
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId
					],
					'checkPermissions' => false,
					'getPermissions' => false
				]);
				if($section && is_array($section) && is_array($section[0]))
				{
					$section = $section[0];
				}
			}

			if($result && !$section)
				$result = false;

			if (!$result)
			{
				$res = CCalendarSect::GetList([
					'arFilter' => [
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId
					],
					'checkPermissions' => false,
					'getPermissions' => false
				]);
				if ($res && count($res) > 0 && $res[0]['ID'])
					$result = $res[0]['ID'];

				if (!$result && $autoCreate)
				{
					$defCalendar = CCalendarSect::CreateDefault(array(
						'type' => 'user',
						'ownerId' => $userId
					));
					if ($defCalendar && $defCalendar['ID'] > 0)
						$result = $defCalendar['ID'];
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
		$type = isset($params['CAL_TYPE']) ? $params['CAL_TYPE'] : self::$type;

		$arFilter = [
			'CAL_TYPE' => $type
		];

		if (isset($params['OWNER_ID']))
		{
			$arFilter['OWNER_ID'] = $params['OWNER_ID'];
		}
		elseif ($type == 'user' || $type == 'group')
		{
			$arFilter['OWNER_ID'] = self::GetOwnerId();
		}

		if (isset($params['ACTIVE']))
		{
			$arFilter['ACTIVE'] = $params['ACTIVE'];
		}

		if (isset($params['ADDITIONAL_IDS']) && count($params['ADDITIONAL_IDS']) > 0)
		{
			$arFilter['ADDITIONAL_IDS'] = $params['ADDITIONAL_IDS'];
		}

		$sectionList = CCalendarSect::GetList([
			'arFilter' => $arFilter,
			'checkPermissions' => $params['checkPermissions'],
			'getPermissions' => $params['getPermissions']
		]);

		if ($params['getImages'])
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

			if (count($userIdList))
			{
				$userIndexList = \CCalendarEvent::getUsersDetails($userIdList);
			}

			if (count($groupIdList) && Loader::includeModule("socialnetwork"))
			{
				$res = Bitrix\Socialnetwork\WorkgroupTable::getList([
					'filter' => [
						'=ACTIVE' => 'Y',
						'@ID' => $groupIdList
					],
					'select' => ['ID', 'IMAGE_ID']
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
				$ownerId = intval($section['OWNER_ID']);
				if ($section['CAL_TYPE'] === 'user'
					&& isset($userIndexList[$ownerId])
					&& !empty($userIndexList[$ownerId]['AVATAR'])
					&& $userIndexList[$ownerId]['AVATAR'] !== '/bitrix/images/1.gif'
				)
				{
					$sectionList[$k]['IMAGE'] = $userIndexList[$ownerId]['AVATAR'];
				}
				elseif ($section['CAL_TYPE'] === 'group'
					&& isset($groupListIndex[$ownerId])
					&& !empty($groupListIndex[$ownerId]['IMAGE']))
				{
					$sectionList[$k]['IMAGE'] = $groupListIndex[$ownerId]['IMAGE'];
				}

				$pathesForSite = \CCalendar::getPathes(SITE_ID);
				if ($section['CAL_TYPE'] === 'user')
				{
					$sectionList[$k]['LINK'] = str_replace(
						['#user_id#', '#USER_ID#'],
						$section['OWNER_ID'],
						$pathesForSite['path_to_user_calendar']
					);
				}
				elseif($section['CAL_TYPE'] === 'group')
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
				}
				$sectionList[$k]['LINK'] = $path;
			}
		}

		return $sectionList;
	}

	public static function GetOwnerId()
	{
		return self::$ownerId;
	}

	public static function GetEventList($params = [], &$arAttendees)
	{
		$type = isset($params['type']) ? $params['type'] : self::$type;
		$ownerId = isset($params['ownerId']) ? $params['ownerId'] : self::$ownerId;
		$userId = isset($params['userId']) ? $params['userId'] : self::$userId;

		if ($type != 'user' && !isset($params['section']) || count($params['section']) <= 0)
			return [];

		$arFilter = [];

		//CCalendarEvent::SetLastAttendees(false);

		if (isset($params['fromLimit']))
			$arFilter["FROM_LIMIT"] = $params['fromLimit'];
		if (isset($params['toLimit']))
			$arFilter["TO_LIMIT"] = $params['toLimit'];

		$arFilter["OWNER_ID"] = $ownerId;

		if ($type == 'user')
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

		$res = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'userId' => $userId,
				'fetchMeetings' => $fetchMeetings,
				'setDefaultLimit' => false,
				'limit' => $params['limit']
			)
		);

		if (count($params['section']) > 0)
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

	public static function getTaskList($params = [])
	{
		$res = [];
		if (Loader::includeModule('tasks'))
		{
			$userSettings = Bitrix\Calendar\UserSettings::get();

			$arFilter = [
				'!STATUS' => [
					CTasks::STATE_DEFERRED,
				],
				'CHECK_PERMISSIONS' => 'Y'
			];

			if ($userSettings['showCompletedTasks'] == 'N')
			{
				$arFilter['!STATUS'][] = CTasks::STATE_COMPLETED;
			}

			if ($params['type'] == 'user')
			{
				$arFilter['DOER'] = $params['ownerId'];
			}
			elseif ($params['type'] == 'group')
			{
				$arFilter['GROUP_ID'] = $params['ownerId'];
			}

			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
			{
				CTimeZone::Disable();
			}

			$mgrResult = \Bitrix\Tasks\Manager\Task::getList(
				\Bitrix\Tasks\Util\User::getId(),
				[
					'order' => ["START_DATE_PLAN" => "ASC"],
					'select' => [
						"ID",
						"TITLE",
						"DESCRIPTION",
						"CREATED_DATE",
						"DEADLINE",
						"START_DATE_PLAN",
						"END_DATE_PLAN",
						"DATE_START",
						"CLOSED_DATE",
						"STATUS_CHANGED_DATE",
						"STATUS",
						"REAL_STATUS"
					],
					'legacyFilter' => $arFilter,
				],
				[]
			);

			$offset = CCalendar::GetOffset();
			foreach ($mgrResult['DATA'] as $task)
			{
				$dtFrom = null;
				$dtTo = null;

				$skipFromOffset = false;
				$skipToOffset = false;

				if (isset($task["START_DATE_PLAN"]) && $task["START_DATE_PLAN"])
				{
					$dtFrom = CCalendar::CutZeroTime($task["START_DATE_PLAN"]);
				}

				if (isset($task["END_DATE_PLAN"]) && $task["END_DATE_PLAN"])
				{
					$dtTo = CCalendar::CutZeroTime($task["END_DATE_PLAN"]);
				}

				if (!isset($dtFrom) && isset($task["DATE_START"]))
				{
					$dtFrom = CCalendar::CutZeroTime($task["DATE_START"]);
				}

				if (!isset($dtTo) && isset($task["CLOSED_DATE"]))
				{
					$dtTo = CCalendar::CutZeroTime($task["CLOSED_DATE"]);
				}

				//Task statuses: 1 - New, 2 - Pending, 3 - In Progress, 4 - Supposedly completed, 5 - Completed, 6 - Deferred, 7 - Declined
				if (!isset($dtTo) &&
					isset($task["STATUS_CHANGED_DATE"]) &&
					in_array($task["REAL_STATUS"], ['4', '5', '6', '7']))
				{
					$dtTo = CCalendar::CutZeroTime($task["STATUS_CHANGED_DATE"]);
				}

				if (isset($dtTo))
				{
					$ts = CCalendar::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
					if (date("H:i", $ts) == '00:00')
					{
						$dtTo = CCalendar::Date($ts - 24 * 60 * 60);
					}
				}
				elseif (isset($task["DEADLINE"]))
				{
					$dtTo = CCalendar::CutZeroTime($task["DEADLINE"]);
					$ts = CCalendar::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
					if (date("H:i", $ts) == '00:00')
					{
						$dtTo = CCalendar::Date($ts - 24 * 60 * 60);
					}

					if (!isset($dtFrom))
					{
						$skipFromOffset = true;
						$dtFrom = CCalendar::Date(time(), false);
					}
				}

				if (!isset($dtTo))
				{
					$dtTo = CCalendar::Date(time(), false);
				}

				if (!isset($dtFrom))
				{
					$dtFrom = $dtTo;
				}

				$dtFromTS = CCalendar::Timestamp($dtFrom);
				$dtToTS = CCalendar::Timestamp($dtTo);

				if ($dtToTS < $dtFromTS)
				{
					$dtToTS = $dtFromTS;
					$dtTo = CCalendar::Date($dtToTS, true);
				}

				$skipTime = date("H:i", $dtFromTS) == '00:00' && date("H:i", $dtToTS) == '00:00';
				if (!$skipTime && $offset != 0)
				{
					if (!$skipFromOffset)
					{
						$dtFromTS += $offset;
						$dtFrom = CCalendar::Date($dtFromTS, true);
					}

					if (!$skipToOffset)
					{
						$dtToTS += $offset;
						$dtTo = CCalendar::Date($dtToTS, true);
					}
				}

				$res[] = [
					"ID" => $task["ID"],
					"~TYPE" => "tasks",
					"NAME" => $task["TITLE"],
					"DATE_FROM" => $dtFrom,
					"DATE_TO" => $dtTo,
					"DT_SKIP_TIME" => $skipTime ? 'Y' : 'N',
					"CAN_EDIT" => CTasks::CanCurrentUserEdit($task)
				];
			}

			if ($tzEnabled)
			{
				CTimeZone::Enable();
			}
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

	function TrimTime($strTime)
	{
		$strTime = trim($strTime);
		$strTime = preg_replace("/:00$/", "", $strTime);
		$strTime = preg_replace("/:00$/", "", $strTime);
		$strTime = preg_replace("/\\s00$/", "", $strTime);
		return rtrim($strTime);
	}

	public static function SetSilentErrorMode($silentErrorMode = true)
	{
		self::$silentErrorMode = $silentErrorMode;
	}

	public function GetId()
	{
		return self::$id ? self::$id : 'EC'.rand();
	}

	public static function GetOriginalDate($originalDateTime, $instanceDateTime, $timeZone = false, $format = null)
	{
		CTimeZone::Disable();
		$dateTimeZone = !empty($timeZone) ? new \DateTimeZone($timeZone) : new \DateTimeZone("UTC");
		$parentEvent = new Main\Type\DateTime($originalDateTime, Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME), $dateTimeZone);
		$parentTimestamp = $parentEvent->getTimestamp($parentEvent);
		date_default_timezone_set($timeZone);
		$parentInfoDate = getdate($parentTimestamp);
		$instanceEvent = new Main\Type\DateTime($instanceDateTime, Main\Type\Date::convertFormatToPhp(FORMAT_DATETIME), $dateTimeZone);
		$eventdate = $instanceEvent->setTime($parentInfoDate['hours'], $parentInfoDate['minutes']);

		if ($format === null)
		{
			$date = $eventdate->toString();
		}
		else
		{
			$date = $eventdate;
		}

		CTimeZone::Enable();
		return $date;
	}

	public static function getSectionListAvailableForUser($userId, $additionalSectionIdList = [])
	{
		$sections = CCalendar::GetSectionList([
			'CAL_TYPE' => 'user',
			'OWNER_ID' => $userId,
			'ACTIVE' => 'Y',
			'ADDITIONAL_IDS' => array_merge($additionalSectionIdList, UserSettings::getFollowedSectionIdList($userId))
		]);
		return $sections;
	}

	public static function getSectionListForContext(array $params = []): array
	{
		$userId = isset($params['userId']) ? (int)$params['userId'] : CCalendar::getCurUserId();
		$sections = [];
		$followedSectionList = UserSettings::getFollowedSectionIdList($userId);
		$hiddenSections = UserSettings::getHiddenSections($userId);

		self::$userMeetingSection = CCalendar::GetCurUserMeetingSection();

		$sectionList = self::GetSectionList(
			[
				'ADDITIONAL_IDS' => $followedSectionList,
				'checkPermissions' => true,
				'getPermissions' => true,
				'getImages' => true
			]
		);

		$sectionList = array_merge($sectionList, \CCalendar::getSectionListAvailableForUser($userId));

		$sectionIdList = [];
		foreach ($sectionList as $i => $section)
		{
			if (!in_array(intval($section['ID']), $sectionIdList))
			{
				$sections[] = $section;
				$sectionIdList[] = intval($section['ID']);
			}
		}

		$readOnly = !self::$perm['edit'] && !self::$perm['section_edit'];

		if (self::$type === 'user' && self::$ownerId != self::$userId)
			$readOnly = true;

		if (self::$bAnonym)
			$readOnly = true;

		$bCreateDefault = !self::$bAnonym;

		if (self::$type === 'user')
			$bCreateDefault = self::$ownerId == self::$userId;

		$additonalMeetingsId = [];
		$groupOrUser = self::$type === 'user' || self::$type === 'group';
		if ($groupOrUser)
		{
			$noEditAccessedCalendars = true;
		}

		$trackingUsers = [];
		$trackingGroups = [];

		foreach ($sections as $i => $section)
		{
			$sections[$i]['~IS_MEETING_FOR_OWNER'] = $section['CAL_TYPE'] === 'user' && $section['OWNER_ID'] !== self::$userId && CCalendar::GetMeetingSection($section['OWNER_ID']) === $section['ID'];

			if (!in_array($section['ID'], $hiddenSections, true) && $section['ACTIVE'] !== 'N')
			{
				// It's superposed calendar of the other user and it's need to show user's meetings
				if ($sections[$i]['~IS_MEETING_FOR_OWNER'])
				{
					$additonalMeetingsId[] = array('ID' => $section['OWNER_ID'], 'SECTION_ID' => $section['ID']);
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

			if (self::$bSuperpose && in_array($section['ID'], $followedSectionList))
			{
				$sections[$i]['SUPERPOSED'] = true;
			}

			if ($bCreateDefault && $section['CAL_TYPE'] == self::$type && $section['OWNER_ID'] == self::$ownerId)
				$bCreateDefault = false;

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
			$readOnly = true;

		self::$readOnly = $readOnly;
	}


	public static function setOwnerId($userId)
	{
		self::$ownerId = $userId;
	}

	/**
	 * @param $id
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function stopGoogleConnectionChannels(int $connectionId)
	{
		$pushConnectionChannelsDb = PushTable::getList([
			'select' => ['*'],
			'filter' => [
				'ENTITY_TYPE' => 'CONNECTION',
				'ENTITY_ID' => $connectionId
			],
		]);
		if ($row = $pushConnectionChannelsDb->fetch())
		{
			\Bitrix\Calendar\Sync\GoogleApiPush::stopChannel($row, self::$ownerId);
		}
		return $row;
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
				'CAL_DAV_CON' => $connectionId
			]
		]);
	}

	private static function deleteGoogleConnectionSections(array $sections)
	{
		foreach ($sections as $section)
		{
			self::stopGoogleSectionChannels($section);
			CCalendarSect::Delete($section['ID'], false);
		}
	}

	private static function editGoogleConnectionsSections(array $sections)
	{
		foreach ($sections as $section)
		{
			self::stopGoogleSectionChannels($section);
			self::markSectionLikeDelete($section);
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
		$pushSectionChannelsDb = PushTable::getList([
			'select' => ['*'],
			'filter' => [
				'ENTITY_TYPE' => 'SECTION',
				'ENTITY_ID' => $section['ID'],
			],
		]);
		if ($row = $pushSectionChannelsDb->fetch())
		{
			\Bitrix\Calendar\Sync\GoogleApiPush::stopChannel($row, $section['OWNER_ID']);
		}
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

	/**
	 * @param array $connection
	 * @throws Main\LoaderException
	 */
	private static function removeGoogleAuthToken(array $connection): void
	{
		$googleCalDavStatus = \CCalendarSync::GetGoogleCalendarConnection();
		$serverPath = Google\Helper::GOOGLE_SERVER_PATH_V3;
		if ($googleCalDavStatus['googleCalendarPrimaryId'] && $connection['ACCOUNT_TYPE'] === Google\Helper::GOOGLE_ACCOUNT_TYPE_CALDAV)
		{
			$serverPath = Google\Helper::GOOGLE_SERVER_PATH_V2 . $googleCalDavStatus['googleCalendarPrimaryId'] . '/user';
		}

		if ($connection['SERVER'] === $serverPath)
		{
			if (Loader::includeModule('socialservices'))
			{
				$client = new CSocServGoogleOAuth(CCalendar::GetCurUserId());
				$client->getEntityOAuth()->addScope(['https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly']);

				// Delete stored tokens
				$client->getEntityOAuth()->deleteStorageTokens();
			}
		}
	}
}
