<?php
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$region = \Bitrix\Main\Application::getInstance()->getLicense()->getRegion();
if ($region !== null && $region !== 'ru')
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

	Loader::includeModule('sale');

	IncludeModuleLangFile(__FILE__);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	global $APPLICATION;
	$APPLICATION->IncludeComponent(
		'bitrix:sale.facebook.conversion',
		'.default',
		[
			'eventName' => 'AddPaymentInfo',
		]
	);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
}
else
{
	include($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/404.php");
}
