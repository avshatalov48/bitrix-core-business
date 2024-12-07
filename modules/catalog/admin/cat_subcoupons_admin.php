<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
IncludeModuleLangFile(__FILE__);

/*
 * this page only for actions and get info
 *
 */
const B_ADMIN_SUBCOUPONS = 1;
const B_ADMIN_SUBCOUPONS_LIST = true;

global $APPLICATION;
global $USER;

CModule::IncludeModule("catalog");

$accessController = AccessController::getCurrent();
if (!($accessController->check(ActionDictionary::ACTION_CATALOG_READ) || $accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET)))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$boolCouponsReadOnly = !$accessController->check(ActionDictionary::ACTION_PRODUCT_DISCOUNT_SET);

$strSubTMP_ID = intval($_REQUEST['TMP_ID']);

$intDiscountID = intval($_REQUEST['find_discount_id']);
if (0 >= $intDiscountID)
{
	if ('' == $strSubTMP_ID)
	{
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}
}

$strSubElementAjaxPath = '/bitrix/admin/cat_subcoupons_admin.php?lang='.LANGUAGE_ID.'&find_discount_id='.$intDiscountID.'&TMP_ID='.urlencode($strSubTMP_ID);
require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/catalog/admin/templates/discount_coupon_list.php');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
