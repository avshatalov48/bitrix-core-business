<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @global CMain $APPLICATION
 */

if(empty($arParams['MENU_EVENT_MODULE']) || empty($arParams['MENU_EVENT']))
{
	return;
}

AddEventHandler($arParams['MENU_EVENT_MODULE'], $arParams['MENU_EVENT'], function ($placement, $eventParam, &$menu)
{
	/** @var \BizprocScriptPlacementComponent $this */

	$scriptList = \Bitrix\Bizproc\Automation\Script\Manager::getListByPlacement($placement);
	$appMenu = [];

	if(count($scriptList) > 0)
	{
		$placementParam = array(
			'ID' => intval($eventParam['ID']),
		);

		$runText = \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_RUN');
		$editText = \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_EDIT');

		foreach($scriptList as $script)
		{
			$itemText = $script['NAME'] <> ''
				? $script['NAME']
				: $script['APP_NAME'];

			$appMenu[] = [
				'TITLE' => $script['APP_NAME'],
				'TEXT' => $itemText,
				'MENU' => [
					[
						'TEXT' => $runText,
						'ONCLICK' => "BX.Bizproc.ScriptPlacementMenu.runScript("
							.intval($script['ID']).", ".intval($eventParam['ID'])
							.")",
					],
					[
						'TEXT' => $editText,
						'HREF' => "/bizproc/script/?id=".intval($script['ID']).'#edit'
					]
				],
			];
		}

		$appMenu[] = array('SEPARATOR' => true);
	}

	$appMenu[] = array(
		'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_CREATE'),
		'ONCLICK' => "BX.Bizproc.ScriptPlacementMenu.createScript(".\Bitrix\Main\Web\Json::encode([
			'MODULE_ID' => $this->arParams['MODULE_ID'],
			'ENTITY' => $this->arParams['ENTITY'],
			'DOCUMENT_TYPE' => $this->arParams['DOCUMENT_TYPE'],
			'DOCUMENT_STATUS' => 'SCRIPT:'.$placement,
		]).", function(){BX.Main.gridManager.reload('".$eventParam['GRID_ID']."')})",
	);

	$appMenu[] = array(
		'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_TITLE'),
		'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_TEXT_MORE'),
		'ONCLICK' => "BX.rest.Marketplace.open({PLACEMENT:'".\CUtil::JSEscape($placement)."'})",
	);

	$menu[] = array(
		'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_TITLE'),
		'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('BP_SCRIPT_MENU_ITEM_TEXT'),
		'MENU' => $appMenu,
	);

});