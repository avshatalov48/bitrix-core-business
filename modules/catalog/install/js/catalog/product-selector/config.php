<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$limitInfo = null;
if (\Bitrix\Main\Loader::includeModule('catalog'))
{
	$limitInfo = \Bitrix\Catalog\Config\State::getCrmExceedingProductLimit();
}

return [
	'css' => 'dist/product-selector.bundle.css',
	'js' => 'dist/product-selector.bundle.js',
	'rel' => [
		'catalog.sku-tree',
		'ui.entity-selector',
		'ui.info-helper',
		'main.core.events',
		'catalog.product-selector',
		'main.core',
		'ui.forms',
	],
	'skip_core' => false,
	'settings' => [
		'limitInfo' => $limitInfo,
	],
];