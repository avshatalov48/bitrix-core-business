<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === 'bitrix24')
{
	\Bitrix\Main\UI\Extension::load('ui.icons.disk');
}
else
{
	$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');
}

\Bitrix\Main\UI\Extension::load([
	'ui.buttons',
	'ui.icons',
	'ui.buttons.icons',
	'ui.alerts',
	'ui.notification',
	'translate.process',
]);
