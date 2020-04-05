<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NAME'),
			'section' => array('cover'),
		),
	'cards' =>
		array(),
	'nodes' =>
		array(
			'.landing-block-node-bgimg' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEBGIMG'),
					'type' => 'img',
					'allowInlineEdit' => false,
					'dimensions' => array('width' => 1920, 'height' => 1080),
				),
			'.landing-block-node-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETITLE'),
					'type' => 'text',
				),
			'.landing-block-node-subtitle' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODESUBTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETEXT'),
					'type' => 'text',
				),
			'.landing-block-node-date-value' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATEVALUE'),
					'type' => 'text',
				),
			'.landing-block-node-date-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATETEXT'),
					'type' => 'text',
				),
		),
	'style' =>
		array(
			'block' => array(
				'type' => array('block-default-background-overlay'),
			),
			'nodes' => array(
				'.landing-block-node-title' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETITLE'),
						'type' => array('typo', 'animation'),
					),
				'.landing-block-node-subtitle-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODE_CONTAINER'),
						'type' => array('animation'),
					),
				'.landing-block-node-date-container' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODE_CONTAINER'),
						'type' => array('animation'),
					),
				'.landing-block-node-subtitle' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODESUBTITLE'),
						'type' => 'typo',
					),
				'.landing-block-node-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODETEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-date-value' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATEVALUE'),
						'type' => 'typo',
					),
				'.landing-block-node-date-text' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEDATETEXT'),
						'type' => 'typo',
					),
				'.landing-block-node-header' =>
					array(
						'name' => Loc::getMessage('LANDING_BLOCK_46.5.COVER_WITH_DATE_COUNTDOWN_NODES_LANDINGBLOCKNODEHEADER'),
						'type' => 'border-color',
					),
			),

		),
);