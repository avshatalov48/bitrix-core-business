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
	"bitrix:main.numerator.edit",
	'',
	[
		"NUMERATOR_TYPE"               => $request->get('NUMERATOR_TYPE'),
		"IS_HIDE_NUMERATOR_NAME"       => $request->get('IS_HIDE_NUMERATOR_NAME'),
		"IS_EMBED_FORM"                => $request->get('IS_EMBED_FORM'),
		"IS_HIDE_IS_DIRECT_NUMERATION" => $request->get('IS_HIDE_IS_DIRECT_NUMERATION'),
		"IS_SHOW_CHANGE_NUMBER"        => $request->get('IS_SHOW_CHANGE_NUMBER'),
		"NUMERATOR_ID"                 => $request->get('ID'),
	]
);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');