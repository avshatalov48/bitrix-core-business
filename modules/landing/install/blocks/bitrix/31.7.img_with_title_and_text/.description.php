<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_31_7_NAME'),
		'section' => array('tiles', 'news'),
	),
	'cards' => array(
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_NAME'),
			'label' => array('.landing-block-node-img', '.landing-block-node-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1254),
			'create2xByDefault' => false,
		),
		'.landing-block-node-read-more' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_READMORE'),
			'type' => 'link',
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_SUBTITLE'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_TITLE'),
			'type' => ['typo', 'margin-bottom', 'heading'],
		),
		'.landing-block-node-read-more' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_READMORE'),
			'type' => array('typo-link', 'margin-bottom'),
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_TEXT'),
			'type' => array('typo', 'margin-top'),
		),
		'.landing-block-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_CARD'),
			'type' => array('animation', 'margin-bottom', 'padding-top', 'padding-bottom'),
		),
		'.landing-block-node-subtitle' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_SUBTITLE'),
			'type' => array('typo'),
		),
		'.landing-block-node-img-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_31_7_SUBTITLE'),
			'type' => array('text-align'),
		),
	),
);