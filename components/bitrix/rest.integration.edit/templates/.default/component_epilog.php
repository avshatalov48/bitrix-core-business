<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'sidepanel',
	'marketplace',
	'ui.buttons',
	'ui.forms',
	'ui.icons',
	'ui.alerts',
	'ui.dialogs.messagebox',
]);

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings');