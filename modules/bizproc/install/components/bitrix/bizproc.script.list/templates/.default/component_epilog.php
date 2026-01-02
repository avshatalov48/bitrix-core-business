<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var array $arParams */

if (\Bitrix\Main\Loader::includeModule('rest'))
{
	\Bitrix\Main\UI\Extension::load(['marketplace', 'applayout']);
	$marketImportUrl = \Bitrix\Rest\Marketplace\Url::getConfigurationImportManifestUrl($arParams['PLACEMENT']);
	$marketExportUrl = \Bitrix\Rest\Marketplace\Url::getConfigurationExportElementUrl(
		$arParams['PLACEMENT'],
		$arParams['DOCUMENT_TYPE'][2],
	);

	$importButton = new \Bitrix\UI\Buttons\Button([
		'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
		'link' => htmlspecialcharsbx($marketImportUrl),
		'text' => Loc::getMessage('BIZPROC_SCRIPT_LIST_TITLE_MARKETPLACE_IMPORT'),
	]);

	$exportButton = new \Bitrix\UI\Buttons\Button([
		'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
		'dataset' => [
			'url' => htmlspecialcharsbx($marketExportUrl),
		],
		'text' => Loc::getMessage('BIZPROC_SCRIPT_LIST_TITLE_MARKETPLACE_EXPORT'),
	]);
	$exportButton->addAttribute('id', 'bp_export_scenario');

	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($importButton);
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($exportButton);
}


$createScriptButton = new \Bitrix\UI\Buttons\Button([
	'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
	'text' => Loc::getMessage('BIZPROC_SCRIPT_LIST_TITLE_BUTTON_ADD_SCENARIO_MSGVER_1'),
	'state' => $arResult['canCreateScript'] ? '' :  \Bitrix\UI\Buttons\State::DISABLED,
	'icon' => \Bitrix\UI\Buttons\Icon::ADD,
]);
$createScriptButton->addAttribute('id', 'bp_add_scenario');
\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($createScriptButton);

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();


