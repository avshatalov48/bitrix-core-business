<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'./dist/osm.bundle.css',
		'/bitrix/js/location/css/map-mobile.css',
	],
	'js' => [
		'./dist/osm.bundle.js',
	],
	'rel' => [
		'ui.design-tokens',
		'main.core',
		'location.core',
	],
	'skip_core' => false,
];