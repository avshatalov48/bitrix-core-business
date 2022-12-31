<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iblock-product-list.bundle.css',
	'js' => 'dist/iblock-product-list.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.hints',
	],
	'skip_core' => false,
];
