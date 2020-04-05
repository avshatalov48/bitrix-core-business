<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__)."/", "/payment_forward.php"));

$psTitle = GetMessage("SPFP_DTITLE");
$psDescription = GetMessage("SPFP_DDESCR");

$arPSCorrespondence = array();
?>