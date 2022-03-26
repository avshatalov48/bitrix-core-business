<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_67_1_NAME'),
		'section' => array('text'),
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_67_1_TEXT_2'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'animation', 'block-border'),
		),
		'nodes' => array(
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_67_1_TEXT_2'),
				'type' => ['typo', 'container', 'animation'],
			),
		),
	),
);