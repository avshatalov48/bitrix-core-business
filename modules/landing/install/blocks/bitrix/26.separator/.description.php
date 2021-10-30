<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_9_SEPARATOR_NAME'),
		'section' => ['separator', 'recommended'],
	],
	'cards' => [],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['block-default'],
		],
		'nodes' => [
			'.landing-block-line' => [
				'name' => Loc::getMessage('LANDING_BLOCK_9_SEPARATOR_LINE'),
				'type' => ['border-color'],
			],
		],
	],
];