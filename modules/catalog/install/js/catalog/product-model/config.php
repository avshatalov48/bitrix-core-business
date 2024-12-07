<?php

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Config\State;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$catalogProductRights = [];
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$accessController = \Bitrix\Catalog\Access\AccessController::getCurrent();

	$checkRights = [
		ActionDictionary::ACTION_PRODUCT_EDIT => ActionDictionary::ACTION_PRODUCT_EDIT,
		ActionDictionary::ACTION_PRODUCT_ADD => ActionDictionary::ACTION_PRODUCT_ADD,
		ActionDictionary::ACTION_PRODUCT_VIEW => ActionDictionary::ACTION_CATALOG_READ,
	];

	foreach ($checkRights as $code => $right)
	{
		$catalogProductRights[$code] = $accessController->check($right);
	}
}


return [
	'css' => 'dist/product-model.bundle.css',
	'js' => 'dist/product-model.bundle.js',
	'rel' => [
		'main.core.events',
		'catalog.product-calculator',
		'main.core',
		'catalog.product-model',
	],
	'skip_core' => false,
	'settings' => [
		'catalogProductRights' => $catalogProductRights,
		'isExternalCatalog' => State::isExternalCatalog(),
	],
];
