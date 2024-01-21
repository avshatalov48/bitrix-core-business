<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_60_2_NAME'),
		'section' => ['contacts', 'social'],
		'type' => ['page', 'store', 'smn'],
		'dynamic' => false,
		'namespace' => 'bitrix',
		'attrsFormDescription' => Loc::getMessage(
			'LANDING_BLOCK_60_2_SETTINGS_DESC_A',
			[
				'#LINK1#' => '<div class="landing-ui-form-description-link" onclick="BX.SidePanel.Instance.open(landingParams[\'PAGE_URL_LANDING_SETTINGS\']);">',
				'#LINK2#' => '</div>',
			]
		),
	],
	'nodes' => [
		'bitrix:landing.blocks.openlines' => [
			'type' => 'component',
			'extra' => [
				'editable' => [
					// visual
					'BUTTON_ID' => [
						'disabled' => true,
					],
				],
			],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_60_2_TITLE'),
				'type' => ['container'],
			],
		],
	],
];