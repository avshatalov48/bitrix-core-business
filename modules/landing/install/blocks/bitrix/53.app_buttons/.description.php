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
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN'),
			'label' => array('.landing-block-node-button-img'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'nodes' => array(
		'.landing-block-node-button' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN'),
			'type' => 'link',
		),
		'.landing-block-node-button-img' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN_IMG'),
			'type' => 'img',
		),
		'.landing-block-node-button-img-custom' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN_IMG'),
			'type' => 'img',
		),
	),
	'style' => array(
		'.landing-block-cards' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_53_APP_BUTTONS--BTN'),
			'type' => array('row-align', 'animation'),
		),
	),
);