<?
/** @global CUser $USER */
/** @global int $IBLOCK_ID */

use Bitrix\Main;
use Bitrix\Iblock;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$selfFolderUrl = $adminPage->getSelfFolderUrl();

/*
 * this page only for actions and get info
 *
 */
const B_ADMIN_SUBELEMENTS = 1;
const B_ADMIN_SUBELEMENTS_LIST = true;

$boolSubBizproc = CModule::IncludeModule("bizproc");
$boolSubWorkFlow = CModule::IncludeModule("workflow");

global $APPLICATION;

$strSubTMP_ID = 0;
if (array_key_exists('TMP_ID', $_REQUEST))
	$strSubTMP_ID = intval($_REQUEST['TMP_ID']);

$strSubIBlockType = '';
$arSubIBlockType = false;
if (array_key_exists('type', $_REQUEST))
	$strSubIBlockType = strval($_REQUEST['type']);
if ('' != $strSubIBlockType)
{
	$arSubIBlockType = CIBlockType::GetByIDLang($strSubIBlockType, LANGUAGE_ID);
}
if (false === $arSubIBlockType)
	$APPLICATION->AuthForm(GetMessage("IBLOCK_BAD_BLOCK_TYPE_ID"));

$intSubIBlockID = 0;
if (array_key_exists('IBLOCK_ID', $_REQUEST))
	$intSubIBlockID = intval($IBLOCK_ID);

$bBadBlock = true;
if (0 < $intSubIBlockID)
{
	$arSubIBlock = CIBlock::GetArrayByID($intSubIBlockID);
	if ($arSubIBlock)
	{
		$bBadBlock = !CIBlockRights::UserHasRightTo($intSubIBlockID, $intSubIBlockID, "iblock_admin_display");
	}
}

if ($bBadBlock)
{
	$APPLICATION->SetTitle($arSubIBlockType["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));?>
	<a href="<?=$selfFolderUrl?>iblock_admin.php?lang=<?echo LANGUAGE_ID?>&type=<?echo htmlspecialcharsbx($strSubIBlockType)?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arSubIBlock["SITE_ID"] = array();
$rsSites = CIBlock::GetSite($intSubIBlockID);
while($arSite = $rsSites->Fetch())
	$arSubIBlock["SITE_ID"][] = $arSite["LID"];

$boolSubWorkFlow = $boolSubBizproc && $arSubIBlock["WORKFLOW"] != "N";
$boolSubBizproc = $boolSubBizproc && $arSubIBlock["BIZPROC"] != "N";

$boolSubCatalog = false;
$bCatalog = CModule::IncludeModule("catalog");
if ($bCatalog)
{
	$arSubCatalog = CCatalogSKU::GetInfoByOfferIBlock($arSubIBlock["ID"]);
	$boolSubCatalog = (!empty($arSubCatalog) && is_array($arSubCatalog));
	if (!$boolSubCatalog)
	{
		die();
	}
	if (
		!(
			AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			|| AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_EDIT)
		)
	)
	{
		$boolSubCatalog = false;
	}
}
else
{
	die();
}

$intSubPropValue = 0;
if (array_key_exists('find_el_property_'.$arSubCatalog['SKU_PROPERTY_ID'], $_REQUEST))
	$intSubPropValue = intval($_REQUEST['find_el_property_'.$arSubCatalog['SKU_PROPERTY_ID']]);
if (0 >= $intSubPropValue)
{
	if (0 == $strSubTMP_ID)
	{
		die();
	}
}

$request = Main\Context::getCurrent()->getRequest();
$urlBuilderManager = Iblock\Url\AdminPage\BuilderManager::getInstance();
$urlBuilder = null;
$urlBuilderId = (string)$request->get('urlBuilderId') ;
if ($urlBuilderId !== '')
{
	$urlBuilder = $urlBuilderManager->getBuilder($urlBuilderId);
}
if ($urlBuilder === null)
{
	$urlBuilder = $urlBuilderManager->getBuilder();
}
unset($urlBuilderManager);
if ($urlBuilder === null)
{
	$APPLICATION->SetTitle($arSubIBlockType["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBEL_ERR_BUILDER_ADSENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$urlBuilderId = $urlBuilder->getId();
//TODO:: temporary hack for open subelements in public inventory section
if (in_array(
	$urlBuilderId,
	[
		'SHOP',
		'CRM',
		'INVENTORY',
	]
))
{
	if (!defined('SELF_FOLDER_URL'))
	{
		define('SELF_FOLDER_URL', '/shop/settings/');
	}
}

$additionalParams = (defined("SELF_FOLDER_URL") ? "&public=y" : "");
$strSubElementAjaxPath = '/bitrix/tools/iblock/iblock_subelement_admin.php?WF=Y&IBLOCK_ID=' . $intSubIBlockID
	. '&type=' . urlencode($strSubIBlockType) .'&lang=' . LANGUAGE_ID
	. '&find_section_section=0&find_el_property_' . $arSubCatalog['SKU_PROPERTY_ID'] . '=' . $intSubPropValue
	. '&TMP_ID=' . urlencode($strSubTMP_ID)
	. '&urlBuilderId=' . urlencode($urlBuilderId)
	. $additionalParams
;
require($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/iblock/admin/templates/iblock_subelement_list.php');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");
