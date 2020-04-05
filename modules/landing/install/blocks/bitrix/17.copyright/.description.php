<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_9_COPYRIGHT_NAME'),
			'section' => array('footer'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_9_COPYRIGHT_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default','animation'),
			),
			'nodes' => array(
				'.landing-block-node-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_9_COPYRIGHT_STYLE_LANDINGBLOCKNODETEXT'),
						'type' => array('typo','animation'),
					),
			),
		),
);