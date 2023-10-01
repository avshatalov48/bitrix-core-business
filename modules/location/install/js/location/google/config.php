<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'./dist/google.bundle.css',
		'/bitrix/js/location/css/map-mobile.css',
	],
	'js' => './dist/google.bundle.js',
	'rel' => [
		'main.core',
		'location.core',
	],
	'skip_core' => false,
	'oninit' => static function()
	{
		CJSCore::Init(['ls']);
	},
];
