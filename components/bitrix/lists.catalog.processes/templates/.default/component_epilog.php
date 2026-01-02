<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */

if(!$arResult['ALL_PROCESSES_INSTALL'])
{
	$randString = $arResult['RAND_STRING'];
	$installButton = new Bitrix\UI\Buttons\Button([
		'text' => Loc::getMessage('LISTS_LCP_TEMPLATE_BUTTON_INSTALL'),
		'color' => Bitrix\UI\Buttons\Color::SUCCESS,
		'click' => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['CatalogProcessesClass_$randString'].installProcesses(this)"
		),
	]);
	Bitrix\UI\Toolbar\Facade\Toolbar::addButton($installButton, Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);
}

$processesButton = new Bitrix\UI\Buttons\Button([
	'text' => Loc::getMessage('LISTS_LCP_TEMPLATE_TRANSITION_PROCESSES'),
	'link' => $arResult['LISTS_URL'],
	'color' => Bitrix\UI\Buttons\Color::LIGHT_BORDER,
]);
Bitrix\UI\Toolbar\Facade\Toolbar::addButton($processesButton);
