<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/cash.php"));

$psTitle = GetMessage("SCSP_DTITLE");
$psDescription = GetMessage("SCSP_DDESCR");
$isAvailable = \Bitrix\Sale\PaySystem\Manager::HANDLER_AVAILABLE_FALSE;

$arPSCorrespondence = array();

?>