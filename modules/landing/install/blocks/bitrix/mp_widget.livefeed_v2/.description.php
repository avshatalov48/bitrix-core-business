<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use \Bitrix\Main\Localization\Loc;

$helpUrl = \Bitrix\Landing\Help::getHelpUrl('WIDGET_LIVEFEED');
$hint = Loc::getMessage('LANDING_BLOCK_WIDGET_LIVEFEED_V2_HINT', ['#LINK#' => $helpUrl]);
$return = [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_WIDGET_LIVEFEED_V2_NAME'),
		'type' => ['mainpage'],
		'section' => ['widgets_company_life', 'widgets_hr'],
		'attrsFormDescription' => $hint,
		'attrsFormDescriptionHintStyle' => 'blueHint',
		'disableEditButton' => Option::get('landing', 'use_demo_data_in_block_widgets') === 'Y',
	],
	'nodes' => [
		"bitrix:landing.blocks.mp_widget.livefeed" => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					'TITLE' => [],
					'GROUP_ID' => [],
					// visual
					'COLOR_HEADERS_V2' => [
						'style' => true,
					],
					'COLOR_BUTTON' => [
						'style' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['widget'],
		],
	],
];

return $return;