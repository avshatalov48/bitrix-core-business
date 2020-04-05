<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/prolog.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/calendar/options.php");

$adminChain->AddItem(array("TEXT" => GetMessage("CAL_SETTINGS"), "LINK" => "all_settings_index.php?lang=".LANG));
$adminChain->AddItem(array("TEXT" => GetMessage("CAL_PRODUCT_SETTINGS"), "LINK" => "settings_index.php?lang=".LANG));
$adminChain->AddItem(array("TEXT" => GetMessage("CAL_MODULES"), "LINK" => "module_admin.php?lang=".LANG));
$adminChain->AddItem(array("TEXT" => GetMessage("CAL_CONVERT"), "LINK" => "calendar_convert.php?lang=".LANG));

$adminMenu->aActiveSections[] = $adminMenu->aGlobalMenu["global_menu_settings"];

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$CNT = 0;

class CCalendarConvert
{
	private static
		$iblockTypes = array(),
		$iblockIds = array(),
		$settings = array(),
		$arIBTypes = array(),
		$arIBlocks = array(),
		$accessTasks,
		$start_time,
		$time_limit = 10,
		$userIblockId,
		$curSite,
		$bSkip = false,
		$lastSite = false,
		$lastPath = false,
		$types;

	function ParseParams($lastPath = false, $lastSite = false)
	{
		CModule::IncludeModule("fileman");
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/classes/general/fileman_utils.php");

		self::$start_time = microtime(true);

		if ($lastPath != "" && $lastSite != "")
		{
			self::$bSkip = true;
			self::$lastSite = $lastSite;
			self::$lastPath = $lastPath;
		}
		else
		{
			self::$types = array(
				'user' => array(
					'sys' => true,
					'title' => GetMessage('CAL_CONVERT_USERS'),
					'iblockType' => 0,
					'iblockId' => 0,
					'checked' => true
				),
				'group' => array(
					'sys' => true,
					'title' => GetMessage('CAL_CONVERT_GROUPS'),
					'iblockType' => 0,
					'iblockId' => 0,
					'checked' => true
				)
			);

			CCalendarConvert::SetOption('__convert_types', self::$types);
			CCalendarConvert::SetOption('__convert_ibl_types', array());
			CCalendarConvert::SetOption('__convert_settings', array());
			CCalendarConvert::SetOption('__convert_doc_roots', array());
		}

		$dbSites = CSite::GetList($by = 'sort', $order = 'asc', array('ACTIVE' => 'Y'));
		$arSites = array();
		$default_site = '';
		$arDocRoots = CCalendarConvert::GetOption('__convert_doc_roots', serialize(array()));
		while ($arRes = $dbSites->GetNext())
		{
			self::$curSite = $arRes['ID'];

			$docRoot = CSite::GetSiteDocRoot($arRes['ID']);

			if (count($arDocRoots) > 0 && in_array($docRoot, $arDocRoots))
				continue;

			$arDocRoots[] = $docRoot;
			CCalendarConvert::SetOption('__convert_doc_roots', $arDocRoots);

			if (self::$bSkip && self::$curSite != self::$lastSite)
				continue;

			$oDir = new CFilemanUtilDir($docRoot, array(
				'obj' => $this,
				'site' => $arRes['ID'],
				'callBack' => "ParseFile",
				'checkBreak' => "CheckBreak",
				'checkSubdirs' => false
			));
			$bSuccess = $oDir->Start();

			$bBreak = $oDir->bBreak;
			$nextPath = $oDir->nextPath;

			if ($bBreak)
				$this->ParseStop(CFilemanUtils::TrimPath($nextPath, $docRoot), self::$curSite);
		}

		$this->AddTypesFromIblockType();
		?><script>top.location = "/bitrix/admin/calendar_convert.php?lang=<?= LANG?>&content_parsed=Y";</script><?
	}

	function ParseFile($file)
	{
		$docRoot = CSite::GetSiteDocRoot(self::$curSite);
		$file = str_replace("//","/",$file);
		$io = CBXVirtualIo::GetInstance();
		$bIsDir = $io->DirectoryExists($file);

		if (is_link($file))
			return;

		if ($bIsDir)
		{
			// Skip 'bitrix' and 'upload' folders
			if ($file == $docRoot.'/bitrix' || $file == $docRoot.'/upload' || $file == $docRoot.'/images')
				return;

			$oDir = new CFilemanUtilDir($file, array(
				'obj' => $this,
				'site' => self::$curSite,
				'callBack' => "ParseFile",
				'checkBreak' => "CheckBreak",
				'checkSubdirs' => false
			));
			$bSuccess = $oDir->Start();
			$bBreak = $oDir->bBreak;
			$nextPath = $oDir->nextPath;

			if ($bBreak)
				$this->ParseStop(CFilemanUtils::TrimPath($nextPath, $docRoot), self::$curSite);
		}
		else
		{
			if (self::$bSkip)
			{
				if ($file == $docRoot.self::$lastPath)
					self::$bSkip = false; // continue handle files from last path
				else
					return; // Files was handled earlier
			}


			$fileName = $io->ExtractNameFromPath($file);

			// Skip files stating from dot '.' or any non .php files
			if (GetFileExtension($fileName) != 'php' || preg_match('/^\..*/i'.BX_UTF_PCRE_MODIFIER, $fileName))
				return;

			// 1. Get file content
			$fTmp = $io->GetFile($file);
			$fileContent = $fTmp->GetContents();
			$fileContent = str_replace("\r", "", $fileContent);
			$fileContent = str_replace("\n", "", $fileContent);

			// Find files with needed components
			$pattern = array(
				'intranet.event_calendar',
				'socialnetwork',
				'socialnetwork_user',
				'socialnetwork_group'
			);
			foreach($pattern as $p)
			{
				if (preg_match('/includecomponent\([\n\t\r\s]*("|\')bitrix:'.$p.'/i'.BX_UTF_PCRE_MODIFIER, $fileContent))
				{
					$this->FetchParams($fileContent);
					break;
				}
			}
		}
	}

	function ParseStop($nextPath, $site)
	{
		?>
		<script>
			top.cal_site = '<?= CUtil::JSEscape($site)?>';
			top.cal_next_path = '<?= CUtil::JSEscape($nextPath)?>';
		</script>
		<?
		die();
	}

	function CheckBreak()
	{
		return microtime(true) - self::$start_time > self::$time_limit;
	}

	function FetchParams($content)
	{
		// 1. Parse file
		$arPHP = PHPParser::ParseFile($content);
		$arComponents = array('bitrix:intranet.event_calendar', 'bitrix:socialnetwork','bitrix:socialnetwork_user','bitrix:socialnetwork_group');

		if (count($arPHP) > 0)
		{
			self::$types = CCalendarConvert::GetOption('__convert_types');
			self::$iblockTypes = CCalendarConvert::GetOption('__convert_ibl_types');
			self::$settings = CCalendarConvert::GetOption('__convert_settings');

			foreach($arPHP as $code)
			{
				$arRes = PHPParser::CheckForComponent2($code[2]);
				if ($arRes && in_array($arRes['COMPONENT_NAME'], $arComponents))
				{
					$PARAMS = $arRes['PARAMS'];

					if ($arRes['COMPONENT_NAME'] == 'bitrix:intranet.event_calendar')
					{
						if (!in_array($PARAMS['IBLOCK_TYPE'], self::$iblockTypes) && $PARAMS['IBLOCK_TYPE'])
							self::$iblockTypes[] = $PARAMS['IBLOCK_TYPE'];

						if (self::$types['user']['iblockType'] == '')
							self::$types['user']['iblockType'] = $PARAMS['IBLOCK_TYPE'];
						if (self::$types['group']['iblockType'] == '')
							self::$types['group']['iblockType'] = $PARAMS['IBLOCK_TYPE'];

						if (isset($PARAMS['USERS_IBLOCK_ID']) && $PARAMS['USERS_IBLOCK_ID'] > 0 && self::$types['user']['iblockId'] <= 0)
							self::$types['user']['iblockId'] = intval($PARAMS['USERS_IBLOCK_ID']);

						if (isset($PARAMS['SUPERPOSE_GROUPS_IBLOCK_ID']) && $PARAMS['SUPERPOSE_GROUPS_IBLOCK_ID'] > 0 && self::$types['group']['iblockId'] <= 0)
							self::$types['group']['iblockId'] = intval($PARAMS['SUPERPOSE_GROUPS_IBLOCK_ID']);

						// Settings
						self::SetModuleOption('path_to_user', $PARAMS['PATH_TO_USER']);
						self::SetModuleOption('week_holidays', $PARAMS['WEEK_HOLIDAYS']);
						self::SetModuleOption('year_holidays', $PARAMS['YEAR_HOLIDAYS']);
						self::SetModuleOption('work_time_start', $PARAMS['WORK_TIME_START']);
						self::SetModuleOption('work_time_end', $PARAMS['WORK_TIME_END']);
						self::SetModuleOption('rm_iblock_type', $PARAMS['CALENDAR_IBLOCK_TYPE']);
						self::SetModuleOption('rm_iblock_id', $PARAMS['CALENDAR_RES_MEETING_IBLOCK_ID']);
						self::SetModuleOption('path_to_rm', $PARAMS['CALENDAR_PATH_TO_RES_MEETING']);
						self::SetModuleOption('vr_iblock_id', $PARAMS['CALENDAR_VIDEO_MEETING_IBLOCK_ID']);
						self::SetModuleOption('path_to_vr', $PARAMS['CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL']);
						self::SetModuleOption('path_to_vr', $PARAMS['CALENDAR_PATH_TO_VIDEO_MEETING']);
					}
					else // socialnetwork
					{
						if (!in_array($PARAMS['CALENDAR_IBLOCK_TYPE'], self::$iblockTypes) && $PARAMS['CALENDAR_IBLOCK_TYPE'])
							self::$iblockTypes[] = $PARAMS['CALENDAR_IBLOCK_TYPE'];
						if (self::$types['user']['iblockType'] == '')
							self::$types['user']['iblockType'] = $PARAMS['CALENDAR_IBLOCK_TYPE'];
						if (self::$types['group']['iblockType'] == '')
							self::$types['group']['iblockType'] = $PARAMS['CALENDAR_IBLOCK_TYPE'];

						if (isset($PARAMS['CALENDAR_USER_IBLOCK_ID']) && $PARAMS['CALENDAR_USER_IBLOCK_ID'] > 0 && self::$types['user']['iblockId'] <= 0)
							self::$types['user']['iblockId'] = intval($PARAMS['CALENDAR_USER_IBLOCK_ID']);

						if (isset($PARAMS['CALENDAR_GROUP_IBLOCK_ID']) && $PARAMS['CALENDAR_GROUP_IBLOCK_ID'] > 0 && self::$types['group']['iblockId'] <= 0)
							self::$types['group']['iblockId'] = intval($PARAMS['CALENDAR_GROUP_IBLOCK_ID']);

						self::SetModuleOption('path_to_user', $PARAMS['PATH_TO_USER']);
						self::SetModuleOption('path_to_group', $PARAMS['PATH_TO_GROUP']);

						if (isset($PARAMS['SEF_URL_TEMPLATES']['group_calendar']) && (strpos($PARAMS['SEF_URL_TEMPLATES']['group_calendar'], 'extranet') === false && strpos($PARAMS['SEF_FOLDER'], 'extranet') === false))
							self::SetModuleOption('path_to_group_calendar', $PARAMS['SEF_FOLDER'].$PARAMS['SEF_URL_TEMPLATES']['group_calendar']);

						if (isset($PARAMS['SEF_URL_TEMPLATES']['user_calendar']) && (strpos($PARAMS['SEF_URL_TEMPLATES']['user_calendar'], 'extranet') === false && strpos($PARAMS['SEF_FOLDER'], 'extranet') === false))
							self::SetModuleOption('path_to_user_calendar', $PARAMS['SEF_FOLDER'].$PARAMS['SEF_URL_TEMPLATES']['user_calendar']);

						self::SetModuleOption('week_holidays', $PARAMS['CALENDAR_WEEK_HOLIDAYS']);
						self::SetModuleOption('year_holidays', $PARAMS['CALENDAR_YEAR_HOLIDAYS']);
						self::SetModuleOption('work_time_start', $PARAMS['CALENDAR_WORK_TIME_START']);
						self::SetModuleOption('work_time_end', $PARAMS['CALENDAR_WORK_TIME_END']);
						self::SetModuleOption('rm_iblock_type', $PARAMS['CALENDAR_IBLOCK_TYPE']);
						self::SetModuleOption('rm_iblock_id', $PARAMS['CALENDAR_RES_MEETING_IBLOCK_ID']);
						self::SetModuleOption('path_to_rm', $PARAMS['CALENDAR_PATH_TO_RES_MEETING']);
						self::SetModuleOption('vr_iblock_id', $PARAMS['CALENDAR_VIDEO_MEETING_IBLOCK_ID']);
						self::SetModuleOption('path_to_vr', $PARAMS['CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL']);
						self::SetModuleOption('path_to_vr', $PARAMS['CALENDAR_PATH_TO_VIDEO_MEETING']);
					}
				}
			}

			CCalendarConvert::SetOption('__convert_types', self::$types);
			CCalendarConvert::SetOption('__convert_ibl_types', self::$iblockTypes);
			CCalendarConvert::SetOption('__convert_settings', self::$settings);
		}
		return true;
	}

	public static function GetIblockTypes()
	{
		$dbIBlockType = CIBlockType::GetList();
		$arIBTypes = array();
		$arIB = array();
		while ($arIBType = $dbIBlockType->Fetch())
		{
			if ($arIBTypeData = CIBlockType::GetByIDLang($arIBType["ID"], LANG))
			{
				$arIB[$arIBType['ID']] = array();
				$arIBTypes[$arIBType['ID']] = '['.$arIBType['ID'].'] '.$arIBTypeData['NAME'];
			}
		}

		$dbIBlock = CIBlock::GetList(array('SORT' => 'ASC'), array('ACTIVE' => 'Y'));
		self::$arIBlocks = array();
		while ($arIBlock = $dbIBlock->Fetch())
		{
			self::$arIBlocks[$arIBlock['IBLOCK_TYPE_ID']][$arIBlock['ID']] = $arIBlock;
			$arIB[$arIBlock['IBLOCK_TYPE_ID']][$arIBlock['ID']] = ($arIBlock['CODE'] ? '['.$arIBlock['CODE'].'] ' : '').$arIBlock['NAME'];
		}

		self::$arIBTypes = $arIBTypes;
		return array(
			'types' => $arIBTypes,
			'iblocks' => $arIB
		);
	}

	function AddTypesFromIblockType()
	{
		self::$types = CCalendarConvert::GetOption('__convert_types');
		self::$iblockTypes = CCalendarConvert::GetOption('__convert_ibl_types');
		CCalendarConvert::GetIblockTypes();

		foreach (self::$iblockTypes as $type)
		{
			$arIB = self::$arIBlocks[$type];
			if (is_array($arIB))
			{
				foreach ($arIB as $iblock)
				{
					// jabber.bx/view.php?id=24200
					//$rsProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $iblock['ID'],	"CODE" => "IS_MEETING"));
					//if(!($arProperty = $rsProperty->Fetch()))
					//	continue;

					// Skip reserve meetings
					$checked = strpos($iblock['CODE'], 'video-meeting') === false && strpos($iblock['CODE'], 'meeting_rooms') === false;

					// Skip users and groups calendars
					if ($iblock['ID'] == self::$types['user']['iblockId'] || $iblock['ID'] == self::$types['group']['iblockId'])
						continue;

					self::$types[$iblock['CODE']] = array(
						'sys' => false,
						'title' => $iblock['NAME'],
						'iblockType' => $type,
						'iblockId' => $iblock['ID'],
						'checked' => $checked
					);
				}
			}
		}

		CCalendarConvert::SetOption('__convert_types', self::$types);
	}

	public static function SetModuleOption($paramName, $value)
	{
		if ($paramName == 'week_holidays' && is_array($value))
		{
			$val = array();
			foreach($value as $day)
				$val[] = CCalendar::WeekDayByInd($day,false);
			$value = $val;
		}

		if ($paramName == 'year_holidays')
		{
			$value = explode(',', $value);
			if (is_array($value))
			{
				$value = array_merge($value, explode(',', self::$settings['year_holidays']));
				$val = array();
				foreach($value as $iii)
				{
					$iii = trim($iii);
					if ($iii != '')
						$val[] = $iii;
				}
				self::$settings['year_holidays'] = implode(',', array_unique($val));
			}
			else
			{
				$value = '';
			}
		}

		if (empty(self::$settings[$paramName]))
			self::$settings[$paramName] = $value;
	}

	public static function Log($mess = '')
	{
		if ($mess != '')
			echo '<div> - '.$mess.'</div>';
	}

	public static function CreateSectionProperty($iblockId)
	{
		// Check UF for iblock sections
		global $USER_FIELD_MANAGER;

		$ent_id = "IBLOCK_".$iblockId."_SECTION";
		$db_res = CUserTypeEntity::GetList(array('ID'=>'ASC'), array("ENTITY_ID" => $ent_id, "FIELD_NAME" => "UF_CAL_CONVERTED"));
		if (!$db_res || !($r = $db_res->GetNext()))
		{
			$arFields = Array(
				"ENTITY_ID" => $ent_id,
				"FIELD_NAME" => "UF_CAL_CONVERTED",
				"USER_TYPE_ID" => "string",
				"MULTIPLE" => "N",
				"MANDATORY" => "N",
			);
			$arFieldName = array();
			$rsLanguage = CLanguage::GetList($by, $order, array());
			while($arLanguage = $rsLanguage->Fetch())
				$arFieldName[$arLanguage["LID"]] = $arProps[$i][1];
			$arFields["EDIT_FORM_LABEL"] = $arFieldName;
			$obUserField  = new CUserTypeEntity;
			$r = $obUserField->Add($arFields);
			$USER_FIELD_MANAGER->arFieldsCache = array();
		}
	}

	public static function DropSectionProperty()
	{
		// Check UF for iblock sections
		global $USER_FIELD_MANAGER;
		$db_res = CUserTypeEntity::GetList(array('ID'=>'ASC'), array("FIELD_NAME" => "UF_CAL_CONVERTED"));
		while ($r = $db_res->GetNext())
		{
			$obUserField  = new CUserTypeEntity;
			$r = $obUserField->Delete($r["ID"]);
			$USER_FIELD_MANAGER->arFieldsCache = array();
		}
	}

	public static function CreateElementProperty($iblockId)
	{
		$prop = array('CODE' => 'CAL_CONVERTED', 'TYPE' => 'S', 'NAME' => 'CAL_CONVERTED');
		$code = $prop['CODE'];
		$rsProperty = CIBlockProperty::GetList(array(), array(
			"IBLOCK_ID" => $iblockId,
			"CODE" => $code
		));
		$arProperty = $rsProperty->Fetch();

		if(!$arProperty)
		{
			$obProperty = new CIBlockProperty;
			$obProperty->Add(array(
				"IBLOCK_ID" => $iblockId,
				"ACTIVE" => "Y",
				"USER_TYPE" => false,
				"PROPERTY_TYPE" => $prop['TYPE'],
				"MULTIPLE" => 'N',
				"NAME" => $prop['NAME'],
				"CODE" => $prop['CODE']
			));
		}
	}

	public static function ConvertEntity($ownerType, $ownerId, $sectionId, $iblockId, $createdBy)
	{
		$eventsCount = 0;
		$sectCount = 0;
		$bs = new CIBlockSection;
		$ent_id = "IBLOCK_".$iblockId."_SECTION";
		$result = array('eventsCount' => 0, 'sectCount' => 0);

		$bSetAccessFromCalendar = true;
		$Access = array(
			'G2' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_denied')
		);

		// CONVERT ACCESS:
		if ($ownerType == 'user') // Socnet
		{
			if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $ownerId, "calendar"))
				return $result;

			// Read
			$read = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $ownerId, "calendar", 'view');
			$taskId = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view');
			if ($read == 'A') // All users
				$Access['G2'] = $taskId;
			elseif($read == 'C') // All autorized
				$Access['AU'] = $taskId;
			elseif($read == 'M' || $read == 'E') // Friends
				$Access['SU'.$ownerId.'_F'] = $taskId;
			elseif ($bSetAccessFromCalendar)
				$bSetAccessFromCalendar = false;

			// Write - will override read access
			$write = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $ownerId, "calendar", 'write');
			$taskId = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');
			if ($write == 'A') // All users
				$Access['G2'] = $taskId;
			elseif($write == 'C') // All autorized
				$Access['AU'] = $taskId;
			elseif($write == 'M' || $write == 'E') // Friends
				$Access['SU'.$ownerId.'_F'] = $taskId;
		}
		elseif($ownerType == 'group')
		{
			if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $ownerId, "calendar"))
				return $result;

			// Read
			$read = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $ownerId, "calendar", 'view');
			$taskId = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view');

			if ($read == 'A') // Group owner
				$Access['SG'.$ownerId.'_A'] = $taskId;
			elseif($read == 'E') // Group moderator
				$Access['SG'.$ownerId.'_E'] = $taskId;
			elseif($read == 'K') // User
				$Access['SG'.$ownerId.'_K'] = $taskId;
			elseif($read == 'L') // Authorized
				$Access['AU'] = $taskId;
			elseif($read == 'N') // Authorized
				$Access['G2'] = $taskId;

			// Write - will override read access
			$write = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $ownerId, "calendar", 'write');
			$taskId = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit');

			if ($write == 'A') // Group owner
				$Access['SG'.$ownerId.'_A'] = $taskId;
			elseif($write == 'E') // Group moderator
				$Access['SG'.$ownerId.'_E'] = $taskId;
			elseif($write == 'K') // User
				$Access['SG'.$ownerId.'_K'] = $taskId;
			elseif($write == 'L') // Authorized
				$Access['AU'] = $taskId;
			elseif($write == 'N') // Authorized
				$Access['G2'] = $taskId;
		}
		else // iblock access
		{
			$arGroupPerm = CIBlock::GetGroupPermissions($iblockId);
			$taskByLetter = array(
				'D' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_denied'),
				'R' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view'),
				'W' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_edit'),
				'X' => CCalendar::GetAccessTasksByName('calendar_section', 'calendar_access')
			);

			foreach($arGroupPerm as $groupId => $letter)
				$Access['G'.$groupId] = $taskByLetter[$letter];
		}

		// 1. Fetch sections
		$arUserSections = CEventCalendar::GetCalendarList(array($iblockId, $sectionId, 0, $ownerType));
		$calendarIndex = array();
		foreach ($arUserSections as $section)
		{
			$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($ent_id, $section['ID']);
			if (isset($arUF["UF_CAL_CONVERTED"]) && strlen($arUF["UF_CAL_CONVERTED"]['VALUE']) > 0)
				continue;

			$SectionAccess = array();
			if ($bSetAccessFromCalendar && $ownerType == 'user')
			{
				if ($section['PRIVATE_STATUS'] == 'private')
				{
					$deniedTask = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_denied');
					$SectionAccess['G2'] = $deniedTask;
				}
				elseif($section['PRIVATE_STATUS'] == 'time')
				{
					$viewTimeTask = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view_time');
					$SectionAccess['G2'] = $viewTimeTask;
				}
				elseif($section['PRIVATE_STATUS'] == 'title')
				{
					$viewTitleTask = CCalendar::GetAccessTasksByName('calendar_section', 'calendar_view_title');
					$SectionAccess['G2'] = $viewTitleTask;
				}
				else
				{
					$SectionAccess = $Access; // nested from common access settings from socnet
				}
			}
			else
			{
				$SectionAccess = $Access; // G2 => denied
			}

			$new_sect_id = CCalendarSect::Edit(array(
				'arFields' => array(
					"CAL_TYPE" => $ownerType,
					"NAME" => $section['NAME'],
					"OWNER_ID" => $ownerId,
					"CREATED_BY" => $createdBy,
					"DESCRIPTION" => $section['DESCRIPTION'],
					"COLOR" => $section["COLOR"],
					"ACCESS" => $SectionAccess
				)
			));

			// Set converted property
			$bs->Update($section['ID'], array('UF_CAL_CONVERTED' => 1));

			$calendarIndex[$section['ID']] = $new_sect_id;
			$sectCount++;
		}

		// 2. Create events
		$arEvents = CEventCalendar::GetCalendarEventsList(array($iblockId, $sectionId, 0), array());
		foreach ($arEvents as $event)
		{
			if (!isset($calendarIndex[$event['IBLOCK_SECTION_ID']]) || $event['PROPERTY_PARENT'] > 0)
				continue;

			$arFields = array(
				"CAL_TYPE" => $ownerType,
				"OWNER_ID" => $ownerId,
				"CREATED_BY" => $event["CREATED_BY"],
				"DT_FROM" => $event['ACTIVE_FROM'],
				"DT_TO" => $event['ACTIVE_TO'],
				'NAME' => htmlspecialcharsback($event['NAME']),
				'DESCRIPTION' => CCalendar::ParseHTMLToBB(htmlspecialcharsback($event['DETAIL_TEXT'])),
				'SECTIONS' => array($calendarIndex[$event['IBLOCK_SECTION_ID']]),
				'ACCESSIBILITY' => $event['PROPERTY_ACCESSIBILITY'],
				'IMPORTANCE' => $event['PROPERTY_IMPORTANCE'],
				'PRIVATE_EVENT' => ($event['PROPERTY_PRIVATE'] && $event['PROPERTY_PRIVATE'] == 'true') ? '1' : '',
				'RRULE' => array(),
				'LOCATION' => array('NEW' => $event['PROPERTY_LOCATION'], 'RE_RESERVE' => 'N'),
				"REMIND" => array(),
				"IS_MEETING" => $event['PROPERTY_IS_MEETING'] == 'Y'
			);

			if (!empty($event['PROPERTY_REMIND_SETTINGS']))
			{
				$ar = explode("_", $event["PROPERTY_REMIND_SETTINGS"]);
				if(count($ar) == 2)
					$arFields["REMIND"][] = array('type' => $ar[1],'count' => floatVal($ar[0]));
			}

			if (isset($event["PROPERTY_PERIOD_TYPE"]) && in_array($event["PROPERTY_PERIOD_TYPE"], array("DAILY", "WEEKLY", "MONTHLY", "YEARLY")))
			{
				$arFields['RRULE']['FREQ'] = $event["PROPERTY_PERIOD_TYPE"];
				$arFields['RRULE']['INTERVAL'] = $event["PROPERTY_PERIOD_COUNT"];

				if (!empty($event['PROPERTY_EVENT_LENGTH']))
					$arFields['DT_LENGTH'] = intval($event['PROPERTY_EVENT_LENGTH']);

				if (!$arFields['DT_LENGTH'])
					$arFields['DT_LENGTH'] = 86400;

				if ($arFields['RRULE']['FREQ'] == "WEEKLY" && !empty($event['PROPERTY_PERIOD_ADDITIONAL']))
				{
					$arFields['RRULE']['BYDAY'] = array();
					$bydays = explode(',',$event['PROPERTY_PERIOD_ADDITIONAL']);
					foreach($bydays as $day)
					{
						$day = CCalendar::WeekDayByInd($day);
						if ($day !== false)
							$arFields['RRULE']['BYDAY'][] = $day;
					}
					$arFields['RRULE']['BYDAY'] = implode(',',$arFields['RRULE']['BYDAY']);
				}

				$arFields['RRULE']['UNTIL'] = $event['ACTIVE_TO'];
			}

			if ($arFields['IS_MEETING'])
			{
				if ($event['PROPERTY_PARENT'] > 0)
					continue;

				$host = intVal($event['CREATED_BY']);
				$arFields['ATTENDEES'] = array();
				if ($event['PROPERTY_HOST_IS_ABSENT'] == 'N')
					$arFields['ATTENDEES'][] = $host;

				$arGuests = CECEvent::GetGuests(self::$userIblockId, $event['ID']);

				$attendeesStatuses = array();
				foreach($arGuests as $userId => $ev)
				{
					$attendeesStatuses[$userId] = $ev['PROPERTY_VALUES']['CONFIRMED'];
					$arFields['ATTENDEES'][] = $userId;
				}

				$arFields['MEETING_HOST'] = $host;
				$arFields['MEETING'] = array(
					'HOST_NAME' => CCalendar::GetUserName($host),
					'TEXT' => (is_array($event['PROPERTY_MEETING_TEXT']) && is_string($event['PROPERTY_MEETING_TEXT']['TEXT'])) ? trim($event['PROPERTY_MEETING_TEXT']['TEXT']) : '',
					'OPEN' => false,
					'NOTIFY' => false,
					'REINVITE' => false
				);
			}

			$new_event_id = CCalendar::SaveEvent(
				array(
					'arFields' => $arFields,
					'bAffectToDav' => false,
					'attendeesStatuses' => $attendeesStatuses,
					'sendInvitations' => false
				)
			);
			$eventsCount++;
		}

		// 3. Set userfield
		$bs->Update($sectionId, array('UF_CAL_CONVERTED' => 1));

		return array(
			'eventsCount' => $eventsCount,
			'sectCount' => $sectCount
		);
	}

	public static function DoConvertStep()
	{
		$types = CCalendarConvert::GetOption('__convert');

		$start_time = microtime(true);
		$time_limit = 2;
		$stage = 'stop';
		$finished = false;

		self::$accessTasks = CCalendar::GetAccessTasks('calendar_section');
		self::$userIblockId = $types['user']['iblockId'];

		foreach($types as $key => $type)
		{
			$iblockId = $type['iblockId'];
			if ($iblockId < 0)
				continue;

			// Fetch type
			if ($key == 'user')
			{
				$arFilter = array(
					'IBLOCK_ID' => $iblockId,
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => 'N',
					"DEPTH_LEVEL" => 1,
					"UF_CAL_CONVERTED" => false
				);

				$dbSections = CIBlockSection::GetList(array('ID' => 'ASC'), $arFilter);
				// For each user:
				while ($arSection = $dbSections->Fetch())
				{
					$ownerId = $arSection["CREATED_BY"];
					CCalendar::SetUserSettings(false, $ownerId);

					$res = CCalendarConvert::ConvertEntity('user', $ownerId, $arSection["ID"], $iblockId, $arSection["CREATED_BY"]);

					if ($res['sectCount'] > 0 || $res['eventsCount'] > 0)
						CCalendarConvert::Log(GetMessage("CAL_CONVERT_STAGE_USER_CALS", array('#USER_NAME#' => $arSection['NAME'], '#SECT_COUNT#' => $res['sectCount'], '#EVENTS_COUNT#' => $res['eventsCount'])));

					if($res && ($res['sectCount'] > 0 || $res['eventsCount'] > 0) && microtime(true) - $start_time > $time_limit)
					{
						$stage = 'go';
						break;
					}
				}
			}
			elseif($key == 'group')
			{
				$arFilter = array(
					'IBLOCK_ID' => $iblockId,
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => 'N',
					"DEPTH_LEVEL" => 1,
					"UF_CAL_CONVERTED" => false
				);
				$dbSections = CIBlockSection::GetList(array('ID' => 'ASC'), $arFilter);

				// For each group:
				while ($arSection = $dbSections->Fetch())
				{
					$ownerId = $arSection["SOCNET_GROUP_ID"];
					$res = CCalendarConvert::ConvertEntity('group', $ownerId, $arSection["ID"], $iblockId, $arSection["CREATED_BY"]);

					if ($res['sectCount'] > 0 || $res['eventsCount'] > 0)
						CCalendarConvert::Log(GetMessage("CAL_CONVERT_STAGE_GROUP_CALS", array('#GROUP_NAME#' => $arSection['NAME'], '#SECT_COUNT#' => $res['sectCount'], '#EVENTS_COUNT#' => $res['eventsCount'])));


					if($res && ($res['sectCount'] > 0 || $res['eventsCount'] > 0) && microtime(true) - $start_time > $time_limit)
					{
						$stage = 'go';
						break;
					}
				}
			}
			else
			{
				$res = CCalendarConvert::ConvertEntity($key, 0, 0, $iblockId, 1);
				if ($res['sectCount'] > 0 || $res['eventsCount'] > 0)
				{
					CCalendarConvert::Log(GetMessage("CAL_CONVERT_STAGE_TYPE", array('#TYPE_NAME#' => $type['name'], '#SECT_COUNT#' => $res['sectCount'], '#EVENTS_COUNT#' => $res['eventsCount'])));
				}

				if($res && ($res['sectCount'] > 0 || $res['eventsCount'] > 0) && microtime(true) - $start_time > $time_limit)
				{
					$stage = 'go';
					break;
				}
			}

			if ($stage == 'go')
				break;
		}

		return $stage;
	}

	public static function GetSettings()
	{
		self::$settings = CCalendarConvert::GetOption('__convert_settings', serialize(array()));
		return self::$settings;
	}

	public static function GetOption($name = '', $default = false)
	{
		if ($name)
		{
			$path = CTempFile::GetAbsoluteRoot()."/cal_convert/".$name.".tmp";
			if(!file_exists($path))
			{
				$value = $default;
			}
			else
			{
				$value = file_get_contents(CTempFile::GetAbsoluteRoot()."/cal_convert/".$name.".tmp");
				if ($value == '')
					$value = $default;
			}
			$value = unserialize($value);
			return $value;
		}
	}

	public static function SetOption($name = '', $value = false, $serialize = true)
	{
		if ($serialize)
			$value = serialize($value);

		$abs_path = CTempFile::GetAbsoluteRoot()."/cal_convert/".$name.".tmp";
		$io = CBXVirtualIo::GetInstance();
		$fileIo = $io->GetFile($abs_path);
		$io->CreateDirectory($fileIo->GetPath());

		$f = fopen($abs_path, "wb");
		fwrite($f, $value);
		fclose($f);
	}
}

if (CModule::IncludeModule("intranet") && CModule::IncludeModule("calendar"))
{
	CModule::IncludeModule("socialnetwork");
	$RES = NULL;

	// 1. Fetch options
	if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["convert"]) && $_POST["convert"] == 'Y' && check_bitrix_sessid())
	{
		// Remember all settings
		$SET = array(
			'work_time_start' => $_REQUEST['work_time_start'],
			'work_time_end' => $_REQUEST['work_time_end'],
			'year_holidays' => $_REQUEST['year_holidays'],
			'week_holidays' => implode('|',$_REQUEST['week_holidays']),
			'path_to_user' => $_REQUEST['path_to_user'],
			'path_to_user_calendar' => $_REQUEST['path_to_user_calendar'],
			'path_to_group' => $_REQUEST['path_to_group'],
			'path_to_group_calendar' => $_REQUEST['path_to_group_calendar'],
			'path_to_vr' => $_REQUEST['path_to_vr'],
			'path_to_rm' => $_REQUEST['path_to_rm'],
			'rm_iblock_type' => $_REQUEST['rm_iblock_type'],
			'rm_iblock_id' => $_REQUEST['rm_iblock_id'],
			'vr_iblock_id' => $_REQUEST['vr_iblock_id']
		);
		$CUR_SET = CCalendar::GetSettings();
		foreach($CUR_SET as $key => $value)
		{
			if (!isset($SET[$key]) && isset($value))
				$SET[$key] = $value;
		}
		CCalendar::SetSettings($SET);
		CCalendar::ClearCache(array(
			'access_tasks',
			'type_list',
			'type_list',
			'section_list'
		));

		// Remember iblocks
		// Create types
		if (isset($_POST["set_params"]) && $_POST["set_params"] == 'Y')
		{
			$types = array();
			foreach($_POST['types'] as $type)
			{
				if (isset($type['allow']) && $type['allow'] == "Y")
				{
					$types[$type['key']] = array(
						'iblockType' => $type['iblock_type'],
						'iblockId' => $type['iblock_id'],
						'name' => $type['title'],
						'desc' => $type['desc']
					);

					$typeAccess = array(
						'G2' => CCalendar::GetAccessTasksByName('calendar_type', 'calendar_type_edit')
					);

					if ($type['key'] != 'user' && $type['key'] != 'group')
					{
						$arGroupPerm = CIBlock::GetGroupPermissions($type['iblock_id']);
						$taskByLetter = array(
							'D' => CCalendar::GetAccessTasksByName('calendar_type', 'calendar_type_denied'),
							'R' => CCalendar::GetAccessTasksByName('calendar_type', 'calendar_type_view'),
							'W' => CCalendar::GetAccessTasksByName('calendar_type', 'calendar_type_edit'),
							'X' => CCalendar::GetAccessTasksByName('calendar_type', 'calendar_type_access')
						);
						foreach($arGroupPerm as $groupId => $letter)
							$typeAccess['G'.$groupId] = $taskByLetter[$letter];
					}

					CCalendarConvert::CreateSectionProperty($type['iblock_id']);

					$XML_ID = CCalendarType::Edit(array(
						'NEW' => true,
						'arFields' => array(
							'XML_ID' => $type['key'],
							'NAME' => $type['title'],
							'DESCRIPTION' => trim($type['desc']),
							'EXTERNAL_ID' => 'iblock_'.$type['iblock_id'],
							'ACCESS' => $typeAccess
						)
					));
				}
			}

			CCalendarConvert::SetOption('__convert', $types);
			CCalendarConvert::Log(GetMessage('CAL_CONVERT_STAGE_SAVE'));
			CCalendarConvert::Log(GetMessage('CAL_CONVERT_STAGE_CREATE_TYPES'));

			$stage = 'go';
		}
		else
		{
			$stage = CCalendarConvert::DoConvertStep();
		}

		if ($stage == 'stop')
		{
			COption::SetOptionString("intranet", "calendar_2", "Y");
			CCalendarConvert::Log(GetMessage('CAL_CONVERT_SUCCESS'));
			CCalendarConvert::DropSectionProperty();
			$io = CBXVirtualIo::GetInstance();
			$io->Delete(CTempFile::GetAbsoluteRoot()."/cal_convert");

			$db_events = GetModuleEvents("calendar", "OnAfterCalendarConvert");
			while($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent);
		}

		?><script>top.bx_cal_convert = '<?= $stage?>';</script><?
		die();
	}

	if (isset($_POST["parse_public"]) && check_bitrix_sessid())
	{
		$oConv = new CCalendarConvert();
		$oConv->ParseParams($_REQUEST['next_path'], $_REQUEST['cur_site']);
		die();
	}

	if (!isset($_POST["convert"]) && !isset($_POST["parse_public"]))
	{
		if (!isset($_GET["content_parsed"]))
		{
			CCalendarConvert::SetOption('__convert_types', false);
		}
		else
		{
			$types = CCalendarConvert::GetOption('__convert_types');
			if (is_array($types))
				$RES = array('TYPES' => $types);
			$IB = CCalendarConvert::GetIblockTypes();
		}
	}

	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
	CUtil::InitJSCore(array('ajax'));

	$SET = CCalendarConvert::GetSettings();
	$CUR_SET = CCalendar::GetSettings();
	foreach($CUR_SET as $key => $value)
	{
		if (!isset($SET[$key]) && !empty($value))
			$SET[$key] = $value;
	}

	$arDays = Array('MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU');
	$arWorTimeList = array();
	for ($i = 0; $i < 24; $i++)
	{
		$arWorTimeList[strval($i)] = strval($i).'.00';
		$arWorTimeList[strval($i).'.30'] = strval($i).'.30';
	}
}
	?>
	<form style="border: 2px solid #B8C1DD; padding: 10px; background: #F8F8F8;" method="post" name="calendar_form" action="/bitrix/admin/calendar_convert.php" enctype="multipart/form-data" encoding="multipart/form-data">
		<?= bitrix_sessid_post();?>
		<input type="hidden" name="lang" value="<?=LANG?>" />
		<h1><?= GetMessage('CAL_CONVERT')?></h1>
		<?if(!isset($RES)):?>
			<?= BeginNote()?>
			<?= GetMessage('CAL_NOT_PARSE')?>
			<?= EndNote();?>

		<input type="hidden" name="parse_public" value="Y" />
		<input id="cal_conv_next_path" type="hidden" name="next_path" value="" />
		<input id="cal_conv_cur_site" type="hidden" name="cur_site" value="" />

		<input id="bxconv_parse" type="button" value="<?= GetMessage('CAL_PARSE_CONTENT')?>" onclick="parseContent();"/>
		<?else:?>

		<input type="hidden" name="convert" value="Y" />
		<input id="bx_set_params" type="hidden" name="set_params" value="Y" />
		<input id="bx_stage" type="hidden" name="stage" value="Y" />

		<?= BeginNote()?>
		<ol style="font-size:110%;">
			<li><?= GetMessage('CAL_NOT_1')?></li>
			<li><?= GetMessage('CAL_NOT_2')?></li>
			<li><?= GetMessage('CAL_NOT_3')?></li>
			<li><?= GetMessage('CAL_NOT_4')?></li>
		</ol>
		<?= EndNote();?>

		<h1><?= GetMessage('CAL_CONVERT_IBLOCK_LIST')?></h1>
		<table class="edit-table">
		<?foreach ($RES['TYPES'] as $k => $type):
			$name = 'types['.$k.']'
			?>
			<tr>
				<td  class="field-name">
					<input name="<?= $name?>[allow]" type="checkbox" <?if($type['checked']){echo ' checked="checked"';}?> <?/*if($type['sys']){echo ' disabled';}*/?> value="Y" title="<?= GetMessage('TYPE_CONVERT_IT')?>"/>
				</td>
				<td class="field-name">
					<?if ($type['sys']):?>
						<?/*<input type="hidden" name="<?= $name?>[allow]" value="Y" /> */?>
						<input type="hidden" name="<?= $name?>[key]" value="<?= htmlspecialcharsbx($k)?>" />
						<input type="hidden" name="<?= $name?>[title]" value="<?= htmlspecialcharsbx($type['title'])?>" />
						<?= htmlspecialcharsbx($type['title'])?>
					<?else:?>
						<span class="display: inline-block;"><?= GetMessage('TYPE_ID')?>:</span>
						<input size="40" name="<?= $name?>[key]" type="text" value="<?= htmlspecialcharsbx($k)?>"><br>
						<span class="display: inline-block;"><?= GetMessage('TYPE_TITLE')?>:</span>
						<input size="40" name="<?= $name?>[title]" type="text" value="<?= htmlspecialcharsbx($type['title'])?>">
					<?endif;?>
				</td>
				<td  class="field-name">
					<?= GetMessage('CAL_CONVERT_FROM')?>
				</td>
				<td style="text-align: left; padding: 3px 0 3px 10px;">
					<select name="<?= $name?>[iblock_type]" onchange="changeIblockList(this.value, [BX('type_iblock_sel_<?= $k?>')]);">
					<?foreach ($IB['types'] as $ibtype_id => $ibtype_name):?>
						<option value="<?= $ibtype_id?>" <?if($ibtype_id == $type['iblockType']){echo ' selected="selected"';}?>><?= $ibtype_name?></option>
					<?endforeach;?>
					</select>
					<select id="type_iblock_sel_<?= $k?>" name="<?= $name?>[iblock_id]">
					<?if ($type['iblockId']):?>
						<?foreach ($IB['iblocks'][$type['iblockType']] as $iblock_id => $iblock):?>
							<option value="<?= $iblock_id?>"<? if($iblock_id == $type['iblockId']){echo ' selected="selected"';}?>><?= $iblock?></option>
						<?endforeach;?>
					<?else:?>
						<option value=""> - </option>
					<?endif;?>
					</select>
				</td>
			</tr>
		<?endforeach;?>
		</table>
		<h1><?= GetMessage('CAL_CONVERT_SET')?></h1>
			<div>
			<table class="edit-table">
				<tr>
					<td class="field-name"><label for="cal_work_time"><?= GetMessage("CAL_WORK_TIME")?>:</label></td>
					<td>
						<select id="cal_work_time" name="work_time_start">
							<?foreach($arWorTimeList as $key => $val):?>
								<option value="<?= $key?>" <? if ($SET['work_time_start'] == $key){echo ' selected="selected" ';}?>><?= $val?></option>
							<?endforeach;?>
						</select>
						&mdash;
						<select id="cal_work_time" name="work_time_end">
							<?foreach($arWorTimeList as $key => $val):?>
								<option value="<?= $key?>" <? if ($SET['work_time_end'] == $key){echo ' selected="selected" ';}?>><?= $val?></option>
							<?endforeach;?>
						</select>
					</td>
				</tr>

				<tr>
					<td class="field-name" style="vertical-align: top;"><label for="cal_week_holidays"><?= GetMessage("CAL_WEEK_HOLIDAYS")?>:</label></td>
					<td>
						<select size="7" multiple=true id="cal_week_holidays" name="week_holidays[]">
							<?foreach($arDays as $day):?>
								<option value="<?= $day?>" <?if (in_array($day, $SET['week_holidays'])){echo ' selected="selected"';}?>><?= GetMessage('CAL_OPTION_FIRSTDAY_'.$day)?></option>
							<?endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_year_holidays"><?= GetMessage("CAL_YEAR_HOLIDAYS")?>:</label></td>
					<td>
						<input name="year_holidays" type="text" value="<?= htmlspecialcharsbx($SET['year_holidays'])?>" id="cal_year_holidays" size="60"/>
					</td>
				</tr>
				<!-- Path parameters title -->
				<tr class="heading"><td colSpan="2"><?= GetMessage('CAL_PATH_TITLE')?></td></tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_user"><?= GetMessage("CAL_PATH_TO_USER")?>:</label></td>
					<td>
						<input name="path_to_user" type="text" value="<?= htmlspecialcharsbx($SET['path_to_user'])?>" id="cal_path_to_user" size="60"/>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_user_calendar"><?= GetMessage("CAL_PATH_TO_USER_CALENDAR")?>:</label></td>
					<td>
						<input name="path_to_user_calendar" type="text" value="<?= htmlspecialcharsbx($SET['path_to_user_calendar'])?>" id="cal_path_to_user_calendar" size="60"/>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_group"><?= GetMessage("CAL_PATH_TO_GROUP")?>:</label></td>
					<td>
						<input name="path_to_group" type="text" value="<?= htmlspecialcharsbx($SET['path_to_group'])?>" id="cal_path_to_group" size="60"/>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_group_calendar"><?= GetMessage("CAL_PATH_TO_GROUP_CALENDAR")?>:</label></td>
					<td>
						<input name="path_to_group_calendar" type="text" value="<?= htmlspecialcharsbx($SET['path_to_group_calendar'])?>" id="cal_path_to_group_calendar" size="60"/>
					</td>
				</tr>
				<!-- Reserve meetings and video reserve meetings -->
				<tr class="heading"><td colSpan="2"><?= GetMessage('CAL_RESERVE_MEETING')?></td></tr>
				<tr>
					<td class="field-name"><label for="cal_rm_iblock_type"><?= GetMessage("CAL_RM_IBLOCK_TYPE")?>:</label></td>
					<td>
						<select name="rm_iblock_type" onchange="changeIblockList(this.value, [BX('cal_rm_iblock_id'), BX('cal_vr_iblock_id')])">
							<option value=""><?= GetMessage('CAL_NOT_SET')?></option>
						<?foreach ($IB['types'] as $ibtype_id => $ibtype_name):?>
							<option value="<?= $ibtype_id?>" <?if($ibtype_id == $SET['rm_iblock_type']){echo ' selected="selected"';}?>><?= htmlspecialcharsbx($ibtype_name)?></option>
						<?endforeach;?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_rm_iblock_id"><?= GetMessage("CAL_RM_IBLOCK_ID")?>:</label></td>
					<td>
						<select id="cal_rm_iblock_id" name="rm_iblock_id">
<?if ($SET['rm_iblock_id']):?>
				<?foreach ($IB['iblocks'][$SET['rm_iblock_type']] as $iblock_id => $iblock):?>
					<option value="<?= $iblock_id?>"<? if($iblock_id == $SET['rm_iblock_id']){echo ' selected="selected"';}?>><?= $iblock?></option>
				<?endforeach;?>
<?else:?>
				<option value=""><?= GetMessage('CAL_NOT_SET')?></option>
<?endif;?>

						</select>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_rm"><?= GetMessage("CAL_PATH_TO_RM")?>:</label></td>
					<td>
						<input name="path_to_rm" type="text" value="<?= htmlspecialcharsbx($SET['path_to_rm'])?>" id="cal_path_to_rm" size="60"/>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_vr_iblock_id"><?= GetMessage("CAL_VR_IBLOCK_ID")?>:</label></td>
					<td>
						<select id="cal_vr_iblock_id" name="vr_iblock_id"">
<?if ($SET['vr_iblock_id']):?>
				<?foreach ($IB['iblocks'][$SET['rm_iblock_type']] as $iblock_id => $iblock):?>
					<option value="<?= $iblock_id?>"<? if($iblock_id == $SET['vr_iblock_id']){echo ' selected="selected"';}?>><?= $iblock?></option>
				<?endforeach;?>
<?else:?>
				<option value=""><?= GetMessage('CAL_NOT_SET')?></option>
<?endif;?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field-name"><label for="cal_path_to_vr"><?= GetMessage("CAL_PATH_TO_VR")?>:</label></td>
					<td><input name="path_to_vr" type="text" value="<?= htmlspecialcharsbx($SET['path_to_vr'])?>" id="cal_path_to_vr" size="60"/></td>
				</tr>
			</table>
			</div>

		<div id="bxconvert_cont" style="display: none;">
			<div id="bxconvert_notice">
			<?echo BeginNote(),GetMessage('CAL_CONVERT_PROCESSING_NOTICE'),EndNote();?>
			</div>
			<h1><?= GetMessage('CAL_CONVERT_PROCES')?></h1>
			<span id="bxconvert_result" style="padding: 10px; display: inline-block; border-radius: 3px; border: 1px solid #A9A9A9; font-size: 11px; color:#808080; margin-bottom: 20px; background: #FFFFFF;">
				<div> - <?= GetMessage('CAL_CONVERT_STAGE_PREPARE')?></div>
			</span>
		</div>

		<input id="bxconv_but_start" type="button" value="<?= GetMessage('CAL_CONVERT_START')?>" onclick="startConvert();"/>
		<input id="bxconv_go_to_settings" type="button" value="<?= GetMessage('CAL_GO_TO_SETTINGS')?>" onclick="window.location='/bitrix/admin/settings.php?lang=<?= LANG?>&mid=calendar';" style="display: none;"/>
		<?endif;?>
	</form>
<script>
function parseContent()
{
	BX('cal_conv_next_path').value = '';
	BX('cal_conv_cur_site').value = '';
	BX('bxconv_parse').style.display = "none";

	BX.showWait(BX('bxconvert_cont'), '<?= GetMessage("CAL_PROC_WAIT")?>');
	BX.ajax.submit(document.forms.calendar_form, onParseResult);
}

function onParseResult(result)
{
	BX.closeWait(BX('bxconvert_cont'), '<?= GetMessage("CAL_PROC_WAIT")?>');
	setTimeout(function()
	{
		if (top.cal_site && top.cal_next_path)
		{
			BX('cal_conv_next_path').value = top.cal_next_path;
			BX('cal_conv_cur_site').value = top.cal_site;

			setTimeout(function(){
				BX.showWait(BX('bxconvert_cont'), '<?= GetMessage("CAL_CONVERT_PROCESSING")?>');
			}, 500);
			BX.ajax.submit(document.forms.calendar_form, onParseResult);

			top.cal_next_path = '';
			top.cal_next_path = '';
		}
	}, 500);
}

function startConvert()
{
	BX('bxconvert_cont').style.display = "";
	BX('bxconv_go_to_settings').style.display = "";
	BX('bxconv_but_start').style.display = "none";
	BX.showWait(BX('bxconvert_cont'), '<?= GetMessage("CAL_CONVERT_PROCESSING")?>');

	BX.ajax.submit(document.forms.calendar_form, onConvertResult);
}

function onConvertResult(res)
{
	BX('bxconvert_result').innerHTML += res;
	var pSetParamsInp = BX('bx_set_params');
	var pStage = BX('bx_stage');

	setTimeout(function()
	{
		// 1. First step
		if (pSetParamsInp.value == 'Y')
			pSetParamsInp.value = 'N';

		if (top.bx_cal_convert != 'stop')
		{
			pStage.value = top.bx_cal_convert || 1;
			BX.ajax.submit(document.forms.calendar_form, onConvertResult);
		}
		else
		{
			clearInterval(window.ProcInt);
			BX('bxconv_but_start').style.display = "none";
			BX('bxconvert_notice').style.display = "none";
			BX.closeWait(BX('bxconvert_cont'));
		}
	}, 100);
}

var arIblocks = <?= CUtil::PhpToJsObject($IB['iblocks'])?>;
function changeIblockList(value, arControls)
{
	var i, j;

	for (i = 0; i < arControls.length; i++)
	{
		if (arControls[i])
			arControls[i].options.length = 0;

		if (!value)
		{
			arControls[i].options[0] = new Option('<?= GetMessage('CAL_NOT_SET')?>', '');
			continue;
		}

		for (j in arIblocks[value])
			arControls[i].options[arControls[i].options.length] = new Option(arIblocks[value][j], j);
	}
}
</script>

<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>