<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_20_4_NAME'),
		'section' => array('columns', 'text_image', 'news'),
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_CARD'),
			'label' => array('.landing-block-node-img', '.landing-block-node-title'),
		],
	],
	'nodes' => [
		'.landing-block-node-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_IMG'),
			'type' => 'img',
			'dimensions' => ['width' => 540],
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_TITLE'),
			'type' => 'text',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_TEXT'),
			'type' => 'text',
		],
		'.landing-block-node-date' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_DATE'),
			'type' => 'text',
		],
		'.landing-block-node-author' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_AUTHOR'),
			'type' => 'text',
		],
		'.landing-block-node-author-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_AUTHORIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 40, 'height' => 40),
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_CARD'),
			'type' => array('columns', 'animation', 'margin-bottom'),
		],
		'.landing-block-card-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_CARD_BLOCK'),
			'type' => array('background-color', 'background-gradient'),
		],
		'.landing-block-inner' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_BLOCK'),
			'type' => 'row-align',
		],
		'.landing-block-node-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_TITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_TEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-date' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_DATE'),
			'type' => array('background-color', 'typo'),
		],
		'.landing-block-node-author' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_AUTHOR'),
			'type' => 'typo',
		],
		'.landing-block-bottom-block' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_BOTTOM_BLOCK'),
			'type' => array('border-color'),
		],
		'.landing-block-node-author-img' => [
			'name' => Loc::getMessage('LANDING_BLOCK_20_4_AUTHORIMG'),
			'type' => 'border-radius',
		],
	],
];