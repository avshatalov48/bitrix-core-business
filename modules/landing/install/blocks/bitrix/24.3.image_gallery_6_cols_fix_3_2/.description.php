<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_2_NAME'),
		'section' => ['partners'],
		'dynamic' => false,
		'version' => '19.0.100', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
		'type' => ['page', 'store', 'smn'],
	],
	'cards' => [
		'.landing-block-node-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_2_CARDS_LANDINGBLOCKNODECARD'),
			'label' => ['.landing-block-node-img'],
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_2_NODES_LANDINGBLOCKNODEIMG'),
			'type' => 'img',
			'dimensions' => ['width' => 525],
		],
	],
	'style' => [
		'block' => [
			'type' => ['block-default', 'animation'],
		],
		'nodes' => [
			'.landing-block-node-card' => [
				'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_2_NODES_LANDINGBLOCKNODEIMG'),
				'type' => ['columns', 'row-align-column', 'align-items-column'],
			],
			'.landing-block-node-container' => [
				'name' => Loc::getMessage('LANDING_BLOCK_24.3.IMAGE_GALLERY_6_COLS_FIX_3_2_NODES_LANDINGBLOCKNODEIMG'),
				'type' => ['border-color'],
			],
		],
	],
];