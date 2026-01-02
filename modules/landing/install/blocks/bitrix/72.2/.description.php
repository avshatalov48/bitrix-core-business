<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => 'test',
		//'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'type' => 'null',
		'section' => ['widgets_columns'],
	],
	'cards' => [],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
		'nodes' => [],
	],
];

return $return;