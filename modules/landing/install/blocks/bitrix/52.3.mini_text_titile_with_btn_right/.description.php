<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_52_3-NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_3-TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_3-TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_3-BTN'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3-CONTAINER'),
				'type' => 'align-items',
			),
			'.landing-block-node-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3-TEXT'),
				'type' => 'animation',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3-TITLE'),
				'type' => ['typo'],
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3-TEXT'),
				'type' => ['typo'],
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3-BTN'),
				'type' => array('button'),
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_3_BTN_AREA'),
				'type' => array('text-align', 'animation'),
			),
		],
	),
);