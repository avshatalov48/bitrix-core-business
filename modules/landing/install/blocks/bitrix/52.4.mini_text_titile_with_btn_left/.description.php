<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_52_4-NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_4-TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_4-TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_4-BTN'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => [
			'type' => ['block-default', 'block-border'],
		],
		'nodes' => [
			'.landing-block-node-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-CONTAINER'),
				'type' => 'align-items',
			),
			'.landing-block-node-text-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-TEXT'),
				'type' => 'animation',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-TITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-button' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-BTN'),
				'type' => array('button'),
			),
			'.landing-block-node-button-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_52_4-BTN'),
				'type' => array('text-align', 'animation'),
			),
		],
	),
);