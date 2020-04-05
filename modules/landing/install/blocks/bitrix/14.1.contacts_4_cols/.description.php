<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NAME'),
			'section' => array('contacts'),
		),
	'cards' =>
		array(
			'.landing-block-card' => array(
			    'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODE_CARD'),
				'label' => array('.landing-block-node-contact-title')
			),
		),
	'nodes' =>
		array(
			'.landing-block-node-contact-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTIMG'),
					'type' => 'icon',
				),
			'.landing-block-node-contact-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTITLE'),
					'type' => 'text',
				),
			'.landing-block-node-contact-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTEXT'),
					'type' => 'text',
				),
			'.landing-block-node-contact-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTLINK'),
					'type' => 'link',
				),
		),
	'style' =>
		array(
			'.landing-block-card' => array(
			    'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODE_CARD'),
			    'type' => array('columns','animation'),
			),
			'.landing-block-node-contact-title' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTITLE'),
					'type' => 'typo',
				),
			'.landing-block-node-contact-text' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTEXT'),
					'type' => 'typo',
				),
			'.landing-block-node-contact-img' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTIMG'),
					'type' => 'color',
				),
			'.landing-block-node-contact-link' =>
				array(
					'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTLINK'),
					'type' => 'typo',
				),
		),
);