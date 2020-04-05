<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!array_key_exists("MAX_ITEMS", $arParams) || intval($arParams["MAX_ITEMS"]) <= 0)
	$arParams["MAX_ITEMS"] = 6;
	
$arParams["ID"] = IntVal($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = IntVal($USER->GetID());

$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);

if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "user_id";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS"] = trim($arParams["PATH_TO_USER_FRIENDS"]);
if(strlen($arParams["PATH_TO_USER_FRIENDS"])<=0)
	$arParams["PATH_TO_USER_FRIENDS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_ADD"] = trim($arParams["PATH_TO_USER_FRIENDS_ADD"]);
if(strlen($arParams["PATH_TO_USER_FRIENDS_ADD"])<=0)
	$arParams["PATH_TO_USER_FRIENDS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_DELETE"] = trim($arParams["PATH_TO_USER_FRIENDS_DELETE"]);
if(strlen($arParams["PATH_TO_USER_FRIENDS_DELETE"])<=0)
	$arParams["PATH_TO_USER_FRIENDS_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_delete&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if(strlen($arParams["PATH_TO_SEARCH"])<=0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_USER_GROUPS"] = trim($arParams["PATH_TO_USER_GROUPS"]);
if(strlen($arParams["PATH_TO_USER_GROUPS"])<=0)
	$arParams["PATH_TO_USER_GROUPS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_groups&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_GROUPS_ADD"] = trim($arParams["PATH_TO_USER_GROUPS_ADD"]);
if(strlen($arParams["PATH_TO_USER_GROUPS_ADD"])<=0)
	$arParams["PATH_TO_USER_GROUPS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_groups_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_EDIT"] = trim($arParams["PATH_TO_USER_EDIT"]);
if(strlen($arParams["PATH_TO_USER_EDIT"])<=0)
	$arParams["PATH_TO_USER_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#&mode=edit");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if (strlen($arParams["PATH_TO_MESSAGE_FORM"]) <= 0)
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_INPUT"] = trim($arParams["PATH_TO_MESSAGES_INPUT"]);
if(strlen($arParams["PATH_TO_MESSAGES_INPUT"])<=0)
	$arParams["PATH_TO_MESSAGES_INPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input");

$arParams["PATH_TO_USER_BLOG"] = trim($arParams["PATH_TO_USER_BLOG"]);
if(strlen($arParams["PATH_TO_USER_BLOG"])<=0)
	$arParams["PATH_TO_USER_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_blog&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_MICROBLOG"] = trim($arParams["PATH_TO_USER_MICROBLOG"]);
if(strlen($arParams["PATH_TO_USER_MICROBLOG"])<=0)
	$arParams["PATH_TO_USER_MICROBLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_microblog&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_PHOTO"] = trim($arParams["PATH_TO_USER_PHOTO"]);
if(strlen($arParams["PATH_TO_USER_PHOTO"])<=0)
	$arParams["PATH_TO_USER_PHOTO"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_photo&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FORUM"] = trim($arParams["PATH_TO_USER_FORUM"]);
if(strlen($arParams["PATH_TO_USER_FORUM"])<=0)
	$arParams["PATH_TO_USER_FORUM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_forum&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_CALENDAR"] = trim($arParams["PATH_TO_USER_CALENDAR"]);
if (strlen($arParams["PATH_TO_USER_CALENDAR"]) <= 0)
{
	$arParams["PATH_TO_USER_CALENDAR"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_calendar&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_TASKS"] = trim($arParams["PATH_TO_USER_TASKS"]);
if(strlen($arParams["PATH_TO_USER_TASKS"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_tasks&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_FILES"] = trim($arParams["PATH_TO_USER_FILES"]);
if (strlen($arParams["PATH_TO_USER_FILES"]) <= 0)
{
	$arParams["PATH_TO_USER_FILES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_files&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_CONTENT_SEARCH"] = trim($arParams["PATH_TO_USER_CONTENT_SEARCH"]);
if (strlen($arParams["PATH_TO_USER_CONTENT_SEARCH"]) <= 0)
{
	$arParams["PATH_TO_USER_CONTENT_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_content_search&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["USE_MAIN_MENU"] = (
	isset($arParams["USE_MAIN_MENU"])
		? $arParams["USE_MAIN_MENU"]
		: false
);

if (
	$arParams["USE_MAIN_MENU"] == "Y"
	&& !array_key_exists("MAIN_MENU_TYPE", $arParams)
)
{
	$arParams["MAIN_MENU_TYPE"] = "left";
}

if ($arParams["ID"] <= 0)
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$dbUser = CUser::GetByID($arParams["ID"]);
	$arResult["User"] = $dbUser->GetNext();
	if (
		!IsModuleInstalled("intranet")
		&& $arResult["User"]["ACTIVE"] != "Y"
	)
	{
		$arResult["User"] = false;
	}

	if (!CSocNetUser::CanProfileView($USER->GetID(), $arResult["User"], SITE_ID, \Bitrix\Socialnetwork\ComponentHelper::getUrlContext()))
	{
		$arResult["User"] = false;
	}

	if (!is_array($arResult["User"]))
	{
		$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER");
	}
	else
	{
		$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

		if (
			CModule::IncludeModule('extranet')
			&& CExtranet::IsExtranetSite()
		)
		{
			$arResult["CurrentUserPerms"]["Operations"]["viewfriends"] = false;
		}

		$arResult["User"]["TYPE"] = '';

		$arResult["User"]["IS_EXTRANET"] = (
			IsModuleInstalled('extranet')
			&& (
				empty($arResult["User"]["UF_DEPARTMENT"])
				|| empty($arResult["User"]["UF_DEPARTMENT"][0])
			)
				? "Y"
				: "N"
		);
		if (
			$arResult["User"]["EXTERNAL_AUTH_ID"] == 'email'
			&& IsModuleInstalled('mail')
		)
		{
			$arResult["User"]["TYPE"] = 'email';
			$arResult["CurrentUserPerms"]["Operations"]["viewgroups"] = false;
		}
		elseif (
			$arResult["User"]["EXTERNAL_AUTH_ID"] == 'replica'
			&& IsModuleInstalled('socialservices')
		)
		{
			$arResult["User"]["TYPE"] = 'replica';
		}
		elseif (
			($arResult["User"]["EXTERNAL_AUTH_ID"] == 'bot' && IsModuleInstalled('im')) ||
			($arResult["User"]["EXTERNAL_AUTH_ID"] == 'imconnector' && IsModuleInstalled('imconnector'))
		)
		{
			$arResult["User"]["TYPE"] = $arResult["User"]["EXTERNAL_AUTH_ID"] == 'bot'? 'bot': 'imconnector';
			$arResult["CurrentUserPerms"]["Operations"]["viewgroups"] = false;
			$arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"] = false;
			$arResult["CurrentUserPerms"]["Operations"]["modifyuser"] = false;
		}
		elseif ($arResult["User"]["IS_EXTRANET"] == "Y")
		{
			$arResult["User"]["TYPE"] = 'extranet';
		}

		$arContext = \Bitrix\Socialnetwork\ComponentHelper::getUrlContext();

		$arResult["Urls"]["Edit"] = \Bitrix\Socialnetwork\ComponentHelper::addContextToUrl(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_EDIT"], array("user_id" => $arResult["User"]["ID"])), $arContext);
		$arResult["Urls"]["Friends"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["FriendsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_ADD"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["FriendsDelete"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS_DELETE"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Groups"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_GROUPS"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SEARCH"], array());
		$arResult["Urls"]["GroupsAdd"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_GROUPS_ADD"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["MessageForm"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Log"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
		$arResult["Urls"]["Main"] = \Bitrix\Socialnetwork\ComponentHelper::addContextToUrl(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"])), $arContext);
		$arResult["Urls"]["MessagesInput"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_INPUT"], array());
		$arResult["Urls"]["Blog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_BLOG"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Microblog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_MICROBLOG"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Photo"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PHOTO"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Forum"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FORUM"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Calendar"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_CALENDAR"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Tasks"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_TASKS"], array("user_id" => $arResult["User"]["ID"]));
		$arResult["Urls"]["Files"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FILES"], array("user_id" => $arResult["User"]["ID"], "path" => ""));

		$arResult["Urls"]["content_search"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_CONTENT_SEARCH"], array("user_id" => $arResult["User"]["ID"]));

		$arExternalAuthId = \Bitrix\Socialnetwork\ComponentHelper::checkPredefinedAuthIdList(array('replica', 'bot', 'email', 'imconnector'));
		$arResult["ActiveFeatures"] = (
			isset($arResult["User"]["EXTERNAL_AUTH_ID"])
			&& in_array($arResult["User"]["EXTERNAL_AUTH_ID"], $arExternalAuthId)
				? array()
				: CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_USER, $arResult["User"]["ID"])
		);

		$arResult["CanView"]["files"] = array_key_exists("files", $arResult["ActiveFeatures"]);
		if($arResult["CanView"]["files"])
		{
			$diskEnabled = CModule::includeModule('disk') && \Bitrix\Disk\Driver::isSuccessfullyConverted();
			if($diskEnabled)
			{
				$arResult["Urls"]["Files"] = CComponentEngine::makePathFromTemplate($arParams["PATH_TO_USER_DISK"], array(
					"user_id" => $arResult["User"]["ID"],
					"PATH" => ""
				));
			}
		}

		$arResult["CanView"]["tasks"] = (array_key_exists("tasks", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "tasks", "view", CSocNetUser::IsCurrentUserModuleAdmin()));

		$arResult["CanView"]["calendar"] = (
			array_key_exists("calendar", $arResult["ActiveFeatures"])
			&& (
				!IsModuleInstalled("extranet")
				|| (
					isset($arResult["User"]["UF_DEPARTMENT"])
					&& is_array($arResult["User"]["UF_DEPARTMENT"])
					&& !empty($arResult["User"]["UF_DEPARTMENT"])
				)
			)
		);
		$arResult["CanView"]["microblog"] = (array_key_exists("microblog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
		$arResult["CanView"]["blog"] = (array_key_exists("blog", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "blog", "view_post", CSocNetUser::IsCurrentUserModuleAdmin()));
		$arResult["CanView"]["photo"] = (array_key_exists("photo", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "photo", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
		$arResult["CanView"]["forum"] = (array_key_exists("forum", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "forum", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
		$arResult["CanView"]["content_search"] = (array_key_exists("search", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "search", "view", CSocNetUser::IsCurrentUserModuleAdmin()));

		$arResult["Title"]["blog"] = ((array_key_exists("blog", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["blog"]) > 0) ? $arResult["ActiveFeatures"]["blog"] : GetMessage("SONET_UM_BLOG"));
		$arResult["Title"]["microblog"] = ((array_key_exists("microblog", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["microblog"]) > 0) ? $arResult["ActiveFeatures"]["microblog"] : GetMessage("SONET_UM_MICROBLOG"));
		$arResult["Title"]["photo"] = ((array_key_exists("photo", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["photo"]) > 0) ? $arResult["ActiveFeatures"]["photo"] : GetMessage("SONET_UM_PHOTO"));
		$arResult["Title"]["forum"] = ((array_key_exists("forum", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["forum"]) > 0) ? $arResult["ActiveFeatures"]["forum"] : GetMessage("SONET_UM_FORUM"));
		$arResult["Title"]["calendar"] = ((array_key_exists("calendar", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["calendar"]) > 0) ? $arResult["ActiveFeatures"]["calendar"] : GetMessage("SONET_UM_CALENDAR"));
		$arResult["Title"]["tasks"] = ((array_key_exists("tasks", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["tasks"]) > 0) ? $arResult["ActiveFeatures"]["tasks"] : GetMessage("SONET_UM_TASKS"));
		$arResult["Title"]["files"] = ((array_key_exists("files", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["files"]) > 0) ? $arResult["ActiveFeatures"]["files"] : GetMessage("SONET_UM_FILES"));
		$arResult["Title"]["content_search"] = ((array_key_exists("search", $arResult["ActiveFeatures"]) && StrLen($arResult["ActiveFeatures"]["search"]) > 0) ? $arResult["ActiveFeatures"]["search"] : GetMessage("SONET_UM_SEARCH"));

		$a = array_keys($arResult["Urls"]);
		foreach ($a as $v)
		{
			$arResult["Urls"][strtolower($v)] = $arResult["Urls"][$v];
		}

		$events = GetModuleEvents("socialnetwork", "OnFillSocNetMenu");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array(&$arResult));
		}

		$externalAuthIdPerms = \Bitrix\Socialnetwork\Util::getPermissionsByExternalAuthId($arResult["User"]["EXTERNAL_AUTH_ID"]);

		$arResult["CAN_MESSAGE"] = (
			($GLOBALS["USER"]->GetID() != $arResult["User"]["ID"]) 
			&& ($arResult["User"]["ACTIVE"] != "N")
			&& (
				IsModuleInstalled("im") 
				|| $arResult["CurrentUserPerms"]["Operations"]["message"]
			)
			&& $externalAuthIdPerms['message']
		);

		$arResult["CAN_MESSAGE_HISTORY"] = (
			($GLOBALS["USER"]->GetID() != $arResult["User"]["ID"]) 
			&& (
				IsModuleInstalled("im") 
				|| (
					$arResult["CurrentUserPerms"]["Operations"]["message"]
					&& ($arResult["User"]["ACTIVE"] != "N")
				)
			)
			&& $externalAuthIdPerms['message']
		);

		$arResult["CAN_VIDEO_CALL"] = (
			($GLOBALS["USER"]->GetID() != $arResult["User"]["ID"]) 
			&& ($arResult["User"]["ACTIVE"] != "N")
			&& $arResult["CurrentUserPerms"]["Operations"]["videocall"]
		);
	}

	$this->IncludeComponentTemplate();
}
?>