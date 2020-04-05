<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
IncludeModuleLangFile(__FILE__);

CUtil::JSPostUnescape();
/*
 * this page only for actions and get info
 *
 */
define('B_ADMIN_SUBCOUPONS',1);
define('B_ADMIN_SUBCOUPONS_LIST',true);

global $APPLICATION;
global $USER;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_discount')))
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
CModule::IncludeModule("catalog");
$boolCouponsReadOnly = !$USER->CanDoOperation('catalog_discount');

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
?>