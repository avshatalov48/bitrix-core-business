<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?

$langFile = GetLangFileName(dirname(__FILE__)."/", "/bill.php");

if(file_exists($langFile))
	include($langFile);

$psDescription = GetMessage("SBLP_DDESCR");

$isAffordPdf = true;

include \Bitrix\Main\Application::getDocumentRoot().'/bitrix/modules/sale/handlers/paysystem/bill/.description.php';

$data['IS_AVAILABLE'] = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;