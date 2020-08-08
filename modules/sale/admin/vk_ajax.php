<?
/** Bitrix Framework
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$arResult = array();

use Bitrix\Sale\TradingPlatform\Vk;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$arResult["ERROR"] = Loc::getMessage("SALE_VK_SALE_NOT_INSTALLED_ERROR");

if (!\Bitrix\Main\Loader::includeModule('iblock'))
	$arResult["ERROR"] = Loc::getMessage("SALE_VK_IBLOCK_NOT_INSTALLED_ERROR");

$result = false;

if (isset($arResult["ERROR"]) && $arResult["ERROR"] <> '')
{
	$arResult["RESULT"] = "ERROR";
	$arResult["ERRORS_CRITICAL"] = Vk\Journal::getCriticalErrorsMessage($exportId, $arResult["ERROR"]);
}
elseif ($APPLICATION->GetGroupRight("sale") >= "W" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
	$exportId = isset($_REQUEST['exportId']) ? trim($_REQUEST['exportId']) : '';
	
	switch ($action)
	{
		case "startFeed":
			$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
			$firstRun = isset($_REQUEST['firstRun']) ? trim($_REQUEST['firstRun']) : '';
			$firstRun = $firstRun === 'true' ? true : false;
			$logger = new Vk\Logger($exportId);
			
			if ($firstRun)
			{
//				remove flag STOP if first run
				Vk\Journal::clearStopProcessParams($exportId);
//				clear error log to preserve dvusmyslennost
				$logger->clearLog();
			}

//			run only if not STOP flag
			if (!Vk\Journal::checkStopProcessFlag($exportId))
			{
				$arResult = Vk\Feed\Manager::runProcess($exportId, $type);
				
				if ($arResult['CONTINUE'])
				{
					$arResult['PROGRESS'] = Vk\Journal::getProgressMessage($exportId, $type);
					if ($arResult['TOO_MUCH_TIMES'])
						$arResult['PROGRESS'] .= $arResult['TOO_MUCH_TIMES'];
				}
				else
				{
					$ok = isset($arResult["ERRORS_CRITICAL"]) && $arResult["ERRORS_CRITICAL"] ? false : true;
					$arResult['PROGRESS'] .= Vk\Journal::getProgressFinishMessage($ok);
				}
				
			}
			else
			{
				$arResult['PROGRESS'] .= Vk\Journal::getProgressFinishMessage(false);
				$arResult['ABORT'] = true;
				$arResult['CONTINUE'] = false;
			}

//			check not critical errors
			$errorsNormal = $logger->getErrorsList(false);
			if ($errorsNormal <> '')
				$arResult['ERRORS_NORMAL'] = $errorsNormal;
			
			$arResult['STATS_ALBUMS'] = Vk\Journal::getStatisticText('ALBUMS', $exportId);
			$arResult['STATS_PRODUCTS'] = Vk\Journal::getStatisticText('PRODUCTS', $exportId);

//			critical errors - STOP export and show message
			if (isset($arResult['ERRORS_CRITICAL']) && $arResult['ERRORS_CRITICAL'])
			{
				Vk\Journal::stopProcessParams($exportId);
				$errorsCritical = $logger->getErrorsList(true);
				if ($errorsCritical <> '')
					$arResult['ERRORS_CRITICAL'] = Vk\Journal::getCriticalErrorsMessage($exportId, $errorsCritical);
				else
					$arResult['ERRORS_CRITICAL'] = false;
			}
			
			break;
		
		
		case "clearErrorLog":
			$logger = new Vk\Logger($exportId);
			if ($logger->clearLog())
			{
				$arResult["COMPLETED"] = true;
			}
			else
			{
				$arResult["COMPLETED"] = false;
				$arResult["MESSAGE"] = Loc::getMessage("SALE_VK_CLEAR_ERROR_LOG_ERROR");
			}
			break;
		
		
		case "stopProcess":
			if (Vk\Journal::stopProcessParams($exportId))
				$arResult['COMPLETED'] = true;
			else
				$arResult["ERROR"] = 'Error during process stopped';
			
			break;
		
		
		case "loadExportMap":
			$sectionsList = new Vk\SectionsList($exportId);
			$sectionsMap = $sectionsList->getSectionMapToPrint();
			
			$arResult['COMPLETED'] = true;
			$arResult['MAP'] = $sectionsMap;
			
			break;
	}
}
else
{
	if ($arResult["ERROR"] == ''){
		$arResult["RESULT"] = "ERROR";
		$arResult["ERROR"] = Loc::getMessage("SALE_VK_ACCESS_DENIED_ERROR");
		$arResult["ERRORS_CRITICAL"].= Vk\Journal::getCriticalErrorsMessage($exportId, $arResult["ERROR"]);
	}
}

if (!isset($arResult["ERROR"]) && $arResult["RESULT"] == '')
	$arResult["RESULT"] = "OK";

if (mb_strtolower(SITE_CHARSET) != 'utf-8')
	$arResult = $APPLICATION->ConvertCharsetArray($arResult, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
print json_encode($arResult);
$APPLICATION::FinalActions();
die();
