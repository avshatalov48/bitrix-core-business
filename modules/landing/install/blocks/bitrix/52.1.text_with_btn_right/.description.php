<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-TEXT'),
			'type' => array('typo', 'animation'),
		),
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => array('button'),
		),
		'.landing-block-node-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_52_TEXT_WITH_BTN_RIGHT-BTN'),
			'type' => array('text-align', 'animation'),
		),
	),
);