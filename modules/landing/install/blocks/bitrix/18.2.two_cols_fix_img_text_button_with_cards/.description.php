<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_18.2_NAME'),
		'section' => ['tiles', 'news'],
	),
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_CARD'),
			'label' => ['.landing-block-node-img', '.landing-block-node-title'],
		],
	],
	'nodes' => array(
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 255),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_LINK'),
			'type' => 'link',
		],
	),
	'style' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_CARD'),
			'type' => ['align-items', 'animation'],
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_TITLE'),
			'type' => ['typo', 'heading'],
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_TEXT'),
			'type' => ['typo'],
		),
		'.landing-block-node-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_18.2_LINK'),
			'type' => ['typo-link'],
		],
	),
);