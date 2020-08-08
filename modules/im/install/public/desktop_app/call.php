<?
define("BX_SKIP_USER_LIMIT_CHECK", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

if (!CModule::IncludeModule('im') || !$USER->GetID())
	return;

$APPLICATION->IncludeComponent("bitrix:im.call", "");

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>
