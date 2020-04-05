<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALL"));
	return;
}

if (!CModule::IncludeModule('idea'))
{
	ShowError(GetMessage("IDEA_MODULE_NOT_INSTALL"));
	return;
}

$arDefaultUrlTemplates404 = array(
	"index" => "index.php",
	"status_0" => "status/#status_code#/",
	"category_1" => "category/#category_1#/",
	"category_1_status" => "category/#category_1#/status/#status_code#/",
	"category_2" => "category/#category_1#/#category_2#/",
	"category_2_status" => "category/#category_1#/#category_2#/status/#status_code#/",

	"user_ideas" => "user/#user_id#/idea/",
	"user_ideas_status" => "user/#user_id#/idea/status/#status_code#/",

	"user" => "user/#user_id#/",
	"user_subscribe" => "user/#user_id#/subscribe/",
	"post_edit" => "edit/#post_id#/",
	"post" => "#post_id#/",
	//RSS
	"rss_category_status" => "rss/#type#/category/#category#/status/#status_code#/",
	"rss_user_ideas_status" => "rss/#type#/user/#user_id#/idea/status/#status_code#/",
	"rss_status" => "rss/#type#/status/#status_code#/",
	"rss_category" => "rss/#type#/category/#category#/",
	"rss_user_ideas" => "rss/#type#/user/#user_id#/idea/",
	"post_rss" => "rss/#type#/#post_id#/",
	"rss" => "rss/#type#/",

);

$arDefaultVariableAliases404 = array(
	"post_edit" => Array(
		"post_id" => "id",
	),
	"post" => Array(
		"post_id" => "id",
	)
);
$arDefaultVariableAliases = array();
$componentPage = "";
$arComponentVariables = array("category_1", "category_2", "status_code", "user_id", "post_id", "tag");

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

$arParams['RATING_TEMPLATE'] = (strlen($arParams['RATING_TEMPLATE'])>0 && in_array($arParams['RATING_TEMPLATE'], array("standart", "like")))
	?$arParams['RATING_TEMPLATE']
	:"standart";

//Prepare necessary UF
CIdeaManagment::getInstance()->Idea()->SetCategoryListId($arParams["IBLOCK_CATEGORIES"] ? $arParams["IBLOCK_CATEGORIES"] : $arParams["IBLOCK_CATOGORIES"]);
$arIdeaUserFields = CIdeaManagment::getInstance()->GetUserFieldsArray();

if(!is_array($arParams["POST_PROPERTY"]))
	$arParams["POST_PROPERTY"] = array();

if(!is_array($arParams["POST_PROPERTY_LIST"]))
	$arParams["POST_PROPERTY_LIST"] = array();

foreach($arIdeaUserFields as $UF)
	$arParams["POST_PROPERTY"][] = $UF;
$arParams["POST_PROPERTY"] = array_unique($arParams["POST_PROPERTY"]);

foreach($arIdeaUserFields as $UF)
	$arParams["POST_PROPERTY_LIST"][] = $UF;
$arParams["POST_PROPERTY_LIST"] = array_unique($arParams["POST_PROPERTY_LIST"]);
//END ->Prepare necessary UF

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
			$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
			$componentPage = "index";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	foreach ($arUrlTemplates as $url => $value)
	{
			if(strlen($arParams["PATH_TO_".ToUpper($url)]) <= 0)
				$arResult["PATH_TO_".ToUpper($url)] = $arParams["SEF_FOLDER"].$value;
			else
				$arResult["PATH_TO_".ToUpper($url)] = $arParams["PATH_TO_".ToUpper($url)];
	}

	$arResult["PATH_TO_BLOG_CATEGORY"] = $arParams["SEF_FOLDER"].$arUrlTemplates["blog"].(strpos($arParams["SEF_FOLDER"].$arUrlTemplates["blog"], "?")===false ? "?" : "&")."tag=#category_id#";
}
else
{
	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
			$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
			$componentPage = "index";

		//Prepare Paths
	foreach ($arDefaultUrlTemplates404 as $url => $value)
	{
		$arNotSefParams = array();
		preg_match_all("/#(.*?)#/i", $value, $arNotSefParams);

		$subURL = '';
		if(is_array($arNotSefParams[1]) && !empty($arNotSefParams[1]))
			foreach($arNotSefParams[1] as $subURLParam)
				$subURL .= '&'.htmlspecialcharsbx($arVariableAliases[$subURLParam]).'=#'.$subURLParam.'#';

		if(strlen($arParams["PATH_TO_".ToUpper($url)]) <= 0)
			$arResult["PATH_TO_".ToUpper($url)] = htmlspecialcharsbx($APPLICATION->GetCurPage())."?".htmlspecialcharsbx($arVariableAliases["page"])."=".$url.$subURL;
	}

		$arResult["PATH_TO_BLOG_CATEGORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()).'?tag=#category_id#';
}

$arResult["~PATH_TO_POST_ADD"] = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_POST_EDIT"], array("post_id" => "new"));
$arResult["~PATH_TO_USER_IDEAS"] = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_IDEAS"], array("user_id" => $USER->GetID()));
$arResult["~PATH_TO_USER_SUBSCRIBE"] = CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_USER_SUBSCRIBE"], array("user_id" => $USER->GetID()));

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
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"PERIOD_DAYS" => $arParams["PERIOD_DAYS"],
		"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
		"ACTIONS" => array(),
		"IS_CORPORTAL" => IsModuleInstalled('intranet')?"Y":"N",
		"IS_AJAX" => $_REQUEST["AJAX"] == 'IDEA'?'Y':"N",
		"LIFE_SEARCH_QUERY" => (CUtil::decodeURIComponent($_REQUEST["LIFE_SEARCH_QUERY"]) || true ? $_REQUEST["LIFE_SEARCH_QUERY"] : ""),
		//"USER_PERMISSION" => array(),
	),
	$arResult
);

if($arParams["DISABLE_SONET_LOG"] == "Y" || !IsModuleInstalled('socialnetwork'))
	CIdeaManagment::getInstance()->Notification()->GetSonetNotify()->Disable();

if($arParams["DISABLE_EMAIL"] == "Y")
	CIdeaManagment::getInstance()->Notification()->GetEmailNotify()->Disable();

//Permissions
$arResult["IDEA_MODERATOR"] = false;
if( (!empty($arParams["POST_BIND_USER"]) && array_intersect($USER->GetUserGroupArray(), $arParams["POST_BIND_USER"]) )
	|| $USER->IsAdmin()
)
	$arResult["IDEA_MODERATOR"] = true;

//Deprecated
$arResult["PATH_TO_POST_ADD"] = $arResult["~PATH_TO_POST_ADD"];
$arParams["COMMENT_EDITOR_CODE_DEFAULT"] = $arParams[(array_key_exists("COMMENT_EDITOR_CODE_DEFAULT", $arParams) ? "COMMENT_EDITOR_CODE_DEFAULT" : "EDITOR_CODE_DEFAULT")];
$arParams["COMMENT_EDITOR_RESIZABLE"] = $arParams[(array_key_exists("COMMENT_EDITOR_RESIZABLE", $arParams) ? "COMMENT_EDITOR_RESIZABLE" : "EDITOR_RESIZABLE")];
$arParams["SMILES_COUNT"] = ($arParams["SMILES_COUNT"] > 0 ? $arParams["SMILES_COUNT"] : 1);

if (!array_key_exists("IBLOCK_CATEGORIES", $arParams) && array_key_exists("IBLOCK_CATOGORIES", $arParams))
	$arParams["IBLOCK_CATEGORIES"] = $arParams["IBLOCK_CATOGORIES"];

if($arResult["IS_AJAX"] == "Y")
	include('component.ajax.php');

if (in_array($componentPage, array(
		"index", "status_0",
		"category_1", "category_1_status",
		"category_2", "category_2_status",
		"user_ideas", "user_ideas_status"
	)) && !$this->initComponentTemplate("", false, ""))
{
	$arResult["PAGE_MODE"] = $this->__pageMode = $componentPage;
	$componentPage = "index";
}
else if (in_array($componentPage, array(
	"rss", "rss_status",
	"rss_category", "rss_category_status",
	"rss_user_ideas", "rss_user_ideas_status"
)) && !$this->initComponentTemplate("", false, ""))
{
	$arResult["PAGE_MODE"] = $this->__pageMode = $componentPage;
	//$componentPage = "rss";
}

$this->IncludeComponentTemplate($componentPage);
?>