<?php

use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Text\Emoji;
use Bitrix\Socialnetwork\UserToGroupTable;

class CSocNetLogDestination
{
	const LIST_USER_LIMIT = 11;

	private const USERS_STEP_COUNT = 500;

	/**
	* Retrieves last used users from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastUser()
	{
		static $resultCache = array();

		$userId = intval(CurrentUser::get()->getId());

		if(!isset($resultCache[$userId]))
		{
			$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
			$arLastSelected = (
				is_array($arLastSelected)
				&& $arLastSelected['users'] <> ''
				&& $arLastSelected['users'] !== '"{}"'
					? array_reverse(CUtil::JsObjectToPhp($arLastSelected['users']))
					: array()
			);

			if (is_array($arLastSelected))
			{
				if (!isset($arLastSelected[$userId]))
				{
					$arLastSelected['U'.$userId] = 'U'.$userId;
				}
			}
			else
			{
				$arLastSelected['U'.$userId] = 'U'.$userId;
			}

			$count = 0;
			$arUsers = Array();
			foreach ($arLastSelected as $userId)
			{
				if ($count < 5)
				{
					$count++;
				}
				else
				{
					break;
				}

				$arUsers[$userId] = $userId;
			}
			$resultCache[$userId] = array_reverse($arUsers);
		}

		return $resultCache[$userId];
	}

	/**
	* Retrieves last used sonet groups from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastSocnetGroup()
	{
		$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
		$arLastSelected = (
			is_array($arLastSelected)
			&& $arLastSelected['sonetgroups'] <> ''
			&& $arLastSelected['sonetgroups'] !== '"{}"'
				? array_reverse(CUtil::JsObjectToPhp($arLastSelected['sonetgroups']))
				: array()
		);

		$count = 0;
		$arSocnetGroups = Array();
		foreach ($arLastSelected as $sgId)
		{
			if ($count <= 4)
			{
				$count++;
			}
			else
			{
				break;
			}

			$arSocnetGroups[$sgId] = $sgId;
		}
		return array_reverse($arSocnetGroups);
	}

	/**
	* Retrieves last used department from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastDepartment()
	{
		$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
		$arLastSelected = (
			is_array($arLastSelected)
			&& $arLastSelected['department'] <> ''
			&& $arLastSelected['department'] !== '"{}"'
				? array_reverse(CUtil::JsObjectToPhp($arLastSelected['department']))
				: array()
		);

		$count = 0;
		$arDepartment = Array();
		foreach ($arLastSelected as $depId)
		{
			if ($count < 4)
			{
				$count++;
			}
			else
			{
				break;
			}

			$arDepartment[$depId] = $depId;
		}

		return array_reverse($arDepartment);
	}

	public static function GetStucture($arParams = Array())
	{
		$bIntranetEnabled = false;
		if(
			IsModuleInstalled('intranet')
			&& IsModuleInstalled('iblock')
		)
		{
			$bIntranetEnabled = true;
		}

		$result = array(
			"department" => array(),
			"department_relation" => array(),
			"department_relation_head" => array(),
		);

		$department_id = (
			isset($arParams["DEPARTMENT_ID"])
			&& intval($arParams["DEPARTMENT_ID"]) > 0
				? intval($arParams["DEPARTMENT_ID"])
				: false
		);

		if($bIntranetEnabled)
		{
			if (!(CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser()))
			{
				if(($iblock_id = COption::GetOptionInt('intranet', 'iblock_structure', 0)) > 0)
				{
					global $CACHE_MANAGER;

					$ttl = (
						defined("BX_COMP_MANAGED_CACHE")
							? 2592000
							: 600
					);

					$cache_id = 'sonet_structure_new4_'.$iblock_id.(intval($department_id) > 0 ? "_".$department_id : "");
					$obCache = new CPHPCache;
					$cache_dir = '/sonet/structure';

					if($obCache->InitCache($ttl, $cache_id, $cache_dir))
					{
						$result = $obCache->GetVars();
					}
					else
					{
						CModule::IncludeModule('iblock');

						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->StartTagCache($cache_dir);
						}

						$arFilter = array(
							"IBLOCK_ID" => $iblock_id,
							"ACTIVE" => "Y"
						);

						if (intval($department_id) > 0)
						{
							$rsSectionDepartment = CIBlockSection::GetList(
								array(),
								array(
									"ID" => intval($department_id)
								),
								false,
								array("ID", "LEFT_MARGIN", "RIGHT_MARGIN")
							);

							if ($arSectionDepartment = $rsSectionDepartment->Fetch())
							{
								$arFilter[">=LEFT_MARGIN"] = $arSectionDepartment["LEFT_MARGIN"];
								$arFilter["<=RIGHT_MARGIN"] = $arSectionDepartment["RIGHT_MARGIN"];
							}
						}

						$dbRes = CIBlockSection::GetList(
							array("left_margin"=>"asc"),
							$arFilter,
							false,
							array("ID", "IBLOCK_SECTION_ID", "NAME")
						);
						while ($ar = $dbRes->Fetch())
						{
							$result["department"]['DR'.$ar['ID']] = array(
								'id' => 'DR'.$ar['ID'],
								'entityId' => $ar["ID"],
								'name' => htmlspecialcharsbx($ar['NAME']),
								'parent' => 'DR'.intval($ar['IBLOCK_SECTION_ID']),
							);
						}
						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->RegisterTag('iblock_id_'.$iblock_id);
							$CACHE_MANAGER->EndTagCache();
						}

						if($obCache->StartDataCache())
						{
							$obCache->EndDataCache($result);
						}
					}
				}
			}
		}

		if (
			!empty( $result["department"])
			&& !isset($arParams["LAZY_LOAD"])
		)
		{
			$result["department_relation"] = self::GetTreeList('DR'.(intval($department_id) > 0 ? $department_id : 0), $result["department"], true);
			if ((int) ($arParams["HEAD_DEPT"] ?? 0) > 0)
			{
				$result["department_relation_head"] = self::GetTreeList('DR'.intval($arParams["HEAD_DEPT"]), $result["department"], true);
			}
		}

		return $result;
	}

	public static function GetExtranetUser(array $arParams = array())
	{
		global $CACHE_MANAGER;

		static $resultCache = array();

		$userId = (int)CurrentUser::get()->getId();

		if(!isset($resultCache[$userId]))
		{
			$arUsers = Array();

			if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			{
				$cacheTtl = 3153600;
				$cacheId = 'socnet_destination_getusers_'.md5(serialize($arParams)).'_'.$userId;
				$cacheDir = '/socnet/dest_extranet/' . (int)($userId / 100) . '/' . $userId . '/';

				$obCache = new CPHPCache;
				if($obCache->initCache($cacheTtl, $cacheId, $cacheDir))
				{
					$arUsers = $obCache->getVars();
				}
				else
				{
					$obCache->startDataCache();
					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->StartTagCache($cacheDir);
					}

					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->registerTag("sonet_user2group_U".$userId);
					}

					$workgroupIdList = [];
					$res = CSocNetUserToGroup::getList(
						array(),
						array(
							"USER_ID" => $userId,
							"<=ROLE" => SONET_ROLES_USER,
							"GROUP_SITE_ID" => 's1',
							"GROUP_ACTIVE" => "Y",
							"!GROUP_CLOSED" => "Y"
						),
						false,
						false,
						array("ID", "GROUP_ID")
					);
					while($relationFields = $res->fetch())
					{
						$workgroupIdList[] = (int)$relationFields["GROUP_ID"];
						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->registerTag("sonet_user2group_G".$relationFields["GROUP_ID"]);
						}
					}

					$arUsers = [];

					if (!empty($workgroupIdList))
					{
						$arUsers = self::getUsersAll([
							'RETURN_FULL_LIST' => 'Y'
						]);

						if (defined("BX_COMP_MANAGED_CACHE"))
						{
							foreach($arUsers as $userData)
							{
								$CACHE_MANAGER->registerTag("USER_NAME_".(int)$userData['entityId']);
							}
						}
					}

					$obCache->endDataCache($arUsers);
				}
			}

			$resultCache[$userId] = $arUsers;
		}

		return $resultCache[$userId];
	}

	public static function GetUsers($arParams = Array(), $bSelf = true)
	{
		global $CACHE_MANAGER;

		$userId = (int)CurrentUser::get()->getId();

		if (
			isset($arParams['all'])
			&& $arParams['all'] === 'Y'
		)
		{
			if (IsModuleInstalled("intranet"))
			{
				return self::getUsersAll($arParams);
			}

			$arParamsNew = $arParams;
			$arParamsNew["id"] = array($userId);
			unset($arParamsNew["all"]);

			return CSocNetLogDestination::GetUsers($arParamsNew, $bSelf);
		}

		$bExtranet = false;
		$filter = [];
		if (
			!isset($arParams['IGNORE_ACTIVITY'])
			|| $arParams['IGNORE_ACTIVITY'] !== 'Y'
		)
		{
			$filter['=ACTIVE'] = 'Y';
		}

		if (
			isset($arParams['ONLY_WITH_EMAIL'])
			&& $arParams['ONLY_WITH_EMAIL'] === 'Y'
		)
		{
			$filter['!=EMAIL'] = false;
		}

		$arExternalAuthId = self::getExternalAuthIdBlackList([
			'ALLOW_BOTS' => (isset($arParams['ALLOW_BOTS']) && $arParams['ALLOW_BOTS'] === true)
		]);

		if (!empty($arExternalAuthId))
		{
			$filter['!=EXTERNAL_AUTH_ID'] = $arExternalAuthId;
		}

		if (
			(
				!isset($arParams['IGNORE_ACTIVITY'])
				|| $arParams['IGNORE_ACTIVITY'] !== 'Y'
			)
			&& (
				IsModuleInstalled("intranet")
				|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y"
			)
		)
		{
			$filter["=CONFIRM_CODE"] = false;
		}

		$select = [ "ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE", "EXTERNAL_AUTH_ID" ];

		if (
			isset($arParams["CRM_ENTITY"])
			&& $arParams["CRM_ENTITY"] === "Y"
			&& ModuleManager::isModuleInstalled('crm')
		)
		{
			$select[] = 'UF_USER_CRM_ENTITY';
		}

		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$select[] = 'USER_TYPE';
			$select[] = 'UF_DEPARTMENT';
		}

		if (array_key_exists('id', $arParams))
		{
			if (empty($arParams['id']))
			{
				$filter['ID'] = $userId;
			}
			else
			{
				$arSelect = array($userId);
				foreach ($arParams['id'] as $value)
				{
					if (
						intval($value) > 0
						&& !in_array($value, $arSelect)
					)
					{
						$arSelect[] = intval($value);
					}
				}
				sort($arSelect);
				$filter['@ID'] = $arSelect;
			}
		}
		elseif (isset($arParams['deportament_id']))
		{
			if (is_array($arParams['deportament_id']))
			{
				$filter['=UF_DEPARTMENT'] = $arParams['deportament_id'];
			}
			else
			{
				if ($arParams['deportament_id'] === 'EX')
				{
					$bExtranet = true;
				}
				else
				{
					$filter['UF_DEPARTMENT'] = intval($arParams['deportament_id']);
				}
			}
		}

		$avatarSize = array(
			"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"] ?? 0) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 100),
			"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"] ?? 0) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 100)
		);

		$cacheTtl = 3153600;
		$cacheId = 'socnet_destination_getusers_'.md5(serialize($filter)).$bSelf.CSocNetUser::IsCurrentUserModuleAdmin().($bExtranet ? '_ex_'.$userId : '').md5(serialize($avatarSize));
		$cacheDir = '/socnet/dest/'.(
			isset($arParams['id'])
				? 'user'
				: 'dept'
		) . '/' . mb_substr(md5($cacheId), 0, 2) . '/';

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$arUsers = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($cacheDir);
			}

			if (
				$bExtranet
				&& CModule::IncludeModule("extranet")
			)
			{
				$extranetSiteId = CExtranet::GetExtranetSiteID();

				// get all extranet groups and set tags
				$rsSonetGroup = CSocNetUserToGroup::GetList(
					array(),
					array(
						"USER_ID" => $userId,
						"GROUP_SITE_ID" => $extranetSiteId
					),
					false,
					false,
					array("GROUP_ID")
				);
				while($arSonetGroup = $rsSonetGroup->Fetch())
				{
					$CACHE_MANAGER->RegisterTag("sonet_user2group_G".$arSonetGroup["GROUP_ID"]);
				}

				$CACHE_MANAGER->RegisterTag("sonet_user2group_U" . $userId);

				$arUsers = Array();
				$arExtranetUsers = CExtranet::GetMyGroupsUsersFull($extranetSiteId, $bSelf);
				foreach($arExtranetUsers as $arUserTmp)
				{
					if (!empty($arUserTmp['UF_DEPARTMENT']))
					{
						continue;
					}

					$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUserTmp, true, false));
					if (empty($sName))
					{
						$sName = $arUserTmp["~LOGIN"];
					}

					$arFileTmp = CFile::ResizeImageGet(
						$arUserTmp["PERSONAL_PHOTO"],
						$avatarSize,
						BX_RESIZE_IMAGE_EXACT,
						false
					);

					$arUsers['U'.$arUserTmp["ID"]] = Array(
						'id' => 'U'.$arUserTmp["ID"],
						'entityId' => $arUserTmp["ID"],
						'email' => $arUserTmp["EMAIL"] ? $arUserTmp["EMAIL"] : '',
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => self::getUserDescription($arUserTmp, [
							'showEmail' => !empty($arParams["ONLY_WITH_EMAIL"])
						]),
					);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".intval($arUserTmp["ID"]));
					}
				}
			}
			else
			{
				$bExtranetInstalled = CModule::IncludeModule("extranet");
				CSocNetTools::InitGlobalExtranetArrays();

				if (
					!isset($filter['UF_DEPARTMENT'])
					&& $bExtranetInstalled
				)
				{
					$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
				}

				$order = [ 'LAST_NAME' => 'ASC' ];

				$arUsers = Array();

				$className = (\Bitrix\Main\Loader::includeModule('intranet') ? '\Bitrix\Intranet\UserTable' : '\Bitrix\Main\UserTable');
				$res = $className::getList([
					'order' => $order,
					'filter' => $filter,
					'select' => $select
				]);

				global $USER;
				
				while ($arUser = $res->fetch())
				{
					foreach($arUser as $key => $value)
					{
						if (is_string($value))
						{
							$arUser[$key] = \Bitrix\Main\Text\HtmlFilter::encode($value);
						}
					}
					
					if (
						!$bSelf
						&& is_object($USER)
						&& $userId == $arUser["ID"]
					)
					{
						continue;
					}

					if (
						!isset($filter['UF_DEPARTMENT']) // all users
						&& $bExtranetInstalled
					)
					{
						if (
							isset($arUser["UF_DEPARTMENT"])
							&& $arUser['EXTERNAL_AUTH_ID'] !== 'email'
							&& (
								$arUser['EXTERNAL_AUTH_ID'] !== 'bot'
								|| !isset($arParams['ALLOW_BOTS'])
								|| $arParams['ALLOW_BOTS'] !== true
							)
							&& (
								!is_array($arUser["UF_DEPARTMENT"])
								|| empty($arUser["UF_DEPARTMENT"])
								|| (int)$arUser["UF_DEPARTMENT"][0] <= 0
							) // extranet user
							&& (
								empty($arUserIdVisible)
								|| !is_array($arUserIdVisible)
								|| !in_array($arUser["ID"], $arUserIdVisible)
							)
						)
						{
							continue;
						}
					}

					$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser, true, false));

					if (empty($sName))
					{
						$sName = htmlspecialcharsBack($arUser["LOGIN"]);
					}

					$arFileTmp = CFile::ResizeImageGet(
						$arUser["PERSONAL_PHOTO"],
						$avatarSize,
						BX_RESIZE_IMAGE_EXACT,
						false
					);

					$arUsers['U'.$arUser["ID"]] = Array(
						'id' => 'U'.$arUser["ID"],
						'entityId' => $arUser["ID"],
						'email' => $arUser["EMAIL"],
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => self::getUserDescription($arUser, [
							'showEmail' => !empty($arParams["ONLY_WITH_EMAIL"])
						]),
						'isExtranet' => (isset($arUser['USER_TYPE']) && $arUser['USER_TYPE'] === 'extranet' ? "Y" : "N"),
						'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] === 'email' ? 'Y' : 'N'),
						'isCrmEmail' => (
							$arUser['EXTERNAL_AUTH_ID'] === 'email'
							&& !empty($arUser['UF_USER_CRM_ENTITY'])
								? 'Y'
								: 'N'
						)
					);

					if ($arUser['EXTERNAL_AUTH_ID'] === 'email')
					{
						$arUsers['U'.$arUser["ID"]]['email'] = $arUser['EMAIL'];
					}

					$arUsers['U'.$arUser["ID"]]['checksum'] = md5(serialize($arUsers['U'.$arUser["ID"]]));

					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".intval($arUser["ID"]));
					}
				}
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->RegisterTag("USER_NAME");
				if (!empty($filter['UF_DEPARTMENT']))
				{
					$CACHE_MANAGER->RegisterTag('intranet_department_'.$filter['UF_DEPARTMENT']);
				}
				$CACHE_MANAGER->EndTagCache();
			}

			$obCache->EndDataCache($arUsers);
		}

		return $arUsers;
	}

	public static function GetGratMedalUsers($arParams = Array())
	{
		static $resultCache = array();

		$userId = intval(CurrentUser::get()->getId());

		if(!isset($resultCache[$userId]))
		{
			$arSubordinateDepts = array();

			if (CModule::IncludeModule("intranet"))
			{
				$arSubordinateDepts = CIntranetUtils::GetSubordinateDepartments($userId, true);
			}

			$arFilter = Array(
				"ACTIVE" => "Y",
				"!UF_DEPARTMENT" => false
			);

			$arExtParams = Array(
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE"),
				"SELECT" => Array("UF_DEPARTMENT")
			);

			if (isset($arParams["id"]))
			{
				if (empty($arParams["id"]))
				{
					$arFilter["ID"] = $userId;
				}
				else
				{
					$arSelect = array();
					foreach ($arParams["id"] as $value)
					{
						$arSelect[] = intval($value);
					}
					$arFilter["ID"] = implode("|", $arSelect);
				}
			}

			$arGratUsers = Array();
			$arMedalUsers = Array();

			$dbUsers = CUser::GetList(Array("last_name" => "asc", "IS_ONLINE" => "desc"), '', $arFilter, $arExtParams);
			while ($arUser = $dbUsers->GetNext())
			{
				$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser));

				if (empty($sName))
				{
					$sName = $arUser["~LOGIN"];
				}

				$arFileTmp = CFile::ResizeImageGet(
					$arUser["PERSONAL_PHOTO"],
					array("width" => 100, "height" => 100),
					BX_RESIZE_IMAGE_EXACT,
					false
				);

				$arGratUsers['U'.$arUser["ID"]] = Array(
					"id" => "U".$arUser["ID"],
					"entityId" => $arUser["ID"],
					"email" => $arUser["EMAIL"],
					"name" => $sName,
					"avatar" => empty($arFileTmp["src"]) ? '' : $arFileTmp["src"],
					"desc" => $arUser["WORK_POSITION"] ? $arUser["WORK_POSITION"] : ($arUser["PERSONAL_PROFESSION"] ? $arUser["PERSONAL_PROFESSION"] : "&nbsp;"),
				);

				if (
					count($arSubordinateDepts) > 0
					&& count(array_intersect($arSubordinateDepts, $arUser["UF_DEPARTMENT"])) > 0
				)
				{
					$arMedalUsers['U'.$arUser["ID"]] = $arGratUsers['U'.$arUser["ID"]];
				}
			}
			$resultCache[$userId] = array("GRAT" => $arGratUsers, "MEDAL" => $arMedalUsers);
		}

		return $resultCache[$userId];
	}

	public static function __percent_walk(&$val)
	{
		$val = str_replace('%', '', $val)."%";
	}

	public static function searchUsers($search, &$nt = "", $bSelf = true, $bEmployeesOnly = false, $bExtranetOnly = false, $departmentId = false)
	{
		global $DB;

		$nameTemplate = $nt;
		$bEmailUsers = false;
		$bCrmEmailUsers = false;
		$bActiveOnly = true;
		$bNetworkSearch = false;
		$bSearchOnlyWithEmail = false;
		$allowBots = false;
		$showAllExtranetContacts = false;

		if (is_array($search))
		{
			$arParams = $search;
			$search = $arParams["SEARCH"];
			$nameTemplate = ($arParams["NAME_TEMPLATE"] ?? '');
			$bSelf = ($arParams["SELF"] ?? true);
			$bEmployeesOnly = ($arParams["EMPLOYEES_ONLY"] ?? false);
			$bExtranetOnly = ($arParams["EXTRANET_ONLY"] ?? false);
			$departmentId = ($arParams["DEPARTAMENT_ID"] ?? false);
			$bEmailUsers = ($arParams["EMAIL_USERS"] ?? false);
			$bCrmEmailUsers = (isset($arParams["CRMEMAIL_USERS"]) && ModuleManager::isModuleInstalled('crm') ? $arParams["CRMEMAIL_USERS"] : false);
			$bActiveOnly = (isset($arParams["CHECK_ACTIVITY"]) && $arParams["CHECK_ACTIVITY"] === false ? false : true);
			$bNetworkSearch = ($arParams["NETWORK_SEARCH"] ?? false);
			$bSearchOnlyWithEmail = ($arParams["ONLY_WITH_EMAIL"] ?? false);
			$allowBots = ($arParams['ALLOW_BOTS'] ?? false);
			$showAllExtranetContacts = ($arParams['SHOW_ALL_EXTRANET_CONTACTS'] ?? false);
		}

		$arUsers = array();
		$search = trim($search);
		if (
			$search == ''
			|| !GetFilterQuery("TEST", $search)
		)
		{
			return $arUsers;
		}

		$bSearchByEmail = false;

		if (preg_match('/^([^<]+)\s<([^>]+)>$/i', $search, $matches)) // email
		{
			$search = $matches[2];
			$nt = $search;
			$bSearchByEmail = true;
		}

		$bIntranetEnabled = IsModuleInstalled('intranet');
		$bExtranetEnabled = CModule::IncludeModule('extranet');
		$bMailEnabled = IsModuleInstalled('mail');
		$bBitrix24Enabled = IsModuleInstalled('bitrix24');

		$bEmailUsersAll = ($bMailEnabled && \Bitrix\Main\Config\Option::get('socialnetwork', 'email_users_all', 'N') === 'Y');
		$bExtranetUser = ($bExtranetEnabled && !CExtranet::IsIntranetUser());

		$current_user_id = (int)CurrentUser::get()->getId();

		if ($bExtranetEnabled)
		{
			CSocNetTools::InitGlobalExtranetArrays();
		}

		$arSearchValue = preg_split('/\s+/', trim(mb_strtoupper($search)));
		array_walk($arSearchValue, array('CSocNetLogDestination', '__percent_walk'));

		$arMyUserId = array();

		$filter = [];

		$useFulltextIndex = class_exists('\Bitrix\Main\UserIndexTable');

		if ($useFulltextIndex)
		{
			$filter['*INDEX.SEARCH_USER_CONTENT'] = \Bitrix\Main\Search\Content::prepareStringToken(implode(' ', $arSearchValue));
		}
		else
		{
			if (count($arSearchValue) == 2)
			{
				$arLogicFilter = array(
					'LOGIC' => 'OR',
					array('LOGIC' => 'AND', 'NAME' => $arSearchValue[0], 'LAST_NAME' => $arSearchValue[1]),
					array('LOGIC' => 'AND', 'NAME' => $arSearchValue[1], 'LAST_NAME' => $arSearchValue[0]),
				);
			}
			else
			{
				$arLogicFilter = array(
					'LOGIC' => 'OR',
					'NAME' => $arSearchValue,
					'LAST_NAME' => $arSearchValue,
				);

				if (
					$bIntranetEnabled
					&& count($arSearchValue) == 1
					&& mb_strlen($arSearchValue[0]) > 2
				)
				{
					$arLogicFilter['LOGIN'] = $arSearchValue[0];
				}
			}

			$filter[] = $arLogicFilter;
		}

		if ($bActiveOnly)
		{
			$filter['=ACTIVE'] = 'Y';
		}

		if ($bIntranetEnabled)
		{
			$arExternalAuthId = self::getExternalAuthIdBlackList(array(
				'NETWORK_SEARCH' => $bNetworkSearch,
				'ALLOW_BOTS' => $allowBots
			));

			if (!empty($arExternalAuthId))
			{
				$filter['!=EXTERNAL_AUTH_ID'] = $arExternalAuthId;
			}

			if (
				($bEmailUsers || $bCrmEmailUsers)
				&& $bMailEnabled
				&& !$bEmailUsersAll
			)
			{
				$finderDestFilter = array(
					"USER_ID" => $current_user_id,
					"=CODE_TYPE" => "U",
					"=CODE_USER.EXTERNAL_AUTH_ID" => 'email'
				);
				$finderDestSelect = array(
					'CODE_USER_ID'
				);

				if ($bCrmEmailUsers)
				{
					$finderDestFilter['!=CODE_USER.UF_USER_CRM_ENTITY'] = false;
					$finderDestSelect[] = 'CODE_USER.UF_USER_CRM_ENTITY';
				}

				$rsUser = \Bitrix\Main\FinderDestTable::getList(array(
					'order' => array(),
					'filter' => $finderDestFilter,
					'group' => array("CODE_USER_ID"),
					'select' => $finderDestSelect
				));

				while ($arUser = $rsUser->fetch())
				{
					$arMyUserId[] = $arUser['CODE_USER_ID'];
				}
			}
		}

		if (
			!$bNetworkSearch
			&& (
				$bIntranetEnabled
				|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y"
			)
		)
		{
			$filter["CONFIRM_CODE"] = false;
		}

		$bFilteredByMyUserId = false;

		if(
			$bIntranetEnabled
			&& $bExtranetEnabled
			&& !$bCrmEmailUsers
		) // consider extranet collaboration
		{
			CExtranet::fillUserListFilterORM(
				array(
					"CURRENT_USER_ID" => $current_user_id,
					"EXTRANET_USER" => $bExtranetUser,
					"INTRANET_ONLY" => ($bEmployeesOnly || ($bBitrix24Enabled && !$bExtranetEnabled)),
					"EXTRANET_ONLY" => $bExtranetOnly,
					"EMAIL_USERS_ALL" => $bEmailUsersAll,
					"MY_USERS" => $arMyUserId,
					'ALLOW_BOTS' => $allowBots,
					'SHOW_ALL_EXTRANET_CONTACTS' => $showAllExtranetContacts
				),
				$filter,
				$bFilteredByMyUserId
			);

			if (!$filter)
			{
				return $arUsers;
			}

			if ($bNetworkSearch)
			{
				end($filter);
				$filter[key($filter)]["=EXTERNAL_AUTH_ID"] = "replica";
			}
		}

		if (
			!empty($arMyUserId)
			&& !$bFilteredByMyUserId
		)
		{
			$filter[] = array(
				'LOGIC' => 'OR',
				'!=EXTERNAL_AUTH_ID' => 'email',
				'ID' => $arMyUserId,
			);
		}

		if ($bSearchOnlyWithEmail)
		{
			$filter["!EMAIL"] = false;
		}

		$select = array(
			"ID",
			"ACTIVE",
			"NAME",
			"LAST_NAME",
			"SECOND_NAME",
			"EMAIL",
			"LOGIN",
			"WORK_POSITION",
			"PERSONAL_PROFESSION",
			"PERSONAL_PHOTO",
			"PERSONAL_GENDER",
			"EXTERNAL_AUTH_ID",
			new \Bitrix\Main\Entity\ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', array('\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE'))
		);

		if ($bCrmEmailUsers)
		{
			$select[] = "UF_USER_CRM_ENTITY";
		}

		if (!$bActiveOnly)
		{
			$select[] = "ACTIVE";
		}

		if ($useFulltextIndex)
		{
			$select['SEARCH_USER_CONTENT'] = 'INDEX.SEARCH_USER_CONTENT';
		}

		$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogDestinationSearchUsers");
		while ($arEvent = $db_events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($arSearchValue, &$filter, &$select));
		}

		$rsUser = null;
		if ($useFulltextIndex)
		{
			$rsUserFulltext = \Bitrix\Main\UserTable::getList(array(
				'order' => array(
					"MAX_LAST_USE_DATE" => 'DESC',
					'LAST_NAME' => 'ASC'
				),
				'filter' => $filter,
				'select' => [
					'ID',
					new \Bitrix\Main\Entity\ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', array('\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE'))
				],
				'limit' => 100,
				'data_doubling' => false
			));

			$userIdList = [];

			while ($arUser = $rsUserFulltext->fetch())
			{
				$userIdList[] = $arUser['ID'];
			}

			if (!empty($userIdList))
			{
				$rsUser = \Bitrix\Main\UserTable::getList(array(
					'order' => array(
						"MAX_LAST_USE_DATE" => 'DESC',
						'LAST_NAME' => 'ASC'
					),
					'filter' => [
						'@ID' => $userIdList
					],
					'select' => $select
				));
			}
		}
		else
		{
			$rsUser = \Bitrix\Main\UserTable::getList(array(
				'order' => array(
					"MAX_LAST_USE_DATE" => 'DESC',
					'LAST_NAME' => 'ASC'
				),
				'filter' => $filter,
				'select' => $select,
				'limit' => 100,
				'data_doubling' => false
			));
		}

		$queryResultCnt = 0;
		if ($rsUser !== null)
		{
			$bUseLogin = (mb_strlen($search) > 3 && mb_strpos($search, '@') > 0);
			$params = array(
				"NAME_TEMPLATE" => $nameTemplate,
				"USE_EMAIL" => $bSearchByEmail,
				"USE_LOGIN" => $bUseLogin,
				"ONLY_WITH_EMAIL" => $bSearchOnlyWithEmail
			);
			while ($arUser = $rsUser->fetch())
			{
				$queryResultCnt++;
				if (
					!$bSelf
					&& $current_user_id === (int)$arUser['ID']
				)
				{
					continue;
				}

				if ((int)$departmentId > 0)
				{
					$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);

					if (!in_array("DR" . (int)$departmentId, $arUserGroupCode, true))
					{
						continue;
					}
				}

				$arUser = (
				$arUser["EXTERNAL_AUTH_ID"] === "replica"
					? self::formatNetworkUser($arUser, $params)
					: self::formatUser($arUser, $params)
				);

				$arUsers[$arUser["id"]] = $arUser;
			}
		}

		if (
			($bEmailUsers || $bCrmEmailUsers || $bSearchOnlyWithEmail)
			&& !$queryResultCnt
			&& check_email($search, true)
		)
		{
			$arEmailFilter = array(
				'ACTIVE' => 'Y',
				'=EMAIL_OK' => 1
			);

			if (!empty($arExternalAuthId))
			{
				$arEmailFilter['!=EXTERNAL_AUTH_ID'] = $arExternalAuthId;
			}

			$rsUser = \Bitrix\Main\UserTable::getList(array(
				'order' => array(),
				'filter' => $arEmailFilter,
				'select' => array(
					"ID",
					"NAME",
					"LAST_NAME",
					"SECOND_NAME",
					"EMAIL",
					"LOGIN",
					"WORK_POSITION",
					"PERSONAL_PROFESSION",
					"PERSONAL_PHOTO",
					"PERSONAL_GENDER",
					"EXTERNAL_AUTH_ID",
					'ACTIVE',
					new \Bitrix\Main\Entity\ExpressionField('EMAIL_OK', 'CASE WHEN UPPER(%s) = \''.$DB->ForSql(mb_strtoupper(str_replace('%', '%%', $search))).'\' THEN 1 ELSE 0 END', ['EMAIL'])
				),
				'limit' => 10
			));

			while ($arUser = $rsUser->fetch())
			{
				$arUsers['U'.$arUser["ID"]] = self::formatUser($arUser, array(
					"NAME_TEMPLATE" => $nameTemplate,
					"USE_EMAIL" => true,
					"ONLY_WITH_EMAIL" => $bSearchOnlyWithEmail
				));
			}
		}

		return $arUsers;
	}

	public static function searchSonetGroups($params = array())
	{
		$result = array();

		$search = is_array($params) && isset($params['SEARCH']) ? trim($params['SEARCH']) : '';
		if (empty($search))
		{
			return $result;
		}

		if (
			$search == ''
			|| !getFilterQuery("TEST", $search)
		)
		{
			return $result;
		}

		$siteId = (
			isset($params['SITE_ID'])
			&& $params['SITE_ID'] <> ''
				? $params['SITE_ID']
				: SITE_ID
		);

		$currentUserAdmin = CSocNetUser::isCurrentUserModuleAdmin($siteId);

		$tmpList = array();

		$filter = array(
			'%NAME' => $search,
			"SITE_ID" => $siteId,
			"ACTIVE" => "Y",
		);

		if (
			!empty($params['LANDING'])
			&& $params['LANDING'] === 'Y'
		)
		{
			$filter['LANDING'] = 'Y';
		}

		if (!$currentUserAdmin)
		{
			$filter["CHECK_PERMISSIONS"] = (int)CurrentUser::get()->getId();
		}

		$res = CSocnetGroup::getList(
			array("NAME" => "ASC"),
			$filter,
			false,
			array("nTopCount" => 50),
			array("ID", "NAME", "DESCRIPTION", "IMAGE_ID")
		);

		$extranetGroupsIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetSonetGroupIdList();

		while ($group = $res->fetch())
		{
			$tmp = array(
				"id" => $group["ID"],
				"entityId" => $group["ID"],
				"name" => htmlspecialcharsbx(Emoji::decode($group["NAME"])),
				"desc" => htmlspecialcharsbx(Emoji::decode($group["DESCRIPTION"])),
				"isExtranet" => (in_array($group["ID"], $extranetGroupsIdList) ? 'Y' : 'N')
			);

			if($group["IMAGE_ID"])
			{
				$imageFile = CFile::getFileArray($group["IMAGE_ID"]);
				if ($imageFile !== false)
				{
					$arFileTmp = CFile::resizeImageGet(
						$imageFile,
						array(
							"width" => ((int)$params["THUMBNAIL_SIZE_WIDTH"] > 0 ? $params["THUMBNAIL_SIZE_WIDTH"] : 100),
							"height" => ((int)$params["THUMBNAIL_SIZE_HEIGHT"] > 0 ? $params["THUMBNAIL_SIZE_HEIGHT"] : 100)
						),
						BX_RESIZE_IMAGE_PROPORTIONAL,
						false
					);
					$tmp["avatar"] = $arFileTmp["src"];
				}
			}
			unset($group["IMAGE_ID"]);
			$tmpList[$tmp['id']] = $tmp;
		}

		if (
			!$currentUserAdmin
			&& isset($params['FEATURES'])
			&& is_array($params['FEATURES'])
			&& !empty($params['FEATURES'])
		)
		{
			self::getSocnetGroupFilteredByFeaturePerms($tmpList, $params['FEATURES']);
		}

		if (
			!$currentUserAdmin
			&& isset($params['INITIATE'])
			&& $params['INITIATE'] === 'Y'
		)
		{
			self::getSocnetGroupFilteredByInitiatePerms($tmpList);
		}

		foreach ($tmpList as $value)
		{
			$value['id'] = 'SG'.$value['id'];
			$result[$value['id']] = $value;
		}

		return $result;
	}

	public static function SearchCrmEntities($arParams)
	{
		$result = array();

		$search = (!empty($arParams['SEARCH']) ? $arParams['SEARCH'] : false);
		if (
			$search
			&& CModule::IncludeModule('crm')
		)
		{
			if (check_email($search, true))
			{
				$result = array();

				if (
					empty($arParams['ENTITIES'])
					|| in_array('CONTACT', $arParams['ENTITIES'])
				)
				{
					$dbRes = CCrmContact::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'@CATEGORY_ID' => 0,
							'FM' => array(
								'EMAIL' => array(
									'VALUE' => $search
								)
							)
						),
						false,
						false,
						array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'CONTACT',
							'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"],
							'EMAIL' => $search
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				if (
					empty($arParams['ENTITIES'])
					|| in_array('COMPANY', $arParams['ENTITIES'])
				)
				{
					$dbRes = CCrmCompany::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'@CATEGORY_ID' => 0,
							'FM' => array(
								'EMAIL' => array(
									'VALUE' => $search
								)
							)
						),
						false,
						false,
						array('ID', 'TITLE', 'LOGO')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'COMPANY',
							'EMAIL' => $search
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				if (
					empty($arParams['ENTITIES'])
					|| in_array('LEAD', $arParams['ENTITIES'])
				)
				{
					$dbRes = CCrmLead::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'FM' => array(
								'EMAIL' => array(
									'VALUE' => $search
								)
							)
						),
						false,
						false,
						array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'LEAD',
							'EMAIL' => $search
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}
			}
			elseif (
				!isset($arParams['SEARCH_BY_EMAIL_ONLY'])
				|| $arParams['SEARCH_BY_EMAIL_ONLY'] !== 'Y'
			)
			{
				$keysList = array();
				$contacts = CCrmActivity::FindContactCommunications($search, 'EMAIL', 50);
				foreach($contacts as $contact)
				{
					$keysList[] = $contact['ENTITY_ID'].'_'.$contact['ENTITY_TYPE_ID'];
				}

				$contactsByName = CCrmActivity::FindContactCommunications($search, '', 50);
				foreach($contactsByName as $contact)
				{
					if (
						in_array($contact['ENTITY_ID'].'_'.$contact['ENTITY_TYPE_ID'], $keysList)
						|| empty($contact["VALUE"])
					)
					{
						continue;
					}
					$contacts[] = $contact;
				}

				if (!empty($contacts))
				{
					$arId = $arEmail = array();
					foreach($contacts as $contact)
					{
						$arEmail[intval($contact["ENTITY_ID"])] = $contact["VALUE"];
						$arId[] = intval($contact["ENTITY_ID"]);
					}

					$dbRes = CCrmContact::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'PHOTO')
					);
					while($ar = $dbRes->fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'CONTACT',
							'NAME_TEMPLATE' => $arParams["NAME_TEMPLATE"],
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				$companies = CCrmActivity::FindCompanyCommunications($search, 'EMAIL', 50);
				foreach($companies as $company)
				{
					$keysList[] = $company['ENTITY_ID'].'_'.$company['ENTITY_TYPE_ID'];
				}

				$companiesByName = CCrmActivity::FindCompanyCommunications($search, '', 50);
				foreach($companiesByName as $company)
				{
					if (
						in_array($company['ENTITY_ID'].'_'.$company['ENTITY_TYPE_ID'], $keysList)
						|| empty($company["VALUE"])
					)
					{
						continue;
					}
					$companies[] = $company;
				}

				if (!empty($companies))
				{
					$arId = $arEmail = array();
					foreach($companies as $company)
					{
						$arEmail[intval($company["ENTITY_ID"])] = $company["VALUE"];
						$arId[] = intval($company["ENTITY_ID"]);
					}
					$dbRes = CCrmCompany::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'TITLE', 'LOGO')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'COMPANY',
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}

				$leads = CCrmActivity::FindLeadCommunications($search, 'EMAIL', 50);
				foreach($leads as $lead)
				{
					$keysList[] = $lead['ENTITY_ID'].'_'.$lead['ENTITY_TYPE_ID'];
				}

				$leadsByName = CCrmActivity::FindLeadCommunications($search, '', 50);
				foreach($leadsByName as $lead)
				{
					if (
						in_array($lead['ENTITY_ID'].'_'.$lead['ENTITY_TYPE_ID'], $keysList)
						|| empty($lead["VALUE"])
					)
					{
						continue;
					}
					$leads[] = $lead;
				}

				if (!empty($leads))
				{
					$arId = $arEmail = array();
					foreach($leads as $lead)
					{
						$arEmail[intval($lead["ENTITY_ID"])] = $lead["VALUE"];
						$arId[] = intval($lead["ENTITY_ID"]);
					}

					$dbRes = CCrmLead::GetListEx(
						array(),
						array(
							'CHECK_PERMISSIONS' => 'Y',
							'ID' => $arId
						),
						false,
						array('nTopCount' => 10),
						array('ID', 'TITLE', 'NAME', 'SECOND_NAME', 'LAST_NAME')
					);
					while($ar = $dbRes->Fetch())
					{
						$formatted = self::formatCrmEmailEntity($ar, array(
							'TYPE' => 'LEAD',
							'EMAIL' => $arEmail[$ar['ID']]
						));
						if (!empty($formatted))
						{
							$result[$formatted['id']] = $formatted;
						}
					}
				}
			}
		}

		return $result;
	}

	public static function getSocnetGroup($arParams = array(), &$limitReached = false)
	{
		static $staticCache = array();

		$userId = (int)CurrentUser::get()->getId();

		$arSocnetGroups = [];
		$arSelect = [];
		if (isset($arParams['id']))
		{
			if (empty($arParams['id']))
			{
				return $arSocnetGroups;
			}

			foreach ($arParams['id'] as $value)
			{
				$arSelect[] = intval($value);
			}
		}

		$siteId = (
			isset($arParams['site_id'])
			&& $arParams['site_id'] <> ''
				? $arParams['site_id']
				: SITE_ID
		);

		$limit = (isset($arParams["limit"]) && intval($arParams["limit"]) > 0 ? intval($arParams["limit"]) : 500);

		$hash = md5(serialize($arParams).$userId.$siteId);
		if (isset($staticCache[$hash]))
		{
			$arSocnetGroups = $staticCache[$hash];
		}
		else
		{
			$arSocnetGroupsTmp = array();
			$tmpList = array();

			$extranetGroupsIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetSonetGroupIdList();

			if (
				!isset($arParams["ALL"])
				|| $arParams["ALL"] !== "Y"
			)
			{
				$filter = array(
					"USER_ID" => $userId,
					"GROUP_ID" => $arSelect,
					"<=ROLE" => UserToGroupTable::ROLE_USER,
					"GROUP_SITE_ID" => $siteId,
					"GROUP_ACTIVE" => "Y"
				);

				if (
					!empty($arParams['landing'])
					&& $arParams['landing'] === 'Y'
				)
				{
					$filter["GROUP_LANDING"] = $arParams['landing'];
				}

				if(isset($arParams['GROUP_CLOSED']))
				{
					$filter['GROUP_CLOSED'] = $arParams['GROUP_CLOSED'];
				}

				$res = CSocNetUserToGroup::getList(
					array("GROUP_NAME" => "ASC"),
					$filter,
					false,
					array("nTopCount" => $limit),
					array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID", "GROUP_PROJECT")
				);
				while($relation = $res->fetch())
				{
					$tmpList[] = array(
						"id" => $relation["GROUP_ID"],
						"entityId" => $relation["GROUP_ID"],
						"name" => htmlspecialcharsbx(Emoji::decode($relation["GROUP_NAME"])),
						"desc" => htmlspecialcharsbx(Emoji::decode($relation["GROUP_DESCRIPTION"])),
						"imageId" => $relation["GROUP_IMAGE_ID"],
						"project" => ($relation["GROUP_PROJECT"] === 'Y' ? 'Y' : 'N'),
						"isExtranet" => (in_array($relation["GROUP_ID"], $extranetGroupsIdList) ? 'Y' : 'N')
					);
				}
			}
			else
			{
				$filter = array(
					"CHECK_PERMISSIONS" => (int)CurrentUser::get()->getId(),
					"SITE_ID" => $siteId,
					"ACTIVE" => "Y",
					"ID" => $arSelect,
				);
				if(isset($arParams['GROUP_CLOSED']))
				{
					$filter['CLOSED'] = $arParams['GROUP_CLOSED'];
				}
				if(
					!empty($arParams['landing'])
					&& $arParams['landing'] === 'Y'
				)
				{
					$filter['LANDING'] = $arParams['landing'];
				}

				$res = CSocnetGroup::getList(
					array("NAME" => "ASC"),
					$filter,
					false,
					array("nTopCount" => $limit),
					array("ID", "NAME", "DESCRIPTION", "IMAGE_ID", "PROJECT")
				);

				while ($group = $res->Fetch())
				{
					$tmpList[] = array(
						"id" => $group["ID"],
						"entityId" => $group["ID"],
						"name" => htmlspecialcharsbx(Emoji::decode($group["NAME"])),
						"desc" => htmlspecialcharsbx(Emoji::decode($group["DESCRIPTION"])),
						"imageId" => $group["IMAGE_ID"],
						"project" => ($group["PROJECT"] === 'Y' ? 'Y' : 'N'),
						"isExtranet" => (in_array($group["ID"], $extranetGroupsIdList) ? 'Y' : 'N')
					);
				}
			}

			$limitReached = (count($tmpList) == $limit);

			foreach ($tmpList as $group)
			{
				if($group["imageId"])
				{
					$imageFile = CFile::GetFileArray($group["imageId"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							[
								"width" => ((int) ($arParams["THUMBNAIL_SIZE_WIDTH"] ?? 0) > 0
									? $arParams["THUMBNAIL_SIZE_WIDTH"]
									: 100
								),
								"height" => ((int) ($arParams["THUMBNAIL_SIZE_HEIGHT"] ?? 0) > 0
									? $arParams["THUMBNAIL_SIZE_HEIGHT"]
									: 100
								)
							],
						);
						$group["avatar"] = $arFileTmp["src"];
					}
				}
				unset($group["imageId"]);
				$arSocnetGroupsTmp[$group['id']] = $group;
			}

			if (isset($arParams['features']) && !empty($arParams['features']))
			{
				self::getSocnetGroupFilteredByFeaturePerms($arSocnetGroupsTmp, $arParams['features']);
			}

			if (isset($arParams['initiate']) && $arParams['initiate'] === 'Y')
			{
				self::getSocnetGroupFilteredByInitiatePerms($arSocnetGroupsTmp);
			}

			foreach ($arSocnetGroupsTmp as $value)
			{
				$value['id'] = 'SG'.$value['id'];
				$arSocnetGroups[$value['id']] = $value;
			}

			$staticCache[$hash] = $arSocnetGroups;
		}

		if (isset($arParams['useProjects']) && $arParams['useProjects'] === 'Y')
		{
			$groupsList = $projectsList = array();
			foreach($arSocnetGroups as $key => $value)
			{
				if (
					isset($value['project'])
					&& $value['project'] === 'Y'
				)
				{
					$projectsList[$key] = $value;
				}
				else
				{
					$groupsList[$key] = $value;
				}
			}

			return array(
				'SONETGROUPS' => $groupsList,
				'PROJECTS' => $projectsList
			);
		}

		return $arSocnetGroups;
	}

	public static function GetTreeList($id, $relation, $compat = false)
	{
		if ($compat)
		{
			$tmp = array();
			foreach($relation as $iid => $rel)
			{
				$p = $rel["parent"];
				if (!isset($tmp[$p]))
				{
					$tmp[$p] = array();
				}
				$tmp[$p][] = $iid;
			}
			$relation = $tmp;
		}

		$arRelations = Array();
		if (is_array($relation[$id] ?? null))
		{
			foreach ($relation[$id] as $relId)
			{
				$arItems = Array();
				if (
					isset($relation[$relId])
					&& !empty($relation[$relId])
				)
				{
					$arItems = self::GetTreeList($relId, $relation);
				}

				$arRelations[$relId] = Array('id'=>$relId, 'type' => 'category', 'items' => $arItems);
			}
		}

		return $arRelations;
	}

	private static function GetSocnetGroupFilteredByFeaturePerms(&$arGroups, $arFeaturePerms)
	{
		$arGroupsIDs = array();
		foreach($arGroups as $value)
		{
			$arGroupsIDs[] = $value["id"];
		}

		if (count($arGroupsIDs) <= 0)
		{
			return;
		}

		$feature = $arFeaturePerms[0];
		$operations = $arFeaturePerms[1];
		if (!is_array($operations))
		{
			$operations = explode(",", $operations);
		}
		$arGroupsPerms = array();
		foreach($operations as $operation)
		{
			$tmpOps = CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arGroupsIDs, $feature, $operation);
			if (is_array($tmpOps))
			{
				foreach ($tmpOps as $key=>$val)
				{
					if (!($arGroupsPerms[$key] ?? null))
					{
						$arGroupsPerms[$key] = $val;
					}
				}
			}
		}
		$arGroupsActive = CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arGroupsIDs, $arFeaturePerms[0]);
		foreach ($arGroups as $key=>$group)
		{
			if (
				!$arGroupsActive[$group["id"]]
				|| !$arGroupsPerms[$group["id"]]
			)
			{
				unset($arGroups[$key]);
			}
		}
	}

	private static function GetSocnetGroupFilteredByInitiatePerms(&$arGroups)
	{
		$arGroupsIDs = array();
		foreach($arGroups as $value)
		{
			$arGroupsIDs[] = $value["id"];
		}

		if (count($arGroupsIDs) <= 0)
		{
			return;
		}

		if (
			CurrentUser::get()->isAdmin()
			|| CSocNetUser::IsCurrentUserModuleAdmin(CSite::GetDefSite())
		)
		{
			return;
		}

		$groupsList = array();
		$userRolesList = array();

		$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
			'filter' => array(
				'@ID' => $arGroupsIDs
			),
			'select' => array('ID', 'OWNER_ID', 'INITIATE_PERMS')
		));

		while ($group = $res->fetch())
		{
			$groupsList[$group['ID']] = array(
				'OWNER_ID' => $group['OWNER_ID'],
				'INITIATE_PERMS' => $group['INITIATE_PERMS']
			);
		}

		$res = UserToGroupTable::getList(array(
			'filter' => array(
				'USER_ID' => (int)CurrentUser::get()->getId(),
				'@GROUP_ID' => $arGroupsIDs
			),
			'select' => array('GROUP_ID', 'ROLE')
		));

		while ($relation = $res->fetch())
		{
			$userRolesList[$relation['GROUP_ID']] = $relation['ROLE'];
		}

		$userId = (int)CurrentUser::get()->getId();

		foreach ($arGroups as $key => $group)
		{
			$groupId = $group["id"];

			$canInitiate = (
				(
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == UserToGroupTable::ROLE_OWNER
					&& $userId == $groupsList[$groupId]["OWNER_ID"]
				)
				|| (
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == UserToGroupTable::ROLE_MODERATOR
					&& isset($userRolesList[$groupId])
					&& in_array($userRolesList[$groupId], array(
						UserToGroupTable::ROLE_OWNER,
						UserToGroupTable::ROLE_MODERATOR
					))
				)
				|| (
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == UserToGroupTable::ROLE_USER
					&& isset($userRolesList[$groupId])
					&& in_array($userRolesList[$groupId], array(
						UserToGroupTable::ROLE_OWNER,
						UserToGroupTable::ROLE_MODERATOR,
						UserToGroupTable::ROLE_USER
					))
				)
			);

			if (!$canInitiate)
			{
				unset($arGroups[$key]);
			}
		}
	}

	public static function GetDestinationUsers($accessCodes, $fetchUsers = false)
	{
		$userIds = [];
		$users = [];
		$fields = $fetchUsers
			? ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHOTO', 'WORK_POSITION', 'EXTERNAL_AUTH_ID']
			: ['ID'];

		$usersToFetch = [];

		if (is_array($accessCodes))
		{
			foreach($accessCodes as $code)
			{
				// All users
				if ($code === 'UA')
				{
					$dbRes = CUser::GetList(
						'ID',
						'ASC',
						['INTRANET_USERS' => true],
						['FIELDS' => $fields]
					);

					while ($user = $dbRes->Fetch())
					{
						if (array_key_exists($user['ID'], $userIds))
						{
							continue;
						}

						$userIds[$user['ID']] = $user['ID'];
						if ($fetchUsers)
						{
							$user['USER_ID'] = $user['ID'];
							$users[] = $user;
						}
					}
					break;
				}
				elseif (mb_substr($code, 0, 1) === 'U')
				{
					$userId = (int)mb_substr($code, 1);
					if (!array_key_exists($userId, $userIds))
					{
						$usersToFetch[] = $userId;
						if (!$fetchUsers)
						{
							$userIds[$userId] = $userId;
						}
					}
				}
				elseif (mb_substr($code, 0, 2) === 'SG')
				{
					$groupId = intval(mb_substr($code, 2));

					$isProjectRoles = preg_match('/^SG([0-9]+)_?([AEKMO])?$/', $code, $match) && isset($match[2]);

					if ($isProjectRoles)
					{
						// todo remove after new system the project roles.
						[$users, $userIds] = self::getUsersByRole($groupId, $match[2], $users, $userIds);

						continue;
					}

					$dbMembers = CSocNetUserToGroup::GetList(
						["RAND" => "ASC"],
						["GROUP_ID" => $groupId, "<=ROLE" => SONET_ROLES_USER, "USER_ACTIVE" => "Y"],
						false,
						false,
						["ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_EMAIL", "USER_PERSONAL_PHOTO", "USER_WORK_POSITION"]
					);

					if ($dbMembers)
					{
						while ($user = $dbMembers->GetNext())
						{
							if (array_key_exists($user['USER_ID'], $userIds))
							{
								continue;
							}
							$userIds[$user['USER_ID']] = $user["USER_ID"];
							$users[] = [
								'ID' => $user["USER_ID"],
								'USER_ID' => $user["USER_ID"],
								'LOGIN' => $user["USER_LOGIN"],
								'NAME' => $user["USER_NAME"],
								'LAST_NAME' => $user["USER_LAST_NAME"],
								'SECOND_NAME' => $user["USER_SECOND_NAME"],
								'EMAIL' => $user["USER_EMAIL"],
								'PERSONAL_PHOTO' => $user["USER_PERSONAL_PHOTO"],
								'WORK_POSITION' => $user["USER_WORK_POSITION"]
							];
						}
					}
				}
				elseif (mb_substr($code, 0, 2) === 'DR')
				{
					$depId = (int)mb_substr($code, 2);

					$res = \Bitrix\Intranet\Util::getDepartmentEmployees([
						'DEPARTMENTS' => $depId,
						'RECURSIVE' => 'Y',
						'ACTIVE' => 'Y',
						'SELECT' => $fields
					]);

					while ($user = $res->Fetch())
					{
						if (!array_key_exists($user['ID'], $userIds))
						{
							$userIds[$user['ID']] = $user['ID'];
							if ($fetchUsers)
							{
								$user['USER_ID'] = $user['ID'];
								$users[] = $user;
							}
						}
					}
				}
			}
		}

		if (
			!empty($usersToFetch)
			&& $fetchUsers
		)
		{
			$usersToFetch = array_chunk(array_values($usersToFetch), self::USERS_STEP_COUNT);

			foreach ($usersToFetch as $chunk)
			{
				$usersRes = \Bitrix\Main\UserTable::getList([
					'select' => $fields,
					'filter' => [
						'@ID' => array_values($chunk)
					],
					'order' => [
						'ID' => 'ASC'
					]
				])->fetchAll();

				foreach ($usersRes as $user)
				{
					if (array_key_exists($user['ID'], $userIds))
					{
						continue;
					}

					$userIds[$user['ID']] = $user['ID'];
					$user['USER_ID'] = $user['ID'];
					$users[] = $user;
				}
			}
		}

		return $fetchUsers ? $users : $userIds;
	}

	private static function getUsersByRole(int $groupId, $role, array $users, array $userIds): array
	{
		$isScrumCustomRole = false;
		$scrumCustomRole = '';

		$availableRoles = [
			SONET_ROLES_USER,
			SONET_ROLES_MODERATOR,
			SONET_ROLES_OWNER,
		];

		// todo maybe remove 'M' role after new system the project roles.
		$customScrumRoles = ['M'];
		$availableRoles = array_merge($availableRoles, $customScrumRoles);

		$group = Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);

		$role = in_array($role, $availableRoles) ? $role : SONET_ROLES_USER;

		if (in_array($role, $customScrumRoles))
		{
			$isScrumCustomRole = true;
			$scrumCustomRole = $role;
			$role = SONET_ROLES_MODERATOR;
		}

		$dbMembers = CSocNetUserToGroup::GetList(
			["RAND" => "ASC"],
			[
				"GROUP_ID" => $groupId,
				"=ROLE" => $isScrumCustomRole ? [SONET_ROLES_OWNER, SONET_ROLES_MODERATOR] : $role,
				"USER_ACTIVE" => "Y"
			],
			false,
			false,
			[
				"ID",
				"USER_ID",
				"ROLE",
				"USER_NAME",
				"USER_LAST_NAME",
				"USER_SECOND_NAME",
				"USER_LOGIN",
				"USER_EMAIL",
				"USER_PERSONAL_PHOTO",
				"USER_WORK_POSITION",
			]
		);

		if ($dbMembers)
		{
			while ($user = $dbMembers->GetNext())
			{
				if ($group && $group->isScrumProject())
				{
					if ($role === SONET_ROLES_MODERATOR)
					{
						$scrumMasterId = $group->getScrumMaster();

						if ($isScrumCustomRole)
						{
							if ($scrumCustomRole === 'M' && $user["USER_ID"] != $scrumMasterId)
							{
								continue;
							}
						}
						else
						{
							if ($user["USER_ID"] == $scrumMasterId)
							{
								continue;
							}
						}
					}
				}

				if (!array_key_exists($user["USER_ID"], $userIds))
				{
					$userIds[$user["USER_ID"]] = $user["USER_ID"];
					$users[] = [
						'ID' => $user["USER_ID"],
						'USER_ID' => $user["USER_ID"],
						'LOGIN' => $user["USER_LOGIN"],
						'NAME' => $user["USER_NAME"],
						'LAST_NAME' => $user["USER_LAST_NAME"],
						'SECOND_NAME' => $user["USER_SECOND_NAME"],
						'EMAIL' => $user["USER_EMAIL"],
						'PERSONAL_PHOTO' => $user["USER_PERSONAL_PHOTO"],
						'WORK_POSITION' => $user["USER_WORK_POSITION"]
					];
				}
			}
		}

		return [$users, $userIds];
	}

	public static function GetDestinationSort($arParams = array(), &$dataAdditional = false)
	{
		$res = \Bitrix\Main\UI\Selector\Entities::getLastSort($arParams);
		$dataAdditional = $res['DATA_ADDITIONAL'];

		return $res['DATA'];
	}

	public static function fillLastDestination($arDestinationSort, &$arLastDestination, $arParams = array())
	{
		$res = \Bitrix\Main\UI\Selector\Entities::fillLastDestination($arDestinationSort, $arParams);
		$arLastDestination = $res['LAST_DESTINATIONS'];

		return $res['DATA'];
	}

	public static function fillEmails(&$arDest)
	{
		$arDest["EMAILS"] = array();
		$arDest["LAST"]["EMAILS"] = array();

		if (
			!empty($arDest)
			&& !empty($arDest["LAST"])
			&& !empty($arDest["LAST"]["USERS"])
			&& !empty($arDest["USERS"])
		)
		{
			foreach($arDest["LAST"]["USERS"] as $key => $value)
			{
				if (
					isset($arDest["USERS"][$key])
					&& is_array($arDest["USERS"][$key])
					&& isset($arDest["USERS"][$key]["isEmail"])
					&& $arDest["USERS"][$key]["isEmail"] === "Y"
				)
				{
					$arDest["EMAILS"][$key] = $arDest["USERS"][$key];
					$arDest["LAST"]["EMAILS"][$key] = $value;
				}
			}
		}
	}

	public static function fillCrmEmails(&$arDest)
	{
		$arDest["CRMEMAILS"] = array();
		$arDest["LAST"]["CRMEMAILS"] = array();

		if (
			!empty($arDest)
			&& !empty($arDest["LAST"])
			&& !empty($arDest["LAST"]["USERS"])
			&& !empty($arDest["USERS"])
		)
		{
			foreach($arDest["LAST"]["USERS"] as $key => $value)
			{
				if (
					isset($arDest["USERS"][$key])
					&& is_array($arDest["USERS"][$key])
					&& isset($arDest["USERS"][$key]["isCrmEmail"])
					&& $arDest["USERS"][$key]["isCrmEmail"] === "Y"
				)
				{
					$arDest["CRMEMAILS"][$key] = $arDest["USERS"][$key];
					$arDest["LAST"]["CRMEMAILS"][$key] = $value;
				}
			}
		}
	}

	public static function getUsersAll($arParams = [])
	{
		global $DB;
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		static $arFieldsStatic = array(
			"ID" => Array("FIELD" => "U.ID", "TYPE" => "int"),
			"ACTIVE" => Array("FIELD" => "U.ACTIVE", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string"),
			"EMAIL" => Array("FIELD" => "U.EMAIL", "TYPE" => "string"),
			"LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string"),
			"SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string"),
			"LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string"),
			"PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int"),
			"WORK_POSITION" => Array("FIELD" => "U.WORK_POSITION", "TYPE" => "string"),
			"CONFIRM_CODE" =>  Array("FIELD" => "U.CONFIRM_CODE", "TYPE" => "string"),
			"PERSONAL_PROFESSION" => Array("FIELD" => "U.PERSONAL_PROFESSION", "TYPE" => "string"),
			"EXTERNAL_AUTH_ID" => Array("FIELD" => "U.EXTERNAL_AUTH_ID", "TYPE" => "string")
		);

		$arFields = $arFieldsStatic;
		$arFields["IS_ONLINE"] = Array(
			"FIELD" => "case when U.LAST_ACTIVITY_DATE > " . $helper->addSecondsToDateTime(-CUser::getSecondsForLimitOnline()) . " then 'Y' else 'N' end"
		);

		$currentUserId = (int)CurrentUser::get()->getId();

		if (!$currentUserId)
		{
			return array();
		}

		$bExtranetEnabled = CModule::includeModule("extranet");

		$bExtranetUser = (
			$bExtranetEnabled
			&& !CExtranet::IsIntranetUser()
		);

		$bExtranetWorkgroupsAllowed = (
			$bExtranetEnabled
			&& CExtranet::WorkgroupsAllowed()
		);

		$bShowAllContactsAllowed = (
			$bExtranetEnabled
			&& CExtranet::ShowAllContactsAllowed()
		);

		$rsData = CUserTypeEntity::GetList(
			array("ID" => "ASC"),
			array(
				"FIELD_NAME" => "UF_DEPARTMENT",
				"ENTITY_ID" => "USER"
			)
		);
		if($arRes = $rsData->Fetch())
		{
			$UFId = (int)$arRes["ID"];
		}
		else
		{
			return array();
		}

		if (
			$bExtranetUser
			&& !$bExtranetWorkgroupsAllowed
		) // limited extranet
		{
			return false;
		}

		$arOrder = array("ID" => "ASC");
		$arFilter = array('ACTIVE' => 'Y');

		if (
			IsModuleInstalled("intranet")
			|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") === "Y"
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$arExternalAuthId = self::getExternalAuthIdBlackList([
			'ALLOW_BOTS' => (isset($arParams['ALLOW_BOTS']) && $arParams['ALLOW_BOTS'] === true)
		]);
		$arExternalAuthId[] = 'email';
		$arFilter['!EXTERNAL_AUTH_ID'] = $arExternalAuthId;

		$arGroupBy = false;
		$arSelectFields = array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "EXTERNAL_AUTH_ID", "EMAIL", "IS_ONLINE");

		$arSqls = CSocNetGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);
		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);
		$strJoin = $strJoin2 = $arSqls2 = false;

		if ($bExtranetEnabled)
		{
			if ($bExtranetWorkgroupsAllowed)
			{
				if (!$bExtranetUser)
				{
					$strJoin = "
						INNER JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".intval($UFId)."
					";

					$tmp = $arSqls;

					$arSqls["WHERE"] .= ($arSqls["WHERE"] <> '' ? " AND " : "")."
						(UM.VALUE_INT > 0)";

					if (!$bShowAllContactsAllowed)
					{
						// select all the users (intranet and extranet from my groups)
						$strJoin2 = "
							INNER JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID AND UG.ROLE <= '".SONET_ROLES_USER."'
							INNER JOIN b_sonet_user2group UG_MY 
								ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".(int)$currentUserId."
								AND UG_MY.ROLE <= '".UserToGroupTable::ROLE_USER."'
							LEFT JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".(int)$UFId."
						";
						$arSqls2 = $tmp;
					}
				}
				else
				{
					$strJoin = "
						INNER JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID AND UG.ROLE <= '".SONET_ROLES_USER."'
						INNER JOIN b_sonet_user2group UG_MY 
							ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".(int)$currentUserId."
							AND UG_MY.ROLE <= '".UserToGroupTable::ROLE_USER."'
						LEFT JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".(int)$UFId."
					";
				}
			}
			elseif (!$bShowAllContactsAllowed) // limited extranet, only for intranet users, don't show extranet
			{
				$strJoin = "INNER JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".intval($UFId);
				$arSqls["WHERE"] .= ($arSqls["WHERE"] <> '' ? " AND " : "")."UM.VALUE_INT > 0";
			}
		}

		$strSql =
			"SELECT
				".(
					$bExtranetEnabled
						? $arSqls["SELECT"].", CASE WHEN UM.VALUE_INT > 0 THEN 'employee' WHEN EXTERNAL_AUTH_ID = 'email' THEN 'email' ELSE 'extranet' END USER_TYPE"
						: $arSqls["SELECT"].", CASE WHEN EXTERNAL_AUTH_ID = 'email' THEN 'email' ELSE 'employee' END USER_TYPE"
				)." 
			FROM b_user U
				".$arSqls["FROM"]." ";

		if ($strJoin)
		{
			$strSql .= $strJoin." ";
		}

		if ($arSqls["WHERE"] <> '')
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}

		$strSql .= "GROUP BY U.ID".($bExtranetEnabled ? ",UM.VALUE_INT" : "")." ";

		if ($strJoin2)
		{
			$strSql .=
				"UNION SELECT
					".$arSqls2["SELECT"].", CASE WHEN UM.VALUE_INT > 0 THEN 'employee' WHEN EXTERNAL_AUTH_ID = 'email' THEN 'email' ELSE 'extranet' END  USER_TYPE 
				FROM b_user U
					".$arSqls2["FROM"]." ";

				$strSql .= $strJoin2." ";

			if ($arSqls2["WHERE"] <> '')
			{
				$strSql .= "WHERE ".$arSqls2["WHERE"]." ";
			}

			$strSql .= "GROUP BY U.ID".($bExtranetEnabled ? ",UM.VALUE_INT" : "")." ";

			$strSql .= "ORDER BY ID ASC"; // cannot use alias
		}
		else // only without union
		{
			if ($arSqls["ORDERBY"] <> '')
			{
				$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
			}
		}

		//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

		$dbRes = $DB->Query($strSql);

		$maxCount = (IsModuleInstalled('bitrix24') ? 200 : 500);
		$resultCount = 0;
		$countExceeded = false;
		$arUsers = array();

		if ($bExtranetEnabled)
		{
			CSocNetTools::InitGlobalExtranetArrays();
		}

		while ($arUser = $dbRes->GetNext())
		{
			if (
				$resultCount > $maxCount
				&& (!isset($arParams['RETURN_FULL_LIST']) || $arParams['RETURN_FULL_LIST'] !== 'Y')
			)
			{
				$countExceeded = true;
				break;
			}

			$sName = trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser, true, false));

			if (empty($sName))
			{
				$sName = $arUser["~LOGIN"];
			}

			$arFileTmp = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"],
				array('width' => 100, 'height' => 100),
				BX_RESIZE_IMAGE_EXACT,
				false
			);

			$arUsers['U'.$arUser["ID"]] = Array(
				'id' => 'U'.$arUser["ID"],
				'entityId' => $arUser["ID"],
				'email' => $arUser["EMAIL"],
				'name' => $sName,
				'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
				'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
				'isExtranet' => (isset($arUser['USER_TYPE']) && $arUser['USER_TYPE'] === 'extranet' ? "Y" : "N"),
				'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] === 'email' ? 'Y' : 'N'),
				'active' => 'Y'
			);

			if ($arUser['EXTERNAL_AUTH_ID'] === 'email')
			{
				$arUsers['U'.$arUser["ID"]]['email'] = $arUser['EMAIL'];
			}

			$arUsers['U'.$arUser["ID"]]['checksum'] = md5(serialize($arUsers['U'.$arUser["ID"]]));
			$arUsers['U'.$arUser["ID"]]['login'] = '';

			$resultCount++;
		}

		if ($countExceeded)
		{
			return CSocNetLogDestination::GetUsers(
				array(
					"id" => array($currentUserId)
				),
				true
			);
		}

		return $arUsers;
	}

	public static function formatUser($arUser, $arParams = array())
	{
		static $siteNameFormat = false;
		static $isIntranetInstalled = false;
		static $extranetUserIdList = false;

		if ($siteNameFormat === false)
		{
			$siteNameFormat = CSite::GetNameFormat(false);
		}

		if ($isIntranetInstalled === false)
		{
			$isIntranetInstalled = (IsModuleInstalled('intranet') ? 'Y' : 'N');
		}

		if ($extranetUserIdList === false)
		{
			$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();
		}

		$arFileTmp = CFile::ResizeImageGet(
			$arUser["PERSONAL_PHOTO"],
			array('width' => 100, 'height' => 100),
			BX_RESIZE_IMAGE_EXACT,
			false
		);

		$arRes = array(
			'id' => 'U'.$arUser["ID"],
			'entityId' => $arUser["ID"]
		);

		if (ModuleManager::isModuleInstalled('intranet'))
		{
			$arRes["email"] = $arUser['EMAIL'];
		}

		$arRes = array_merge($arRes, array(
			'name' => CUser::FormatName(
				(
					!empty($arParams["NAME_TEMPLATE"])
						? $arParams["NAME_TEMPLATE"]
						: $siteNameFormat
				),
				$arUser,
				true,
				true
			),
			'avatar' => (
				empty($arFileTmp['src'])
					? ''
					: $arFileTmp['src']
			),
			'desc' => self::getUserDescription($arUser, [
				'showEmail' => !empty($arParams["ONLY_WITH_EMAIL"])
			]),
			'isExtranet' => (
				in_array($arUser["ID"], $extranetUserIdList)
					? "Y"
					: "N"
			),
			'isEmail' => (
				isset($arUser['EXTERNAL_AUTH_ID'])
				&& $arUser['EXTERNAL_AUTH_ID'] === 'email'
					? 'Y'
					: 'N'
			)
		));

		if (!empty($arUser["UF_USER_CRM_ENTITY"]))
		{
			$arRes['crmEntity'] = $arUser["UF_USER_CRM_ENTITY"];
		}

		if (!empty($arUser["ACTIVE"]))
		{
			$arRes['active'] = $arUser["ACTIVE"];
		}

		if (
			(
				isset($arParams['USE_EMAIL'])
				&& $arParams['USE_EMAIL']
			)
			|| $arRes['isEmail'] === 'Y'
		)
		{
			if ($arUser["NAME"] <> '')
			{
				$arRes['showEmail'] = "Y";
			}
		}

		$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogDestinationFormatUser");
		while ($arEvent = $db_events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($arUser, &$arRes));
		}

		$checksum = md5(serialize($arRes));
		$arRes['checksum'] = $checksum;

		$arRes['login'] = (
			$isIntranetInstalled === 'Y'
			&& isset($arParams['USE_LOGIN'])
			&& $arParams['USE_LOGIN']
				? $arUser["LOGIN"]
				: ''
		);

		$arRes['index'] = (
			isset($arUser["SEARCH_USER_CONTENT"])
				? $arUser["SEARCH_USER_CONTENT"]
				: ''
		);

		return $arRes;
	}

	public static function formatCrmEmailEntity($fields, $params = array())
	{
		static $siteNameFormat = false;

		$result = array();
		$userParams = array();

		if (
			is_array($params)
			&& isset($params["TYPE"])
			&& in_array($params["TYPE"], array('CONTACT', 'COMPANY', 'LEAD'))
		)
		{
			if ($siteNameFormat === false)
			{
				$siteNameFormat = (
					!empty($params["NAME_TEMPLATE"])
						? $params["NAME_TEMPLATE"]
						: CSite::GetNameFormat(false)
				);
			}

			$prefix = '';
			if ($params["TYPE"] === 'CONTACT')
			{
				$prefix = 'C_';
				$imageField = 'PHOTO';
				$userParams = array(
					'name' => $fields['NAME'],
					'lastName' => $fields['LAST_NAME']
				);
				$name = CUser::FormatName(
					$siteNameFormat,
					$fields,
					true,
					true
				);
			}
			elseif ($params["TYPE"] === 'COMPANY')
			{
				$prefix = 'CO_';
				$imageField = 'LOGO';
				$name = $fields['TITLE'];
				$userParams = array(
					'name' => '',
					'lastName' => $fields['TITLE']
				);
			}
			elseif ($params["TYPE"] === 'LEAD')
			{
				$prefix = 'L_';
				$imageField = false;
				$name = $fields['TITLE'];
				$userParams = array(
					'name' => $fields['NAME'],
					'lastName' => $fields['LAST_NAME']
				);
				$username = CUser::FormatName(
					$siteNameFormat,
					$fields,
					true,
					true
				);
				if (!empty($username))
				{
					$name .= ', '.$username;
				}
			}

			if (
				$imageField
				&& isset($fields[$imageField])
			)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$fields[$imageField],
					array('width' => 100, 'height' => 100),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
			}
			else
			{
				$arFileTmp = array();
			}

			$result = array(
				'id' => $prefix.$fields["ID"],
				'crmEntity' => $prefix.$fields["ID"],
				'entityId' => $fields['ID'],
				'name' => $name,
				'avatar' => (
					empty($arFileTmp['src'])
						? ''
						: $arFileTmp['src']
				),
				'desc' => (!empty($params['EMAIL']) ? $params['EMAIL'] : ''),
				'email' => (!empty($params['EMAIL']) ? $params['EMAIL'] : ''),
				'isExtranet' => 'N',
				'isEmail' => 'Y',
				'isCrmEmail' => 'Y',
				'params' => $userParams
			);
		}

		return $result;
	}

	public static function formatNetworkUser($fields, $params = array())
	{
		static $siteNameFormat = false;

		if ($siteNameFormat === false)
		{
			$siteNameFormat = (
				!empty($params["NAME_TEMPLATE"])
					? $params["NAME_TEMPLATE"]
					: CSite::GetNameFormat(false)
			);
		}

		$name = CUser::FormatName(
			$siteNameFormat,
			$fields,
			true,
			true
		);

		if (isset($fields["EXTERNAL_AUTH_ID"]) && $fields["EXTERNAL_AUTH_ID"] === "replica")
			[,$domain] = explode("@", $fields["LOGIN"], 2);
		else
			$domain = $fields["CLIENT_DOMAIN"];

		if ($fields["PERSONAL_PHOTO"])
		{
			$arFileTmp = CFile::ResizeImageGet(
				$fields["PERSONAL_PHOTO"],
				array('width' => 32, 'height' => 32),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
		}

		$userParams = array(
			'name' => $fields['NAME'],
			'lastName' => $fields['LAST_NAME'],
			'domain' => $domain,
		);

		$result = array(
			'id' => isset($fields['ID'])? $fields['ID']: $fields['XML_ID'],
			'entityId' => isset($fields['ID'])? $fields['ID']: $fields['XML_ID'],
			'name' => $name,
			'avatar' => $fields["PERSONAL_PHOTO"] && !empty($arFileTmp['src'])? $arFileTmp['src']: '',
			'desc' => $domain,
			'showDesc' => true,
			'email' => (!empty($fields['EMAIL']) ? $fields['EMAIL'] : ''),
			'networkId' => $fields['NETWORK_ID'],
			'isExtranet' => 'N',
			'isEmail' => 'N',
			'isNetwork' => 'Y',
			'params' => $userParams
		);

		return $result;
	}

	private static function getExternalAuthIdBlackList($params = array())
	{
		$result = [
			"imconnector"
		];

		if (
			!is_array($params)
			|| !isset($params["NETWORK_SEARCH"])
			|| !$params["NETWORK_SEARCH"]
		)
		{
			$result[] = 'replica';
		}

		if (
			!is_array($params)
			|| !isset($params['ALLOW_BOTS'])
			|| !$params['ALLOW_BOTS']
		)
		{
			$result[] = 'bot';
		}

		return $result;
	}

	public static function getUserDescription(array $userFields = [], array $params = [])
	{
		$showEmail = (!empty($params['showEmail']) ? !!$params['showEmail'] : false);

		return (
			$showEmail
			&& ModuleManager::isModuleInstalled('intranet')
				? (
					isset($userFields["EMAIL"])
					&& $userFields["EMAIL"] <> ''
						? $userFields["EMAIL"]
						: '&nbsp;'
				)
				: (
					isset($userFields['WORK_POSITION'])
					&& $userFields['WORK_POSITION'] <> ''
						? $userFields['WORK_POSITION']
						: (
							isset($userFields['PERSONAL_PROFESSION'])
							&& $userFields['PERSONAL_PROFESSION'] <> ''
								? $userFields['PERSONAL_PROFESSION']
								: '&nbsp;'
						)
			)
		);
	}
}
