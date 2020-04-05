<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage('SALE_REFUND_HANDLERS_TITLE'));

$instance = \Bitrix\Main\Application::getInstance();
$context = $instance->getContext();
$request = $context->getRequest();
$handler = $request->get('handler');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$pathToSettings = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/handlers/paysystem/".$handler."/settings/refund.php";
if (Bitrix\Main\IO\File::isFileExists($pathToSettings))
{
	require $pathToSettings;
}
else
{
	LocalRedirect("sale_ps_handler_refund.php?lang=".$context->getLanguage());
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
