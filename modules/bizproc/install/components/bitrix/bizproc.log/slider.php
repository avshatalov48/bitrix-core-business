<?php

define('STOP_STATISTICS', true);

$siteId = '';
if (isset($_REQUEST['site_id']) && is_string($_REQUEST['site_id']))
{
	$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['site_id']), 0, 2);
}
if ($siteId !== '')
{
	define('SITE_ID', $siteId);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
?>
<head><? $APPLICATION->showHead(); ?></head>
<?php
$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('BPABL_SLIDER_TITLE'));
if (!empty($_REQUEST['WORKFLOW_ID']))
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		[
			'POPUP_COMPONENT_NAME' => 'bitrix:bizproc.log',
			'POPUP_COMPONENT_TEMPLATE_NAME' => 'modern',
			'POPUP_COMPONENT_PARAMS' => [
				'COMPONENT_VERSION' => 2,
				'ID' => (string)$_REQUEST['WORKFLOW_ID'],
				'SET_TITLE' => 'N',
				'AJAX_MODE' => 'Y',
				'AJAX_OPTION_JUMP' => 'N',
				'AJAX_OPTION_HISTORY' => 'N'
			],
		]
	);
}
else
{
	$APPLICATION->ShowTitle();
	ShowError(\Bitrix\Main\Localization\Loc::getMessage('BPABL_SLIDER_ERROR'));
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
