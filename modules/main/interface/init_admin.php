<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");
define("ADMIN_THEME_ID", CAdminTheme::GetCurrentTheme());

global $adminPage, $adminMenu, $adminChain, $adminAjaxHelper, $adminSidePanelHelper;
$adminPage = new CAdminPage();
if(class_exists('CAdminAjaxHelper'))
{
	//updater sequence
	$adminAjaxHelper = new CAdminAjaxHelper();
}
$adminSidePanelHelper = new CAdminSidePanelHelper();
$adminMenu = new CAdminMenu();
$adminChain = new CAdminMainChain("main_navchain");

// todo: a temporary solution for blocking access to admin pages bypassing the interface
if (defined("SELF_FOLDER_URL") && !$adminSidePanelHelper->isPublicSidePanel() && !defined("INTERNAL_ADMIN_PAGE") && !isset($_REQUEST["bxpublic"]) && !isset($_REQUEST["public"]))
{
	if (IsModuleInstalled("bitrix24"))
	{
		LocalRedirect("/");
	}
	else
	{
		$APPLICATION->AuthForm("");
	}
}
