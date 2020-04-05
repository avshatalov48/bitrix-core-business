<?
/** Bitrix Framework
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$arResult = array();

use \Bitrix\Main\Localization\Loc,
	\Bitrix\Sale\TradingPlatform;
Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Module sale is not installed!";

if(isset($arResult["ERROR"]) <= 0 && $USER->IsAdmin() && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

	switch ($action)
	{
		case "get_categories_list":

			$topCategory = isset($_REQUEST['topCategory']) ? intval($_REQUEST['topCategory']): '';
			$categoriesList = array();

			$categoriesRes = TradingPlatform\Ebay\CategoryTable::getList(array(
				'select' =>array('CATEGORY_ID', 'NAME', 'LEVEL'),
				'order' => array('NAME' =>'ASC'),
				'filter'=> array('PARENT_ID' => $topCategory)
			));

			while($category = $categoriesRes->fetch())
				$categoriesList[] = array($category["CATEGORY_ID"], $category["NAME"]);

			$arResult["CATEGORIES_LIST"] = $categoriesList;

			break;

		case "get_variations_list":

			$siteId = isset($_REQUEST['siteId']) ? trim($_REQUEST['siteId']): '';
			$category = isset($_REQUEST['category']) ? trim($_REQUEST['category']): '';
			$arResult["VARIATIONS_LIST"] = TradingPlatform\Ebay\Helper::getEbayCategoryVariations($category, $siteId);

			break;

		case "get_variation_values":

			$variationId = isset($_REQUEST['variationId']) ? trim($_REQUEST['variationId']): '';
			$variationRes = Bitrix\Sale\TradingPlatform\Ebay\CategoryVariationTable::getById($variationId);

			if($variation = $variationRes->fetch())
				$arResult["VARIATION_VALUES"] = $variation["VALUE"];

			break;

		case "set_category_property_link":

			if (!\Bitrix\Main\Loader::includeModule('iblock'))
				$arResult["ERROR"] = "Can't include module Iblock!";

			$bitrixCategoryId = isset($_REQUEST['bitrixCategoryId']) ? trim($_REQUEST['bitrixCategoryId']): '';
			$properyId = isset($_REQUEST['properyId']) ? trim($_REQUEST['properyId']): '';

			CIBlockSectionPropertyLink::Add($bitrixCategoryId, $properyId);
			break;

		case "get_category_children":

			$ebayCategoryId = isset($_REQUEST['ebayCategoryId']) ? trim($_REQUEST['ebayCategoryId']): '';

			$categoriesRes = TradingPlatform\Ebay\CategoryTable::getList( array(
				'select' =>array('CATEGORY_ID', 'PARENT_ID', 'NAME'),
				'order' => array('NAME' =>'ASC'),
				'filter' => array('PARENT_ID' => $ebayCategoryId)
			));

			while($cat = $categoriesRes->fetch())
				if($cat["CATEGORY_ID"] != $ebayCategoryId)
					$arResult["CATEGORY_CHILDREN"][$cat["CATEGORY_ID"]] =  $cat;

			break;

		case "delete_category_map":

			$bitrixCategoryId = isset($_REQUEST['bitrixCategoryId']) ? intval($_REQUEST['bitrixCategoryId']) : 0;
			$iBlockId = isset($_REQUEST['iBlockId']) ? intval($_REQUEST['iBlockId']) : 0;

			$categoryEntityId = TradingPlatform\Ebay\MapHelper::getCategoryEntityId($iBlockId);

			$catMapRes = TradingPlatform\MapTable::getList(array(
				"filter" => array(
					"ENTITY_ID" => $categoryEntityId,
					"VALUE_INTERNAL" => $bitrixCategoryId
				)
			));

			if($res = $catMapRes->fetch())
			{
				$catVarEntId = TradingPlatform\Ebay\MapHelper::getCategoryVariationEntityId($iBlockId, $res['VALUE_EXTERNAL']);
				TradingPlatform\MapTable::deleteByMapEntityId($catVarEntId);
				TradingPlatform\MapTable::delete($res['ID']);
			}

			break;

		default:
			$arResult["ERROR"] = "Wrong action!";
			break;
	}
}
else
{
	if(strlen($arResult["ERROR"]) <= 0)
		$arResult["ERROR"] = "Access denied";
}

if(isset($arResult["ERROR"]))
	$arResult["RESULT"] = "ERROR";
else
	$arResult["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
die(json_encode($arResult));