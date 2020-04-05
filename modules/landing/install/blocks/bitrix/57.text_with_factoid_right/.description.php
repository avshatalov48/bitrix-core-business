<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NAME'),
		'section' => array('text'),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-text-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT_BLOCK'),
			'type' => 'animation',
		),
		'.landing-block-node-number-block' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT_BLOCK'),
			'type' => array('animation', 'align-items'),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-number' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-number-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_57.TEXT_WITH_FACTOID_NODE_TEXT'),
			'type' => 'typo',
		),
	),
);