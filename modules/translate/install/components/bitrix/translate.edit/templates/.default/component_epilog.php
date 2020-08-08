<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'main.loader',
	'ui.buttons',
	'ui.icons',
	'ui.buttons.icons',
	'ui.alerts',
	'ui.notification',
	'translate.process',
]);
