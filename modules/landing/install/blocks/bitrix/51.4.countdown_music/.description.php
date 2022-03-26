<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NAME'),
		'section' => ['countdowns', 'cover'],
		'dynamic' => false,
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'type' => ['page', 'store', 'smn'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--EVENT'),
			'label' => '.landing-block-node-text-title',
			'group_label' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--EVENTS'),
			'additional' => [
				'attrs' => [
					[
						'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--DATE'),
						'time' => true,
						'type' => 'date',
						'format' => 'ms',
						'attribute' => 'data-end-date',
					],
				],
			],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 1920, 'height' => 1080],
			'create2xByDefault' => false,
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TEXT'),
			'type' => 'text',
		],
		
		'.landing-block-node-number-text-days' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-number-text-hours' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-number-text-minutes' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-number-text-seconds' => [
			'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-background-overlay'],
		],
		'nodes' => [
			'.landing-block-node-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
				'type' => ['typo', 'heading'],
			],
			'.landing-block-node-text-title' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TITLE'),
				'type' => 'typo',
			],
			'.landing-block-node-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--TEXT'),
				'type' => 'typo',
			],
			'.landing-block-node-number-number' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_NUMBER'),
				'type' => ['color', 'font-family'],
			],
			'.landing-block-node-number-text' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_TEXT'),
				'type' => ['color', 'font-family'],
			],
			'.landing-block-node-number' => [
				'name' => Loc::getMessage('LANDING_BLOCK_51_4_COUNTDOWN_MUSIC--NUMBER_NUMBER'),
				'type' => ['bg', 'border-color'],
			],
		],
	],
	'assets' => [
		'ext' => ['landing_countdown'],
	],
];