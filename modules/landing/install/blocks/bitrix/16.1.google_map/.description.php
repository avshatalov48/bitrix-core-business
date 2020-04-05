<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage("LANDING_BLOCK_16_1_GOOGLE_MAP--NAME"),
		'section' => ['contacts'],
		'version' => '18.5.0',
		'subtype' => 'map',
		'subtype_params' =>[
			'required' => 'google'
		],
	],
	'cards' => [],
	'nodes' => [
		'.landing-block-node-map' => [
			'name' => 'Map',
			'type' => 'map',
		]
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background-vh-animation']
		],
		'nodes' => [],
	],
	'assets' => [
		'ext' => ['landing_google_maps_new'],
	]
];