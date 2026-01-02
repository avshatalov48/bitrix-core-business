<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_VIBE_COLLABORATION_SECTION_NAME'),
		'type' => ['page', 'store', 'smn', 'knowledge', 'group', 'mainpage'],
		'section' => ['widgets_columns'],
		'system' => true,
	],
	'cards' => [],
	'nodes' => [],
	'style' => [
		'block' => [
			'type' => ['widget', 'font-family'],
		],
		'nodes' => [],
	],
	'assets' => [
		'ext' => ['landing_icon_fonts'],
	],
];

return $return;