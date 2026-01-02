<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => 'dist/navigationpanel.bundle.css',
	'js' => 'dist/navigationpanel.bundle.js',
	'rel' => [
		'ui.actions-bar',
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.icon-set.api.core',
		'ui.icon-set.outline',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
	'settings' => [
		'useAirDesign' => defined('AIR_SITE_TEMPLATE'),
	]
];
