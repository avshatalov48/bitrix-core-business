<?
define("NO_KEEP_STATISTIC", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");?>

<?$APPLICATION->IncludeComponent("bitrix:webservice.sale", ".default", Array());?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>