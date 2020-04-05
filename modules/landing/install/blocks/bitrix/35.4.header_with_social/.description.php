<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
//		todo: add socials presets and go!
		'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NAME'),
		'section' => array('menu'),
	),
	'cards' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
			'presets' => include __DIR__ . '/presets.php',
			'label' => array(
				'.landing-block-node-card-icon',
				'.landing-block-node-card-contactlink-icon',
				'.landing-block-node-card-title',
				'.landing-block-node-menu-contactlink-title',
			),
		),
		'.landing-block-node-social-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_SOCIAL'),
			'label' => array('.landing-block-node-social-icon'),
			'presets' => include __DIR__ . '/presets_social.php',
		),
	),
	'groups' => array(
		'logo' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODELOGO'),
		'contact' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
	),
	'nodes' => array(
//		logo
		'.landing-block-node-logo' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODELOGO'),
			'type' => 'img',
			'group' => 'logo',
			'dimensions' => array('width' => 180, 'height' => 60),
		),
		'.landing-block-node-menu-logo-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
			'group' => 'logo',
		),

//		contact-text
		'.landing-block-node-card-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-card-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),

//		contact-link
		'.landing-block-node-card-contactlink-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		),
		'.landing-block-node-card-contactlink-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'icon',
			'group' => 'contact',
		),
		'.landing-block-node-menu-contactlink-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
		'.landing-block-node-card-contactlink-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
		
//		social
		'.landing-block-node-social-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_LINK'),
			'type' => 'link',
		),
		'.landing-block-node-social-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_ICON'),
			'type' => 'icon',
		),
	),
	'style' => array(
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD'),
			'type' => 'border-color',
		),
		'.landing-block-node-card-title-style' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCK_CARD_TITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card-text-style' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_ICON'),
			'type' => 'color',
		),
		
//		social
		'.landing-block-node-social-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.4.HEADER_NODES_LANDINGBLOCKNODE_SOCIAL_ICON'),
			'type' => ['color', 'color-hover', 'background-color', 'background-hover']
		),
	),
);