<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/actions-bar.bundle.css',
	'js' => 'dist/actions-bar.bundle.js',
	'rel' => [
		'ui.buttons',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'useAirDesign' => defined('AIR_SITE_TEMPLATE'),
	],
];
