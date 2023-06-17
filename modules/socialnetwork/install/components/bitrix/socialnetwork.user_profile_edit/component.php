<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @global CCacheManager $CACHE_MANAGER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */

global $USER_FIELD_MANAGER, $CACHE_MANAGER;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = intval($arParams["ID"]);
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
if($arParams["PATH_TO_USER_EDIT"] == '')
	$arParams["PATH_TO_USER_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#&mode=edit");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$arParams['IS_FORUM'] = CModule::IncludeModule('forum') ? 'Y' : 'N';
$arParams['IS_BLOG'] = (CModule::IncludeModule('blog') && !IsModuleInstalled("intranet")) ? 'Y' : 'N';

TrimArr($arParams['USER_FIELDS_PERSONAL']);
TrimArr($arParams['USER_FIELDS_CONTACT']);
TrimArr($arParams['USER_FIELDS_MAIN']);
TrimArr($arParams['USER_PROPERTY_PERSONAL']);
TrimArr($arParams['USER_PROPERTY_CONTACT']);
TrimArr($arParams['USER_PROPERTY_MAIN']);
TrimArr($arParams['EDITABLE_FIELDS']);

if (!is_array($arParams['EDITABLE_FIELDS']) || count($arParams['EDITABLE_FIELDS']) <= 0)
{
	$arParams['EDITABLE_FIELDS'] = array('LOGIN', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'EMAIL', 'TIME_ZONE', 'PERSONAL_BIRTHDAY', 'PERSONAL_CITY', 'PERSONAL_COUNTRY', 'PERSONAL_FAX', 'PERSONAL_GENDER', 'PERSONAL_ICQ', 'PERSONAL_MAILBOX', 'PERSONAL_MOBILE', 'PERSONAL_PAGER', 'PERSONAL_PHONE', 'PERSONAL_PHOTO', 'PERSONAL_STATE', 'PERSONAL_STREET', 'PERSONAL_WWW', 'PERSONAL_ZIP');

	if ($arParams['IS_FORUM'] == 'Y')
		$arParams['EDITABLE_FIELDS'] = array_merge($arParams['EDITABLE_FIELDS'], array('FORUM_SHOW_NAME', 'FORUM_DESCRIPTION', 'FORUM_INTERESTS', 'FORUM_SIGNATURE', 'FORUM_AVATAR', 'FORUM_HIDE_FROM_ONLINE', 'FORUM_SUBSC_GROUP_MESSAGE', 'FORUM_SUBSC_GET_MY_MESSAGE'));

	if ($arParams['IS_BLOG'] == 'Y')
		$arParams['EDITABLE_FIELDS'] = array_merge($arParams['EDITABLE_FIELDS'], array('BLOG_ALIAS', 'BLOG_DESCRIPTION', 'BLOG_INTERESTS', 'BLOG_AVATAR', 'BLOG_SIGNATURE'));
}
$arResult["arSocServ"] = array();
if (CModule::IncludeModule("socialservices"))
{
	$oAuthManager = new CSocServAuthManager();
	$arResult["arSocServ"] = $oAuthManager->GetActiveAuthServices(array());
	if (!empty($arResult["arSocServ"]))
	{
		$arParams['EDITABLE_FIELDS'][] = 'SOCSERVICES';
	}
}

if(in_array('TIME_ZONE', $arParams['EDITABLE_FIELDS']))
{
	$arParams['EDITABLE_FIELDS'][] = 'AUTO_TIME_ZONE';
}

$arResult['CONTEXT'] = \Bitrix\Socialnetwork\ComponentHelper::getUrlContext();
$arParams['PATH_TO_USER'] = \Bitrix\Socialnetwork\ComponentHelper::addContextToUrl($arParams['PATH_TO_USER'], $arResult["CONTEXT"]);

$arResult["urlToCancel"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"]));

$CurrentUserPerms = CSocNetUserPerms::InitUserPerms(
	$USER->GetID(),
	$arParams["ID"],
	CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, (CModule::IncludeModule("bitrix24") && CBitrix24::IsPortalAdmin($USER->GetID()) ? false : true))
);

$dbUser = CUser::GetByID($arParams["ID"]);
$arResult["User"] = $dbUser->GetNext();

if (in_array($arResult["User"]["EXTERNAL_AUTH_ID"], \Bitrix\Socialnetwork\ComponentHelper::checkPredefinedAuthIdList(array('bot', 'imconnector', 'replica'))))
{
	$CurrentUserPerms["Operations"]["modifyuser_main"] = false;
	$CurrentUserPerms["Operations"]["modifyuser"] = false;
}

if (
	!$CurrentUserPerms["Operations"]["modifyuser"]
	|| !$CurrentUserPerms["Operations"]["modifyuser_main"]
)
{
	$arResult["FATAL_ERROR"] = GetMessage("SONET_P_PU_NO_RIGHTS");
}

$arResult["bEdit"] = (
	!isset($arResult["FATAL_ERROR"])
	&& (
		$USER->CanDoOperation('edit_own_profile')
		|| $USER->IsAdmin()
	)
		? "Y"
		: "N"
);

//check integrator for cloud
if (
	CModule::IncludeModule("bitrix24")
	&& $arResult["User"]["ID"] != $USER->GetID()
	&& \CBitrix24::isIntegrator($USER->GetID())
	&& \CBitrix24::IsPortalAdmin($arResult["User"]["ID"])
)
{
	$arResult["bEdit"] = "N";
}

if ($arResult['bEdit'] != 'Y')
{
	$APPLICATION->AuthForm(GetMessage('SONET_P_PU_NO_RIGHTS'));
}

$arResult["User"]["IS_EMAIL"] = (
	$arResult["User"]['EXTERNAL_AUTH_ID'] == 'email'
	&& IsModuleInstalled('mail')
);

if (in_array($arResult['User']['EXTERNAL_AUTH_ID'], \Bitrix\Socialnetwork\ComponentHelper::checkPredefinedAuthIdList('email')))
{
	$arParams['EDITABLE_FIELDS'] = array_intersect($arParams['EDITABLE_FIELDS'], array("NAME", "LAST_NAME", "SECOND_NAME", "PERSONAL_PHOTO"));
}

if ($arResult['User']['EXTERNAL_AUTH_ID'])
{
	foreach ($arParams['EDITABLE_FIELDS'] as $key => $value)
	{
		if ($value == 'LOGIN' || $value == 'PASSWORD')
		{
			unset($arParams['EDITABLE_FIELDS'][$key]);
		}
	}
}
elseif (in_array('PASSWORD', $arParams['EDITABLE_FIELDS']))
{
	$arParams['EDITABLE_FIELDS'][] = 'CONFIRM_PASSWORD';
}

if(!is_array($arResult["User"]))
{
	$arResult["FATAL_ERROR"] = GetMessage("SONET_P_USER_NO_USER");
}
else
{
	$arResult["GROUPS_CAN_EDIT"] = array();

	if ($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users'))
	{
		if($USER->CanDoOperation('edit_all_users'))
		{
			$dbGroup = CGroup::GetList(
				"c_sort",
				"asc",
				array("ACTIVE" => "Y")
			);
			while($arGroup = $dbGroup->Fetch())
			{
				$arResult["GROUPS_CAN_EDIT"][$arGroup["ID"]] = $arGroup;
				$arGroupsCanEditID[] = $arGroup["ID"];
			}
		}
		elseif($USER->CanDoOperation('edit_subordinate_users'))
		{
			$arGroupsCanEditID = CSocNetTools::GetSubordinateGroups();
			if (
				is_array($arGroupsCanEditID)
				&& count($arGroupsCanEditID) > 0
			)
			{
				$dbGroup = CGroup::GetList(
					"c_sort",
					"asc",
					array(
						"ID" => implode(" | ", $arGroupsCanEditID),
						"ACTIVE" => "Y"
					)
				);
				while($arGroup = $dbGroup->Fetch())
				{
					$arResult["GROUPS_CAN_EDIT"][$arGroup["ID"]] = $arGroup;
				}
			}
		}
	}
	else
	{
		foreach ($arParams['EDITABLE_FIELDS'] as $key => $value)
			if ($value == 'GROUP_ID' || $value == 'ACTIVE')
				unset($arParams['EDITABLE_FIELDS'][$key]);
	}

	if ($arParams['IS_FORUM'] == 'Y')
	{
		$arForumUser = CForumUser::GetByUSER_ID($arParams["ID"]);
		if (is_array($arForumUser) && count($arForumUser) > 0)
		{
			foreach ($arForumUser as $key => $value)
			{
				if (true || in_array('FORUM_'.$key, $arParams['EDITABLE_FIELDS']))
				{
					$arResult['User']['FORUM_'.$key] = htmlspecialcharsbx($value);
					$arResult['User']['~FORUM_'.$key] = $value;
				}
			}
		}
	}

	if ($arParams['IS_BLOG'] == 'Y')
	{
		$dbRes = CBlogUser::GetList(array(), array("USER_ID" => $arParams['ID']));
		if ($arBlogUser = $dbRes->Fetch())
		{
			foreach ($arBlogUser as $key => $value)
			{
				$arResult['User']['BLOG_'.$key] = htmlspecialcharsbx($value);
				$arResult['User']['~BLOG_'.$key] = $value;
			}
		}
	}

	$SONET_USER_ID = $arParams['ID'];//intval($_POST["SONET_USER_ID"]);

	if($arResult['bEdit'] == 'Y' && $_SERVER["REQUEST_METHOD"]=="POST" && $_POST["submit"] <> '' && check_bitrix_sessid())
	{
		if(CModule::IncludeModule("socialservices"))
		{
			$arPerm = array();
			if(is_array($_POST["SPERM"]) && isset($_POST["USER_ID_TWITTER"]) && !empty($_POST["USER_ID_TWITTER"]))
				$arPerm = $_POST["SPERM"];

			$arFields = array("PERMISSIONS" => serialize($arPerm));
			$arFields['SEND_ACTIVITY'] = 'N';
			$arFields['USER_ID'] = $SONET_USER_ID;
			if(isset($_POST["ss-send-my-actives"]) && $_POST["ss-send-my-actives"] == 'Y')
				$arFields['SEND_ACTIVITY'] = 'Y';
			if(is_array($_POST["USER_ID_TWITTER"]))
				foreach($_POST["USER_ID_TWITTER"] as $value)
					CSocServAuth::Update($value, $arFields);
			if(is_array($_POST["USER_ID_OTHER"]))
				foreach($_POST["USER_ID_OTHER"] as $value)
					CSocServAuth::Update($value, array("SEND_ACTIVITY" => $arFields['SEND_ACTIVITY'], "USER_ID" => $arFields['USER_ID']));
		}

		$arPICTURE = array();
		$picturesToDelete = array();
		$arPICTURE_WORK = array();

		//PERSONAL_PHOTO upload
		//bitrix24 template
		if (
			$_POST['PERSONAL_PHOTO_ID']
			&& intval($_POST['PERSONAL_PHOTO_ID']) > 0
			&& intval($_POST['PERSONAL_PHOTO_ID']) != intval($arResult["User"]["PERSONAL_PHOTO"])
			&& in_array($_POST['PERSONAL_PHOTO_ID'], \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
				'PERSONAL_PHOTO_IMAGE_ID',
				array($_POST['PERSONAL_PHOTO_ID'])
			))
		)
		{
			$arPICTURE = CFile::MakeFileArray($_POST['PERSONAL_PHOTO_ID']);
			$arPICTURE["old_file"] = $arResult["User"]["PERSONAL_PHOTO"];
			$picturesToDelete[] = $_POST['PERSONAL_PHOTO_ID'];
		}
		elseif ( //usual template
			$_FILES["PERSONAL_PHOTO"]["name"] <> ''
		)
		{
			$arPICTURE = $_FILES["PERSONAL_PHOTO"];
			$arPICTURE["old_file"] = $arResult["User"]["PERSONAL_PHOTO"];
		}
		else if (($_POST["PERSONAL_PHOTO_del"] ?: $_POST["PERSONAL_PHOTO_ID_del"]) <> '')
		{
			$arPICTURE["old_file"] = $arResult["User"]["PERSONAL_PHOTO"];
			$arPICTURE["del"] = ($_POST["PERSONAL_PHOTO_del"] ?: $_POST["PERSONAL_PHOTO_ID_del"]);
		}

		//WORK_LOGO upload
		//bitrix24 template
		if (
			$_POST['WORK_LOGO_ID']
			&& intval($_POST['WORK_LOGO_ID']) > 0
			&& intval($_POST['WORK_LOGO_ID']) != intval($arResult["User"]["WORK_LOGO"])
			&& in_array($_POST['WORK_LOGO_ID'], \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles(
				'WORK_LOGO_IMAGE_ID',
				array($_POST['WORK_LOGO_ID'])
			))
		)
		{
			$arPICTURE_WORK = CFile::MakeFileArray($_POST['WORK_LOGO_ID']);
			$picturesToDelete[] = $_POST['WORK_LOGO_ID'];
		}
		elseif ( // usual template
			$_FILES["WORK_LOGO"]["name"] <> ''
			|| isset($_POST["WORK_LOGO_del"])
		)
		{
			$arPICTURE_WORK = $_FILES["WORK_LOGO"];
			$arPICTURE_WORK["old_file"] = $arResult["User"]["WORK_LOGO"];
			$arPICTURE_WORK["del"] = $_POST["WORK_LOGO_del"];
		}

		if (sizeof($arPICTURE_WORK) != 0)
		{
			$arPICTURE_WORK["old_file"] = $arResult["User"]["WORK_LOGO"];
			$arPICTURE_WORK["del"] = ($_POST["WORK_LOGO_del"] ?: $_POST["WORK_LOGO_ID_del"]);
		}

		$arFields = Array(
			'ACTIVE', 'GROUP_ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHOTO', 'PERSONAL_GENDER', 'PERSONAL_BIRTHDAY', 'PERSONAL_BIRTHDATE', 'PERSONAL_PROFESSION', 'PERSONAL_NOTES',
			'EMAIL', 'PERSONAL_PHONE', 'PERSONAL_MOBILE', 'PERSONAL_WWW', 'PERSONAL_ICQ', 'PERSONAL_FAX', 'PERSONAL_PAGER', 'PERSONAL_COUNTRY', 'PERSONAL_STREET', 'PERSONAL_MAILBOX', 'PERSONAL_CITY', 'PERSONAL_STATE', 'PERSONAL_ZIP',
			'WORK_COUNTRY', 'WORK_CITY', 'WORK_STATE', 'WORK_COMPANY', 'WORK_DEPARTMENT', 'WORK_PROFILE', 'WORK_WWW', 'WORK_PHONE', 'WORK_FAX', 'WORK_PAGER', 'WORK_LOGO', 'WORK_POSITION',
			'LOGIN', 'PASSWORD', 'CONFIRM_PASSWORD',
		);

		$arFieldsValue = array();
		foreach ($arFields as $key)
		{
			if ('PERSONAL_PHOTO' == $key)
			{
				if (sizeof($arPICTURE) != 0)
					$arFieldsValue[$key] = $arPICTURE;
			}
			elseif ('WORK_LOGO' == $key)
			{
				if (sizeof($arPICTURE_WORK) != 0)
					$arFieldsValue[$key] = $arPICTURE_WORK;
			}
			elseif ('GROUP_ID' == $key && !IsModuleInstalled("bitrix24"))
			{
				if (is_array($arGroupsCanEditID) && is_array($_POST[$key]))
				{
					$arFieldsValue[$key] = array_intersect($_POST[$key], $arGroupsCanEditID);
				}
			}
			elseif ($_POST[$key] !== $arResult['User'][$key])
				$arFieldsValue[$key] = $_POST[$key];
		}

		$removeAdminRights = false;

		//groups for bitrix24 cloud
		if (
			\Bitrix\Main\Loader::includeModule("bitrix24")
			&& \CBitrix24::IsPortalAdmin($USER->GetID())
			&& $USER->GetID() != $arResult["User"]['ID']
			&& !( // not extranet
				!is_array($arResult["User"]['UF_DEPARTMENT'])
				|| empty($arResult["User"]['UF_DEPARTMENT'][0])
			)
			&& $arResult["User"]['ACTIVE'] == "Y"
		)
		{
			//moving admin rights to another user
			if (
				!\CBitrix24::IsPortalAdmin($arResult["User"]['ID'])
				&& $_POST["IS_ADMIN"] == "Y"
				&& !CBitrix24::isMoreAdminAvailable()
			)
			{
				$removeAdminRights = true;
			}

			$curUserGroups = CUser::GetUserGroup($arResult["User"]['ID']);
			foreach ($curUserGroups as $groupKey => $group)
			{
				if ($group == 1 || $group == 12 || $group == 11)
				{
					unset($curUserGroups[$groupKey]);
				}
			}
			if (isset($_POST["IS_ADMIN"]) && $_POST["IS_ADMIN"] == "Y")
			{
				$curUserGroups[] = "1";
				$curUserGroups[] = "12";
			}
			else
			{
				$curUserGroups[] = "11";
			}

			$arFieldsValue["GROUP_ID"] = $curUserGroups;
		}

		//time zones
		$arFieldsValue['AUTO_TIME_ZONE'] = ($_POST['AUTO_TIME_ZONE'] == "Y" || $_POST['AUTO_TIME_ZONE'] == "N"? $_POST['AUTO_TIME_ZONE'] : "");
		if(isset($_POST['TIME_ZONE']))
			$arFieldsValue['TIME_ZONE'] = $_POST['TIME_ZONE'];

		if ($arFieldsValue['PASSWORD'] == '')
		{
			unset($arFieldsValue['PASSWORD']);
			unset($arFieldsValue['CONFIRM_PASSWORD']);
		}

		$USER_FIELD_MANAGER->EditFormAddFields("USER", $arFieldsValue);

		if (in_array('PASSWORD', $arParams['EDITABLE_FIELDS']))
			$arParams['EDITABLE_FIELDS'][] = 'CONFIRM_PASSWORD';
		$arKeys = array_intersect(array_keys($arFieldsValue), $arParams['EDITABLE_FIELDS']);

		$arNewFieldsValue = array();
		foreach ($arKeys as $key)
			$arNewFieldsValue[$key] = $arFieldsValue[$key];

		$res = $USER->Update($SONET_USER_ID, $arNewFieldsValue);

		while ($f = array_pop($picturesToDelete))
			CFile::Delete($f);

		if (!$res)
			$strErrorMessage = $USER->LAST_ERROR;
		else
		{
			if ($removeAdminRights)
			{
				$curAdminGroups = CUser::GetUserGroup($USER->GetID());
				foreach ($curAdminGroups as $groupKey => $group)
				{
					if ($group == 1 || $group == 12)
					{
						unset($curAdminGroups[$groupKey]);
					}
				}
				$curAdminGroups[] = "11";
				CUser::SetUserGroup($USER->GetID(), $curAdminGroups);
			}

			if ($arParams['IS_FORUM'] == 'Y')
			{
				$arForumFields = array(
					"SHOW_NAME" => ($_POST["FORUM_SHOW_NAME"]=="Y") ? "Y" : "N",
					"HIDE_FROM_ONLINE" => ($_POST["FORUM_HIDE_FROM_ONLINE"]=="Y") ? "Y" : "N",
					"SUBSC_GROUP_MESSAGE" => ($_POST["FORUM_SUBSC_GROUP_MESSAGE"]=="Y") ? "Y" : "N",
					"SUBSC_GET_MY_MESSAGE" => ($_POST["FORUM_SUBSC_GET_MY_MESSAGE"]=="Y") ? "Y" : "N",
					"DESCRIPTION" => $_POST["FORUM_DESCRIPTION"],
					"INTERESTS" => $_POST["FORUM_INTERESTS"],
					"SIGNATURE" => $_POST["FORUM_SIGNATURE"]
				);

				if ($_FILES["FORUM_AVATAR"]["name"] <> '' || isset($_POST["FORUM_AVATAR_del"]))
					$arForumFields["AVATAR"] = $_FILES["FORUM_AVATAR"];

				foreach ($arForumFields as $key => $value)
					if (!in_array('FORUM_'.$key, $arParams['EDITABLE_FIELDS']))
						unset($arForumFields[$key]);

				if (count($arForumFields) > 0)
				{
					if (isset($arForumFields['AVATAR']))
					{
						$arForumFields["AVATAR"]["del"] = $_POST["FORUM_AVATAR_del"];
						$arForumFields["AVATAR"]["old_file"] = $arResult['User']['FORUM_AVATAR'];
					}

					if ($arResult['User']['FORUM_ID'])
						$FID = CForumUser::Update($arResult['User']['FORUM_ID'], $arForumFields);
					else
					{
						$arForumFields["USER_ID"] = $arResult["User"]['ID'];
						$FID = CForumUser::Add($arForumFields);
					}

					if (!$FID && ($ex = $APPLICATION->GetException()))
						$strErrorMessage = $ex->GetString();
				}
			}

			if ($strErrorMessage == '' && $arParams['IS_BLOG'] == 'Y')
			{
				$arBlogFields = Array(
					"ALIAS" => $_POST['BLOG_ALIAS'],
					"DESCRIPTION" => $_POST['BLOG_DESCRIPTION'],
					"INTERESTS" => $_POST['BLOG_INTERESTS']
				);

				if ($_FILES["BLOG_AVATAR"]["name"] <> '' || isset($_POST["BLOG_AVATAR_del"]))
					$arBlogFields["AVATAR"] = $_FILES["BLOG_AVATAR"];

				foreach ($arBlogFields as $key => $value)
					if (!in_array('BLOG_'.$key, $arParams['EDITABLE_FIELDS']))
						unset($arBlogFields[$key]);

				if (isset($arBlogFields['AVATAR']))
				{
					$arBlogFields["AVATAR"]["del"] = $_POST['BLOG_AVATAR_del'];
					$arBlogFields["AVATAR"]["old_file"] = $arResult['User']["BLOG_AVATAR"];
				}

				if (count($arBlogFields) > 0)
				{
					if ($arResult['User']['BLOG_ID'])
						$BID = CBlogUser::Update($arResult['User']['BLOG_ID'], $arBlogFields);
					else
					{
						$arBlogFields["USER_ID"] = $arParams['ID'];
						$arBlogFields["~DATE_REG"] = CDatabase::CurrentTimeFunction();
						$BID = CBlogUser::Add($arBlogFields);
					}

					if (!$BID && ($ex = $APPLICATION->GetException()))
						$strErrorMessage = $ex->GetString();
				}
			}

			if(IsModuleInstalled("bitrix24") && isset($arFieldsValue["GROUP_ID"]) && defined("BX_COMP_MANAGED_CACHE"))
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->ClearByTag('sonet_group');
			}
		}

		if($strErrorMessage == '')
			if (!empty($_REQUEST['backurl']))
			{
				LocalRedirect($_REQUEST['backurl']);
			}
			else
			{
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arParams["ID"])));
			}
		else
		{
			$arResult["ERROR_MESSAGE"] = $strErrorMessage;
			$bVarsFromForm = true;
		}
	}

	if($arResult['bEdit'] == 'Y' && $_SERVER["REQUEST_METHOD"]=="POST" && ($_POST["submit_fire"] <> '' || $_POST["submit_recover"] <> '') && check_bitrix_sessid())
	{
		if ($CurrentUserPerms["Operations"]["modifyuser_main"] && $SONET_USER_ID != $USER->GetID())
		{
			$arFields = array("ACTIVE" => $_POST["submit_fire"] <> '' ? "N" : "Y");
			$res = $USER->Update($SONET_USER_ID, $arFields);
			$arResult["User"]["ACTIVE"] = $_POST["submit_fire"] <> '' ? "N" : "Y";
		}
	}

	$arResult["User"]["PERSONAL_LOCATION"] = GetCountryByID($arResult["User"]["PERSONAL_COUNTRY"]);
	if ($arResult["User"]["PERSONAL_LOCATION"] <> '' && $arResult["User"]["PERSONAL_CITY"] <> '')
		$arResult["User"]["PERSONAL_LOCATION"] .= ", ";
	$arResult["User"]["PERSONAL_LOCATION"] .= $arResult["User"]["PERSONAL_CITY"];
	$arResult["User"]["WORK_LOCATION"] = GetCountryByID($arResult["User"]["WORK_COUNTRY"]);
	if ($arResult["User"]["WORK_LOCATION"] <> '' && $arResult["User"]["WORK_CITY"] <> '')
		$arResult["User"]["WORK_LOCATION"] .= ", ";
	$arResult["User"]["WORK_LOCATION"] .= $arResult["User"]["WORK_CITY"];

	if (CModule::IncludeModule('mail'))
	{
		$dbMailbox = \Bitrix\Mail\MailboxTable::getList(array(
			'filter' => array(
				'=LID' => SITE_ID,
				'=ACTIVE' => 'Y',
				'=USER_ID' => $arParams['ID'],
				'=SERVER_TYPE' => 'imap',
			),
			'order' => array(
				'TIMESTAMP_X' => 'DESC',
			),
		));
		$mailbox = $dbMailbox->fetch();
		\Bitrix\Mail\MailboxTable::normalizeEmail($mailbox);
		if (mb_strpos($mailbox['LOGIN'], '@') !== false)
			$arResult['User']['MAILBOX'] = $mailbox['LOGIN'];
	}

	if ($USER->CanDoOperation('edit_all_users') || $USER->CanDoOperation('edit_subordinate_users'))
	{
		$arResult["User"]["GROUP_ID"] = array();
		$rsGroup = CUser::GetUserGroupList($arResult["User"]["ID"]);
		while ($arGroup = $rsGroup->Fetch())
		{
			if ($arGroup["DATE_ACTIVE_FROM"] == '' && $arGroup["DATE_ACTIVE_TO"] == '')
				$arResult["User"]["GROUP_ID"][] = $arGroup["GROUP_ID"];
		}

		$arResult["User"]["GROUP_ID"] = array_intersect($arResult["User"]["GROUP_ID"], $arGroupsCanEditID);
	}

	$arResult["arSex"] = array(
		"M" => GetMessage("SONET_P_USER_SEX_M"),
		"F" => GetMessage("SONET_P_USER_SEX_F"),
	);

	if($bVarsFromForm)
	{
		static $skip = array("PERSONAL_PHOTO"=>1, "WORK_LOGO"=>1, "FORUM_AVATAR"=>1, "BLOG_AVATAR"=>1);
		foreach($_POST as $k => $v)
		{
			if(!isset($skip[$k]))
			{
				if(is_array($v))
				{
					foreach($v as $k1 => $v1)
					{
						$arResult["User"][$k][$k1] = htmlspecialcharsbx($v1);
						$arResult["User"]['~'.$k][$k1] = $v1;
					}
				}
				else
				{
					$arResult["User"][$k] = htmlspecialcharsbx($v);
					$arResult["User"]['~'.$k] = $v;
				}
			}
		}
	}

	$userName = '';
	if ($arParams["SET_TITLE"] == "Y" || $arParams["SET_NAV_CHAIN"] != "N")
	{
		if ($arParams["NAME_TEMPLATE"] == '')
			$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"),
			array("", ""),
			$arParams["NAME_TEMPLATE"]
		);
		$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

		$arTmpUser = array(
			'NAME' => $arResult["User"]["~NAME"],
			'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
			'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
			'LOGIN' => $arResult["User"]["~LOGIN"],
		);

		$userName = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
	}

	if($arParams["SET_TITLE"]=="Y")
		$APPLICATION->SetTitle(GetMessage("SONET_P_USER_TITLE")." \"".trim($userName, " ")."\"");

	if ($arParams["SET_NAV_CHAIN"] != "N")
	{
		$APPLICATION->AddChainItem($userName, CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])));
		$APPLICATION->AddChainItem(GetMessage("SONET_P_USER_TITLE_VIEW"));
	}

	if($arResult["User"]["PERSONAL_WWW"] <> '')
		$arResult["User"]["PERSONAL_WWW"] = ((mb_strpos($arResult["User"]["PERSONAL_WWW"], "http") === false)? "http://" : "").$arResult["User"]["PERSONAL_WWW"];

	$arResult["User"]["PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($arResult["User"]["PERSONAL_PHOTO"]);
	if ($arResult["User"]["PERSONAL_PHOTO_FILE"] !== false)
		$arResult["User"]["PERSONAL_PHOTO_IMG"] = CFile::ShowImage($arResult["User"]["PERSONAL_PHOTO_FILE"]["ID"], 150, 150, "border=0", "", true);

	$arResult["User"]["WORK_LOGO_FILE"] = CFile::GetFileArray($arResult["User"]["WORK_LOGO"]);
	if ($arResult["User"]["WORK_LOGO_FILE"] !== false)
		$arResult["User"]["WORK_LOGO_IMG"] = CFile::ShowImage($arResult["User"]["WORK_LOGO_FILE"]["ID"], 150, 150, "border=0", "", true);

	if ($arParams['IS_FORUM'] == 'Y')
	{
		$arResult["User"]["FORUM_AVATAR_FILE"] = CFile::GetFileArray($arResult["User"]["FORUM_AVATAR"]);
		if ($arResult["User"]["FORUM_AVATAR_FILE"] !== false)
			$arResult["User"]["FORUM_AVATAR_IMG"] = CFile::ShowImage($arResult["User"]["FORUM_AVATAR_FILE"]["ID"], 150, 150, "border=0", "", true);
	}

	if ($arParams['IS_BLOG'] == 'Y')
	{
		$arResult["User"]["BLOG_AVATAR_FILE"] = CFile::GetFileArray($arResult["User"]["BLOG_AVATAR"]);
		if ($arResult["User"]["BLOG_AVATAR_FILE"] !== false)
			$arResult["User"]["BLOG_AVATAR_IMG"] = CFile::ShowImage($arResult["User"]["BLOG_AVATAR_FILE"]["ID"], 150, 150, "border=0", "", true);
	}

	$arPolicy = $USER->GetGroupPolicy($arResult["User"]["ID"]);
	$arResult["PASSWORD_MIN_LENGTH"] = intval($arPolicy["PASSWORD_LENGTH"]);
	if($arResult["PASSWORD_MIN_LENGTH"] <= 0)
		$arResult["PASSWORD_MIN_LENGTH"] = 6;
}

//time zones
$arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
if($arResult["TIME_ZONE_ENABLED"])
	$arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();

$this->IncludeComponentTemplate();
?>