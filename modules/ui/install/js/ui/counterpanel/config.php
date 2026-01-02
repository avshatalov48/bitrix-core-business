<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/counterpanel.bundle.css',
	'js' => 'dist/counterpanel.bundle.js',
	'rel' => [
		'ui.actions-bar',
		'ui.design-tokens.air',
		'main.popup',
		'main.core',
		'ui.cnt',
		'main.core.events',
		'ui.design-tokens',
		'ui.icon-set.api.core',
		'ui.icon-set.outline',
	],
	'skip_core' => false,
	'settings' => [
		'useAirDesign' => defined('AIR_SITE_TEMPLATE'),
	],
];
