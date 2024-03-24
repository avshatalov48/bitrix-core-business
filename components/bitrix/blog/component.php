<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arDefaultUrlTemplates404 = array(
		"index" => "index.php",
		"group" => "group/#group_id#.php",
		"blog" => "#blog#/",
		"user" => "user/#user_id#.php",
		"user_friends" => "friends/#user_id#.php",
		"rss_all" => "rss/#type#/#group_id#",
		"search" => "search.php",
		"user_settings" => "#blog#/user_settings.php",
		"user_settings_edit" => "#blog#/user_settings_edit.php?id=#user_id#",
		"group_edit" => "#blog#/group_edit.php",
		"blog_edit" => "#blog#/blog_edit.php",
		"category_edit" => "#blog#/category_edit.php",
		"post_edit" => "#blog#/post_edit.php?id=#post_id#",
		"draft" => "#blog#/draft.php",
		"trackback" => POST_FORM_ACTION_URI."&page=trackback&blog=#blog#&id=#post_id#",
		"moderation" => "#blog#/moderation.php",
		"post" => "#blog#/#post_id#.php",
		"post_rss" => "#blog#/rss/#type#/#post_id#",
		"rss" => "#blog#/rss/#type#",
		"history" => "history.php",
		"metaweblog" => "metaweblog.php",

	);

$arDefaultVariableAliases404 = array(
		"user_settings_edit" => Array(
			"user_id" => "id",
		),
		"post_edit" => Array(
			"post_id" => "id",
		),
		"post" => Array(
			"post_id" => "id",
		),
		"trackback" => Array(
			"post_id" => "id",
		),
	);
$arDefaultVariableAliases = array();
$componentPage = "";
$arComponentVariables = array("user_id", "post_id", "blog", "group_id", "type", "category", "day", "month", "year", "title", "url", "excerpt", "blog_name", "page");

if (trim($arParams["NAME_TEMPLATE"]) == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
	
$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";	

if (IsModuleInstalled('intranet') && !array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";

if (IsModuleInstalled("socialnetwork")):
	if(!isset($arParams["PATH_TO_MESSAGES_CHAT"]) && IsModuleInstalled("intranet")):
		$arParams["PATH_TO_MESSAGES_CHAT"] = "/company/personal/messages/chat/#user_id#/";
	elseif(!isset($arParams["PATH_TO_MESSAGES_CHAT"])):
		$arParams["PATH_TO_MESSAGES_CHAT"] = "/club/messages/chat/#user_id#/";
	endif;

	if(!isset($arParams["PATH_TO_SONET_USER_PROFILE"]) && IsModuleInstalled("intranet")):
		$arParams["PATH_TO_SONET_USER_PROFILE"] = "/company/personal/user/#user_id#/";
	elseif(!isset($arParams["PATH_TO_SONET_USER_PROFILE"])):
		$arParams["PATH_TO_SONET_USER_PROFILE"] = "/club/user/#user_id#/";
	endif;

endif;


if($_REQUEST["auth"]=="Y" && $USER->IsAuthorized())
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);


	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if ($_REQUEST["page"] == "trackback")
		$componentPage = "trackback";

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		$componentPage = "index";
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
	{
		if($arParams["PATH_TO_".mb_strtoupper($url)] == '')
			$arResult["PATH_TO_".mb_strtoupper($url)] = $arParams["SEF_FOLDER"].$value;
		else
			$arResult["PATH_TO_".mb_strtoupper($url)] = $arParams["PATH_TO_".mb_strtoupper($url)];
	}

	$arResult["PATH_TO_USER_EDIT"] = $arParams["SEF_FOLDER"].$arUrlTemplates["user"].(mb_strpos($arParams["SEF_FOLDER"].$arUrlTemplates["user"], "?") === false ? "?" : "&")."mode=edit";
	$arResult["PATH_TO_BLOG_CATEGORY"] = $arParams["SEF_FOLDER"].$arUrlTemplates["blog"].(mb_strpos($arParams["SEF_FOLDER"].$arUrlTemplates["blog"], "?") === false ? "?" : "&")."category=#category_id#";
	$arResult["PATH_TO_BLOG_INDEX"] = $arParams["SEF_FOLDER"];

	if($_REQUEST["auth"]=="Y")
		$componentPage = "auth";
	$arResult["PATH_TO_TRACKBACK"] = $arUrlTemplates["trackback"];
}
else
{
	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		$componentPage = "index";
	}
	if($_REQUEST["auth"]=="Y")
		$componentPage = "auth";

	if($arResult["PATH_TO_SEARCH"] == '')
		$arResult["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["page"]."=search");

}

if($arResult["PATH_TO_BLOG_EDIT"] <> '')
	$blogEdit = $arResult["PATH_TO_BLOG_EDIT"];
else
	$blogEdit = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arVariableAliases["page"]."=blog_edit&".$arVariableAliases["blog"]."=#blog#");

$arResult["PATH_TO_NEW_BLOG"] = CComponentEngine::MakePathFromTemplate($blogEdit, array("blog" => "new"));

$arResult = array_merge(
	array(
			"SEF_MODE" => $arParams["SEF_MODE"],
			"SEF_FOLDER" => $arParams["SEF_FOLDER"],
			"VARIABLES" => $arVariables,
			"ALIASES" => $arParams["SEF_MODE"] == "Y"? array(): $arVariableAliases,
			"SET_TITLE" => $arParams["SET_TITLE"],
			"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CACHE_TIME_LONG" => $arParams["CACHE_TIME_LONG"],
			"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
			"MESSAGE_COUNT" => $arParams["MESSAGE_COUNT"],
			"BLOG_COUNT" => $arParams["BLOG_COUNT"],
			"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
			"BLOG_COUNT_MAIN" => $arParams["BLOG_COUNT_MAIN"],
			"MESSAGE_COUNT_MAIN" => $arParams["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH" => $arParams["MESSAGE_LENGTH"],
			"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
			"PERIOD_DAYS" => $arParams["PERIOD_DAYS"],
			"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
		),
		$arResult
	);

$this->IncludeComponentTemplate($componentPage);
?>