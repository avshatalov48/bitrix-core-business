<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/product-selector.bundle.css',
	'js' => 'dist/product-selector.bundle.js',
	'rel' => [
		'catalog.sku-tree',
		'ui.entity-selector',
		'main.core.events',
		'catalog.product-selector',
		'main.core',
		'ui.forms',
	],
	'skip_core' => false,
];