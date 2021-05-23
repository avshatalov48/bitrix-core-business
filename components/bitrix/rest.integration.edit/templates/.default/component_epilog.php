<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;

CUtil::InitJSCore(
	[
		'sidepanel',
		'marketplace'
	]
);
Extension::load(
	[
		'ui.buttons',
		'ui.forms',
		'ui.icons',
		'ui.alerts',
		'ui.dialogs.messagebox'
	]
);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings');