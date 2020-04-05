<?
/** var CMain $APPLICATION */

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Calendar\PushTable;
use Bitrix\Calendar\Sync\GoogleApiSync;

IncludeModuleLangFile(__FILE__);

class CCalendar
{
	const
		INTEGRATION_GOOGLE_API = "googleapi",
		CALENDAR_MAX_TIMESTAMP = 2145938400,
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
		$userSettings = array(),
		$pathToUser,
		$bOwner,
		$userId,
		$curUserId,
		$userMeetingSection,
		$meetingSections = array(),
		$crmSections = array(),
		$offset,
		$arTimezoneOffsets = array(),
		$perm = array(),
		$isArchivedGroup = false,
		$userNameTemplate = "#NAME# #LAST_NAME#",
		$bSuperpose,
		$bCanAddToSuperpose,
		$bExtranet,
		$bIntranet,
		$bWebservice,
		$arSPTypes = array(),
		$showTasks,
		$viewTaskPath = '',
		$editTaskPath = '',
		$actionUrl,
		$path = '',
		$outerUrl,
		$accessNames = array(),
		$bSocNet,
		$bAnonym,
		$allowReserveMeeting = true,
		$SectionsControlsDOMId = 'sidebar',
		$arAccessTask = array(),
		$ownerNames = array(),
		$meetingRoomList,
		$cachePath = "calendar/",
		$cacheTime = 2592000, // 30 days by default
		$bCache = true,
		$readOnly,
		$showLogin = true,
		$pathesForSite = false,
		$pathes = array(), // links for several sites
		$userManagers = array(),
		$arUserDepartment = array(),
		$bAMPM = false,
		$bWideDate = false,
		$arExchEnabledCache = array(),
		$silentErrorMode = false,
		$weekStart,
		$bCurUserSocNetAdmin,
		$serverPath,
		$pathesList = array('path_to_user','path_to_user_calendar','path_to_group','path_to_group_calendar','path_to_vr','path_to_rm'),
		$pathesListEx = null,
		$isGoogleApiEnabled = null,
		$timezones = array();

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
		self::$userId = (isset($params['userId']) && $params['userId'] > 0) ? intVal($params['userId']) : CCalendar::GetCurUserId(true);
		self::$bOwner = self::$type == 'user' || self::$type == 'group';
		self::$settings = self::GetSettings();
		self::$userSettings = CCalendarUserSettings::Get();
		self::$pathesForSite = self::GetPathes(self::$siteId);
		self::$pathToUser = self::$pathesForSite['path_to_user'];
		self::$bSuperpose = $params['allowSuperpose'] != false && self::$bSocNet;
		self::$bAnonym = !$USER || !$USER->IsAuthorized();
		self::$userNameTemplate = self::$settings['user_name_template'];
		self::$bAMPM = IsAmPmMode();
		self::$bWideDate = strpos(FORMAT_DATETIME, 'MMMM') !== false;
		self::$id = $this->GetId();

		if (isset($params['SectionControlsDOMId']))
			self::$SectionsControlsDOMId = $params['SectionControlsDOMId'];

		if (self::$bOwner && isset($params['ownerId']) && $params['ownerId'] > 0)
			self::$ownerId = intVal($params['ownerId']);

		self::$showTasks = (self::$type == 'user' || self::$type == 'group')
			&& $params['showTasks'] !== false
			&& \Bitrix\Main\Loader::includeModule('tasks')
			&& self::$userSettings['showTasks'] != 'N';

		if (self::$showTasks)
		{
			self::$viewTaskPath = $params['viewTaskPath'];
			self::$editTaskPath = $params['editTaskPath'];
			if (!self::$viewTaskPath)
			{
				self::$showTasks = false;
			}
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
				"/back_url_admin=.*?\&/i"
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

	public function Show($params = array())
	{
		global $APPLICATION;
		$arType = false;

		foreach(self::$arTypes as $type)
		{
			if(self::$type == $type['XML_ID'])
				$arType = $type;
		}

		if (!$arType)
		{
			$APPLICATION->ThrowException('[EC_WRONG_TYPE] '.Loc::getMessage('EC_WRONG_TYPE'));
			return false;
		}

		if (!CCalendarType::CanDo('calendar_type_view', self::$type))
		{
			$APPLICATION->ThrowException(Loc::getMessage("EC_ACCESS_DENIED"));
			return false;
		}

		$startupEvent = false;
		$showNewEventDialog = false;
		//Show new event dialog
		if (isset($_GET['EVENT_ID']))
		{
			if ($_GET['EVENT_ID'] == 'NEW')
			{
				$showNewEventDialog = true;
			}
			elseif(substr($_GET['EVENT_ID'], 0, 4) == 'EDIT')
			{
				$startupEvent = self::GetStartUpEvent(intval(substr($_GET['EVENT_ID'], 4)));
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

		if (!$init_month && !$init_year && strlen($params["initDate"]) > 0 && strpos($params["initDate"], '.') !== false)
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

		$weekHolidays = array();
		if (isset(self::$settings['week_holidays']))
		{
			$days = array('MO' => 0, 'TU' => 1, 'WE' => 2,'TH' => 3,'FR' => 4,'SA' => 5,'SU' => 6);
			foreach(self::$settings['week_holidays'] as $day)
				$weekHolidays[] = $days[$day];
		}
		else
			$weekHolidays = array(5, 6);

		$yearHolidays = array();
		if (isset(self::$settings['year_holidays']))
		{
			foreach(explode(',', self::$settings['year_holidays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) == 2 && $ardate[0] && $ardate[1])
					$yearHolidays[] = intVal($ardate[0]).'.'.(intVal($ardate[1]) - 1);
			}
		}

		$yearWorkdays = array();
		if (isset(self::$settings['year_workdays']))
		{
			foreach(explode(',', self::$settings['year_workdays']) as $date)
			{
				$date = trim($date);
				$ardate = explode('.', $date);
				if (count($ardate) == 2 && $ardate[0] && $ardate[1])
					$yearWorkdays[] = intVal($ardate[0]).'.'.(intVal($ardate[1]) - 1);
			}
		}

		$bSyncPannel = self::IsPersonal();
		$bExchange = CCalendar::IsExchangeEnabled() && self::$type == 'user';
		$bExchangeConnected = $bExchange && CDavExchangeCalendar::IsExchangeEnabledForUser(self::$ownerId);
		$bCalDAV = CCalendar::IsCalDAVEnabled() && self::$type == "user";
		$bGoogleApi = CCalendar::isGoogleApiEnabled() && self::$type == "user";
		$bWebservice = CCalendar::IsWebserviceEnabled();
		$bExtranet = CCalendar::IsExtranetEnabled();

		$userTimezoneOffsetUTC = self::GetCurrentOffsetUTC(self::$userId);
		$userTimezoneName = self::GetUserTimezoneName(self::$userId);
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
			'sectionCustomization' => CCalendarUserSettings::getSectionCustomization(self::$userId),
			'locationFeatureEnabled' => !self::IsBitrix24() || \Bitrix\Bitrix24\Feature::isFeatureEnabled("calendar_location")
		);

		$JSConfig['lastSection'] = CCalendarSect::GetLastUsedSection(self::$type, self::$ownerId, self::$userId);

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


		if(self::$type == 'user' && self::$userId == self::$ownerId)
		{
			$JSConfig['counters'] = array(
				'invitation' => CUserCounter::GetValue(self::$userId, 'calendar')
			);

			$JSConfig['filterId'] = \Bitrix\Calendar\Ui\CalendarFilter::getFilterId(self::$type, self::$ownerId, self::$userId);
		}

		// Access permissons for type
		if (CCalendarType::CanDo('calendar_type_edit_access', self::$type))
			$JSConfig['TYPE_ACCESS'] = $arType['ACCESS'];

		if ($bCalDAV || $bGoogleApi)
		{
			self::InitExternalCalendarsSyncParams($JSConfig);
		}

		if ($bSyncPannel)
		{
			$macSyncInfo = self::GetSyncInfo(self::$userId, 'mac');
			$iphoneSyncInfo = self::GetSyncInfo(self::$userId, 'iphone');
			$androidSyncInfo = self::GetSyncInfo(self::$userId, 'android');
			$outlookSyncInfo = self::GetSyncInfo(self::$userId, 'outlook');
			$exchangeSyncInfo = self::GetSyncInfo(self::$userId, 'exchange');

			$JSConfig['syncInfo'] = array(
				'google' => array(
					'active' => ($bCalDAV || $bGoogleApi) && ($JSConfig['googleCalDavStatus']['connection_id'] > 0 || $JSConfig['googleCalDavStatus']['authLink']),
					'connected' => $JSConfig['googleCalDavStatus']['connection_id'] > 0,
					'syncDate' => $JSConfig['googleCalDavStatus']['sync_date']
				),
				'macosx' => array(
					'active' => true,
					'connected' => $macSyncInfo['connected'],
					'syncDate' => $macSyncInfo['date'],
				),
				'iphone' => array(
					'active' => true,
					'connected' => $iphoneSyncInfo['connected'],
					'syncDate' => $iphoneSyncInfo['date'],
				),
				'android' => array(
					'active' => true,
					'connected' => $androidSyncInfo['connected'],
					'syncDate' => $androidSyncInfo['date'],
				),
				'outlook' => array(
					'active' => true,
					'connected' => $outlookSyncInfo['connected'],
					'syncDate' => $outlookSyncInfo['date'],
				),
				'office365' => array(
					'active' => false,
					'connected' => false,
					'syncDate' => false
				),
				'exchange' => array(
					'active' => $bExchange,
					'connected' => $bExchangeConnected,
					'syncDate' => $exchangeSyncInfo['date']
				)
			);
		}
		else
		{
			$JSConfig['syncInfo'] = false;
		}

		// If enabled superposed sections - fetch it
		$arAddSections = self::GetDisplayedSuperposed(self::$userId);

		if (!is_array($arAddSections))
			$arAddSections = array();

		$arSectionIds = array();
		$hiddenSections = CCalendarUserSettings::getHiddenSections(self::$userId);

		$arDisplayedSPSections = array();
		$arDisplayedNowSPSections = array();

		foreach($arAddSections as $sect)
		{
			$arDisplayedSPSections[] = $sect;
			if (!in_array($sect, $hiddenSections))
				$arDisplayedNowSPSections[] = $sect;
		}

		self::$userMeetingSection = CCalendar::GetCurUserMeetingSection();
		//  **** GET SECTIONS ****
		$sections = self::GetSectionList(array(
			'ADDITIONAL_IDS' => $arDisplayedSPSections
		));

		$readOnly = !self::$perm['edit'] && !self::$perm['section_edit'];

		if (self::$type == 'user' && self::$ownerId != self::$userId)
			$readOnly = true;

		if (self::$bAnonym)
			$readOnly = true;

		$bCreateDefault = !self::$bAnonym;

		if (self::$type == 'user')
			$bCreateDefault = self::$ownerId == self::$userId;

		$additonalMeetingsId = array();
		$groupOrUser = self::$type == 'user' || self::$type == 'group';
		if ($groupOrUser)
		{
			$noEditAccessedCalendars = true;
		}

		$trackingUsers = array();
		$trackingGroups = array();
		foreach ($sections as $i => $section)
		{
			$sections[$i]['~IS_MEETING_FOR_OWNER'] = $section['CAL_TYPE'] == 'user' && $section['OWNER_ID'] != self::$userId && CCalendar::GetMeetingSection($section['OWNER_ID']) == $section['ID'];

			if (!in_array($section['ID'], $hiddenSections) && $section['ACTIVE'] !== 'N')
			{
				$arSectionIds[] = $section['ID'];
				// It's superposed calendar of the other user and it's need to show user's meetings
				if ($sections[$i]['~IS_MEETING_FOR_OWNER'])
					$additonalMeetingsId[] = array('ID' => $section['OWNER_ID'], 'SECTION_ID' => $section['ID']);
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

			if (self::$bSuperpose && in_array($section['ID'], $arAddSections))
				$sections[$i]['SUPERPOSED'] = true;

			if ($bCreateDefault && $section['CAL_TYPE'] == self::$type && $section['OWNER_ID'] == self::$ownerId)
				$bCreateDefault = false;

			if ($sections[$i]['SUPERPOSED'])
			{
				$type = $sections[$i]['CAL_TYPE'];
				if ($type == 'user')
				{
					$path = self::$pathesForSite['path_to_user_calendar'];
					$path = CComponentEngine::MakePathFromTemplate($path, array("user_id" => $sections[$i]['OWNER_ID']));
					$trackingUsers[] = $sections[$i]['OWNER_ID'];
				}
				elseif($type == 'group')
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
		if (!$readOnly && $showNewEventDialog)
		{
			$JSConfig['showNewEventDialog'] = true;
			$JSConfig['bChooseMR'] = isset($_GET['CHOOSE_MR']) && $_GET['CHOOSE_MR'] == "Y";
		}

		if (!in_array($JSConfig['lastSection'], $arSectionIds))
		{
			$JSConfig['lastSection'] = $arSectionIds[0];
		}

		$JSConfig = array_merge($JSConfig, Array(
			'trackingUsersList' => CCalendarUserSettings::getTrackingUsers(false, array('userList' => $trackingUsers)),
			'trackingGroupList' => CCalendarUserSettings::getTrackingGroups(false, array('groupList' => $trackingGroups))
		));

		//  **** GET TASKS ****
		if (self::$showTasks)
		{
			$JSConfig['viewTaskPath'] = self::$viewTaskPath;
			$JSConfig['editTaskPath'] = self::$editTaskPath;
		}

		// We don't have any section
		if ($bCreateDefault)
		{
			$fullSectionsList = $groupOrUser ? self::GetSectionList(array('checkPermissions' => false)) : array();
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
				$arSectionIds[] = $defCalendar['ID'];
				$sections[] = $defCalendar;
				self::$userMeetingSection = $defCalendar['ID'];
			}
		}

		if (CCalendarType::CanDo('calendar_type_edit', self::$type))
			$JSConfig['new_section_access'] = CCalendarSect::GetDefaultAccess(self::$type, self::$ownerId);

		$colors = array(
			'#9dcf00','#2FC6F6','#56D1E0','#FFA900','#47E4C2','#F87396','#9985DD','#A8ADB4','#AF7E00'
		);

		$JSConfig['hiddenSections'] = $hiddenSections;
		$JSConfig['readOnly'] = $readOnly;

		// access
		$JSConfig['accessNames'] = self::GetAccessNames();
		$JSConfig['sectionAccessTasks'] = self::GetAccessTasks('calendar_section');
		$JSConfig['typeAccessTasks'] = self::GetAccessTasks('calendar_type');

		$JSConfig['bSuperpose'] = self::$bSuperpose;
		$JSConfig['additonalMeetingsId'] = $additonalMeetingsId;

		$additionalParams = array(
			'socnetDestination' => CCalendar::GetSocNetDestination(false, array('U'.self::$userId, 'U'.self::$ownerId)),
			'locationList' => CCalendarLocation::GetList(),
			'timezoneList' => CCalendar::GetTimezoneList(),
			'defaultColorsList' => $colors,
			'formSettings' => array(
				'slider_main' => CCalendarUserSettings::getFormSettings('slider_main')
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

	public static function GetUserGroups($userId = 0)
	{
		if (!$userId || !class_exists('CSocNetUserToGroup') || !class_exists('CSocNetFeatures'))
			return;

		$dbGroups = CSocNetUserToGroup::GetList(
			array("GROUP_NAME" => "ASC"),
			array(
				"USER_ID" => $userId,
				"<=ROLE" => SONET_ROLES_USER,
				"GROUP_SITE_ID" => SITE_ID,
				"GROUP_ACTIVE" => "Y"
			),
			false,
			false,
			array("GROUP_ID", "GROUP_NAME")
		);

		$arRes = array();
		if ($dbGroups)
		{
			$arGroupIds = array();
			$arGroups = array();
			while ($g = $dbGroups->GetNext())
			{
				$arGroups[] = $g;
				$arGroupIds[] = $g['GROUP_ID'];
			}
			if (count($arGroupIds) > 0)
			{
				$arFeaturesActive = CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arGroupIds, "calendar");
				$arView = CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arGroupIds, "calendar", 'view');
				$arWrite = CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arGroupIds, "calendar", 'write');

				foreach($arGroups as $group)
				{
					$groupId = intVal($group['~GROUP_ID']);
					// Calendar is disabled as feature or user can't even view it
					if (!$arFeaturesActive[$groupId] || !$arView[$groupId])
						continue;

					$arRes[$groupId] = array(
						'ID' => $groupId,
						'NAME' => $group['~GROUP_NAME'],
						'READONLY' => !$arWrite[$groupId] // Can't write to group's calendars
					);
				}
			}
		}

		return $arRes;
	}

	public static function SetDisplayedSuperposed($userId = false, $arIds = array())
	{
		if (!class_exists('CUserOptions') || !$userId)
			return false;
		$res = array();


		if (is_array($arIds))
		{
			foreach($arIds as $id)
			{
				if (intVal($id) > 0)
				{
					$res[] = intVal($id);
				}
			}
		}

		CUserOptions::SetOption("calendar", "superpose_displayed", serialize($res));

		return true;
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

	public static function CollectExchangeErrors($arErrors = array())
	{
		if (count($arErrors) == 0 || !is_array($arErrors))
			return '[EC_NO_EXCH] '.Loc::getMessage('EC_NO_EXCHANGE_SERVER');

		$str = "";
		$errorCount = count($arErrors);
		for($i = 0; $i < $errorCount; $i++)
			$str .= "[".$arErrors[$i][0]."] ".$arErrors[$i][1]."\n";

		return $str;
	}

	public static function DeleteEvent($id, $doExternalSync = true, $params = array())
	{
		global $CACHE_MANAGER;

		$id = intVal($id);
		if (!$id)
			return false;

		if (!isset(self::$userId))
			self::$userId = CCalendar::GetCurUserId();

		CCalendar::SetOffset(false, 0);
		$res = CCalendarEvent::GetList(
			array(
				'arFilter' => array("ID" => $id),
				'parseRecursion' => false,
				'setDefaultLimit' => false,
				'fetchAttendees' => true
			)
		);

		if ($event = $res[0])
		{
			if (!isset(self::$type))
				self::$type = $event['CAL_TYPE'];

			if (!isset(self::$ownerId))
				self::$ownerId = $event['OWNER_ID'];

			if (!self::IsPersonal($event['CAL_TYPE'], $event['OWNER_ID'], self::$userId) && !CCalendarSect::CanDo('calendar_edit', $event['SECT_ID'], self::$userId))
			{
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
				'sendNotification' => $sendNotification
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

			if($params['recursionMode'] == 'all')
			{
				foreach($event['~ATTENDEES'] as $attendee)
				{
					if ($attendee['STATUS'] != 'N')
					{
						$CACHE_MANAGER->ClearByTag('calendar_user_'.$attendee["USER_ID"]);
						CCalendarNotify::Send(array(
							"mode" => 'cancel_all',
							"name" => $event['NAME'],
							"from" => $event['DATE_FROM'],
							"guestId" => $attendee["USER_ID"],
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

	public static function CollectCalDAVErros($arErrors = array())
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
		$bExtranet = \Bitrix\Main\Loader::includeModule('extranet');
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

			if (self::$siteId == $siteId && isset(self::$pathesForSite) && is_array(self::$pathesForSite))
				self::$pathes[$siteId] = self::$pathesForSite;
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

	public static function SetUserDepartment($userId = 0, $dep = array())
	{
		if (!is_array($dep))
			$dep = array();
		self::$arUserDepartment[$userId] = $dep;
	}

	public static function HandleImCallback($module, $tag, $value, $arNotify)
	{
		$userId = CCalendar::GetCurUserId();
		if ($module == "calendar" && $userId)
		{
			$arTag = explode("|", $tag);
			$eventId = intVal($arTag[2]);
			if ($arTag[0] == "CALENDAR" && $arTag[1] == "INVITE" && $eventId > 0 && $userId)
			{
				CCalendarEvent::SetMeetingStatus(array(
					'userId' => $userId,
					'eventId' => $eventId,
					'status' => $value == 'Y' ? 'Y' : 'N',
					'personalNotification' => true
				));

				return $value == 'Y' ? Loc::getMessage('EC_PROP_CONFIRMED_TEXT_Y') : Loc::getMessage('EC_PROP_CONFIRMED_TEXT_N');
			}
		}
	}

	public static function ClearSettings()
	{
		self::SetSettings(array(), true);
	}

	public static function SetSettings($settings = array(), $bClear = false)
	{
		$arPathes = self::GetPathesList();
		$arOpt = array('work_time_start', 'work_time_end', 'year_holidays', 'year_workdays', 'week_holidays', 'week_start', 'user_name_template', 'sync_by_push', 'user_show_login', 'rm_iblock_type', 'rm_iblock_id', 'denied_superpose_types', 'pathes_for_sites', 'pathes', 'dep_manager_sub', 'forum_id', 'rm_for_sites');

		$arOpt = array_merge($arOpt, $arPathes);
		if ($settings['rm_iblock_ids'] && !$settings['rm_for_sites'])
		{
			foreach($settings['rm_iblock_ids'] as $site => $value)
			{
				COption::SetOptionString("calendar", 'rm_iblock_id', $value, false, $site);
			}
		}

		foreach($arOpt as $opt)
		{
			if ($bClear)
			{
				COption::RemoveOption("calendar", $opt);
			}
			elseif (isset($settings[$opt]))
			{
				if ($opt == 'rm_iblock_id' && !$settings['rm_for_sites'])
				{
					continue;
				}
				elseif ($opt == 'sync_by_push' && self::isGoogleApiEnabled())
				{
					if ($settings[$opt])
					{
						// start push agents
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();", "calendar");
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::createWatchChannels(0);", "calendar", "N", 60);
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();", "calendar", "N", 180);
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar", "N", 14400);
					}
					else
					{
						// start clear push channels agent
						\CAgent::AddAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::clearPushChannels();", "calendar", "N", 60);
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::processPush();", "calendar");
						\CAgent::RemoveAgent("\\Bitrix\\Calendar\\Sync\\GoogleApiPush::renewWatchChannels();", "calendar");
					}
				}
				elseif ($opt == 'pathes' && is_array($settings[$opt]))
				{
					$sitesPathes = $settings[$opt];

					$ar = array();
					$arAffectedSites = array();
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
				elseif ($opt == 'denied_superpose_types' && is_array($settings[$opt]))
				{
					$settings[$opt] = serialize($settings[$opt]);
				}
				COption::SetOptionString("calendar", $opt, $settings[$opt]);
			}
		}
	}

	public static function IsBitrix24()
	{
		return \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24');
	}

	public static function SearchAttendees($name = '', $Params = array())
	{
		if (!isset($Params['arFoundUsers']))
			$Params['arFoundUsers'] = CSocNetUser::SearchUser($name);

		$arUsers = array();
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
				$userId = intVal($userId);

				$by = "id";
				$order = "asc";
				$r = CUser::GetList($by, $order, array("ID_EQUAL_EXACT" => $userId, "ACTIVE" => "Y"));

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

		$arMembers = array();
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

	public static function ReminderAgent($eventId = 0, $userId = 0, $viewPath = '', $calendarType = '', $ownerId = 0)
	{
		CCalendarReminder::ReminderAgent($eventId, $userId, $viewPath, $calendarType, $ownerId);
	}

	public static function GetMaxTimestamp()
	{
		return self::CALENDAR_MAX_TIMESTAMP;
	}

	public static function GetOwnerName($type = '', $ownerId = '')
	{
		$type = strtolower($type);
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
			if (!\Bitrix\Main\Loader::includeModule("socialnetwork"))
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
					$res[$userId] = array();

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

	public static function GetNearestEventsList($params = array())
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

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => $arFilter,
				'parseRecursion' => true,
				'fetchAttendees' => true,
				'userId' => $curUserId,
				'fetchMeetings' => $type == 'user',
				'preciseLimits' => true,
				'skipDeclined' => true
			)
		);

		if (CCalendar::Date(time(), false) == $params['fromLimit'])
			$limitTime = time();
		else
			$limitTime = CCalendar::Timestamp($params['fromLimit']);

		$arResult = array();
		$serverOffset = intVal(date("Z"));

		foreach($arEvents as $event)
		{
			if ($event['IS_MEETING'] && $event["MEETING_STATUS"] == 'N')
				continue;

			if ($type == 'user' && !$event['IS_MEETING'] && $event['CAL_TYPE'] != 'user')
				continue;

			// $serverToTs = timestamp in utc + server offset;
			$serverToTs = (CCalendar::Timestamp($event['DATE_TO']) - $event['TZ_OFFSET_TO'])  + $serverOffset;
			if ($event['DT_SKIP_TIME'] == 'Y')
			{
				$serverToTs += self::DAY_LENGTH;
			}

			if ($serverToTs >= $limitTime)
			{
				$fromTs = CCalendar::Timestamp($event['DATE_FROM']);
				$toTs = CCalendar::Timestamp($event['DATE_TO']);
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

				$arResult[] = $event;
			}
		}

		// Sort by DATE_FROM_TS
		usort($arResult, array('CCalendar', '_NearestSort'));
		return $arResult;
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
		$arResult = array();
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
			if(IntVal($curEventId) > 0)
				$arFilter["!ID"] = IntVal($curEventId);

			$rsElement = CIBlockElement::GetList(Array('ACTIVE_FROM' => 'ASC'), $arFilter, false, false, $arSelect);
			while($obElement = $rsElement->GetNextElement())
			{
				$arItem = $obElement->GetFields();
				$arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat(self::DFormat(true), MakeTimeStamp($arItem["ACTIVE_FROM"]));
				$arItem["DISPLAY_ACTIVE_TO"] = CIBlockFormatProperties::DateFormat(self::DFormat(true), MakeTimeStamp($arItem["ACTIVE_TO"]));

				$arResult[] = array(
					"ID" => intVal($arItem['ID']),
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
		if (IntVal($Params['RMiblockId']) > 0 && CIBlock::GetPermission($Params['RMiblockId']) >= "R")
		{
			$arFilter = array("IBLOCK_ID" => $Params['RMiblockId'], "ACTIVE" => "Y", "ID" => $Params['id']);
			$arSelectFields = array("NAME");
			$res = CIBlockSection::GetList(array(), $arFilter, false, array("NAME"));
			if ($arMeeting = $res->GetNext())
				return $arMeeting;
		}

		if(IntVal($Params['VMiblockId']) > 0 && CIBlock::GetPermission($Params['VMiblockId']) >= "R")
		{
			$arFilter = array("IBLOCK_ID" => $Params['VMiblockId'], "ACTIVE" => "Y");
			$arSelectFields = array("ID", "NAME", "DESCRIPTION", "IBLOCK_ID");
			$res = CIBlockSection::GetList(Array(), $arFilter, false, $arSelectFields);
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

		$res = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
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

	public static function GetCalendarList($calendarId, $params = array())
	{
		self::SetSilentErrorMode();
		list($sectionId, $entityType, $entityId) = $calendarId;
		$arFilter = array(
			'CAL_TYPE' => $entityType,
			'OWNER_ID' => $entityId
		);

		if (!is_array($params))
			$params = array();

		if ($sectionId > 0)
			$arFilter['ID'] = $sectionId;

		$res = CCalendarSect::GetList(array('arFilter' => $arFilter));

		$arCalendars = array();
		foreach($res as $calendar)
		{
			if ($params['skipExchange'] == true && strlen($calendar['DAV_EXCH_CAL']) > 0)
				continue;

			$arCalendars[] = array(
				'ID' => $calendar['ID'],
				'~NAME' => $calendar['NAME'],
				'NAME' => htmlspecialcharsbx($calendar['NAME']),
				'DESCRIPTION' => htmlspecialcharsbx($calendar['DESCRIPTION']),
				'COLOR' => htmlspecialcharsbx($calendar['COLOR'])
				//"DATE_CREATE" => date("d.m.Y H:i", self::Timestamp($arSection['DATE_CREATE']))
			);
		}

		self::SetSilentErrorMode(false);
		return $arCalendars;
	}

	/*
	 * $params['from'], $params['from'] - datetime in UTC
	 * */

	public static function GetDavCalendarEventsList($calendarId, $arFilter = array())
	{
		list($sectionId, $entityType, $entityId) = $calendarId;

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

		$result = array();
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
		$location = self::ParseLocation($loc);
		$result = $loc;

		if ($location['mrid'] === false && $location['room_id'] === false)
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
				if ($MR['ID'] == $location['mrid'])
				{
					$result = $MR['NAME'];
					break;
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

		if (strlen($location) > 5 && substr($location, 0, 5) == 'ECMR_')
		{
			$location = explode('_', $location);
			if (count($location) >= 2)
			{
				if (intVal($location[1]) > 0)
					$res['mrid'] = intVal($location[1]);
				if (intVal($location[2]) > 0)
					$res['mrevid'] = intVal($location[2]);
			}
		}
		elseif (strlen($location) > 9 && substr($location, 0, 9) == 'calendar_')
		{
			$location = explode('_', $location);
			if (count($location) >= 2)
			{
				if (intVal($location[1]) > 0)
					$res['room_id'] = intVal($location[1]);
				if (intVal($location[2]) > 0)
					$res['room_event_id'] = intVal($location[2]);
			}
		}
		return $res;
	}

	/* * * * RESERVE MEETING ROOMS  * * * */

	public static function GetUserPermissionsForCalendar($calendarId, $userId)
	{
		list($sectionId, $entityType, $entityId) = $calendarId;
		$entityType = strtolower($entityType);

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
			if ($location['mrid'] === false)
			{
				$MRList = CCalendar::GetMeetingRoomList();
				$loc_ = trim(strtolower($loc));
				foreach($MRList as $MR)
				{
					if (trim(strtolower($MR['NAME'])) == $loc_)
					{
						$result['NEW'] = 'ECMR_'.$MR['ID'];
						break;
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
		$TextParser->allow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "N", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "Y", "VIDEO" => "Y", "TABLE" => "Y", "CUT_ANCHOR" => "Y", "ALIGN" => "Y");

		$html = $TextParser->convertText($html);

		$html = htmlspecialcharsback($html);
		// Replace BR
		$html = preg_replace("/\<br\s*\/*\>/is".BX_UTF_PCRE_MODIFIER,"\n", $html);
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
			$arDays = array('SU','MO','TU','WE','TH','FR','SA');
		else
			$arDays = array('MO','TU','WE','TH','FR','SA','SU');
		return isset($arDays[$i]) ? $arDays[$i] : false;
	}

	public static function SaveEvent($params = array())
	{
		$res = self::SaveEventEx($params);

		if (is_array($res) && isset($res['id']))
		{
			return $res['id'];
		}
		else
		{
			return $res;
		}
	}

	public static function SaveEventEx($params = array())
	{
		$arFields = $params['arFields'];
		if (self::$type && !isset($arFields['CAL_TYPE']))
			$arFields['CAL_TYPE'] = self::$type;
		if (self::$bOwner && !isset($arFields['OWNER_ID']))
			$arFields['OWNER_ID'] = self::$ownerId;

		if (!isset($arFields['SKIP_TIME']) && isset($arFields['DT_SKIP_TIME']))
			$arFields['SKIP_TIME'] = $arFields['DT_SKIP_TIME'] == 'Y';

		$result = array();
		$userId = isset($params['userId']) ? $params['userId'] : self::GetCurUserId();
		$sectionId = (is_array($arFields['SECTIONS']) && count($arFields['SECTIONS']) > 0) ? $arFields['SECTIONS'][0] : intval($arFields['SECTIONS']);
		$bPersonal = self::IsPersonal($arFields['CAL_TYPE'], $arFields['OWNER_ID'], $userId);
		$checkPermission = !isset($params['checkPermission']) || $params['checkPermission'] !== false;
		$silentErrorMode = isset($params['silentErrorMode']) ? $params['silentErrorMode'] : true;
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
				array(
					'arFilter' => array(
						"ID" => intVal($arFields['ID']),
						"DELETED" => "N"
					),
					'parseRecursion' => false,
					'fetchAttendees' => true,
					'fetchMeetings' => false,
					'userId' => $userId
				)
			);

			if ($curEvent)
				$curEvent = $curEvent[0];

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
				&& $arFields['IS_MEETING'] && isset($curEvent['~ATTENDEES']))
			{
				$arFields['ATTENDEES'] = array();
				foreach($curEvent['~ATTENDEES'] as $att)
				{
					$arFields['ATTENDEES'][] = $att['USER_ID'];
				}
			}
			if (!isset($arFields['ATTENDEES_CODES']) && $arFields['IS_MEETING'])
			{
				$arFields['ATTENDEES_CODES'] = $curEvent['ATTENDEES_CODES'];
			}

			if (!isset($arFields['LOCATION']) && $curEvent['LOCATION'] != "")
			{
				$arFields['LOCATION'] = Array(
					"OLD" => $curEvent['LOCATION'],
					"NEW" => $curEvent['LOCATION']
				);
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
			// If it's attendee and but modifying called from CalDav methods
			if ($params['bSilentAccessMeeting'] && $curEvent['IS_MEETING'] && $curEvent['PARENT_ID'] != $curEvent['ID'])
			{
				$params['recursionEditMode'] = 'skip';
				$params['sendInvitations'] = false;
				$params['sendEditNotification'] = false;
				$params['significantChanges'] = false;
				$params['arFields'] = array(
					"ID" => $arFields["ID"],
					"PARENT_ID" => $arFields["PARENT_ID"],
					"OWNER_ID" => $arFields["OWNER_ID"],
					"DAV_XML_ID" => $arFields['DAV_XML_ID'],
					"CAL_DAV_LABEL" => $arFields['CAL_DAV_LABEL'],
					"DAV_EXCH_LABEL" => $arFields['DAV_EXCH_LABEL']
				);
				$params['userId'] = $userId;
				CCalendarEvent::Edit($params);
				return true; // CalDav will return 204
			}

			if (!isset($arFields["RRULE"]) && $curEvent["RRULE"] != '' && $params['fromWebservice'] !== true)
			{
				$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent["RRULE"]);
			}

			if ($params['fromWebservice'] === true)
			{
				if ($arFields["RRULE"] == -1 && CCalendarEvent::CheckRecurcion($curEvent))
					$arFields["RRULE"] = CCalendarEvent::ParseRRULE($curEvent['RRULE']);
			}

			if (!isset($arFields['EXDATE']) && $arFields["RRULE"])
				$arFields['EXDATE'] = $curEvent['EXDATE'];

			if ($curEvent)
				$params['currentEvent'] = $curEvent;

			if ($checkPermission && !$bPersonal && !CCalendarSect::CanDo('calendar_edit', $curEvent['SECT_ID'], $userId))
			{
				return Loc::getMessage('EC_ACCESS_DENIED');
			}
		}
		elseif ($sectionId > 0 && $checkPermission && !$bPersonal && !CCalendarSect::CanDo('calendar_add', $sectionId, $userId))
		{
			return CCalendar::ThrowError(Loc::getMessage('EC_ACCESS_DENIED'));
		}

		if ($params['autoDetectSection'] && $sectionId <= 0)
		{
			$sectionId = false;
			if ($arFields['CAL_TYPE'] == 'user')
			{
				$sectionId = CCalendarSect::GetLastUsedSection('user', $arFields['OWNER_ID'], $userId);
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

				if ($sectionId)
					$arFields['SECTIONS'] = array($sectionId);
			}

			if (!$sectionId)
			{
				$sectRes = self::GetSectionForOwner($arFields['CAL_TYPE'], $arFields['OWNER_ID'], $params['autoCreateSection']);
				if ($sectRes['sectionId'] > 0)
				{
					$arFields['SECTIONS'] = array($sectRes['sectionId']);
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
			$res = CCalendarSect::GetList(array('arFilter' => array('CAL_TYPE' => $arFields['CAL_TYPE'],'OWNER_ID' => $arFields['OWNER_ID']), 'checkPermissions' => false));
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
		if ($params['bAffectToDav'] !== false && ($bExchange || $bCalDav || $bGoogleApi) && $sectionId > 0)
		{
			$res = CCalendarSync::DoSaveToDav(array(
				'bCalDav' => $bCalDav,
				'bExchange' => $bExchange,
				'sectionId' => $sectionId
			), $arFields, $curEvent);
			if ($res !== true)
				return CCalendar::ThrowError($res);
		}

		$params['arFields'] = $arFields;
		$params['userId'] = $userId;

		if (self::$ownerId != $arFields['OWNER_ID'] && self::$type != $arFields['CAL_TYPE'])
			$params['path'] = self::GetPath($arFields['CAL_TYPE'], $arFields['OWNER_ID'], 1);
		else
			$params['path'] = self::$path;

		if ($curEvent && in_array($params['recursionEditMode'], array('this', 'next')) &&
			CCalendarEvent::CheckRecurcion($curEvent))
		{
			// Edit only current instance of the set of reccurent events
			if ($params['recursionEditMode'] == 'this')
			{
				// 1. Edit current reccurent event: exclude current date
				$excludeDates = CCalendarEvent::GetExDate($curEvent['EXDATE']);
				$excludeDate = self::Date(self::Timestamp($params['currentEventDateFrom']), false);
				$excludeDates[] = $excludeDate;

				// Save current event
				$id = CCalendar::SaveEvent(array(
					'arFields' => array(
						"ID" => $curEvent["ID"],
						'EXDATE' => CCalendarEvent::SetExDate($excludeDates)
					),
					'recursionEditMode' => 'skip',
					'silentErrorMode' => $params['silentErrorMode'],
					'sendInvitations' => false,
					'sendEditNotification' => false,
					'userId' => $userId
				));

				// 2. Copy event with new changes, but without reccursion
				$newParams = $params;
				$newParams['sendEditNotification'] = false;

				if (!$newParams['arFields']['MEETING']['REINVITE'])
					$newParams['saveAttendeesStatus'] = true;

				$newParams['arFields']['RECURRENCE_ID'] = $curEvent['RECURRENCE_ID'] ? $curEvent['RECURRENCE_ID'] : $newParams['arFields']['ID'];

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
				$eventMod['DATE_FROM'] = $newParams['currentEventDateFrom'];
				$commentXmlId = CCalendarEvent::GetEventCommentXmlId($eventMod);
				$newParams['arFields']['RELATIONS'] = array('COMMENT_XML_ID' => $commentXmlId);

				$result['recEventId'] = CCalendar::SaveEvent($newParams);
			}
			// Edit all next instances of the set of reccurent events
			elseif($params['recursionEditMode'] == 'next')
			{
				$currentDateTimestamp = self::Timestamp($params['currentEventDateFrom']);

				// Copy event with new changes
				$newParams = $params;

				$recId = $newParams['arFields']['RECURRENCE_ID'] = $curEvent['RECURRENCE_ID'] ? $curEvent['RECURRENCE_ID'] : $newParams['arFields']['ID'];

				// Check if it's first instance of the series, so we shoudn't create another event
				if (CCalendar::Date(self::Timestamp($curEvent['DATE_FROM']), false) == CCalendar::Date($currentDateTimestamp, false))
				{
					$newParams['recursionEditMode'] = 'skip';
				}
				else
				{
					// 1. Edit current reccurent event - finish it
					$arFieldsCurrent = array(
						"ID" => $curEvent["ID"],
						"RRULE" => CCalendarEvent::ParseRRULE($curEvent['RRULE'])
					);
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
						'userId' => $userId
					));

					unset($newParams['arFields']['ID']);
					unset($newParams['arFields']['DAV_XML_ID']);
					unset($newParams['recursionEditMode']);
				}

				if (!$newParams['arFields']['MEETING']['REINVITE'])
					$newParams['saveAttendeesStatus'] = true;

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

				$result['recEventId'] = CCalendar::SaveEvent($newParams);

				if ($recId)
				{
					$recRelatedEvents = CCalendarEvent::GetEventsByRecId($recId, false);
					foreach($recRelatedEvents as $ev)
					{
						$evFromTs = CCalendar::Timestamp($ev['DATE_FROM']);
						if($evFromTs > $currentDateTimestamp)
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
			if($id)
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
							CCalendar::UpdateUFRights($UFs['UF_WEBDAV_CAL_EVENT'], $arFields['ATTENDEES_CODES'], $UF['UF_WEBDAV_CAL_EVENT']);
						}
					}
				}
			}

			// Here we should select all events connected with edited via RECURRENCE_ID:
			// It could be original source event (without RECURRENCE_ID) or sibling events
			if ($curEvent && CCalendarEvent::CheckRecurcion($curEvent)
				&& !$params['recursionEditMode']
				&& !$params['arFields']['RECURRENCE_ID']
			)
			{
				$events = array();
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
				ExecuteModuleEventEx($arEvent, array('arFields' => $arFields, 'bNew' => $bNew, 'userId' => $userId));
		}

		self::SetSilentErrorMode($silentErrorModePrev);

		$result['id'] = $id;

		return $result;
	}

	public static function ThrowError($str)
	{
		if (self::$silentErrorMode)
			return false;

		global $APPLICATION;
		echo '<!-- BX_EVENT_CALENDAR_ACTION_ERROR:'.$str.'-->';
		return $APPLICATION->ThrowException($str);
	}

	public static function GetSectionForOwner($calType, $ownerId, $autoCreate = true)
	{
		return CCalendarSect::GetSectionForOwner($calType, $ownerId, $autoCreate);
	}

	public static function UpdateUFRights($files, $rights, $ufEntity = array())
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
		if (!\Bitrix\Main\Loader::includeModule('iblock') || !\Bitrix\Main\Loader::includeModule('webdav'))
			return false;

		$arFiles = array();
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

		$arCodes = array();
		foreach($rights as $value)
		{
			if (substr($value, 0, 2) === 'SG')
				$arCodes[] = $value.'_K';
			$arCodes[] = $value;
		}
		$arCodes = array_unique($arCodes);

		$i=0;
		$arViewRights = $arEditRights = array();
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
		$dbWDFile = $ibe->GetList(array(), array('ID' => $arFiles, 'SHOW_NEW' => 'Y'), false, false, array('ID', 'NAME', 'SECTION_ID', 'IBLOCK_ID', 'WF_NEW'));
		$iblockIds = array();
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
			if ($ID && intVal($ID) > 0)
				$USER->Authorize(intVal($ID));
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
			$arPath = array(
				'access_tasks',
				'type_list',
				'section_list',
				'attendees_list',
				'event_list'
			);
		elseif (!is_array($arPath))
			$arPath = array($arPath);

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

	public static function SyncCalendarItems($connectionType, $calendarId, $arCalendarItems)
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

		list($sectionId, $entityType, $entityId) = $calendarId;
		$entityType = strtolower($entityType);

		if ($connectionType == 'exchange')
			$xmlIdField = "DAV_EXCH_LABEL";
		elseif ($connectionType == 'caldav')
			$xmlIdField = "CAL_DAV_LABEL";
		else
			return array();

		$arCalendarItemsMap = array();
		foreach ($arCalendarItems as $value)
			$arCalendarItemsMap[$value["XML_ID"]] = $value["MODIFICATION_LABEL"];

		$arModified = array();
		$eventsList = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					'CAL_TYPE' => $entityType,
					'OWNER_ID' => $entityId,
					'SECTION' => $sectionId
				),
				'getUserfields' => false,
				'parseRecursion' => false,
				'fetchAttendees' => false,
				'fetchMeetings' => false,
				'userId' => $entityType == 'user' ? $entityId : '0'
			)
		);

		foreach ($eventsList as $event)
		{
			if (isset($arCalendarItemsMap[$event["DAV_XML_ID"]]))
			{
				if ($event[$xmlIdField] != $arCalendarItemsMap[$event["DAV_XML_ID"]])
					$arModified[$event["DAV_XML_ID"]] = $event["ID"];

				unset($arCalendarItemsMap[$event["DAV_XML_ID"]]);
			}
			else
			{
				if ($connectionType == 'exchange')
				{
					if ($event['ID'] == $event['PARENT_ID'])
					{
						self::DeleteCalendarEvent($calendarId, $event["ID"], self::$userId, $event);
					}
				}
				else
				{
					self::DeleteCalendarEvent($calendarId, $event["ID"], self::$userId, $event);
				}
			}
		}

		$arResult = array();
		foreach ($arCalendarItems as $value)
		{
			if (array_key_exists($value["XML_ID"], $arModified))
			{
				$arResult[] = array(
					"XML_ID" => $value["XML_ID"],
					"ID" => $arModified[$value["XML_ID"]]
				);
			}
		}

		foreach ($arCalendarItemsMap as $key => $value)
		{
			$arResult[] = array(
				"XML_ID" => $key,
				"ID" => 0
			);
		}

		self::$silentErrorMode = false;
		return $arResult;
	}

	public static function DeleteCalendarEvent($calendarId, $eventId, $userId, $oEvent = false)
	{
		self::$silentErrorMode = true;
		list($sectionId, $entityType, $entityId) = $calendarId;

		$res = CCalendarEvent::Delete(array(
			'id' => $eventId,
			'userId' => $userId,
			'bMarkDeleted' => true,
			'Event' => $oEvent
		));
		self::$silentErrorMode = false;
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
			if (strlen($color) > 6)
				$color = substr($color, 0, 6);
			elseif(strlen($color) < 6)
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
		$m = intVal($m);

		if ($m > 59)
			$m = 59;
		elseif ($m < 0)
			$m = 0;

		if ($m < 10)
			$m = '0'.$m;

		$h = intVal($h);
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

	public static function GetUserAvatarSrc($user = array(), $params = array())
	{
		if (!is_array($user) && intVal($user) > 0)
			$user = self::GetUser($user);

		$avatar_src = self::GetUserAvatar($user, $params);
		if ($avatar_src === false)
			$avatar_src = '/bitrix/images/1.gif';
		return $avatar_src;
	}

	public static function GetUserAvatar($user = array(), $params = array())
	{
		if (!is_array($user) && intVal($user) > 0)
			$user = self::GetUser($user);

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
				false
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
		$arTasks = Array();
		$res = CTask::GetList(Array('ID' => 'asc'), Array('MODULE_ID' => 'calendar', 'BINDING' => $binging));
		while($arRes = $res->Fetch())
		{
			if (!$bIntranet && (strtolower($arRes['NAME']) == 'calendar_view_time' || strtolower($arRes['NAME']) == 'calendar_view_title'))
				continue;

			$name = '';
			if ($arRes['SYS'])
				$name = Loc::getMessage('TASK_NAME_'.strtoupper($arRes['NAME']));
			if (strlen($name) == 0)
				$name = $arRes['NAME'];

			$arTasks[$arRes['ID']] = array(
				'name' => $arRes['NAME'],
				'title' => $name
			);
		}

		self::$arAccessTask[$binging] = $arTasks;

		return $arTasks;
	}

	public static function PushAccessNames($arCodes = array())
	{
		foreach($arCodes as $code)
		{
			if (!array_key_exists($code, self::$accessNames))
			{
				self::$accessNames[$code] = null;
			}
		}
	}

	public static function SetLocation($old = '', $new = '', $params = array())
	{
		$tzEnabled = CTimeZone::Enabled();
		if ($tzEnabled)
		{
			CTimeZone::Disable();
		}

		$res = '';
		// *** ADD MEETING ROOM ***
		$locOld = CCalendar::ParseLocation($old);
		$locNew = CCalendar::ParseLocation($new);
		CCalendar::GetSettings(array('request' => false));

		$allowReserveMeeting = isset($params['allowReserveMeeting']) ? $params['allowReserveMeeting'] : self::$allowReserveMeeting;

		$RMiblockId = isset($params['RMiblockId']) ? $params['RMiblockId'] : self::$settings['rm_iblock_id'];

		// If not allowed
		if (!$allowReserveMeeting)
		{
			$res = $locNew['mrid'] ? $locNew['str'] : $new;
		}
		else
		{
			if ($locOld['mrid'] !== false && $locOld['mrevid'] !== false) // Release MR
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
				if ($params['bRecreateReserveMeetings'] && $allowReserveMeeting)
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
				$roomEventId = CCalendarLocation::reserveRoom(array(
					'room_id' => $locNew['room_id'],
					'room_event_id' => false,
					'parentParams' => $params['parentParams']
				));

				$locNew = $roomEventId ? 'calendar_'.$locNew['room_id'].'_'.$roomEventId : '';
			}
			else
			{
				$locNew = $locNew['str'];
			}

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

	public static function ManageConnections($arConnections = array())
	{
		global $APPLICATION;
		$bSync = false;
		$l = count($arConnections);

		for ($i = 0; $i < $l; $i++)
		{
			$con = $arConnections[$i];
			$conId = intVal($con['id']);
			if ($conId <= 0) // It's new connection
			{
				if ($con['del'] != 'Y')
				{
					if(!CCalendar::CheckCalDavUrl($con['link'], $con['user_name'], $con['pass']))
						return Loc::getMessage("EC_CALDAV_URL_ERROR");

					CDavConnection::Add(array(
						"ENTITY_TYPE" => 'user',
						"ENTITY_ID" => self::$ownerId,
						"ACCOUNT_TYPE" => 'caldav',
						"NAME" => $con['name'],
						"SERVER" => $con['link'],
						"SERVER_USERNAME" => $con['user_name'],
						"SERVER_PASSWORD" => $con['pass'])
					);
					$bSync = true;
				}
			}
			elseif ($con['del'] != 'Y') // Edit connection
			{
				$arFields = array(
					"NAME" => $con['name'],
					"SERVER" => $con['link'],
					"SERVER_USERNAME" => $con['user_name']
				);
				if ($con['pass'] !== 'bxec_not_modify_pass')
					$arFields["SERVER_PASSWORD"] = $con['pass'];

				$resCon = CDavConnection::GetList(array("ID" => "ASC"), array("ID" => $conId));
				if ($arCon = $resCon->Fetch())
				{
					if($arCon['ACCOUNT_TYPE'] !== 'caldav_google_oauth' && $arCon['ACCOUNT_TYPE'] !== 'google_api_oauth')
					{
						CDavConnection::Update($conId, $arFields);
					}
				}

				if (is_array($con['sections']))
				{
					foreach ($con['sections'] as $sectId => $active)
					{
						$sectId = intVal($sectId);

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
			CDavGroupdavClientCalendar::DataSync("user", self::$ownerId);

		$res = CDavConnection::GetList(
			array("ID" => "DESC"),
			array(
				"ENTITY_TYPE" => "user",
				"ENTITY_ID" => self::$ownerId
			),
			false,
			false
		);

		while($arCon = $res->Fetch())
		{
			if ($arCon['ACCOUNT_TYPE'] == 'caldav_google_oauth' || $arCon['ACCOUNT_TYPE'] == 'caldav')
			{
				if(strpos($arCon['LAST_RESULT'], "[200]") === false)
					return Loc::getMessage('EC_CALDAV_CONNECTION_ERROR', array('#CONNECTION_NAME#' => $arCon['NAME'], '#ERROR_STR#' => $arCon['LAST_RESULT']));
			}
		}

		return true;
	}

	public static function CheckCalDavUrl($url, $username, $password)
	{
		$arServer = parse_url($url);

		// Mantis #71074
		if (strpos(strtolower($_SERVER['SERVER_NAME']), strtolower($arServer['host'])) !== false || strpos(strtolower($_SERVER['HTTP_HOST']), strtolower($arServer['host'])) !== false)
			return false;

		return CDavGroupdavClientCalendar::DoCheckCalDAVServer($arServer["scheme"], $arServer["host"], $arServer["port"], $username, $password, $arServer["path"]);
	}

	public static function RemoveConnection($connection = array())
	{
		// Clean sections
		$res = CCalendarSect::GetList(array('arFilter' => array('CAL_TYPE' => 'user', 'OWNER_ID' => self::$ownerId, 'CAL_DAV_CON' => $connection['id'])));

		$pushChannelResult = PushTable::getList(array(
			'select' => array('*'),
			'filter' => array('ENTITY_TYPE' => 'CONNECTION', 'ENTITY_ID' => $connection['id'])
		));
		if ($row = $pushChannelResult->fetch())
		{
			\Bitrix\Calendar\Sync\GoogleApiPush::stopChannel($row, $connection['ENTITY_ID']);
		}

		foreach ($res as $sect)
		{
			if ($connection['del_calendars'] == 'Y') // Delete all callendars from this connection
			{
				$pushChannelResult = PushTable::getList(array(
					'select' => array('*'),
					'filter' => array('ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $sect['ID'])
				));
				if ($row = $pushChannelResult->fetch())
				{
					\Bitrix\Calendar\Sync\GoogleApiPush::stopChannel($row, $sect['OWNER_ID']);
				}
				CCalendarSect::Delete($sect['ID'], false);
			}
			else
				CCalendarSect::Edit(array('arFields' => array("ID" => $sect['ID'], "CAL_DAV_CON" => '', 'CAL_DAV_CAL' => '', 'CAL_DAV_MOD' => '')));
		}

		// Delete Google oauth token if it's google oauth caldav connection
		$resCon = CDavConnection::GetList(array("ID" => "ASC"), array("ID" => $connection['id']));
		if ($arCon = $resCon->Fetch())
		{
			$googleCalDavStatus = CCalendarSync::GetGoogleCalendarConnection();
			if($googleCalDavStatus['googleCalendarPrimaryId'] && $arCon['ACCOUNT_TYPE'] == 'caldav_google_oauth')
			{
				$serverPath = 'https://apidata.googleusercontent.com/caldav/v2/' . $googleCalDavStatus['googleCalendarPrimaryId'] . '/user';
			}
			elseif ($googleCalDavStatus['googleCalendarPrimaryId'] && $arCon['ACCOUNT_TYPE'] == 'google_api_oauth')
			{
				$serverPath = 'https://www.googleapis.com/calendar/v3';
			}

			if ($arCon['SERVER'] == $serverPath)
			{
				if (\Bitrix\Main\Loader::includeModule('socialservices'))
				{
					$client = new CSocServGoogleOAuth(CCalendar::GetCurUserId());
					$client->getEntityOAuth()->addScope(array('https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/calendar.readonly'));

					// Delete stored tokens
					$client->getEntityOAuth()->deleteStorageTokens();
				}
			}
		}
		// Delete dav connections
		CDavConnection::Delete($connection['id']);
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
			self::$cacheTime = $time;
		return self::$cacheTime;
	}

	public static function _ParseHack(&$text, &$TextParser)
	{
		$text = preg_replace(array("/\&lt;/is".BX_UTF_PCRE_MODIFIER, "/\&gt;/is".BX_UTF_PCRE_MODIFIER),array('<', '>'),$text);

		$text = preg_replace("/\<br\s*\/*\>/is".BX_UTF_PCRE_MODIFIER,"", $text);
		$text = preg_replace("/\<(\w+)[^>]*\>(.+?)\<\/\\1[^>]*\>/is".BX_UTF_PCRE_MODIFIER,"\\2",$text);
		$text = preg_replace("/\<*\/li\>/is".BX_UTF_PCRE_MODIFIER,"", $text);

		$text = str_replace(array("<", ">"),array("&lt;", "&gt;"),$text);

		$TextParser->allow = array();
		return true;
	}

	public static function GetUserManagers($userId, $bReturnIds = false)
	{
		if (!isset(self::$userManagers[$userId]))
		{
			$rsUser = CUser::GetByID($userId);
			if($arUser = $rsUser->Fetch())
			{
				self::SetUserDepartment($userId, $arUser["UF_DEPARTMENT"]);
				self::$userManagers[$userId] = CIntranetUtils::GetDepartmentManager($arUser["UF_DEPARTMENT"], $arUser["ID"], true);
			}
			else
			{
				self::$userManagers[$userId] = false;
			}
		}

		if (!$bReturnIds)
			return self::$userManagers[$userId];

		$res = array();
		if (is_array(self::$userManagers[$userId]))
		{
			foreach(self::$userManagers[$userId] as $user)
				$res[] = $user['ID'];
		}
		return $res;
	}

	public static function IsSocnetAdmin()
	{
		if (!isset(self::$bCurUserSocNetAdmin))
			self::$bCurUserSocNetAdmin = self::IsSocNet() && CSocNetUser::IsCurrentUserModuleAdmin();

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
			return array();
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

	public static function GetFormatedDestination($codes = array())
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
		if (intVal($fromTs) != $fromTs)
		{
			$fromTs = self::Timestamp($fromTs);
		}
		if (intVal($toTs) != $toTs)
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

	public static function GetSocNetDestination($user_id = false, $selected = array(), $userList = array())
	{
		\Bitrix\Main\Loader::includeModule("socialnetwork");

		global $CACHE_MANAGER;

		if (!is_array($selected))
		{
			$selected = array();
		}

		if (method_exists('CSocNetLogDestination','GetDestinationSort'))
		{
			$DESTINATION = array(
				'LAST' => array(),
				'DEST_SORT' => CSocNetLogDestination::GetDestinationSort(array("DEST_CONTEXT" => "CALENDAR"))
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

		$destinationUserList = Array();
		$DESTINATION['SELECTED'] = Array();

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
			if (substr($code, 0 , 2) == 'DR')
			{
				$DESTINATION['SELECTED'][$code] = "department";
			}
			elseif (substr($code, 0 , 2) == 'UA')
			{
				$DESTINATION['SELECTED'][$code] = "groups";
			}
			elseif (substr($code, 0 , 2) == 'SG')
			{
				$DESTINATION['SELECTED'][$code] = "sonetgroups";
			}
			elseif (substr($code, 0 , 1) == 'U')
			{
				$DESTINATION['SELECTED'][$code] = "users";
				$destinationUserList[] = intVal(str_replace('U', '', $code));
			}
		}

		// intranet structure
		$arStructure = CSocNetLogDestination::GetStucture();
		//$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
		$DESTINATION['DEPARTMENT'] = $arStructure['department'];
		$DESTINATION['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$DESTINATION['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		if (\Bitrix\Main\Loader::includeModule('extranet') && !CExtranet::IsIntranetUser())
		{
			$DESTINATION['EXTRANET_USER'] = 'Y';
			$DESTINATION['USERS'] = CSocNetLogDestination::GetExtranetUser();
		}
		else
		{
			if (is_array($DESTINATION['LAST']['USERS']))
			{
				foreach ($DESTINATION['LAST']['USERS'] as $value)
				{
					$destinationUserList[] = intVal(str_replace('U', '', $value));
				}
			}

			if (!empty($userList))
			{
				$destinationUserList = array_unique(array_merge($destinationUserList, $userList));
			}

			$DESTINATION['EXTRANET_USER'] = 'N';
			$DESTINATION['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $destinationUserList));
		}

		$users = array();
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
		if (!is_array($user) && intVal($user) > 0)
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
		return intVal($offset);
	}

	public static function OnSocNetGroupDelete($groupId)
	{
		$groupId = intVal($groupId);
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
		$userAgent = strtolower($event->getParameter('agent'));
		$agent = false;
		list($sectionId, $entityType, $entityId) = $calendarId;

		static $arAgentsMap = array(
				'android' => 'android', // Android/iOS CardDavBitrix24
				'iphone' => 'iphone', // Apple iPhone iCal
				'davkit' => 'mac', // Apple iCal
				'mac os' => 'mac', // Apple iCal (Mac Os X > 10.8)
				'mac_os_x' => 'mac', // Apple iCal (Mac Os X > 10.8)
				'mac+os+x' => 'mac', // Apple iCal (Mac Os X > 10.10)
				'dataaccess' => 'iphone', // Apple addressbook iPhone
				//'sunbird' => 'sunbird', // Mozilla Sunbird
				'ios' => 'iphone'
		);

		foreach ($arAgentsMap as $pattern => $name)
		{
			if (strpos($userAgent, $pattern) !== false)
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
	 * @param string $date Date of synchronization.
	 * @return null
	 */
	public static function SaveSyncDate($userId, $syncType, $date = false)
	{
		if ($date === false)
			$date = self::Date(time());
		$syncTypes = array('iphone', 'android', 'mac', 'exchange', 'outlook', 'office365');
		if (in_array($syncType, $syncTypes))
		{
			CUserOptions::SetOption("calendar", "last_sync_".$syncType, $date, false, $userId);
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

		$ids = array();
		foreach($users as $user)
		{
			if (intVal($user) > 0)
				$ids[] = intVal($user);
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

			$counters = array();
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
			\Bitrix\Main\Loader::includeModule("socialnetwork");
			self::$bSocNet = class_exists('CSocNetUserToGroup') && CBXFeatures::IsFeatureEnabled("Calendar") && self::IsIntranetEnabled();
		}

		return self::$bSocNet;
	}

	public static function GetCurUserId($refresh = false)
	{
		global $USER;

		if (!isset(self::$curUserId) || $refresh || !self::$curUserId)
		{
			if (is_object($USER) && $USER->IsAuthorized())
				self::$curUserId = $USER->GetId();
			else
				self::$curUserId = 0;
		}

		return self::$curUserId;
	}

	public static function GetSettings($params = array())
	{
		if (!is_array($params))
			$params = array();
		if (isset(self::$settings) && count(self::$settings) > 0 && $params['request'] === false)
			return self::$settings;

		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if ($params['forseGetSitePathes'] || !$pathes_for_sites)
			$pathes = self::GetPathes(isset($params['site']) ? $params['site'] : false);
		else
			$pathes = array();

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
			'denied_superpose_types' => unserialize(COption::GetOptionString('calendar', 'denied_superpose_types', serialize(array()))),
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
			if (self::$settings['forum_id'] == "" && \Bitrix\Main\Loader::includeModule("forum"))
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
		$pathes = array();
		$pathes_for_sites = COption::GetOptionString('calendar', 'pathes_for_sites', true);
		if ($forSite === false)
		{
			$arAffectedSites = COption::GetOptionString('calendar', 'pathes_sites', false);

			if ($arAffectedSites != false && CheckSerializedData($arAffectedSites))
				$arAffectedSites = unserialize($arAffectedSites);
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
					$ar = unserialize($ar);
					if(is_array($ar))
						$pathes[$s] = $ar;
				}
			}
		}

		if ($forSite !== false)
		{
			$result = array();
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

	public static function SetUserSettings($settings = array(), $userId = false)
	{
		CCalendarUserSettings::Set($settings, $userId);
	}

	public static function GetUserSettings($userId = false)
	{
		return CCalendarUserSettings::Get($userId);
	}

	public static function GetPermissions($Params = array())
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

				$arCodes = array();
				$rCodes = CAccess::GetUserCodes($userId);
				while($code = $rCodes->Fetch())
					$arCodes[] = $code['ACCESS_CODE'];

				if (\Bitrix\Main\Loader::includeModule("socialnetwork"))
				{
					$group = CSocNetGroup::getByID($ownerId);
					if(!empty($group['CLOSED']) && $group['CLOSED'] === 'Y' &&
						\Bitrix\Main\Config\Option::get('socialnetwork', 'work_with_closed_groups', 'N') === 'N')
					{
						self::$isArchivedGroup = true;
					}
				}

				if (in_array($keyOwner, $arCodes))// Is owner
				{
					$bEdit = true;
					$bEditSection = true;
				}
				elseif(in_array($keyMod, $arCodes) && !self::$isArchivedGroup)// Is moderator
				{
					$bEdit = true;
					$bEditSection = true;
				}
				elseif(in_array($keyMember, $arCodes) && !self::$isArchivedGroup)// Is member
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
				if ($type == 'user')
				{
					$path = COption::GetOptionString(
						'calendar',
						'path_to_user_calendar',
						COption::getOptionString('socialnetwork', 'user_page', "/company/personal/")."user/#user_id#/calendar/"
					);
				}
				elseif($type == 'group')
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
		if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0)
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
		if (!$eventId)
			return false;

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

			return $event;
		}
		else
		{
			CCalendarNotify::ClearNotifications($eventId);
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
		elseif (!\Bitrix\Main\Loader::includeModule('dav'))
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
				&& \Bitrix\Main\Loader::includeModule('socialservices'))
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
		return \Bitrix\Main\Loader::includeModule('dav') && CDavGroupdavClientCalendar::IsCalDAVEnabled();
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
			self::$bExtranet = \Bitrix\Main\Loader::includeModule('extranet') && CExtranet::IsExtranetSite();
		return self::$bExtranet;
	}

	public static function IsAbsenceEnabled()
	{
		return !self::IsBitrix24()
		|| COption::GetOptionString("bitrix24",  "absence_limits_enabled", "") != "Y"
		|| \Bitrix\Bitrix24\Feature::isFeatureEnabled("absence");
	}

	public static function GetMeetingRoomList($Params = array())
	{
		if (isset(self::$meetingRoomList))
			return self::$meetingRoomList;

		if (!isset($Params['RMiblockId']) && !isset($Params['VMiblockId']))
		{
			if (!isset(self::$settings))
				self::$settings = self::GetSettings();

			if (!self::$pathesForSite)
				self::$pathesForSite = self::GetSettings(array('forseGetSitePathes' => true,'site' =>self::GetSiteId()));
			$RMiblockId = self::$settings['rm_iblock_id'];
			$pathToMR = self::$pathesForSite['path_to_rm'];
		}
		else
		{
			$RMiblockId = $Params['RMiblockId'];
			$pathToMR = $Params['pathToMR'];
		}
		$MRList = Array();
		if (IntVal($RMiblockId) > 0 && CIBlock::GetPermission($RMiblockId) >= "R" && self::$allowReserveMeeting)
		{
			$arOrderBy = array("NAME" => "ASC", "ID" => "DESC");
			$arFilter = array("IBLOCK_ID" => $RMiblockId, "ACTIVE" => "Y");
			$arSelectFields = array("IBLOCK_ID","ID","NAME","DESCRIPTION","UF_FLOOR","UF_PLACE","UF_PHONE");
			$res = CIBlockSection::GetList($arOrderBy, $arFilter, false, $arSelectFields );
			while ($arMeeting = $res->GetNext())
			{
				$MRList[] = array(
					'ID' => $arMeeting['ID'],
					'NAME' => $arMeeting['~NAME'],
					'DESCRIPTION' => $arMeeting['~DESCRIPTION'],
					'UF_PLACE' => $arMeeting['UF_PLACE'],
					'UF_PHONE' => $arMeeting['UF_PHONE'],
					'URL' => str_replace(array("#id#", "#ID#"), $arMeeting['ID'], $pathToMR)
				);
			}
		}

		self::$meetingRoomList = $MRList;
		return $MRList;
	}

	public static function GetCurrentOffsetUTC($userId = false)
	{
		if (!$userId && self::$userId)
			$userId = self::$userId;
		return intVal(date("Z") + self::GetOffset($userId));
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

	public static function GetUserTimezoneName($user)
	{
		if (!is_array($user) && intVal($user) > 0)
			$user = self::GetUser($user, true);

		$tzName = CUserOptions::GetOption("calendar", "timezone".self::GetCurrentOffsetUTC($user['ID']), false, $user['ID']);
		if ($tzName === 'undefined' || $tzName === 'false')
			$tzName = false;

		if (!$tzName && $user['AUTO_TIME_ZONE'] !== 'Y' && $user['TIME_ZONE'])
		{
			$tzName = $user['TIME_ZONE'];
		}

		return $tzName;
	}

	public static function GetUser($userId, $bPhoto = false)
	{
		global $USER;
		if (is_object($USER) && intVal($userId) == $USER->GetId() && !$bPhoto)
		{
			$user = array(
				'ID' => $USER->GetId(),
				'NAME' => $USER->GetFirstName(),
				'LAST_NAME' => $USER->GetLastName(),
				'SECOND_NAME' => $USER->GetParam('SECOND_NAME'),
				'LOGIN' => $USER->GetLogin(),
				'PERSONAL_PHOTO' => $USER->GetParam('PERSONAL_PHOTO'),
			);
		}
		else
		{
			$rsUser = CUser::GetByID(intVal($userId));
			$user = $rsUser->Fetch();
		}
		return $user;
	}

	public static function GetGoodTimezoneForOffset($offset)
	{
		$timezones = self::GetTimezoneList();
		$goodTz = array();
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
				elseif (strpos($tz['timezone_id'], 'Europe') !== false)
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
			self::$timezones = array();
			static $aExcept = array("Etc/", "GMT", "UTC", "UCT", "HST", "PST", "MST", "CST", "EST", "CET", "MET", "WET", "EET", "PRC", "ROC", "ROK", "W-SU");
			foreach(DateTimeZone::listIdentifiers() as $tz)
			{
				foreach($aExcept as $ex)
					if(strpos($tz, $ex) === 0)
						continue 2;
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
		if (!is_array($user) && intVal($user) > 0)
			$user = self::GetUser($user);

		if(!$user || !is_array($user))
			return '';

		return CUser::FormatName(self::$userNameTemplate, $user, self::$showLogin, false);
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
		$googleCalDavStatus = array();
		$googleApiStatus = array();
		$JSConfig['bCalDAV'] = true;
		$JSConfig['caldav_link_all'] = CCalendar::GetServerPath();

		if (self::$type == 'user' && self::IsPersonal() && \Bitrix\Main\Loader::includeModule('dav'))
		{
			$tzEnabled = CTimeZone::Enabled();
			if ($tzEnabled)
				CTimeZone::Disable();

			$connectionList = array();
			$res = CDavConnection::GetList(
				array("ID" => "DESC"),
				array(
					"ENTITY_TYPE" => "user",
					"ENTITY_ID" => self::$ownerId,
					'ACCOUNT_TYPE' => array('caldav_google_oauth', 'google_api_oauth', 'caldav')
				), false, false);

			if ($isGoogleApiEnabled)
			{
				$googleApiConnection = new GoogleApiSync(self::getCurUserId());
				$transportErrors = $googleApiConnection->getTransportErrors();
				if (!$transportErrors)
				{
					$googleApiStatus['googleCalendarPrimaryId'] = $googleApiConnection->getPrimaryId();
				}

				if (empty($googleApiStatus['googleCalendarPrimaryId']))
				{
					$curPath = CCalendar::GetPath();
					if($curPath)
						$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
					$client = new CSocServGoogleOAuth(self::$ownerId);
					$client->getEntityOAuth()->addScope(array(
						'https://www.googleapis.com/auth/calendar',
						'https://www.googleapis.com/auth/calendar.readonly'
					));
					$googleApiStatus['authLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
				}
			}
			else
			{
				$googleCalDavStatus = CCalendarSync::GetGoogleCalendarConnection();
			}

			$googleApiConnections = array();
			while($connection = $res->Fetch())
			{
				$connectionListItem = array(
					'id' => $connection['ID'],
					'server_host' => $connection['SERVER_HOST'],
					'account_type' => $connection['ACCOUNT_TYPE'],
					'name' => $connection['NAME'],
					'link' => $connection['SERVER'],
					'user_name' => $connection['SERVER_USERNAME'],
					'last_result' => $connection['LAST_RESULT'],
					'sync_date' => $connection['SYNCHRONIZED']
				);
				$connectionList[] = $connectionListItem;
				if ($connection['ACCOUNT_TYPE'] == 'caldav_google_oauth' || $connection['ACCOUNT_TYPE'] == 'google_api_oauth')
				{
					$googleApiConnections[] = $connectionListItem;
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
					}

					if (empty($googleApiStatus['googleCalendarPrimaryId']))
					{
						$curPath = CCalendar::GetPath();
						if($curPath)
							$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
						$client = new CSocServGoogleOAuth(self::$ownerId);
						$client->getEntityOAuth()->addScope(array(
							'https://www.googleapis.com/auth/calendar',
							'https://www.googleapis.com/auth/calendar.readonly'
						));
						$googleApiStatus['authLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
					}
				}

				if ($googleApiStatus['googleCalendarPrimaryId'] || !empty($googleApiConnections))
				{
					$serverPath = 'https://www.googleapis.com/calendar/v3';
					foreach ($googleApiConnections as $connection)
					{
						if (($connection['link'] == $serverPath || $connection['account_type'] == 'caldav_google_oauth') || $connection['account_type'] == 'google_api_oauth')
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
						$sAccountType = 'google_api_oauth';
						$sServer = 'https://www.googleapis.com/calendar/v3';
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
							CCalendarSync::dataSync(array_merge($connectionData, array('ID' => $conId)));

							$pushOptionEnabled = COption::GetOptionString('calendar', 'sync_by_push', false);
							if ($pushOptionEnabled || CCalendar::IsBitrix24())
							{
								$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID']);
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
							$res = CDavConnection::GetList(array("ID" => "DESC"), array("ID" => $conId), false, false);
							if ($connection = $res->Fetch())
							{
								$connectionList[] = array(
									'id' => $connection['ID'],
									'server_host' => $connection['SERVER_HOST'],
									'account_type' => $connection['ACCOUNT_TYPE'],
									'name' => $connection['NAME'],
									'link' => $connection['SERVER'],
									'user_name' => $connection['SERVER_USERNAME'],
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
				$serverPath = 'https://apidata.googleusercontent.com/caldav/v2/'.$googleCalDavStatus['googleCalendarPrimaryId'].'/user';

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
						"ACCOUNT_TYPE" => 'caldav_google_oauth',
						"NAME" => 'Google Calendar ('.$googleCalDavStatus['googleCalendarPrimaryId'].')',
						"SERVER" => 'https://apidata.googleusercontent.com/caldav/v2/'.$googleCalDavStatus['googleCalendarPrimaryId'].'/user'
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
								'user_name' => $connection['SERVER_USERNAME'],
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

//	public static function InitGoogleApiParams(&$JSConfig)
//	{
//		if (!\Bitrix\Main\Loader::includeModule('socialservices') || !\Bitrix\Main\Loader::includeModule('dav'))
//		{
//			return;
//		}
//		$googleApiStatus = array();
//
//		if (self::$type != 'user')
//		{
//			return;
//		}
//
//		$connectionList = array();
//		$res = CDavConnection::GetList(
//			array("ID" => "DESC"),
//			array(
//				"ENTITY_TYPE" => "user",
//				"ENTITY_ID" => self::$ownerId,
//				'ACCOUNT_TYPE' => array('caldav_google_oauth', 'google_api_oauth')
//			), false, false);
//
//		while ($arCon = $res->Fetch())
//		{
//			$connectionList[] = array(
//				'id' => $arCon['ID'],
//				'server_host' => $arCon['SERVER_HOST'],
//				'account_type' => $arCon['ACCOUNT_TYPE'],
//				'name' => $arCon['NAME'],
//				'link' => $arCon['SERVER'],
//				'user_name' => $arCon['SERVER_USERNAME'],
//				'last_result' => $arCon['LAST_RESULT'],
//				'sync_date' => $arCon['SYNCHRONIZED']
//			);
//		}
//
//		if (self::IsPersonal())
//		{
//			if (!$connectionList)
//			{
//				$googleApiConnection = new GoogleApiSync(self::getCurUserId());
//				$transportErrors = $googleApiConnection->getTransportErrors();
//				if (!$transportErrors)
//				{
//					$googleApiStatus['googleCalendarPrimaryId'] = $googleApiConnection->getPrimaryId();
//				}
//
//				if (empty($googleApiStatus['googleCalendarPrimaryId']))
//				{
//					$curPath = CCalendar::GetPath();
//					if($curPath)
//						$curPath = CHTTP::urlDeleteParams($curPath, array("action", "sessid", "bx_event_calendar_request", "EVENT_ID"));
//					$client = new CSocServGoogleOAuth(self::$ownerId);
//					$client->getEntityOAuth()->addScope(array(
//						'https://www.googleapis.com/auth/calendar',
//						'https://www.googleapis.com/auth/calendar.readonly'
//					));
//					$googleApiStatus['authLink'] = $client->getUrl('opener', null, array('BACKURL' => $curPath));
//				}
//			}
//
//			if ($googleApiStatus['googleCalendarPrimaryId'] || $connectionList)
//			{
//				$serverPath = 'https://www.googleapis.com/calendar/v3';
//				$addConnection = true;
//
//				foreach ($connectionList as $arCon)
//				{
//					if (($arCon['link'] == $serverPath || $arCon['account_type'] == 'caldav_google_oauth') || $arCon['account_type'] == 'google_api_oauth')
//					{
//						$googleApiStatus['last_result']   = $arCon['last_result'];
//						$googleApiStatus['sync_date']     = CCalendar::Date(self::Timestamp($arCon['sync_date']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
//						$googleApiStatus['connection_id'] = $arCon['id'];
//
//						$addConnection = false;
//						break;
//					}
//
//				}
//
//				if ($addConnection && !$connectionList)
//				{
//					$sAccountType = 'google_api_oauth';
//					$sServer = 'https://www.googleapis.com/calendar/v3';
//					$connectionData = array(
//						"ENTITY_TYPE" => 'user',
//						"ENTITY_ID" => self::$ownerId,
//						"ACCOUNT_TYPE" => $sAccountType,
//						"NAME" => 'Google Calendar ('.$googleApiStatus['googleCalendarPrimaryId'].')',
//						"SERVER" => $sServer,
//						"SYNC_TOKEN" => NULL,
//					);
//					$conId = CDavConnection::Add($connectionData);
//
//					if ($conId)
//					{
//						CCalendarSync::dataSync(array_merge($connectionData, array('ID' => $conId)));
//
//						$pushOptionEnabled = COption::GetOptionString('calendar', 'sync_by_push', false);
//						if ($pushOptionEnabled || CCalendar::IsBitrix24())
//						{
//							$googleApiConnection = new GoogleApiSync($connectionData['ENTITY_ID']);
//							$channelInfo = $googleApiConnection->startWatchCalendarList($connectionData['NAME']);
//							if ($channelInfo)
//							{
//								PushTable::delete(array("ENTITY_TYPE" => 'CONNECTION', 'ENTITY_ID' => $conId));
//								PushTable::add(array(
//									'ENTITY_TYPE' => 'CONNECTION',
//									'ENTITY_ID' => $conId,
//									'CHANNEL_ID' => $channelInfo['id'],
//									'RESOURCE_ID' => $channelInfo['resourceId'],
//									'EXPIRES' => $channelInfo['expiration'],
//									'NOT_PROCESSED' => 'N'
//								));
//							}
//						}
//						$res = CDavConnection::GetList(array("ID" => "DESC"), array("ID" => $conId), false, false);
//						if ($arCon = $res->Fetch())
//						{
//							$connectionList[] = array(
//								'id' => $arCon['ID'],
//								'server_host' => $arCon['SERVER_HOST'],
//								'account_type' => $arCon['ACCOUNT_TYPE'],
//								'name' => $arCon['NAME'],
//								'link' => $arCon['SERVER'],
//								'user_name' => $arCon['SERVER_USERNAME'],
//								'last_result' => $arCon['LAST_RESULT'],
//								'sync_date' => $arCon['SYNCHRONIZED']
//							);
//							$googleApiStatus['connection_id'] = $arCon['ID'];
//							$googleApiStatus['last_result']   = $arCon['LAST_RESULT'];
//							$googleApiStatus['sync_date']     = CCalendar::Date(self::Timestamp($arCon['SYNCHRONIZED']) + CCalendar::GetOffset(self::$ownerId), true, true, true);
//						}
//					}
//				}
//			}
//		}
//
//		$JSConfig['googleCalDavStatus'] = $googleApiStatus;
//		$JSConfig['connections'] = $connectionList;
//	}

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
			$period = time() - self::Timestamp($result['date']);
			if ($period >= 0 && $period <= $activeSyncPeriod)
			{
				$result['date'] = CCalendar::Date(self::Timestamp($result['date']) + CCalendar::GetOffset($userId), true, true, true);
				$result['connected'] = true;
			}
		}

		return $result;
	}

	public static function GetDisplayedSuperposed($userId = false)
	{
		if (!class_exists('CUserOptions') || !$userId)
			return false;

		$res = array();

		$def = CUserOptions::GetOption("calendar", "superpose_displayed_default", false, $userId);
		$saveOption = false;
		if (intval($def) > 0)
		{
			$saveOption = true;
			$res[] = intVal($def);
		}

		$str = CUserOptions::GetOption("calendar", "superpose_displayed", false, $userId);
		if (CheckSerializedData($str))
		{
			$arIds = unserialize($str);
			if (is_array($arIds) && count($arIds) > 0)
			{
				foreach($arIds as $id)
				{
					if (intVal($id) > 0)
					{
						$res[] = intVal($id);
					}
				}
			}
		}

		if ($saveOption)
		{
			CUserOptions::SetOption("calendar", "superpose_displayed", serialize($res));
			CUserOptions::SetOption("calendar", "superpose_displayed_default", false);
		}

		return $res;
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
			$set = CCalendarUserSettings::Get($userId);

			$result = $set['meetSection'];
			$section = false;

			if ($result)
			{
				$section = CCalendarSect::GetList(
					array('arFilter' => array('ID' => $result),
					'checkPermissions' => false,
					'getPermissions' => false

				));
				if($section && is_array($section) && is_array($section[0]))
				{
					$section = $section[0];
				}
			}

			if($result && !$section)
				$result = false;

			if (!$result)
			{
				$res = CCalendarSect::GetList(array(
					'arFilter' => array(
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId
					),
					'checkPermissions' => false,
					'getPermissions' => false
				));
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
					$set['meetSection'] = $result;
					CCalendarUserSettings::Set($set, $userId);
				}
			}
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
			$set = CCalendarUserSettings::Get($userId);

			$result = $set['crmSection'];
			$section = false;

			if ($result)
			{
				$section = CCalendarSect::GetList(
					array('arFilter' => array('ID' => $result),
					'checkPermissions' => false,
					'getPermissions' => false
				));
				if($section && is_array($section) && is_array($section[0]))
				{
					$section = $section[0];
				}
			}

			if($result && !$section)
				$result = false;

			if (!$result)
			{
				$res = CCalendarSect::GetList(array(
					'arFilter' => array(
						'CAL_TYPE' => 'user',
						'OWNER_ID' => $userId
					),
					'checkPermissions' => false,
					'getPermissions' => false
				));
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
					CCalendarUserSettings::Set($set, $userId);
				}
			}
		}

		self::$crmSections[$userId] = $result;
		return $result;
	}

	public static function GetSectionList($params = array())
	{
		$type = isset($params['CAL_TYPE']) ? $params['CAL_TYPE'] : self::$type;
		$arFilter = array(
			'CAL_TYPE' => $type,
		);

		if (isset($params['OWNER_ID']))
			$arFilter['OWNER_ID'] = $params['OWNER_ID'];
		elseif ($type == 'user' || $type == 'group')
			$arFilter['OWNER_ID'] = self::GetOwnerId();
		if (isset($params['ACTIVE']))
			$arFilter['ACTIVE'] = $params['ACTIVE'];

		if (isset($params['ADDITIONAL_IDS']) && count($params['ADDITIONAL_IDS']) > 0)
			$arFilter['ADDITIONAL_IDS'] = $params['ADDITIONAL_IDS'];

		$res = CCalendarSect::GetList(
			array(
				'arFilter' => $arFilter,
				'checkPermissions' => $params['checkPermissions']
			)
		);
		return $res;
	}

	public static function GetOwnerId()
	{
		return self::$ownerId;
	}

	public static function GetEventList($params = array(), &$arAttendees)
	{
		$type = isset($params['type']) ? $params['type'] : self::$type;
		$ownerId = isset($params['ownerId']) ? $params['ownerId'] : self::$ownerId;
		$userId = isset($params['userId']) ? $params['userId'] : self::$userId;

		if ($type != 'user' && !isset($params['section']) || count($params['section']) <= 0)
			return array();

		$arFilter = array();

		CCalendarEvent::SetLastAttendees(false);

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
			$NewRes = array();
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

		$arAttendees = CCalendarEvent::GetLastAttendees();
		return $res;
	}

	public static function getTaskList($params = array())
	{
		$res = array();

		$arFilter = array(
			'!STATUS' => array(
				CTasks::STATE_DEFERRED,
			),
			'CHECK_PERMISSIONS' => 'Y'
		);

		if (self::$userSettings['showCompletedTasks'] == 'N')
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
		if($tzEnabled)
			CTimeZone::Disable();

		$mgrResult = \Bitrix\Tasks\Manager\Task::getList(
			\Bitrix\Tasks\Util\User::getId(),
			array(
				'order' => array("START_DATE_PLAN" => "ASC"),
				'select' => array("ID", "TITLE", "DESCRIPTION", "CREATED_DATE", "DEADLINE", "START_DATE_PLAN", "END_DATE_PLAN", "CLOSED_DATE", "STATUS_CHANGED_DATE", "STATUS", "REAL_STATUS"),
				'legacyFilter' => $arFilter,
			),
			array()
		);

		$offset = CCalendar::GetOffset();
		foreach ($mgrResult['DATA'] as $task)
		{
			$dtFrom = NULL;
			$dtTo = NULL;

			$skipFromOffset = false;
			$skipToOffset = false;

			if(isset($task["START_DATE_PLAN"]) && $task["START_DATE_PLAN"])
				$dtFrom = CCalendar::CutZeroTime($task["START_DATE_PLAN"]);

			if(isset($task["END_DATE_PLAN"]) && $task["END_DATE_PLAN"])
				$dtTo = CCalendar::CutZeroTime($task["END_DATE_PLAN"]);

			if(!isset($dtTo) && isset($task["CLOSED_DATE"]))
				$dtTo = CCalendar::CutZeroTime($task["CLOSED_DATE"]);

			//Task statuses: 1 - New, 2 - Pending, 3 - In Progress, 4 - Supposedly completed, 5 - Completed, 6 - Deferred, 7 - Declined
			if(!isset($dtTo) && isset($task["STATUS_CHANGED_DATE"]) && in_array($task["REAL_STATUS"], array('4', '5', '6', '7')))
				$dtTo = CCalendar::CutZeroTime($task["STATUS_CHANGED_DATE"]);

			if(isset($dtTo))
			{
				$ts = CCalendar::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
				if(date("H:i", $ts) == '00:00')
					$dtTo = CCalendar::Date($ts - 24 * 60 * 60);
			}
			elseif(isset($task["DEADLINE"]))
			{
				$dtTo = CCalendar::CutZeroTime($task["DEADLINE"]);
				$ts = CCalendar::Timestamp($dtTo); // Correction display logic for harmony with Tasks interfaces
				if(date("H:i", $ts) == '00:00')
					$dtTo = CCalendar::Date($ts - 24 * 60 * 60);

				if(!isset($dtFrom))
				{
					$skipFromOffset = true;
					$dtFrom = CCalendar::Date(time(), false);
				}
			}

			if(!isset($dtTo))
				$dtTo = CCalendar::Date(time(), false);

			if(!isset($dtFrom))
				$dtFrom = $dtTo;

			$dtFromTS = CCalendar::Timestamp($dtFrom);
			$dtToTS = CCalendar::Timestamp($dtTo);

			if($dtToTS < $dtFromTS)
			{
				$dtToTS = $dtFromTS;
				$dtTo = CCalendar::Date($dtToTS, true);
			}

			$skipTime = date("H:i", $dtFromTS) == '00:00' && date("H:i", $dtToTS) == '00:00';
			if(!$skipTime && $offset != 0)
			{
				if(!$skipFromOffset)
				{
					$dtFromTS += $offset;
					$dtFrom = CCalendar::Date($dtFromTS, true);
				}

				if(!$skipToOffset)
				{
					$dtToTS += $offset;
					$dtTo = CCalendar::Date($dtToTS, true);
				}
			}

			$res[] = array(
				"ID" => $task["ID"],
				"~TYPE" => "tasks",
				"NAME" => $task["TITLE"],
				"DATE_FROM" => $dtFrom,
				"DATE_TO" => $dtTo,
				"DT_SKIP_TIME" => $skipTime ? 'Y' : 'N',
				"CAN_EDIT" => CTasks::CanCurrentUserEdit($task)
			);
		}

		if($tzEnabled)
			CTimeZone::Enable();

		return $res;
	}

	public static function CutZeroTime($date)
	{
		if (preg_match('/.*\s\d\d:\d\d:\d\d/i', $date))
		{
			$date = trim($date);
			if (substr($date, -9) == ' 00:00:00')
				return substr($date, 0, -9);
			if (substr($date, -3) == ':00')
				return substr($date, 0, -3);
		}
		return $date;
	}

	public static function GetType()
	{
		return self::$type;
	}

	public static function GetAccessNames()
	{
		$arCodes = array();
		foreach (self::$accessNames as $code => $name)
		{
			if ($name === null)
				$arCodes[] = $code;
		}

		if ($arCodes)
		{
			$access = new CAccess();
			$arNames = $access->GetNames($arCodes);
			foreach($arNames as $code => $name)
			{
				self::$accessNames[$code] = trim(htmlspecialcharsbx($name['provider'].' '.$name['name']));
			}
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
}
?>