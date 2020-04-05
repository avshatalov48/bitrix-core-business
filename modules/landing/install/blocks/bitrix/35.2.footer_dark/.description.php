<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NAME'),
		'section' => array('footer'),
		'subtype' => 'menu',
		'subtype_params' => array(
			'selector' => '.landing-block-node-list-item',
			'source' => 'catalog'
		)
	),
	'cards' => array(
		'.landing-block-card-contact' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_CARDS_LANDINGBLOCKCARDCONTACT'),
			'label' => array('.landing-block-node-card-contact-icon', '.landing-block-node-card-contact-text', '.landing-block-node-card-contact-link'),
			'presets' => include __DIR__ . '/presets.php',
		),
		'.landing-block-card-list1-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_CARDS_LANDINGBLOCKCARDLISTITEM'),
			'label' => array('.landing-block-node-list-item'),
		),
		'.landing-block-card-list2-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_CARDS_LANDINGBLOCKCARDLISTITEM'),
			'label' => array('.landing-block-node-list-item'),
		),
		'.landing-block-card-list3-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_CARDS_LANDINGBLOCKCARDLISTITEM'),
			'label' => array('.landing-block-node-list-item'),
		),
	),
	'nodes' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODETITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODETEXT'),
			'type' => 'text',
		),
		'.landing-block-node-card-contact-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODE_CONTACT_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-card-contact-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODE_CONTACT_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODELISTITEM'),
			'type' => 'link',
		),
		'.landing-block-node-card-contact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODELINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODETITLE'),
			'type' => 'typo',
		),
		'.landing-block-node-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODECARD'),
			'type' => 'animation',
		),
		'.landing-block-node-main-card' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODECARD'),
			'type' => 'animation',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODETEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-card-contact-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODE_CONTACT_TEXT'),
			'type' => 'typo',
		),
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODELISTITEM'),
			'type' => 'typo',
		),
		'.landing-block-node-card-contact-icon-container' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_NODES_LANDINGBLOCKNODE_CONTACT_ICON'),
			'type' => 'color',
		),
		'.landing-block-node-card-contact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_35.2.FOOTER_LIGHT_STYLE_LANDINGBLOCKNODELINK'),
			'type' => 'typo',
		),
	),
);