<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NAME'),
			'section' => array('cover')
		),
	'cards' => array(),
	'nodes' =>
		array(
			'.landing-block-node-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODEIMG'),
					'type' => 'img',
					'dimensions' => array('width' => 500, 'height' => 700),
					'allowInlineEdit' => false,
				),
			'.landing-block-node-img-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_TITLE'),
					'type' => 'text',
				),
			'.landing-block-node-img-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_TEXT'),
					'type' => 'text',
				),
			'.landing-block-node-img-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_BUTTON'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'.landing-block-node-img-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_TITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-img-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_TEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-img-button' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_BUTTON'),
					'type' => 'button',
				),
			'.landing-block-node-img-container' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_BLOCK'),
					'type' => 'animation',
				),
			'.landing-block-node-button-container' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_46.2.COVER_WITH_2_BIG_IMAGES_NODES_LANDINGBLOCKNODE_BUTTON'),
					'type' => 'text-align',
				),
		),
);