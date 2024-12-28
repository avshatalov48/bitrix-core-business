<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Landing\Mainpage;
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
		'disableEditButton' => Mainpage\Manager::isUseDemoData(),
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