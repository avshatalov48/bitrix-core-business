<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (
	isset($arParams["SET_EXPERT_MODE"])
	&& $arParams["SET_EXPERT_MODE"] == "Y"
)
{
	CUserOptions::setOption("socialnetwork", "~log_expertmode_popup_show", "Y");
	$arResult["SHOW_EXPERT_MODE_POPUP"] = "Y";
}
elseif ($USER->IsAuthorized())
{
	$arResult["SHOW_EXPERT_MODE_POPUP"] = CUserOptions::getOption("socialnetwork", "~log_expertmode_popup_show", "N");
}

$arResult["SHOW_VIDEO_TRANSFORM_POPUP"] = CUserOptions::getOption("socialnetwork", "~log_videotransform_popup_show", "N");
$arResult["ajaxControllerURL"] = $this->GetFolder()."/ajax.php";

if ($arResult["MODE"] == "AJAX")
{
	CJSCore::Init(array('socnetlogdest'));

	$arResult["CREATED_BY_DEST"] = array(
		"SORT" => CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "FEED_FILTER_CREATED_BY",
			"CODE_TYPE" => 'U',
			"ALLOW_EMAIL_INVITATION" => false
		)),
		"LAST" => array(),
		"ITEMS" => array(
			"USERS" => array()
		)
	);

	$arResult["TO_DEST"] = array(
		"SORT" => CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "FEED_FILTER_TO",
			"ALLOW_EMAIL_INVITATION" => (IsModuleInstalled('mail') && IsModuleInstalled('intranet'))
		)),
		"LAST" => array(),
		"ITEMS" => array(
			"USERS" => array()
		)
	);

	CSocNetLogDestination::fillLastDestination($arResult["CREATED_BY_DEST"]["SORT"], $arResult["CREATED_BY_DEST"]["LAST"]);
	CSocNetLogDestination::fillLastDestination($arResult["TO_DEST"]["SORT"], $arResult["TO_DEST"]["LAST"]);
	if ($arParams["USE_SONET_GROUPS"] == "N")
	{
		$arResult["TO_DEST"]["LAST"]["SONETGROUPS"] = array();
	}

	// get user items
	$arUserIDToGet = array();
	$arUserIDCreatedBy = array();
	$arUserIDTo = array();

	if (
		is_array($arResult["CREATED_BY_DEST"]["LAST"]["USERS"])
		&& !empty($arResult["CREATED_BY_DEST"]["LAST"]["USERS"])
	)
	{
		$arUserIDToGet = array();

		foreach($arResult["CREATED_BY_DEST"]["LAST"]["USERS"] as $user_code)
		{
			if(preg_match('/^U(\d+)$/', $user_code, $match))
			{
				$arUserIDToGet[] = $match[1];
				$arUserIDCreatedBy[] = $match[1];
			}
		}
	}

	if (
		is_array($arResult["TO_DEST"]["LAST"]["USERS"])
		&& !empty($arResult["TO_DEST"]["LAST"]["USERS"])
	)
	{
		$arUserIDToGet = array();

		foreach($arResult["TO_DEST"]["LAST"]["USERS"] as $user_code)
		{
			if(preg_match('/^U(\d+)$/', $user_code, $match))
			{
				$arUserIDToGet[] = intval($match[1]);
				$arUserIDTo[] = intval($match[1]);
			}
		}
	}

	$arResult["CREATED_BY_DEST"]['SELECTED'] = Array();
	if (intval($arParams["CREATED_BY_ID"]) > 0)
	{
		$arResult["CREATED_BY_DEST"]['SELECTED']['U'.intval($arParams["CREATED_BY_ID"])] = 'users';
		$arUserIDToGet[] = intval($arParams["CREATED_BY_ID"]);
		$arUserIDCreatedBy[] = intval($arParams["CREATED_BY_ID"]);
	}

	if (!empty($arUserIDToGet))
	{
		$extranetUserIdList = \Bitrix\Socialnetwork\ComponentHelper::getExtranetUserIdList();

		$siteDepartmentID = COption::GetOptionString("main", "wizard_departament", false, SITE_ID, true);
		$nameTemplate = (
			empty($arParams["NAME_TEMPLATE"])
				? CSite::GetNameFormat(false)
				: $arParams["NAME_TEMPLATE"]
		);

		$arSelect = array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION", "EXTERNAL_AUTH_ID");
		if (IsModuleInstalled('intranet'))
		{
			$arSelect[] = "UF_DEPARTMENT";
		}
		$dbUsers = \Bitrix\Main\UserTable::getList(array(
			'order' => array('LAST_NAME' => 'ASC'),
			'filter' => array("ID" => $arUserIDToGet),
			'select' => $arSelect
		));

		while($arUser = $dbUsers->fetch())
		{
			if (intval($siteDepartmentID) > 0)
			{
				$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);
				if (!in_array("DR".intval($siteDepartmentID), $arUserGroupCode))
				{
					continue;
				}
			}

			$arFileTmp = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"],
				array('width' => 32, 'height' => 32),
				BX_RESIZE_IMAGE_EXACT,
				false
			);

			$arUserTmp = array(
				"id" => "U".$arUser["ID"],
				"entityId" => $arUser["ID"],
				"name" => trim(CUser::FormatName($nameTemplate, $arUser)),
				"avatar" => (empty($arFileTmp['src'])? '': $arFileTmp['src']),
				"desc" => $arUser["WORK_POSITION"] ? $arUser["WORK_POSITION"] : ($arUser["PERSONAL_PROFESSION"] ? $arUser["PERSONAL_PROFESSION"] : "&nbsp;"),
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

			if (
				in_array($arUser["ID"], $arUserIDCreatedBy)
				&& !array_key_exists("U".$arUser["ID"], $arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"])
			)
			{
				$arResult["CREATED_BY_DEST"]["ITEMS"]["USERS"]["U".$arUser["ID"]] = $arUserTmp;
			}

			if (
				in_array($arUser["ID"], $arUserIDTo)
				&& !array_key_exists("U".$arUser["ID"], $arResult["TO_DEST"]["ITEMS"]["USERS"])
			)
			{
				$arResult["TO_DEST"]["ITEMS"]["USERS"]["U".$arUser["ID"]] = $arUserTmp;
			}
		}
	}

	$arResult["TO_DEST"]['SELECTED'] = Array();

	if (!empty($arParams["DESTINATION"]))
	{
		foreach ($arParams["DESTINATION"] as $code)
		{
			if (preg_match('/^U(\d+)$/i', $code, $matches))
			{
				$arResult["TO_DEST"]['SELECTED']['U'.intval($matches[1])] = 'users';
			}
			elseif (preg_match('/^SG(\d+)$/i', $code, $matches))
			{
				$arResult["TO_DEST"]['SELECTED']['SG'.intval($matches[1])] = 'sonetgroups';
			}
			elseif (preg_match('/^DR(\d+)$/i', $code, $matches))
			{
				$arResult["TO_DEST"]['SELECTED']['DR'.intval($matches[1])] = 'department';
			}
		}
	}
	elseif (
		!empty($arResult["Group"])
		&& intval($arResult["Group"]["ID"]) > 0
	)
	{
		$arResult["TO_DEST"]['SELECTED']['SG'.intval($arResult["Group"]["ID"])] = 'sonetgroups';
	}
	elseif (
		!empty($arResult["ToUser"])
		&& intval($arResult["ToUser"]["ID"]) > 0
	)
	{
		$arResult["TO_DEST"]['SELECTED']['U'.intval($arResult["ToUser"]["ID"])] = 'users';
	}

	if ($arParams["USE_SONET_GROUPS"] != "N")
	{
		$arResult["TO_DEST"]["ITEMS"]["SONETGROUPS"] = \Bitrix\Socialnetwork\ComponentHelper::getSonetGroupAvailable();
	}

	if (IsModuleInstalled('intranet'))
	{
		$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
		$arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT'] = $arResult["TO_DEST"]["ITEMS"]['DEPARTMENT'] = $arStructure['department'];
		$arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT_RELATION'] = $arResult["TO_DEST"]["ITEMS"]['DEPARTMENT_RELATION'] = $arStructure['department_relation'];
		$arResult["CREATED_BY_DEST"]["ITEMS"]['DEPARTMENT_RELATION_HEAD'] = $arResult["TO_DEST"]["ITEMS"]['DEPARTMENT_RELATION_HEAD'] = $arStructure['department_relation_head'];

		if (
			IsModuleInstalled("extranet")
			&& COption::GetOptionString("extranet", "extranet_site") <> ''
		)
		{
			$arResult["CREATED_BY_DEST"]["EXTRANET_ROOT"] = $arResult["TO_DEST"]["EXTRANET_ROOT"] = array(
				"EX" => array (
					'id' => 'EX',
					'entityId' => 'EX',
					'name' => GetMessage("SONET_C30_EXTRANET_ROOT"),
					'parent' => 'DR0',
				)
			);
		}
	}
}

if (SITE_TEMPLATE_ID === 'bitrix24')
{
	$arResult["EnableFulltextSearch"] = \Bitrix\Socialnetwork\LogIndexTable::getEntity()->fullTextIndexEnabled("CONTENT");

	$config = \Bitrix\Main\Application::getConnection()->getConfiguration();
	$arResult["ftMinTokenSize"] = (isset($config["ft_min_token_size"]) ? $config["ft_min_token_size"] : CSQLWhere::FT_MIN_TOKEN_SIZE);
}

$arResult["VIDEO_TRANSFORM_POST_URL"] = CUserOptions::getOption("socialnetwork", "~log_videotransform_post_url", "#");
$arResult["VIDEO_TRANSFORM_POST_ID"] = CUserOptions::getOption("socialnetwork", "~log_videotransform_post_id", 0);
?>