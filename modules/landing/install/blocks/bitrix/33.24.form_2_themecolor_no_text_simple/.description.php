<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.10'),
		'section' => ['sidebar'],
		'dynamic' => false,
		'subtype' => 'form',
	],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-form-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33_10_CONTAINER'),
				'type' => ['row-align'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_form'],
	],
];