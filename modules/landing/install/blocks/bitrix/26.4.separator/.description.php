<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_26_4_SEPARATOR_NAME'),
		'section' => ['separator'],
	],
	'cards' => [],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['display', 'fill-first', 'fill-second', 'height-increased--md'],
		],
		'nodes' => [],
	],
];