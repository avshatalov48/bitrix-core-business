<?php
##############################################
# Bitrix Site Manager 6                      #
# Copyright (c) 2002-2007 Bitrix             #
# https://www.bitrixsoft.com                 #
# admin@bitrixsoft.com                       #
##############################################

/** @global CMain $APPLICATION */

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale;

require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php";

if ($APPLICATION->GetGroupRight("sale") < "R")
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

IncludeModuleLangFile(__FILE__);

if (Loader::includeModule('sale') && !Sale\Configuration::isCanUse1c())
{
	LocalRedirect('/bitrix/admin/');
}

$request = Context::getCurrent()->getRequest();

$aSTabs = array();
if(IsModuleInstalled("catalog"))
{
	$aSTabs[] = array(
		"DIV" => "edit_catalog",
		"TAB" => GetMessage("MAIN_1C_CATALOG_TAB"),
		"TITLE" => GetMessage("MAIN_1C_CATALOG_TAB_TITLE"),
		"FILE" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/1c_admin.php",
		"NOTE" => GetMessage("MAIN_1C_CATALOG_NOTE"),
	);
	$aSTabs[] = array(
		"DIV" => "edit_get_catalog",
		"TAB" => GetMessage("MAIN_1CE_CATALOG_TAB"),
		"TITLE" => GetMessage("MAIN_1CE_CATALOG_TAB_TITLE"),
		"FILE" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/admin/1ce_admin.php",
	);
}
if(IsModuleInstalled("sale"))
{
	$aSTabs[] = array(
		"DIV" => "edit_sale",
		"TAB" => GetMessage("MAIN_1C_SALE_TAB"),
		"TITLE" => GetMessage("MAIN_1C_SALE_TAB_TITLE"),
		"FILE" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_admin_inc.php",
	);
	/*$aSTabs[] = array(
		"DIV" => "edit_sale_profile",
		"TAB" => GetMessage("MAIN_1C_SALE_PROFILE_TAB"),
		"TITLE" => GetMessage("MAIN_1C_SALE_PROFILE_TITLE"),
		"FILE" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_admin_profile.php",
	);*/
	$aSTabs[] = array(
		"DIV" => "edit_sale_bizval",
		"TAB" => GetMessage("MAIN_1C_SALE_PROFILE_TAB"),
		"TITLE" => GetMessage("MAIN_1C_SALE_PROFILE_TITLE"),
		"FILE" => $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/admin/1c_admin_bizval.php",
	);
}

if(count($aSTabs)<1)
{
	$aSTabs[] = array(
		"DIV" => "edit_none",
		"TAB" => GetMessage("MAIN_1C_TAB"),
		"TITLE" => GetMessage("MAIN_1C_TAB_TITLE"),
	);
}

$tabControl = new CAdminTabControl("tabControl", $aSTabs);

if ($request->isPost() && $request->getPost('Update') !== null && check_bitrix_sessid())
{
	foreach($aSTabs as $arTab)
	{
		if ($arTab["FILE"] ?? '')
		{
			include($arTab["FILE"]);
		}
	}
	LocalRedirect($APPLICATION->GetCurPage()."?lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
}


$APPLICATION->SetTitle(GetMessage("MAIN_1C_TITLE"));
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';
?>

<form method="post" action="<?= $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>">
<?php
$tabControl->Begin();

foreach($aSTabs as $arTab)
{
	$tabControl->BeginNextTab();
	if ($arTab["FILE"] ?? '')
	{
		include($arTab["FILE"]);
	}
}

$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" class="adm-btn-save">
	<?=bitrix_sessid_post();?>
<?php
$tabControl->End();
?>
</form>
<?php
$bNote = false;
foreach($aSTabs as $arTab)
{
	if ($arTab["NOTE"] ?? '')
	{
		$bNote = true;
	}
}
if($bNote):
	echo BeginNote();
	?><table class="message"><tr><td valign="center"><div class="icon-error"></div></td><td><?php
	foreach($aSTabs as $arTab)
	{
		if ($arTab["NOTE"] ?? ''):
			?><?= $arTab["NOTE"]; ?><br><?php
		endif;
	}
	?></tr></table><?php
	echo EndNote();
endif;

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
