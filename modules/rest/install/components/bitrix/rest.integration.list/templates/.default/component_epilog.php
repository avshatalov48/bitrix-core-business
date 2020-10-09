<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;

CUtil::InitJSCore(
	[
		'sidepanel'
	]
);

Extension::load(
	[
		'ui.tilegrid',
		'ui.icons',
		'ui.fontawesome4',
		'ui.fonts.opensans',
		'ui.notification'
	]
);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings no-background no-hidden'
);
