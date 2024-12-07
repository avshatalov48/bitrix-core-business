<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/external-catalog-stub.bundle.css',
	'js' => 'dist/external-catalog-stub.bundle.js',
	'rel' => [
		'catalog.config.settings',
		'main.core',
		'ui.sidepanel',
	],
	'skip_core' => false,
];
