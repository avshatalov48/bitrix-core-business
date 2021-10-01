<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sku-tree.bundle.css',
	'js' => 'dist/sku-tree.bundle.js',
	'rel' => [
		'main.core',
		'catalog.sku-tree',
		'main.core.events',
		'ui.forms',
		'ui.buttons',
	],
	'skip_core' => false,
];