<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;

Extension::load(
	[
		'sidepanel',
		'ui.tilegrid',
		'ui.icons',
		'ui.fontawesome4',
		'ui.fonts.opensans',
		'ui.notification',
		'rest.integration',
		'ui.fonts.opensans',
	]
);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings no-background no-hidden'
);
