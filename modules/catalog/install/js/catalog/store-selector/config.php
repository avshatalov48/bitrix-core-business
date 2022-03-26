<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$defaultStoreId = 0;
$defaultStoreName = '';
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$storeData = \Bitrix\Catalog\StoreTable::getList([
		'select' => ['ID', 'TITLE', 'IS_DEFAULT']
	]);

	while ($store = $storeData->fetch())
	{
		if ($defaultStoreId === 0)
		{
			$defaultStoreId = $store['ID'];
			$defaultStoreName = $store['TITLE'];
		}

		if ($store['IS_DEFAULT'] === 'Y')
		{
			$defaultStoreId = $store['ID'];
			$defaultStoreName = $store['TITLE'];
			break;
		}
	}
}

return [
	'css' => 'dist/store-selector.bundle.css',
	'js' => 'dist/store-selector.bundle.js',
	'rel' => [
		'ui.forms',
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
	],
];