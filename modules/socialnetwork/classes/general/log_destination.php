<?
use Bitrix\Main\ModuleManager;

class CSocNetLogDestination
{
	/**
	* Retrieves last used users from socialnetwork/log_destination UserOption
	* @deprecated
	*/
	public static function GetLastUser()
	{
		global $USER;

		static $resultCache = array();

		$userId = intval($USER->GetID());

		if(!isset($resultCache[$userId]))
		{
			$arLastSelected = CUserOptions::GetOption("socialnetwork", "log_destination", array());
			$arLastSelected = (
				is_array($arLastSelected)
				&& strlen($arLastSelected['users']) > 0
				&& $arLastSelected['users'] != '"{}"'
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
			&& strlen($arLastSelected['sonetgroups']) > 0
			&& $arLastSelected['sonetgroups'] != '"{}"'
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
			&& strlen($arLastSelected['department']) > 0
			&& $arLastSelected['department'] != '"{}"'
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
			if (intval($arParams["HEAD_DEPT"]) > 0)
			{
				$result["department_relation_head"] = self::GetTreeList('DR'.intval($arParams["HEAD_DEPT"]), $result["department"], true);
			}
		}

		return $result;
	}

	public static function GetExtranetUser(array $arParams = array())
	{
		global $USER, $CACHE_MANAGER;

		static $resultCache = array();

		$userId = intval($USER->getID());

		if(!isset($resultCache[$userId]))
		{
			$arUsers = Array();

			$arFilter = Array();
			if (CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser())
			{
				$cacheTtl = 3153600;
				$cacheId = 'socnet_destination_getusers_'.md5(serialize($arParams)).'_'.$userId;
				$cacheDir = '/socnet/dest_extranet/'.intval($userId / 100).'/';

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

					$arSelect = Array($userId);
					$arSocnetGroups = array();

					if(defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->registerTag("sonet_user2group_U".$userId);
					}

					$rsRelation = CSocNetUserToGroup::GetList(
						array("GROUP_NAME" => "ASC"),
						array(
							"USER_ID" => $userId,
							"<=ROLE" => SONET_ROLES_USER,
							"GROUP_SITE_ID" => SITE_ID,
							"GROUP_ACTIVE" => "Y",
							"!GROUP_CLOSED" => "Y"
						),
						false,
						array("nTopCount" => 500),
						array("ID", "GROUP_ID")
					);
					while($arRelation = $rsRelation->Fetch())
					{
						$arGroupTmp = array(
							"id" => $arRelation["GROUP_ID"],
							"entityId" => $arRelation["GROUP_ID"]
						);
						$arSocnetGroups[$arRelation["GROUP_ID"]] = $arGroupTmp;

						if(defined("BX_COMP_MANAGED_CACHE"))
						{
							$CACHE_MANAGER->registerTag("sonet_user2group_G".$arRelation["GROUP_ID"]);
						}
					}

					if (count($arSocnetGroups) > 0)
					{
						$arUserSocNetGroups = Array();
						foreach ($arSocnetGroups as $groupId => $ar)
						{
							$arUserSocNetGroups[] = $groupId;
						}

						$dbRelation = CSocNetUserToGroup::GetList(
							array(),
							array(
								"GROUP_ID" => $arUserSocNetGroups,
								"<=ROLE" => SONET_ROLES_USER,
								"USER_ACTIVE" => "Y"
							),
							false,
							false,
							array("ID", "USER_ID", "GROUP_ID")
						);
						while ($arRelation = $dbRelation->fetch())
						{
							$arSelect[] = intval($arRelation["USER_ID"]);
						}
					}
					$arFilter['ID'] = implode('|', $arSelect);

					if (!empty($arFilter['ID']))
					{
						$arExtParams = Array(
							"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE")
						);

						$dbUsers = CUser::GetList(($sort_by = Array('last_name'=>'asc', 'IS_ONLINE'=>'desc')), ($dummy=''), $arFilter, $arExtParams);
						while ($arUser = $dbUsers->GetNext())
						{
							$sName = trim(CUser::FormatName(CSite::GetNameFormat(), $arUser, true, false));

							if (empty($sName))
							{
								$sName = $arUser["~LOGIN"];
							}

							$arFileTmp = CFile::ResizeImageGet(
								$arUser["PERSONAL_PHOTO"],
								array(
									"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 100),
									"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 100)
								),
								BX_RESIZE_IMAGE_EXACT,
								false
							);

							$arUsers['U'.$arUser["ID"]] = Array(
								'id' => 'U'.$arUser["ID"],
								'entityId' => $arUser["ID"],
								'name' => $sName,
								'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
								'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION']?$arUser['PERSONAL_PROFESSION']:'&nbsp;'),
							);

							if (defined("BX_COMP_MANAGED_CACHE"))
							{
								$CACHE_MANAGER->registerTag("USER_NAME_".intval($arUser["ID"]));
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
		global $USER, $CACHE_MANAGER;

		$userId = intval($USER->GetID());
		$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();

		if (
			isset($arParams['all'])
			&& $arParams['all'] == 'Y'
		)
		{
			if (IsModuleInstalled("intranet"))
			{
				return self::getUsersAll($arParams);
			}
			else
			{
				$arParamsNew = $arParams;
				$arParamsNew["id"] = array($userId);
				unset($arParamsNew["all"]);
				return CSocNetLogDestination::GetUsers($arParamsNew, $bSelf);
			}
		}

		$bExtranet = false;
		$arFilter = array();
		if (
			!isset($arParams['IGNORE_ACTIVITY'])
			|| $arParams['IGNORE_ACTIVITY'] != 'Y'
		)
		{
			$arFilter['ACTIVE'] = 'Y';
		}

		$arExternalAuthId = self::getExternalAuthIdBlackList();

		if (!empty($arExternalAuthId))
		{
			$arFilter['!EXTERNAL_AUTH_ID'] = $arExternalAuthId;
		}

		if (
			(
				!isset($arParams['IGNORE_ACTIVITY'])
				|| $arParams['IGNORE_ACTIVITY'] != 'Y'
			)
			&& (
				IsModuleInstalled("intranet")
				|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
			)
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$arExtParams = Array(
			"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "EMAIL", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE", "EXTERNAL_AUTH_ID"),
			"SELECT" => array()
		);

		if (
			isset($arParams["CRM_ENTITY"])
			&& $arParams["CRM_ENTITY"] == "Y"
			&& ModuleManager::isModuleInstalled('crm')
		)
		{
			$arExtParams['SELECT'][] = 'UF_USER_CRM_ENTITY';
		}

		if (array_key_exists('id', $arParams))
		{
			if (empty($arParams['id']))
			{
				$arFilter['ID'] = $userId;
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
				$arFilter['ID'] = implode('|', $arSelect);
			}
		}
		elseif (isset($arParams['deportament_id']))
		{
			if (is_array($arParams['deportament_id']))
			{
				$arFilter['UF_DEPARTMENT'] = $arParams['deportament_id'];
			}
			else
			{
				if ($arParams['deportament_id'] == 'EX')
				{
					$bExtranet = true;
				}
				else
				{
					$arFilter['UF_DEPARTMENT'] = intval($arParams['deportament_id']);
				}
			}

			$arExtParams['SELECT'][] = 'UF_DEPARTMENT';
		}

		$avatarSize = array(
			"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 100),
			"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 100)
		);

		$cacheTtl = 3153600;
		$cacheId = 'socnet_destination_getusers_'.md5(serialize($arFilter)).$bSelf.CSocNetUser::IsCurrentUserModuleAdmin().($bExtranet ? '_ex_'.$userId : '').md5(serialize($avatarSize));
		$cacheDir = '/socnet/dest/'.(
			isset($arParams['id'])
				? 'user'
				: 'dept'
		).'/';

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
						"USER_ID" => $USER->GetId(),
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

				$CACHE_MANAGER->RegisterTag("sonet_user2group_U".$USER->GetId());

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
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => $arUserTmp['WORK_POSITION'] ? $arUserTmp['WORK_POSITION'] : ($arUserTmp['PERSONAL_PROFESSION'] ? $arUserTmp['PERSONAL_PROFESSION'] : '&nbsp;'),
					);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".IntVal($arUserTmp["ID"]));
					}
				}
			}
			else
			{
				$bExtranetInstalled = CModule::IncludeModule("extranet");
				CSocNetTools::InitGlobalExtranetArrays();

				if (
					!isset($arFilter['UF_DEPARTMENT'])
					&& $bExtranetInstalled
				)
				{
					$arUserIdVisible = CExtranet::GetMyGroupsUsersSimple(SITE_ID);
				}

				$arUsers = Array();

				$dbUsers = CUser::GetList(
					($sort_by = array('last_name'=> 'asc', 'IS_ONLINE'=>'desc')),
					($dummy=''),
					$arFilter,
					$arExtParams
				);

				while ($arUser = $dbUsers->GetNext())
				{
					if (
						!$bSelf
						&& is_object($USER)
						&& $userId == $arUser["ID"]
					)
					{
						continue;
					}

					if (
						!isset($arFilter['UF_DEPARTMENT']) // all users
						&& $bExtranetInstalled
					)
					{
						if (
							isset($arUser["UF_DEPARTMENT"])
							&& (
								!is_array($arUser["UF_DEPARTMENT"])
								|| empty($arUser["UF_DEPARTMENT"])
								|| intval($arUser["UF_DEPARTMENT"][0]) <= 0
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
						$sName = $arUser["~LOGIN"];
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
						'name' => $sName,
						'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
						'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
						'isExtranet' => (in_array($arUser["ID"], $extranetUserIdList) ? "Y" : "N"),
						'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] == 'email' ? 'Y' : 'N'),
						'isCrmEmail' => (
							$arUser['EXTERNAL_AUTH_ID'] == 'email'
							&& !empty($arUser['UF_USER_CRM_ENTITY'])
								? 'Y'
								: 'N'
						)
					);

					if ($arUser['EXTERNAL_AUTH_ID'] == 'email')
					{
						$arUsers['U'.$arUser["ID"]]['email'] = $arUser['EMAIL'];
					}

					$arUsers['U'.$arUser["ID"]]['checksum'] = md5(serialize($arUsers['U'.$arUser["ID"]]));

					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$CACHE_MANAGER->RegisterTag("USER_NAME_".IntVal($arUser["ID"]));
					}
				}
			}

			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->RegisterTag("USER_NAME");
				if (!empty($arFilter['UF_DEPARTMENT']))
				{
					$CACHE_MANAGER->RegisterTag('intranet_department_'.$arFilter['UF_DEPARTMENT']);
				}
				$CACHE_MANAGER->EndTagCache();
			}

			$obCache->EndDataCache($arUsers);
		}

		return $arUsers;
	}

	public static function GetGratMedalUsers($arParams = Array())
	{
		global $USER;

		static $resultCache = array();

		$userId = intval($USER->GetID());

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
				"FIELDS" => Array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "IS_ONLINE"),
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

			$dbUsers = CUser::GetList(($sort_by = Array("last_name" => "asc", "IS_ONLINE" => "desc")), ($dummy=''), $arFilter, $arExtParams);
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

	public function __percent_walk(&$val)
	{
		$val = str_replace('%', '', $val)."%";
	}

	public static function SearchUsers($search, &$nt = "", $bSelf = true, $bEmployeesOnly = false, $bExtranetOnly = false, $departmentId = false)
	{
		global $USER, $DB;

		CUtil::JSPostUnescape();

		if (is_array($search))
		{
			$arParams = $search;
			$search = $arParams["SEARCH"];
			$nameTemplate = (isset($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : '');
			$bSelf = (isset($arParams["SELF"]) ? $arParams["SELF"] : true);
			$bEmployeesOnly = (isset($arParams["EMPLOYEES_ONLY"]) ? $arParams["EMPLOYEES_ONLY"] : false);
			$bExtranetOnly = (isset($arParams["EXTRANET_ONLY"]) ? $arParams["EXTRANET_ONLY"] : false);
			$departmentId = (isset($arParams["DEPARTAMENT_ID"]) ? $arParams["DEPARTAMENT_ID"] : false);
			$bEmailUsers = (isset($arParams["EMAIL_USERS"]) ? $arParams["EMAIL_USERS"] : false);
			$bCrmEmailUsers = (isset($arParams["CRMEMAIL_USERS"]) && ModuleManager::isModuleInstalled('crm') ? $arParams["CRMEMAIL_USERS"] : false);
			$bActiveOnly = (isset($arParams["CHECK_ACTIVITY"]) && $arParams["CHECK_ACTIVITY"] === false ? false : true);
			$bNetworkSearch = (isset($arParams["NETWORK_SEARCH"]) ? $arParams["NETWORK_SEARCH"] : false);
		}
		else
		{
			$nameTemplate = $nt;
			$bEmailUsers = false;
			$bCrmEmailUsers = false;
			$bActiveOnly = true;
			$bNetworkSearch = false;
		}

		$arUsers = array();
		$search = trim($search);
		if (
			strlen($search) <= 0
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

		$bEmailUsersAll = ($bMailEnabled && \Bitrix\Main\Config\Option::get('socialnetwork', 'email_users_all', 'N') == 'Y');
		$bExtranetUser = ($bExtranetEnabled && !CExtranet::IsIntranetUser());

		$current_user_id = intval($USER->GetID());

		if ($bExtranetEnabled)
		{
			CSocNetTools::InitGlobalExtranetArrays();
		}

		$arSearchValue = preg_split('/\s+/', trim(ToUpper($search)));
		array_walk($arSearchValue, array('CSocNetLogDestination', '__percent_walk'));

		$arMyUserId = array();

		if ($bIntranetEnabled)
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
					count($arSearchValue) == 1
					&& strlen($arSearchValue[0]) > 2
				)
				{
					$arLogicFilter['LOGIN'] = $arSearchValue[0];
				}
			}

			$arFilter = array(
				$arLogicFilter
			);

			if ($bActiveOnly)
			{
				$arFilter['=ACTIVE'] = 'Y';
			}

			$arExternalAuthId = self::getExternalAuthIdBlackList(array(
				"NETWORK_SEARCH" => $bNetworkSearch
			));

			if (!empty($arExternalAuthId))
			{
				$arFilter['!=EXTERNAL_AUTH_ID'] = $arExternalAuthId;
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
		else
		{
			if (count($arSearchValue) == 2)
			{
				$arFilter = array(
					array(
						'LOGIC' => 'OR',
						array('LOGIC' => 'AND', 'NAME' => $arSearchValue[0], 'LAST_NAME' => $arSearchValue[1]),
						array('LOGIC' => 'AND', 'NAME' => $arSearchValue[1], 'LAST_NAME' => $arSearchValue[0]),
					)
				);
			}
			else
			{
				$arFilter = array(
					array(
						'LOGIC' => 'OR',
						'NAME' => $arSearchValue,
						'LAST_NAME' => $arSearchValue,
					)
				);
			}

			if ($bActiveOnly)
			{
				$arFilter['=ACTIVE'] = 'Y';
			}
		}

		if (
			!$bNetworkSearch
			&& (
				$bIntranetEnabled
				|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
			)
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
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
					"MY_USERS" => $arMyUserId
				),
				$arFilter,
				$bFilteredByMyUserId
			);

			if (!$arFilter)
			{
				return $arUsers;
			}

			if ($bNetworkSearch)
			{
				end($arFilter);
				$arFilter[key($arFilter)]["=EXTERNAL_AUTH_ID"] = "replica";
			}
		}

		if (
			!empty($arMyUserId)
			&& !$bFilteredByMyUserId
		)
		{
			$arFilter[] = array(
				'LOGIC' => 'OR',
				'!=EXTERNAL_AUTH_ID' => 'email',
				'ID' => $arMyUserId,
			);
		}

		$arSelect = array(
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
			$arSelect[] = "UF_USER_CRM_ENTITY";
		}

		if (!$bActiveOnly)
		{
			$arSelect[] = "ACTIVE";
		}

		$db_events = GetModuleEvents("socialnetwork", "OnSocNetLogDestinationSearchUsers");
		while ($arEvent = $db_events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($arSearchValue, &$arFilter, &$arSelect));
		}

		$rsUser = \Bitrix\Main\UserTable::getList(array(
			'order' => array(
				"MAX_LAST_USE_DATE" => 'DESC',
				'LAST_NAME' => 'ASC'
			),
			'filter' => $arFilter,
			'select' => $arSelect,
			'limit' => 100,
			'data_doubling' => false
		));

		$queryResultCnt = 0;
		$bUseLogin = (strlen($search) > 3 && strpos($search, '@') > 0);
		$params = array(
			"NAME_TEMPLATE" => $nameTemplate,
			"USE_EMAIL" => $bSearchByEmail,
			"USE_LOGIN" => $bUseLogin
		);
		while ($arUser = $rsUser->fetch())
		{
			$queryResultCnt++;
			if (
				!$bSelf
				&& $current_user_id == $arUser['ID']
			)
			{
				continue;
			}

			if (intval($departmentId) > 0)
			{
				$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);

				if (!in_array("DR".intval($departmentId), $arUserGroupCode))
				{
					continue;
				}
			}

			$arUser = (
				$arUser["EXTERNAL_AUTH_ID"] == "replica"
					? self::formatNetworkUser($arUser, $params)
					: self::formatUser($arUser, $params)
			);

			$arUsers[$arUser["id"]] = $arUser;
		}

		if (
			($bEmailUsers || $bCrmEmailUsers)
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
					new \Bitrix\Main\Entity\ExpressionField('EMAIL_OK', 'CASE WHEN UPPER(%s) = "'.$DB->ForSql(strtoupper(str_replace('%', '%%', $search))).'" THEN 1 ELSE 0 END', 'EMAIL')
				),
				'limit' => 10
			));

			while ($arUser = $rsUser->fetch())
			{
				$arUsers['U'.$arUser["ID"]] = self::formatUser($arUser, array(
					"NAME_TEMPLATE" => $nameTemplate,
					"USE_EMAIL" => true
				));
			}
		}

		return $arUsers;
	}

	public static function searchSonetGroups($params = array())
	{
		global $USER;

		$result = array();

		CUtil::JSPostUnescape();

		$search = is_array($params) && isset($params['SEARCH']) ? trim($params['SEARCH']) : '';
		if (empty($search))
		{
			return $result;
		}
		
		if (
			strlen($search) <= 0
			|| !getFilterQuery("TEST", $search)
		)
		{
			return $result;
		}

		$siteId = (
			isset($params['SITE_ID'])
			&& strlen($params['SITE_ID']) > 0
				? $params['SITE_ID']
				: SITE_ID
		);

		$arSocnetGroupsTmp = array();
		$tmpList = array();

		$filter = array(
			'%NAME' => $search,
			"CHECK_PERMISSIONS" => $USER->getId(),
			"SITE_ID" => $siteId,
			"ACTIVE" => "Y",
		);

		$res = \CSocnetGroup::getList(
			array("NAME" => "ASC"),
			$filter,
			false,
			array("nTopCount" => 50),
			array("ID", "NAME", "DESCRIPTION", "IMAGE_ID")
		);

		while ($group = $res->fetch())
		{
			$tmp = array(
				"id" => $group["ID"],
				"entityId" => $group["ID"],
				"name" => htmlspecialcharsbx($group["NAME"]),
				"desc" => htmlspecialcharsbx($group["DESCRIPTION"]),
			);

			if($group["IMAGE_ID"])
			{
				$imageFile = CFile::getFileArray($group["IMAGE_ID"]);
				if ($imageFile !== false)
				{
					$arFileTmp = CFile::resizeImageGet(
						$imageFile,
						array(
							"width" => (intval($params["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $params["THUMBNAIL_SIZE_WIDTH"] : 100),
							"height" => (intval($params["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $params["THUMBNAIL_SIZE_HEIGHT"] : 100)
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
			isset($params['FEATURES'])
			&& is_array($params['FEATURES'])
			&& !empty($params['FEATURES'])
		)
		{
			self::getSocnetGroupFilteredByFeaturePerms($tmpList, $params['FEATURES']);
		}

		if (
			isset($params['INITIATE'])
			&& $params['INITIATE'] == 'Y'
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
				|| $arParams['SEARCH_BY_EMAIL_ONLY'] != 'Y'
			)
			{
				$contacts = CCrmActivity::FindContactCommunications($search, 'EMAIL', 50);
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
		global $USER;
		static $staticCache = array();

		$userId = intval($USER->GetID());

		$arSocnetGroups = array();
		$arSelect = Array();
		if (isset($arParams['id']))
		{
			if (empty($arParams['id']))
			{
				return $arSocnetGroups;
			}
			else
			{
				foreach ($arParams['id'] as $value)
				{
					$arSelect[] = intval($value);
				}
			}
		}

		$siteId = (
			isset($arParams['site_id'])
			&& strlen($arParams['site_id']) > 0
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

			if (
				!isset($arParams["ALL"])
				|| $arParams["ALL"] != "Y"
			)
			{
				$filter = array(
					"USER_ID" => $userId,
					"GROUP_ID" => $arSelect,
					"<=ROLE" => \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER,
					"GROUP_SITE_ID" => $siteId,
					"GROUP_ACTIVE" => "Y"
				);

				if(isset($arParams['GROUP_CLOSED']))
				{
					$filter['GROUP_CLOSED'] = $arParams['GROUP_CLOSED'];
				}

				$res = CSocNetUserToGroup::getList(
					array("GROUP_NAME" => "ASC"),
					$filter,
					false,
					array("nTopCount" => $limit),
					array("ID", "GROUP_ID", "GROUP_NAME", "GROUP_DESCRIPTION", "GROUP_IMAGE_ID")
				);
				while($relation = $res->fetch())
				{
					$tmpList[] = array(
						"id" => $relation["GROUP_ID"],
						"entityId" => $relation["GROUP_ID"],
						"name" => htmlspecialcharsbx($relation["GROUP_NAME"]),
						"desc" => htmlspecialcharsbx($relation["GROUP_DESCRIPTION"]),
						"imageId" => $relation["GROUP_IMAGE_ID"]
					);
				}
			}
			else
			{
				$filter = array(
					"CHECK_PERMISSIONS" => $USER->GetID(),
					"SITE_ID" => $siteId,
					"ACTIVE" => "Y",
					"ID" => $arSelect,
				);
				if(isset($arParams['GROUP_CLOSED']))
				{
					$filter['CLOSED'] = $arParams['GROUP_CLOSED'];
				}

				$res = CSocnetGroup::GetList(
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
						"name" => htmlspecialcharsbx($group["NAME"]),
						"desc" => htmlspecialcharsbx($group["DESCRIPTION"]),
						"imageId" => $group["IMAGE_ID"],
						"project" => ($group["PROJECT"] == 'Y' ? 'Y' : 'N')
					);
				}
			}

			$limitReached = (count($tmpList) == $limit);

			foreach ($tmpList as $key => $group)
			{
				if($group["imageId"])
				{
					$imageFile = CFile::GetFileArray($group["imageId"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array(
								"width" => (intval($arParams["THUMBNAIL_SIZE_WIDTH"]) > 0 ? $arParams["THUMBNAIL_SIZE_WIDTH"] : 100),
								"height" => (intval($arParams["THUMBNAIL_SIZE_HEIGHT"]) > 0 ? $arParams["THUMBNAIL_SIZE_HEIGHT"] : 100)
							),
							BX_RESIZE_IMAGE_PROPORTIONAL,
							false
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

			if (isset($arParams['initiate']) && $arParams['initiate'] == 'Y')
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

		if (isset($arParams['useProjects']) && $arParams['useProjects'] == 'Y')
		{
			$groupsList = $projectsList = array();
			foreach($arSocnetGroups as $key => $value)
			{
				if (
					isset($value['project'])
					&& $value['project'] == 'Y'
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
		if (is_array($relation[$id]))
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

		if (sizeof($arGroupsIDs) <= 0)
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
					if (!$arGroupsPerms[$key])
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
		global $USER;

		$arGroupsIDs = array();
		foreach($arGroups as $value)
		{
			$arGroupsIDs[] = $value["id"];
		}

		if (sizeof($arGroupsIDs) <= 0)
		{
			return;
		}

		if (
			$USER->IsAdmin()
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

		$res = \Bitrix\Socialnetwork\UserToGroupTable::getList(array(
			'filter' => array(
				'USER_ID' => $USER->getId(),
				'@GROUP_ID' => $arGroupsIDs
			),
			'select' => array('GROUP_ID', 'ROLE')
		));

		while ($relation = $res->fetch())
		{
			$userRolesList[$relation['GROUP_ID']] = $relation['ROLE'];
		}

		$userId = $USER->getId();

		foreach ($arGroups as $key => $group)
		{
			$groupId = $group["id"];

			$canInitiate = (
				(
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == \Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER
					&& $userId == $groupsList[$groupId]["OWNER_ID"]
				)
				|| (
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == \Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR
					&& isset($userRolesList[$groupId])
					&& in_array($userRolesList[$groupId], array(
						\Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER,
						\Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR
					))
				)
				|| (
					isset($groupsList[$groupId])
					&& $groupsList[$groupId]["INITIATE_PERMS"] == \Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER
					&& isset($userRolesList[$groupId])
					&& in_array($userRolesList[$groupId], array(
						\Bitrix\Socialnetwork\UserToGroupTable::ROLE_OWNER,
						\Bitrix\Socialnetwork\UserToGroupTable::ROLE_MODERATOR,
						\Bitrix\Socialnetwork\UserToGroupTable::ROLE_USER
					))
				)
			);

			if (!$canInitiate)
			{
				unset($arGroups[$key]);
			}
		}
	}

	public static function GetDestinationUsers($arCodes, $fetchUsers = false)
	{
		$userIds = array();
		$users = array();
		$fields = $fetchUsers ? array('ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHOTO', 'WORK_POSITION') : array('ID');

		$usersToFetch = array();
		foreach($arCodes as $code)
		{
			// All users
			if ($code === 'UA')
			{
				$dbRes = CUser::GetList($by = 'ID', $order = 'ASC', array('INTRANET_USERS' => true), array('FIELDS' => $fields));
				while ($user = $dbRes->Fetch())
				{
					if (!in_array($user['ID'], $userIds))
					{
						$userIds[] = $user['ID'];
						if ($fetchUsers)
						{
							$user['USER_ID'] = $user['ID'];
							$users[] = $user;
						}
					}
				}
				$usersToFetch = array();
				break;
			}
			elseif (substr($code, 0, 1) === 'U')
			{
				$userId = intval(substr($code, 1));
				if (!in_array($userId, $userIds))
				{
					$usersToFetch[] = $userId;
					if (!$fetchUsers)
					{
						$userIds[] = $userId;
					}
				}
			}
			elseif (substr($code, 0, 2) === 'SG')
			{
				$groupId = intval(substr($code, 2));
				$dbMembers = CSocNetUserToGroup::GetList(
					array("RAND" => "ASC"),
					array("GROUP_ID" => $groupId, "<=ROLE" => SONET_ROLES_USER, "USER_ACTIVE" => "Y"),
					false,
					false,
					array("ID", "USER_ID", "ROLE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_EMAIL", "USER_PERSONAL_PHOTO", "USER_WORK_POSITION")
				);

				if ($dbMembers)
				{
					while ($user = $dbMembers->GetNext())
					{
						if (!in_array($user["USER_ID"], $userIds))
						{
							$userIds[] = $user["USER_ID"];
							$users[] = array(
								'ID' => $user["USER_ID"],
								'USER_ID' => $user["USER_ID"],
								'LOGIN' => $user["USER_LOGIN"],
								'NAME' => $user["USER_NAME"],
								'LAST_NAME' => $user["USER_LAST_NAME"],
								'SECOND_NAME' => $user["USER_SECOND_NAME"],
								'EMAIL' => $user["USER_EMAIL"],
								'PERSONAL_PHOTO' => $user["USER_PERSONAL_PHOTO"],
								'WORK_POSITION' => $user["USER_WORK_POSITION"]
							);
						}
					}
				}
			}
			elseif (substr($code, 0, 2) === 'DR')
			{
				$depId = intval(substr($code, 2));

				$res = \Bitrix\Intranet\Util::getDepartmentEmployees(array(
					'DEPARTMENTS' => $depId,
					'RECURSIVE' => 'Y',
					'ACTIVE' => 'Y',
					'SELECT' => $fields
				));

				while ($user = $res->Fetch())
				{
					if (!in_array($user['ID'], $userIds))
					{
						$userIds[] = $user['ID'];
						if ($fetchUsers)
						{
							$user['USER_ID'] = $user['ID'];
							$users[] = $user;
						}
					}
				}
			}
		}

		if (count($usersToFetch) > 0 && $fetchUsers)
		{
			$dbRes = CUser::GetList($by = 'ID', $order = 'ASC',
				array(
					'INTRANET_USERS' => true,
					'ID' => implode('|', $usersToFetch)
				), array('FIELDS' => $fields));

			while ($user = $dbRes->Fetch())
			{
				if (!in_array($user['ID'], $userIds))
				{
					$userIds[] = $user['ID'];
					$user['USER_ID'] = $user['ID'];
					$users[] = $user;
				}
			}
		}

		return $fetchUsers ? $users : $userIds;
	}

	public static function GetDestinationSort($arParams = array())
	{
		global $USER;

		$arResult = array();

		$userId = (
			isset($arParams["USER_ID"])
			&& intval($arParams["USER_ID"]) > 0
				? intval($arParams["USER_ID"])
				: false
		);

		$arContextFilter = (
			isset($arParams["CONTEXT_FILTER"])
			&& is_array($arParams["CONTEXT_FILTER"])
				? $arParams["CONTEXT_FILTER"]
				: false
		);

		$arCodeFilter = (
			isset($arParams["CODE_FILTER"])
				? $arParams["CODE_FILTER"]
				: false
		);

		if (
			$arCodeFilter
			&& !is_array($arCodeFilter)
		)
		{
			$arCodeFilter = array($arCodeFilter);
		}

		if (!$userId)
		{
			if ($USER->IsAuthorized())
			{
				$userId = $USER->GetId();
			}
			else
			{
				return $arResult;
			}
		}

		$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
		$cacheId = 'dest_sort'.$userId.serialize($arParams);
		$cacheDir = '/sonet/log_dest_sort/'.intval($userId / 100);

		$obCache = new CPHPCache;
		if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
		{
			$arDestAll = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			$arFilter = array(
				"USER_ID" => $USER->GetId()
			);

			if (
				IsModuleInstalled('mail')
				&& IsModuleInstalled('intranet')
				&& (
					!isset($arParams["ALLOW_EMAIL_INVITATION"])
					|| !$arParams["ALLOW_EMAIL_INVITATION"]
				)
			)
			{
				$arFilter["!=CODE_USER.EXTERNAL_AUTH_ID"] = 'email';
			}

			if (!empty($arParams["CODE_TYPE"]))
			{
				$arFilter["=CODE_TYPE"] = strtoupper($arParams["CODE_TYPE"]);
			}
			elseif (
				!empty($arParams["DEST_CONTEXT"])
				&& strtoupper($arParams["DEST_CONTEXT"]) != 'CRM_POST'
			)
			{
				$arFilter["!=CODE_TYPE"] = "CRM";
			}

			if (
				is_array($arContextFilter)
				&& !empty($arContextFilter)
			)
			{
				$arFilter["CONTEXT"] = $arContextFilter;
			}

			if (
				is_array($arCodeFilter)
				&& !empty($arCodeFilter)
			)
			{
				$arFilter["CODE"] = $arCodeFilter;
			}

			$arRuntime = array();
			$arOrder = array();

			if (!empty($arParams["DEST_CONTEXT"]))
			{
				$conn = \Bitrix\Main\Application::getConnection();
				$helper = $conn->getSqlHelper();

				$arRuntime = array(
					new \Bitrix\Main\Entity\ExpressionField('CONTEXT_SORT', "CASE WHEN CONTEXT = '".$helper->forSql($arParams["DEST_CONTEXT"])."' THEN 1 ELSE 0 END")
				);

				$arOrder = array(
					'CONTEXT_SORT' => 'DESC'
				);
			}

			$arOrder['LAST_USE_DATE'] = 'DESC';

			$rsDest = \Bitrix\Main\FinderDestTable::getList(array(
				'order' => $arOrder,
				'filter' => $arFilter,
				'select' => array(
					'CONTEXT',
					'CODE',
					'LAST_USE_DATE'
				),
				'runtime' => $arRuntime
			));

			$arDestAll = array();

			while($arDest = $rsDest->Fetch())
			{
				$arDest["LAST_USE_DATE"] = MakeTimeStamp($arDest["LAST_USE_DATE"]->toString());
				$arDestAll[] = $arDest;
			}
			$obCache->EndDataCache($arDestAll);
		}

		foreach ($arDestAll as $arDest)
		{
			if(!isset($arResult[$arDest["CODE"]]))
			{
				$arResult[$arDest["CODE"]] = array();
			}

			$contextType = (
				isset($arParams["DEST_CONTEXT"])
				&& $arParams["DEST_CONTEXT"] == $arDest["CONTEXT"]
					? "Y"
					: "N"
			);

			if (
				$contextType == "Y"
				|| !isset($arResult[$arDest["CODE"]]["N"])
				|| $arDest["LAST_USE_DATE"] > $arResult[$arDest["CODE"]]["N"]
			)
			{
				$arResult[$arDest["CODE"]][$contextType] = $arDest["LAST_USE_DATE"];
			}
		}

		return $arResult;
	}

	public static function CompareDestinations($a, $b)
	{
		if(!is_array($a) && !is_array($b))
		{
			return 0;
		}
		elseif(is_array($a) && !is_array($b))
		{
			return -1;
		}
		elseif(!is_array($a) && is_array($b))
		{
			return 1;
		}
		else
		{
			if(isset($a["SORT"]["Y"]) && !isset($b["SORT"]["Y"]))
			{
				return -1;
			}
			elseif(!isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				return 1;
			}
			elseif(isset($a["SORT"]["Y"]) && isset($b["SORT"]["Y"]))
			{
				if(intval($a["SORT"]["Y"]) > intval($b["SORT"]["Y"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["Y"]) < intval($b["SORT"]["Y"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
			else
			{
				if(intval($a["SORT"]["N"]) > intval($b["SORT"]["N"]))
				{
					return -1;
				}
				elseif(intval($a["SORT"]["N"]) < intval($b["SORT"]["N"]))
				{
					return 1;
				}
				else
				{
					return 0;
				}
			}
		}
	}

	public static function SortDestinations(&$arAllDest, $arSort)
	{
		foreach($arAllDest as $type => $arLastDest)
		{
			if (is_array($arLastDest))
			{
				foreach($arLastDest as $key => $value)
				{
					if (isset($arSort[$key]))
					{
						$arAllDest[$type][$key] = array(
							"VALUE" => $value,
							"SORT" => $arSort[$key]
						);
					}
				}

				uasort($arAllDest[$type], array(__CLASS__, 'CompareDestinations'));
			}
		}

		foreach($arAllDest as $type => $arLastDest)
		{
			if (is_array($arLastDest))
			{
				foreach($arLastDest as $key => $val)
				{
					if (is_array($val))
					{
						$arAllDest[$type][$key] = $val["VALUE"];
					}
				}
			}
		}
	}

	public static function fillLastDestination($arDestinationSort, &$arLastDestination, $arParams = array())
	{
		global $USER;

		$result = array();

		$iUCounter = $iSGCounter = $iDCounter = 0;
		$iCRMContactCounter = $iCRMCompanyCounter = $iCRMDealCounter = $iCRMLeadCounter = 0;
		$bCrm = (
			is_array($arParams)
			&& isset($arParams["CRM"])
			&& $arParams["CRM"] == "Y"
		);
		$bAllowEmail = (
			is_array($arParams)
			&& isset($arParams["EMAILS"])
			&& $arParams["EMAILS"] == "Y"
		);
		$bAllowCrmEmail = (
			is_array($arParams)
			&& isset($arParams["CRMEMAILS"])
			&& $arParams["CRMEMAILS"] == "Y"
			&& ModuleManager::isModuleInstalled('crm')
		);
		$bAllowProject = (
			is_array($arParams)
			&& isset($arParams["PROJECTS"])
			&& $arParams["PROJECTS"] == "Y"
		);
		if (is_array($arDestinationSort))
		{
			$userIdList = $sonetGroupIdList = array();
			$userLimit = 11;
			$sonetGroupLimit = 6;
			$departmentLimit = 6;
			$crmContactLimit = $crmCompanyLimit = $crmDealLimit = $crmLeadLimit = 6;

			foreach ($arDestinationSort as $code => $sortInfo)
			{
				if (
					!$bAllowEmail
					&& !$bAllowCrmEmail
					&& !$bAllowProject
					&& ($iUCounter >= $userLimit)
					&& $iSGCounter >= $sonetGroupLimit
					&& $iDCounter >= $departmentLimit
					&& $iCRMContactCounter >= $crmContactLimit
					&& $iCRMCompanyCounter >= $crmCompanyLimit
					&& $iCRMDealCounter >= $crmDealLimit
					&& $iCRMLeadCounter >= $crmLeadLimit
				)
				{
					break;
				}

				if (preg_match('/^U(\d+)$/i', $code, $matches))
				{
					if (
						!$bAllowEmail
						&& !$bAllowCrmEmail
						&& $iUCounter >= $userLimit
					)
					{
						continue;
					}
					if (!isset($arLastDestination['USERS']))
					{
						$arLastDestination['USERS'] = array();
					}
					$arLastDestination['USERS'][$code] = $code;
					$userIdList[] = intval($matches[1]);
					$iUCounter++;
				}
				elseif (preg_match('/^SG(\d+)$/i', $code, $matches))
				{
					if (
						!$bAllowProject
						&& $iSGCounter >= $sonetGroupLimit
					)
					{
						continue;
					}
					if (!isset($arLastDestination['SONETGROUPS']))
					{
						$arLastDestination['SONETGROUPS'] = array();
					}
					$arLastDestination['SONETGROUPS'][$code] = $code;
					$sonetGroupIdList[] = intval($matches[1]);
					$iSGCounter++;
				}
				elseif (
					preg_match('/^D(\d+)$/i', $code, $matches)
					|| preg_match('/^DR(\d+)$/i', $code, $matches)
				)
				{
					if ($iDCounter >= $departmentLimit)
					{
						continue;
					}
					if (!isset($arLastDestination['DEPARTMENT']))
					{
						$arLastDestination['DEPARTMENT'] = array();
					}
					$arLastDestination['DEPARTMENT'][$code] = $code;
					$iDCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCONTACT(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMContactCounter >= $crmContactLimit)
					{
						continue;
					}
					if (!isset($arLastDestination['CONTACTS']))
					{
						$arLastDestination['CONTACTS'] = array();
					}
					$arLastDestination['CONTACTS'][$code] = $code;
					$iCRMContactCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMCOMPANY(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMCompanyCounter >= $crmCompanyLimit)
					{
						continue;
					}
					if (!isset($arLastDestination['COMPANIES']))
					{
						$arLastDestination['COMPANIES'] = array();
					}
					$arLastDestination['COMPANIES'][$code] = $code;
					$iCRMCompanyCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMDEAL(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMDealCounter >= $crmDealLimit)
					{
						continue;
					}
					if (!isset($arLastDestination['DEALS']))
					{
						$arLastDestination['DEALS'] = array();
					}
					$arLastDestination['DEALS'][$code] = $code;
					$iCRMDealCounter++;
				}
				elseif (
					$bCrm
					&& preg_match('/^CRMLEAD(\d+)$/i', $code, $matches)
				)
				{
					if ($iCRMLeadCounter >= $crmLeadLimit)
					{
						continue;
					}
					if (!isset($arLastDestination['LEADS']))
					{
						$arLastDestination['LEADS'] = array();
					}
					$arLastDestination['LEADS'][$code] = $code;
					$iCRMLeadCounter++;
				}
			}

			if (
				(
					$bAllowEmail
					|| $bAllowCrmEmail
				)
				&& !empty($userIdList)
			)
			{
				$iUCounter = $iUECounter = $iUCRMCounter = 0;
				$emailLimit = $crmLimit = 10;
				$userId = $USER->getId();
				$destUList = $destUEList = $destUCRMList =array();

				$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
				$cacheId = 'dest_sort_users'.$userId.serialize($arParams).intval($bAllowCrmEmail);
				$cacheDir = '/sonet/log_dest_sort/'.intval($userId / 100);
				$obCache = new CPHPCache;

				if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
				{
					$cacheVars = $obCache->GetVars();
					$destUList = $cacheVars['U'];
					$destUEList = $cacheVars['UE'];
					$destUCRMList = $cacheVars['UCRM'];
				}
				else
				{
					$obCache->StartDataCache();

					$selectList = array('ID', 'EXTERNAL_AUTH_ID');
					if ($bAllowCrmEmail)
					{
						$selectList[] = 'UF_USER_CRM_ENTITY';
					}
					$selectList[] = new \Bitrix\Main\Entity\ExpressionField('MAX_LAST_USE_DATE', 'MAX(%s)', array('\Bitrix\Main\FinderDest:CODE_USER_CURRENT.LAST_USE_DATE'));

					$res = \Bitrix\Main\UserTable::getList(array(
						'order' => array(
							"MAX_LAST_USE_DATE" => 'DESC',
						),
						'filter' => array(
							'@ID' => $userIdList
						),
						'select' => $selectList
					));

					while($destUser = $res->fetch())
					{
						if (
							$iUCounter >= $userLimit
							&& $iUECounter >= $emailLimit
							&& $iUCRMCounter >= $crmLimit
						)
						{
							break;
						}

						$code = 'U'.$destUser['ID'];

						if ($bAllowEmail && $destUser['EXTERNAL_AUTH_ID'] == 'email')
						{
							if ($iUECounter >= $emailLimit)
							{
								continue;
							}
							$destUEList[$code] = $code;
							$iUECounter++;
						}
						elseif (
							$bAllowCrmEmail
							&& !empty($destUser['UF_USER_CRM_ENTITY'])
						)
						{
							if ($iUCRMCounter >= $crmLimit)
							{
								continue;
							}
							$destUCRMList[$code] = $code;
							$iUCRMCounter++;
						}
						else
						{
							if ($iUCounter >= $userLimit)
							{
								continue;
							}
							$destUList[$code] = $code;
							$iUCounter++;
						}
					}

					$obCache->EndDataCache(array(
						'U' => $destUList,
						'UE' => $destUEList,
						'UCRM' => $destUCRMList
					));
				}

				$arLastDestination['USERS'] = array_merge($destUList, $destUEList, $destUCRMList);
				$tmp = array('USERS' => $arLastDestination['USERS']);
				CSocNetLogDestination::sortDestinations($tmp, $arDestinationSort);
				$arLastDestination['USERS'] = $tmp['USERS'];
			}

			if (
				$bAllowProject
				&& !empty($sonetGroupIdList)
			)
			{
				$iSGCounter = $iSGPCounter = 0;
				$projectLimit = 10;
				$userId = $USER->getId();

				$destSGList = $destSGPList = array();

				$cacheTtl = defined("BX_COMP_MANAGED_CACHE") ? 3153600 : 3600*4;
				$cacheId = 'dest_sort_sonetgroups'.$userId.serialize($arParams);
				$cacheDir = '/sonet/log_dest_sort/'.intval($userId / 100);
				$obCache = new CPHPCache;

				if($obCache->InitCache($cacheTtl, $cacheId, $cacheDir))
				{
					$cacheVars = $obCache->GetVars();
					$destSGList = $cacheVars['SG'];
					$destSGPList = $cacheVars['SGP'];
				}
				else
				{
					$obCache->StartDataCache();

					$res = \Bitrix\Socialnetwork\WorkgroupTable::getList(array(
						'filter' => array(
							'@ID' => $sonetGroupIdList
						),
						'select' => array('ID', 'PROJECT')
					));

					while($destSonetGroup = $res->fetch())
					{
						if (
							$iSGCounter >= $sonetGroupLimit
							&& $iSGPCounter >= $projectLimit
						)
						{
							break;
						}

						$code = 'SG'.$destSonetGroup['ID'];

						if ($destSonetGroup['PROJECT'] == 'Y')
						{
							if ($iSGPCounter >= $projectLimit)
							{
								continue;
							}
							$destSGPList[$code] = $code;
							$iSGPCounter++;
						}
						else
						{
							if ($iSGCounter >= $sonetGroupLimit)
							{
								continue;
							}
							$destSGList[$code] = $code;
							$iSGCounter++;
						}
					}

					$obCache->EndDataCache(array(
						'SG' => $destSGList,
						'SGP' => $destSGPList
					));
				}

				$tmp = array(
					'SONETGROUPS' => $destSGList,
					'PROJECTS' => $destSGPList
				);

				CSocNetLogDestination::sortDestinations($tmp, $arDestinationSort);

				$arLastDestination['SONETGROUPS'] = $tmp['SONETGROUPS'];
				$arLastDestination['PROJECTS'] = $tmp['PROJECTS'];
			}
		}

		foreach($arLastDestination as $groupKey => $entitiesList)
		{
			$result[$groupKey] = array();

			if (is_array($entitiesList))
			{
				$tmp = array();
				$sort = 0;
				foreach($entitiesList as $key => $value)
				{
					$tmp[$key] = $sort++;
				}
				$result[$groupKey] = $tmp;
			}
		}

		return $result;
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
					&& $arDest["USERS"][$key]["isEmail"] == "Y"
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
					&& $arDest["USERS"][$key]["isCrmEmail"] == "Y"
				)
				{
					$arDest["CRMEMAILS"][$key] = $arDest["USERS"][$key];
					$arDest["LAST"]["CRMEMAILS"][$key] = $value;
				}
			}
		}
	}

	public static function getUsersAll($arParams)
	{
		global $DB, $USER;

		static $arFields = array(
			"ID" => Array("FIELD" => "U.ID", "TYPE" => "int"),
			"ACTIVE" => Array("FIELD" => "U.ACTIVE", "TYPE" => "string"),
			"NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string"),
			"LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string"),
			"SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string"),
			"LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string"),
			"PERSONAL_PHOTO" => Array("FIELD" => "U.PERSONAL_PHOTO", "TYPE" => "int"),
			"WORK_POSITION" => Array("FIELD" => "U.WORK_POSITION", "TYPE" => "string"),
			"CONFIRM_CODE" =>  Array("FIELD" => "U.CONFIRM_CODE", "TYPE" => "string"),
			"PERSONAL_PROFESSION" => Array("FIELD" => "U.PERSONAL_PROFESSION", "TYPE" => "string"),
			"EXTERNAL_AUTH_ID" => Array("FIELD" => "U.EXTERNAL_AUTH_ID", "TYPE" => "string")
		);

		$currentUserId = $USER->GetId();
		$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();

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
			$UFId = intval($arRes["ID"]);
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
			|| COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y"
		)
		{
			$arFilter["CONFIRM_CODE"] = false;
		}

		$arExternalAuthId = self::getExternalAuthIdBlackList();

		if (!empty($arExternalAuthId))
		{
			$arFilter['!EXTERNAL_AUTH_ID'] = $arExternalAuthId;
		}

		$arGroupBy = false;
		$arSelectFields = array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "EXTERNAL_AUTH_ID", "EMAIL");

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

					$arSqls["WHERE"] .= (strlen($arSqls["WHERE"]) > 0 ? " AND " : "")."
						(UM.VALUE_INT > 0)";

					if (!$bShowAllContactsAllowed)
					{
						// select all the users (intranet and extranet from my groups)
						$strJoin2 = "
							INNER JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID
							INNER JOIN b_sonet_user2group UG_MY ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".intval($currentUserId)."
						";
						$arSqls2 = $tmp;
					}
				}
				else
				{
					$strJoin = "
						INNER JOIN b_sonet_user2group UG ON UG.USER_ID = U.ID
						INNER JOIN b_sonet_user2group UG_MY ON UG_MY.GROUP_ID = UG.GROUP_ID AND UG_MY.USER_ID = ".intval($currentUserId)."
					";
				}
			}
			elseif (!$bShowAllContactsAllowed) // limited extranet, only for intranet users, don't show extranet
			{
				$strJoin = "INNER JOIN b_utm_user UM ON UM.VALUE_ID = U.ID and FIELD_ID = ".intval($UFId);
				$arSqls["WHERE"] .= (strlen($arSqls["WHERE"]) > 0 ? " AND " : "")."UM.VALUE_INT > 0";
			}
		}

		$strSql =
			"SELECT
				".$arSqls["SELECT"]."
			FROM b_user U
				".$arSqls["FROM"]." ";

		if ($strJoin)
		{
			$strSql .= $strJoin." ";
		}

		if (strlen($arSqls["WHERE"]) > 0)
		{
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		}

		if ($strJoin2)
		{
			$strSql .=
				"UNION SELECT
					".$arSqls2["SELECT"]."
				FROM b_user U
					".$arSqls2["FROM"]." ";

				$strSql .= $strJoin2." ";

			if (strlen($arSqls2["WHERE"]) > 0)
			{
				$strSql .= "WHERE ".$arSqls2["WHERE"]." ";
			}

			$strSql .= "ORDER BY ID ASC"; // cannot use alias
		}
		else // only without union
		{
			if (strlen($arSqls["ORDERBY"]) > 0)
			{
				$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";
			}
		}

		//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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
			if ($resultCount > $maxCount)
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
				'name' => $sName,
				'avatar' => empty($arFileTmp['src'])? '': $arFileTmp['src'],
				'desc' => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : ($arUser['PERSONAL_PROFESSION'] ? $arUser['PERSONAL_PROFESSION'] : '&nbsp;'),
				'isExtranet' => (in_array($arUser["ID"], $extranetUserIdList) ? "Y" : "N"),
				'isEmail' => ($arUser['EXTERNAL_AUTH_ID'] == 'email' ? 'Y' : 'N'),
				'active' => 'Y'
			);

			if ($arUser['EXTERNAL_AUTH_ID'] == 'email')
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
			'entityId' => $arUser["ID"],
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
			'desc' => (
				$arUser['WORK_POSITION']
					? $arUser['WORK_POSITION']
					: (
						$arUser['PERSONAL_PROFESSION']
							? $arUser['PERSONAL_PROFESSION']
							: '&nbsp;'
				)
			),
			'isExtranet' => (
				in_array($arUser["ID"], $extranetUserIdList)
					? "Y"
					: "N"
			),
			'isEmail' => (
				isset($arUser['EXTERNAL_AUTH_ID'])
				&& $arUser['EXTERNAL_AUTH_ID'] == 'email'
					? 'Y'
					: 'N'
			)
		);

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
			|| $arRes['isEmail'] == 'Y'
		)
		{
			$arRes["email"] = $arUser['EMAIL'];
			if (
				strlen($arUser["NAME"]) > 0
				|| strlen($arUser["NAME"]) > 0
			)
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
			$isIntranetInstalled == 'Y'
			&& isset($arParams['USE_LOGIN'])
			&& $arParams['USE_LOGIN']
				? $arUser["LOGIN"]
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
			if ($params["TYPE"] == 'CONTACT')
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
			elseif ($params["TYPE"] == 'COMPANY')
			{
				$prefix = 'CO_';
				$imageField = 'LOGO';
				$name = $fields['TITLE'];
				$userParams = array(
					'name' => '',
					'lastName' => $fields['TITLE']
				);
			}
			elseif ($params["TYPE"] == 'LEAD')
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

		$result = array();
		$userParams = array();

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

		if (isset($fields["EXTERNAL_AUTH_ID"]) && $fields["EXTERNAL_AUTH_ID"] == "replica")
			list(,$domain) = explode("@", $fields["LOGIN"], 2);
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
		$result = array(
			"bot",
			"imconnector"
		);

		if (
			!is_array($params)
			|| !isset($params["NETWORK_SEARCH"])
			|| !$params["NETWORK_SEARCH"]
		)
		{
			$result[] = 'replica';
		}

		return $result;
	}


}
?>