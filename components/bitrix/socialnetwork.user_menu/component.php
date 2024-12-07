<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$currentUserId = \Bitrix\Main\Engine\CurrentUser::get()?->getId();
$isSignAvailable = \Bitrix\Main\Loader::includeModule('sign')
	&& method_exists(\Bitrix\Sign\Config\Storage::class, 'isB2eAvailable')
	&& \Bitrix\Sign\Config\Storage::instance()->isB2eAvailable()
	&& $arParams['PAGE_ID'] === 'user'
	&& (int)$arParams['ID'] === (int)$currentUserId;
;

if (!array_key_exists("MAX_ITEMS", $arParams) || intval($arParams["MAX_ITEMS"]) <= 0)
	$arParams["MAX_ITEMS"] = 6;
	
$arParams["ID"] = intval($arParams["ID"]);
if ($arParams["ID"] <= 0)
	$arParams["ID"] = intval($USER->GetID());

$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);

if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS"] = trim($arParams["PATH_TO_USER_FRIENDS"]);
if($arParams["PATH_TO_USER_FRIENDS"] == '')
	$arParams["PATH_TO_USER_FRIENDS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_ADD"] = trim($arParams["PATH_TO_USER_FRIENDS_ADD"]);
if($arParams["PATH_TO_USER_FRIENDS_ADD"] == '')
	$arParams["PATH_TO_USER_FRIENDS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS_DELETE"] = trim($arParams["PATH_TO_USER_FRIENDS_DELETE"]);
if($arParams["PATH_TO_USER_FRIENDS_DELETE"] == '')
	$arParams["PATH_TO_USER_FRIENDS_DELETE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends_delete&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"] ?? '');
if($arParams["PATH_TO_SEARCH"] == '')
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["PATH_TO_USER_GROUPS"] = trim($arParams["PATH_TO_USER_GROUPS"]);
if($arParams["PATH_TO_USER_GROUPS"] == '')
	$arParams["PATH_TO_USER_GROUPS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_groups&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_GROUPS_ADD"] = trim($arParams["PATH_TO_USER_GROUPS_ADD"] ?? '');
if($arParams["PATH_TO_USER_GROUPS_ADD"] == '')
	$arParams["PATH_TO_USER_GROUPS_ADD"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_groups_add&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_EDIT"] = trim($arParams["PATH_TO_USER_EDIT"]);
if($arParams["PATH_TO_USER_EDIT"] == '')
	$arParams["PATH_TO_USER_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#&mode=edit");

$arParams["PATH_TO_MESSAGE_FORM"] = trim($arParams["PATH_TO_MESSAGE_FORM"]);
if ($arParams["PATH_TO_MESSAGE_FORM"] == '')
	$arParams["PATH_TO_MESSAGE_FORM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=message_form&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_INPUT"] = trim($arParams["PATH_TO_MESSAGES_INPUT"]);
if($arParams["PATH_TO_MESSAGES_INPUT"] == '')
	$arParams["PATH_TO_MESSAGES_INPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input");

$arParams["PATH_TO_USER_BLOG"] = trim($arParams["PATH_TO_USER_BLOG"]);
if($arParams["PATH_TO_USER_BLOG"] == '')
	$arParams["PATH_TO_USER_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_blog&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_MICROBLOG"] = trim($arParams["PATH_TO_USER_MICROBLOG"] ?? '');
if($arParams["PATH_TO_USER_MICROBLOG"] == '')
	$arParams["PATH_TO_USER_MICROBLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_microblog&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_PHOTO"] = trim($arParams["PATH_TO_USER_PHOTO"]);
if($arParams["PATH_TO_USER_PHOTO"] == '')
	$arParams["PATH_TO_USER_PHOTO"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_photo&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FORUM"] = trim($arParams["PATH_TO_USER_FORUM"]);
if($arParams["PATH_TO_USER_FORUM"] == '')
	$arParams["PATH_TO_USER_FORUM"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_forum&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_CALENDAR"] = trim($arParams["PATH_TO_USER_CALENDAR"]);
if ($arParams["PATH_TO_USER_CALENDAR"] == '')
{
	$arParams["PATH_TO_USER_CALENDAR"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_calendar&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_TASKS"] = trim($arParams["PATH_TO_USER_TASKS"]);
if($arParams["PATH_TO_USER_TASKS"] == '')
{
	$arParams["PATH_TO_USER_TASKS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_tasks&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_FILES"] = trim($arParams["PATH_TO_USER_FILES"]);
if ($arParams["PATH_TO_USER_FILES"] == '')
{
	$arParams["PATH_TO_USER_FILES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_files&".$arParams["USER_VAR"]."=#user_id#");
}

$arParams["PATH_TO_USER_CONTENT_SEARCH"] = trim($arParams["PATH_TO_USER_CONTENT_SEARCH"]);
if ($arParams["PATH_TO_USER_CONTENT_SEARCH"] == '')
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
		if ($isSignAvailable)
		{
			$arResult["Urls"]["Sign"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SIGN"], ["user_id" => $arResult["User"]["ID"]]);
		}

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
		if ($isSignAvailable)
		{
			$arResult["CanView"]["sign"] = (array_key_exists("sign", $arResult["ActiveFeatures"]) && CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), SONET_ENTITY_USER, $arResult["User"]["ID"], "sign", "view", CSocNetUser::IsCurrentUserModuleAdmin()));
		}

		$arResult["Title"]["blog"] = ((array_key_exists("blog", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["blog"] <> '') ? $arResult["ActiveFeatures"]["blog"] : GetMessage("SONET_UM_STREAM_NEWS_2"));
		$arResult["Title"]["microblog"] = ((array_key_exists("microblog", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["microblog"] <> '') ? $arResult["ActiveFeatures"]["microblog"] : GetMessage("SONET_UM_MICROBLOG"));
		$arResult["Title"]["photo"] = ((array_key_exists("photo", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["photo"] <> '') ? $arResult["ActiveFeatures"]["photo"] : GetMessage("SONET_UM_PHOTO"));
		$arResult["Title"]["forum"] = ((array_key_exists("forum", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["forum"] <> '') ? $arResult["ActiveFeatures"]["forum"] : GetMessage("SONET_UM_FORUM"));
		$arResult["Title"]["calendar"] = ((array_key_exists("calendar", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["calendar"] <> '') ? $arResult["ActiveFeatures"]["calendar"] : GetMessage("SONET_UM_CALENDAR"));
		$arResult["Title"]["tasks"] = ((array_key_exists("tasks", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["tasks"] <> '') ? $arResult["ActiveFeatures"]["tasks"] : GetMessage("SONET_UM_TASKS"));
		$arResult["Title"]["files"] = ((array_key_exists("files", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["files"] <> '') ? $arResult["ActiveFeatures"]["files"] : GetMessage("SONET_UM_DISK"));
		$arResult["Title"]["content_search"] = ((array_key_exists("search", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["search"] <> '') ? $arResult["ActiveFeatures"]["search"] : GetMessage("SONET_UM_SEARCH"));

		if ($isSignAvailable)
		{
			$arResult["Title"]["sign"] = ((array_key_exists("sign", $arResult["ActiveFeatures"]) && $arResult["ActiveFeatures"]["sign"] <> '')
				? $arResult["ActiveFeatures"]["sign"]
				: GetMessage("SONET_UM_SIGN"));
		}

		$a = array_keys($arResult["Urls"]);
		foreach ($a as $v)
		{
			$arResult["Urls"][mb_strtolower($v)] = $arResult["Urls"][$v];
		}

		$events = GetModuleEvents("socialnetwork", "OnFillSocNetMenu");
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array(&$arResult, $arParams));
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
			&& ($arResult["CurrentUserPerms"]["Operations"]["videocall"] ?? false)
		);
	}

	$this->IncludeComponentTemplate();
}
?>
