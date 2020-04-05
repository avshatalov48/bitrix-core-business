<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN'),
			'type' => 'link',
		),
		'.landing-block-node-button-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN_IMG'),
			'type' => 'img',
			'group' => 'button',
		),
	),
	'style' => array(
		'.landing-block-node-button-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN'),
			'type' => array('text-align', 'animation'),
		),
	),
);