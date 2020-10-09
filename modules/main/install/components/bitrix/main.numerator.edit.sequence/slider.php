<?php
$siteId = '';
if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
{
	$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site_id']), 0, 2);
}

if ($siteId)
{
	define('SITE_ID', $siteId);
}
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$APPLICATION->IncludeComponent(
	"bitrix:main.numerator.edit.sequence",
	'',
	[
		"NUMERATOR_ID" => $request->get('NUMERATOR_ID'),
	]
);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');