<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var array $arResult */
/** @var array $arParams */

$jsClass = 'ListsIblockClass_' . $arResult['RAND_STRING'];
$isLiveFeed = $arParams['IBLOCK_TYPE_ID'] === COption::GetOptionString('lists', 'livefeed_iblock_type_id');

if ($arParams['CAN_EDIT'])
{
	$addNewButton = new Bitrix\UI\Buttons\Button([
		'text' => Loc::getMessage('CT_BLL_TOOLBAR_ADD_NEW'),
		'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
		'link' => $arResult['LIST_EDIT_URL'],
	]);
	Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addNewButton, Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);

	if ($isLiveFeed)
	{
		$catalogButton = new Bitrix\UI\Buttons\Button([
			'text' => Loc::getMessage('CT_BLL_TOOLBAR_TRANSITION_PROCESSES'),
			'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
			'link' => $arParams['CATALOG_PROCESSES_URL']
		]);
		Bitrix\UI\Toolbar\Facade\Toolbar::addButton($catalogButton);
	}
}

if ($arParams['IBLOCK_TYPE_ID'] !== 'lists' && $arParams['IBLOCK_TYPE_ID'] !== 'lists_socnet' && empty($arResult['ITEMS']))
{
	$addDefaultButton = new Bitrix\UI\Buttons\Button([
		'text' => Loc::getMessage('CT_BLL_TOOLBAR_ADD_DEFAULT'),
		'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
		'click' => new \Bitrix\UI\Buttons\JsCode("BX.Lists['$jsClass'].createDefaultProcesses(this)"),
	]);
	Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addDefaultButton);
}
