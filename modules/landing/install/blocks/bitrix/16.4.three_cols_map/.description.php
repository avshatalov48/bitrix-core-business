<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--NAME"),
		'section' => ['contacts'],
		'version' => '18.5.0',
		'subtype' => ['map', 'form'],
		'subtype_params' =>[
			'required' => 'google'
		],
	],
	'cards' => [
		'.landing-block-card-address' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--CONTACT"),
			'label' => array('.landing-block-card-address-title'),
			'presets' => include __DIR__ . '/presets.php',
		],
		'.landing-block-card-social' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SOCIAL"),
			'label' => array('.landing-block-card-social-icon'),
			'presets' => include __DIR__ . '/presets_social.php',
		]
	],
	'nodes' => [
		'.landing-block-node-map' => [
			'name' => 'Map',
			'type' => 'map',
		],
		'.landing-block-node-address-subtitle' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SUBTITLE"),
			'type' => 'text',
		],
		'.landing-block-node-address-title' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
			'type' => 'text',
		],
		'.landing-block-node-address-text' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TEXT"),
			'type' => 'text',
		],
		
		'.landing-block-card-address-title' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
			'type' => 'text',
		],
		'.landing-block-card-address-text' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TEXT"),
			'type' => 'text',
		],
		'.landing-block-card-address-link' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--CONTACT_LINK"),
			'type' => 'link',
		],
		
		'.landing-block-card-social-icon' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SOCIAL_ICON"),
			'type' => 'icon',
		],
		'.landing-block-card-social-link' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SOCIAL_LINK"),
			'type' => 'link',
		],
		
		'.landing-block-node-third-col-title' => [
			'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
			'type' => 'text',
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default-wo-background']
		],
		'nodes' => [
			'.landing-block-node-map' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--MAP"),
				'type' => 'animation',
			],
			'.landing-block-node-address-col' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--COL"),
				'type' => ['bg','animation'],
			],
			'.landing-block-node-third-col' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--COL"),
				'type' => ['bg','animation'],
			],
			'.landing-block-node-address-subtitle' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SUBTITLE"),
				'type' => ['typo', 'background-color'],
			],
			'.landing-block-node-address-title' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
				'type' => 'typo',
			],
			'.landing-block-node-address-text' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TEXT"),
				'type' => 'typo',
			],
			
			'.landing-block-card-address-title' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
				'type' => 'typo',
			],
			'.landing-block-card-address-text' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TEXT"),
				'type' => 'typo',
			],
			'.landing-block-card-address-link' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--LINK"),
				'type' => 'typo',
			],
			
			'.landing-block-card-social-link' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--SOCIAL_LINK"),
				'type' => ['background-color', 'background-hover'],
			],
			
			'.landing-block-node-third-col-title' => [
				'name' => Loc::getMessage("LANDING_BLOCK_16_4_THREE_COLS_MAP--TITLE"),
				'type' => 'typo',
			],
		]
	],
	'assets' => [
		'ext' => ['landing_google_maps_new', 'landing_form'],
	]
];