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

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

if(!function_exists('restMenuBuildEventHandler'))
{
	function restMenuBuildEventHandler($placement, $eventParam, &$menu)
	{
		$appList = \Bitrix\Rest\HandlerHelper::getApplicationList($placement);
		if(count($appList) > 0)
		{
			$placementParam = array(
				'ID' => intval($eventParam['ID']),
			);

			$appMenu = array();
			foreach($appList as $app)
			{
				$itemText = strlen($app['TITLE']) > 0
					? $app['TITLE']
					: $app['APP_NAME'];

				$appMenu[] = array(
					'TITLE' => $app['APP_NAME'],
					'TEXT' => $itemText,
					'ONCLICK' => "BX.rest.AppLayout.getPlacement('".\CUtil::JSEscape($placement)."').load('".intval($app['ID'])."', '".intval($app['APP_ID'])."', ".\CUtil::PhpToJSObject($placementParam).", function(){BX.Main.gridManager.reload('".$eventParam['GRID_ID']."')});"
				);
			}

			$appMenu[] = array('SEPARATOR' => true);
			$appMenu[] = array(
				'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TITLE'),
				'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TEXT_MORE'),
				'ONCLICK' => "BX.rest.Marketplace.open({PLACEMENT:'".\CUtil::JSEscape($placement)."'})",
			);

			$menu[] = array(
				'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TITLE'),
				'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TEXT'),
				'MENU' => $appMenu,
			);
		}
		else
		{
			$menu[] = array(
				'TITLE' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TITLE'),
				'TEXT' => \Bitrix\Main\Localization\Loc::getMessage('REST_AP_MENU_ITEM_TEXT'),
				'ONCLICK' => "BX.rest.Marketplace.open({PLACEMENT:'".\CUtil::JSEscape($placement)."'})",
			);
		}
	}
}

AddEventHandler($arParams['MENU_EVENT_MODULE'], $arParams['MENU_EVENT'], 'restMenuBuildEventHandler');