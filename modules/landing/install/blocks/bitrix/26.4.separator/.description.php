<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_26_4_SEPARATOR_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['separator', 'widgets_separators'],
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