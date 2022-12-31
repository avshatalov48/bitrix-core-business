<?php

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$defaultStoreId = 0;
$defaultStoreName = '';
$allowCreateItem = false;

if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$controller = AccessController::getCurrent();

	$allowStoresIds = $controller->getPermissionValue(ActionDictionary::ACTION_STORE_VIEW) ?? [];
	$allAllowed = in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowStoresIds, true);

	$storeId = $controller->getAllowedDefaultStoreId();
	if (isset($storeId))
	{
		$storeData = \Bitrix\Catalog\StoreTable::getRow([
			'select' => [
				'ID',
				'TITLE',
			],
			'filter' => [
				'=ID' => $storeId,
			],
		]);

		$defaultStoreId = $storeData['ID'];
		$defaultStoreName = $storeData['TITLE'];
	}

	$allowCreateItem = $allAllowed && $controller->check(ActionDictionary::ACTION_STORE_MODIFY);
}

return [
	'css' => 'dist/store-selector.bundle.css',
	'js' => 'dist/store-selector.bundle.js',
	'rel' => [
		'ui.forms',
		'ui.hint',
		'main.core.events',
		'main.core',
		'ui.entity-selector',
		'catalog.store-selector',
		'ui.notification',
		'catalog.product-model',
	],
	'skip_core' => false,
	'settings' => [
		'defaultStoreId' => $defaultStoreId,
		'defaultStoreName' => $defaultStoreName,
		'allowCreateItem' => $allowCreateItem,
		'disableByRights' => empty($allowStoresIds),
	],
];
