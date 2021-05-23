<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!$GLOBALS["USER"]->IsAuthorized())
	return;

$arParams["PAGE_ID"] = Trim($arParams["PAGE_ID"]);
if ($arParams["PAGE_ID"] == '')
	$arParams["PAGE_ID"] = "messages_input";

if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGES_INPUT"] = trim($arParams["PATH_TO_MESSAGES_INPUT"]);
if($arParams["PATH_TO_MESSAGES_INPUT"] == '')
	$arParams["PATH_TO_MESSAGES_INPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_input");

$arParams["PATH_TO_MESSAGES_OUTPUT"] = trim($arParams["PATH_TO_MESSAGES_OUTPUT"]);
if($arParams["PATH_TO_MESSAGES_OUTPUT"] == '')
	$arParams["PATH_TO_MESSAGES_OUTPUT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_output");

$arParams["PATH_TO_USER_BAN"] = trim($arParams["PATH_TO_USER_BAN"]);
if($arParams["PATH_TO_USER_BAN"] == '')
	$arParams["PATH_TO_USER_BAN"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_ban");

$arParams["PATH_TO_MESSAGES_USERS"] = trim($arParams["PATH_TO_MESSAGES_USERS"]);
if ($arParams["PATH_TO_MESSAGES_USERS"] == '')
	$arParams["PATH_TO_MESSAGES_USERS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_users");

$arParams["PATH_TO_LOG"] = trim($arParams["PATH_TO_LOG"]);
if ($arParams["PATH_TO_LOG"] == '')
	$arParams["PATH_TO_LOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=log");

$arParams["PATH_TO_TASKS"] = trim($arParams["PATH_TO_TASKS"]);
if ($arParams["PATH_TO_TASKS"] == '')
	$arParams["PATH_TO_TASKS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=tasks");

$arParams["PATH_TO_SUBSCRIBE"] = trim($arParams["PATH_TO_SUBSCRIBE"]);
if ($arParams["PATH_TO_SUBSCRIBE"] == '')
	$arParams["PATH_TO_SUBSCRIBE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=subscribe");

$arParams["PATH_TO_BIZPROC"] = trim($arParams["PATH_TO_BIZPROC"]);
if ($arParams["PATH_TO_BIZPROC"] == '')
	$arParams["PATH_TO_BIZPROC"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bizproc");

	
$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $GLOBALS["USER"]->GetID()));
$arResult["Urls"]["MessagesUsers"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_USERS"], array());
$arResult["Urls"]["MessagesInput"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_INPUT"], array());
$arResult["Urls"]["MessagesOutput"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_OUTPUT"], array());
$arResult["Urls"]["UserBan"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_BAN"], array());
$arResult["Urls"]["Log"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LOG"], array());
//$arResult["Urls"]["Tasks"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS"], array());
$arResult["Urls"]["Subscribe"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SUBSCRIBE"], array());

if(IsModuleInstalled("bizproc") && CBXFeatures::IsFeatureEnabled("BizProc"))
	$arResult["Urls"]["BizProc"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BIZPROC"], array());

/* Themes */

$arThemes = array();
$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/socialnetwork.menu/templates/.default/themes/");
$dir = $sTemplateDirFull;
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$parent = & $this->GetParent();
if (is_object($parent) && $parent->__name <> '')
{
	$parent = & $parent->GetParent();

	if (is_object($parent) && is_array($parent->arParams) && array_key_exists("SM_THEME", $parent->arParams) && $parent->arParams["SM_THEME"] <> '')
		$arParams["SM_THEME"] = $parent->arParams["SM_THEME"];
	else
	{
		$site_template = CSite::GetCurTemplate();

		if (mb_strpos($site_template, "bright") === 0)
			$arParams["SM_THEME"] = "grey";
		else
		{
			$theme_tmp_id = COption::GetOptionString("main", "wizard_".$site_template."_sm_theme_id");
			if ($theme_tmp_id <> '')
				$theme_id = $theme_tmp_id;
			elseif (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
				$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id_extranet");
			else
				$theme_id = COption::GetOptionString("main", "wizard_".$site_template."_theme_id");

			if ($theme_id <> '')
				$arParams["SM_THEME"] = $theme_id;
			else
				$arParams["SM_THEME"] = "grey";
		}
	}
}

if (!in_array($arParams["SM_THEME"], $arThemes))
	$arParams["SM_THEME"] = (in_array("grey", $arThemes) ? "grey" : $arThemes[0]);

if (in_array($arParams["SM_THEME"], $arThemes))
	$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.menu/templates/.default/themes/".$arParams["SM_THEME"]."/style.css");

/* -- Themes */

$this->IncludeComponentTemplate();
?>