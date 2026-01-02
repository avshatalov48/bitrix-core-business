<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_ENT_2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_hr'],
		'system' => true,
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.enterprise_west" => [
			'type' => 'component',
			'extra' => [
				'editable' => [],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget', 'font-family'],
		],
	],
];

return $return;