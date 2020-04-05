<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NAME'),
		'section' => array('text'),
	),
	'cards' => array(
		'.landing-block-node-card-text-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_CARD_TEXT_BLOCK'),
			'label' => array('.landing-block-card-title'),
		),
	),
	'nodes' => array(
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_CARD_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TITLE'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-texts' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TEXTS'),
			'type' => 'animation',
		),
		'.landing-block-node-title-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TITLE'),
			'type' => 'animation',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_CARD_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_56.1.TITLE_LEFT_WITH_COLUMNS_RIGHT_NODE_TITLE'),
			'type' => 'typo',
		),
	),
);