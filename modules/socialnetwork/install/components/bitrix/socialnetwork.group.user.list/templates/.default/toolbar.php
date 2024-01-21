<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */


use Bitrix\Main\Component\ParameterSigner;
use Bitrix\UI\Toolbar\ButtonLocation;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons;

$component = $this->getComponent();

$toolbarId = mb_strtolower($arResult['GRID_ID']) . '_toolbar';

Toolbar::addFilter([
	'GRID_ID' => $arResult['GRID_ID'],
	'FILTER_ID' => $arResult['FILTER_ID'],
	'FILTER' => $arResult['FILTER'],
	'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
	'RESET_TO_DEFAULT_MODE' => true,
	'ENABLE_LIVE_SEARCH' => true,
	'ENABLE_LABEL' => true,
	'LAZY_LOAD' => [
		'CONTROLLER' => [
			'getList' => 'socialnetwork.filter.usertogroup.getlist',
			'getField' => 'socialnetwork.filter.usertogroup.getfield',
			'componentName' => 'socialnetwork.group.user.list',
			'signedParameters' => ParameterSigner::signParameters(
				'socialnetwork.group.user.list',
				[]
			),
		]
	],
	'CONFIG' => [
		'AUTOFOCUS' => false,
	],
]);

if (
	isset($_REQUEST['IFRAME'])
	&& $_REQUEST['IFRAME'] === 'Y'
)
{
	Toolbar::deleteFavoriteStar();
}

if (!empty($arResult['TOOLBAR_BUTTONS']))
{
	foreach($arResult['TOOLBAR_BUTTONS'] as $button)
	{
		Toolbar::addButton([
			'link' => $button['LINK'],
			'color' => Buttons\Color::SUCCESS,
			'text' => $button['TITLE'],
			'click' => $button['CLICK'] ?? '',
		], ButtonLocation::AFTER_TITLE);
	}
}