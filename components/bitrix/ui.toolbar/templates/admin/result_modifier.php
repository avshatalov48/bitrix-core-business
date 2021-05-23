<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $adminSidePanelHelper;
if (!is_object($adminSidePanelHelper))
{
	require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/interface/admin_lib.php');
	$adminSidePanelHelper = new CAdminSidePanelHelper();
}

$currentFavId = null;
if ($adminSidePanelHelper->isSidePanel())
{
	$requestUri = CHTTP::urlDeleteParams($_SERVER['REQUEST_URI'], ['IFRAME', 'IFRAME_TYPE']);
	$currentFavId = CFavorites::getIDByUrl($requestUri);
}
else
{
	$currentFavId = CFavorites::getIDByUrl($_SERVER['REQUEST_URI']);
}

$arResult['FAVORITE_STAR'] = '<span class="ui-toolbar-star" id="uiToolbarStar"></span>';
$arResult['CURRENT_FAVORITE_ID'] = $currentFavId;