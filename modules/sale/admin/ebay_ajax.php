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

use \Bitrix\Sale\TradingPlatform\Logger;
use \Bitrix\Sale\TradingPlatform\Ebay\Ebay;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = "Module sale is not installed!";

$result = false;

if(isset($arResult["ERROR"]) <= 0 && $APPLICATION->GetGroupRight("sale") >= "W" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';
	$siteId = isset($_REQUEST['siteId']) ? trim($_REQUEST['siteId']): '';

	switch ($action)
	{
		case "startFeed":

			$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']): '';
			$startPosition = isset($_REQUEST['startPos']) ? trim($_REQUEST['startPos']) : '';

			try
			{
				$ebayFeed = \Bitrix\Sale\TradingPlatform\Ebay\Feed\Manager::createFeed($type, $siteId);
				$ebayFeed->processData($startPosition);

				if($type != "ORDER")
				{
					$queue = \Bitrix\Sale\TradingPlatform\Ebay\Feed\Manager::createSftpQueue($type, $siteId);
					$queue->sendData();
				}

				$arResult["COMPLETED"] = true;
			}
			catch(\Bitrix\Sale\TradingPlatform\TimeIsOverException $e)
			{
				$arResult["END_POS"] = $e->getEndPosition();
			}
			catch(\Exception $e)
			{
				Ebay::log(Logger::LOG_LEVEL_ERROR, "EBAY_FEED_ERROR", $type, $e->getMessage(), $siteId);
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		case "refreshCategoriesData":
			try
			{
				$categories = new \Bitrix\Sale\TradingPlatform\Ebay\Api\Categories($siteId);
				$arResult["COUNT"] = $categories->refreshTableData();
			}
			catch(\Bitrix\Main\SystemException $e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		case "refreshCategoriesPropsData":
			try
			{
				$categoriesProps = new \Bitrix\Sale\TradingPlatform\Ebay\Api\Categories($siteId);
				$arResult["COUNT"] = $categoriesProps->refreshVariationsTableData();
			}
			catch(\Bitrix\Main\SystemException$e)
			{
				$arResult["ERROR"] = $e->getMessage();
			}

			break;

		default:
			$arResult["ERROR"] = "Wrong action";
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