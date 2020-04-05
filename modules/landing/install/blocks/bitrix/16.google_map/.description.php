<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
//			'name' => Loc::getMessage('LANDING_BLOCK_10_GOOGLE_MAP_NAME'),
			'section' => array('other'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(),
	'style' =>
		array(),
	'attrs' => array(
		'.landing-block-node-map' =>
			array(
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_10_GOOGLE_MAP_LAT'),
					'type' => 'text',
					'attribute' => 'data-lat',
				),
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_10_GOOGLE_MAP_LNG'),
					'type' => 'text',
					'attribute' => 'data-lng',
				),
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_10_GOOGLE_MAP_ZOOM'),
					'type' => 'slider',
					'attribute' => 'data-zoom',
					'items' => array(
						array('name' => 'World', 'value' => 1),
						array('name' => '2', 'value' => 2),
						array('name' => '3', 'value' => 3),
						array('name' => '4', 'value' => 4),
						array('name' => '5', 'value' => 5),
						array('name' => '6', 'value' => 6),
						array('name' => '7', 'value' => 7),
						array('name' => '8', 'value' => 8),
						array('name' => '9', 'value' => 9),
						array('name' => '10', 'value' => 10),
						array('name' => '11', 'value' => 11),
						array('name' => '12', 'value' => 12),
						array('name' => '13', 'value' => 13),
						array('name' => '14', 'value' => 14),
						array('name' => '15', 'value' => 15),
						array('name' => '16', 'value' => 16),
						array('name' => '17', 'value' => 17),
						array('name' => '18', 'value' => 18),
						array('name' => '19', 'value' => 19),
						array('name' => '20', 'value' => 20),
						array('name' => 'Buildings', 'value' => 21),
					),
				),
			),
	),
	'assets' => array(
		'ext' => array('landing_google_map'),
	),
);