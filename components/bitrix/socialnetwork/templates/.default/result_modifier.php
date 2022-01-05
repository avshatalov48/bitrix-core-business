<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

if (empty($arParams["RATING_TYPE"]))
{
	$arParams["RATING_TYPE"] = COption::GetOptionString("main", "rating_vote_template", Option::get("main", "rating_vote_type", "standart") === "like"? "like": "standart");
}

$arParams["RATING_TYPE"] = ($arParams["RATING_TYPE"] === "like_graphic" ? "like" : ($arParams["RATING_TYPE"] === "standart" ? "standart_text" : $arParams["RATING_TYPE"]));
if ($this->__page === "user_files_menu" || $this->__page === "group_files_menu")
{
	return true;
}

if (mb_strpos($this->__page, "user_files") !== false || mb_strpos($this->__page, "group_files") !== false)
{
	$prefix = (mb_strpos($this->__page, "user_files") !== false ? "user_files" : "group_files");
	$page_name = mb_substr($this->__page, mb_strlen($prefix) + 1);

	$this->__component->__count_chain_item = count($APPLICATION->arAdditionalChain);
	$this->__component->__buffer_template = false;
	$this->__component->__template_html = "";

	if (in_array($page_name, array("section_edit_simple", "element_upload", "webdav_bizproc_workflow_edit", "webdav_bizproc_log")))
	{
		$sTempatePage = $this->__page;
		$sTempateFile = $this->__file;
		$this->__component->IncludeComponentTemplate($prefix."_menu");
		$this->__page = $sTempatePage;
		$this->__file = $sTempateFile;
	}
	else
	{
		$this->__component->__socnet_page = $this->__page;
		$this->__component->__buffer_template = true;
		ob_start();
	}
}

if (ModuleManager::isModuleInstalled('webdav'))
{
	?>
	<script type="text/javascript">
		var phpVars;
		if (typeof(phpVars) != "object")
			var phpVars = {};
		phpVars.cookiePrefix = '<?=htmlspecialcharsbx(CUtil::JSEscape(COption::GetOptionString("main", "cookie_name", "BITRIX_SM")))?>';
		phpVars.titlePrefix = '<?=htmlspecialcharsbx(CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"])))?> - ';
		phpVars.messLoading = '<?=CUtil::JSEscape(GetMessage("SONET_LOADING"))?>';
		phpVars.LANGUAGE_ID = '<?=CUtil::JSEscape(LANGUAGE_ID)?>';
		phpVars.bitrix_sessid = '<?=bitrix_sessid()?>';
		if (!phpVars.ADMIN_THEME_ID)
			phpVars.ADMIN_THEME_ID = '.default';
		if (typeof oObjectWD != "object")
			var oObjectWD = {};
	</script>
	<?php
}

$arParams['IMAGE_MAX_WIDTH'] = 600;
$arParams['IMAGE_MAX_HEIGHT'] = 600;
