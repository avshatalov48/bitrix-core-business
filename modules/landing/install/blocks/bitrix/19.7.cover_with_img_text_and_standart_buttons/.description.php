<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_19_9_NAME'),
		'section' => array('tiles'),
		'dynamic' => false,
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_CARD'),
			'label' => array('.landing-block-node-card-button'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_IMG'),
			'type' => 'img',
			'dimensions' => array('width' => 540),
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_BUTTON'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_IMG'),
			'type' => array('animation'),
		),
		'.landing-block-node-card-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_19_9_BUTTON'),
			'type' => 'button',
		),
	),
);