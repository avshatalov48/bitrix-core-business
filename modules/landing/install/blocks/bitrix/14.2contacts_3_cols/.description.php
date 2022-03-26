<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NAME'),
		'section' => ['contacts'],
		'version' => '18.5.0', // old param for backward compatibility. Can used for old versions of module via repo. Do not delete!
	],
	'cards' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODE_CARD'),
			'label' => [
				'.landing-block-node-contact-icon',
				'.landing-block-node-contact-title',
				'.landing-block-node-linkcontact-title',
			],
			'presets' => include __DIR__ . '/presets.php',
		],
	],
	'nodes' => [
		'.landing-block-node-contact-icon' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTIMG'),
			'type' => 'icon',
			'group' => 'contact',
		],
		'.landing-block-node-linkcontact-link' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTLINK'),
			'type' => 'link',
			'group' => 'contact',
			'skipContent' => true,
		],
		'.landing-block-node-linkcontact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTITLE'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
		'.landing-block-node-linkcontact-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTEXT'),
			'type' => 'text',
			'group' => 'contact',
			'allowInlineEdit' => false,
			'textOnly' => true,
		],
		'.landing-block-node-contact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTITLE'),
			'type' => 'text',
		],
		'.landing-block-node-contact-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTTEXT'),
			'type' => 'text',
		],
		'.landing-block-node-contact-img' => [
			//		deprecated
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTIMG'),
			'type' => 'icon',
		],
	],
	'style' => [
		'.landing-block-card' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODE_CARD'),
			'type' => ['border-color', 'columns', 'animation'],
		],
		'.landing-block-node-contact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTITLE'),
			'type' => 'typo',
		],
		'.landing-block-node-contact-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTEXT'),
			'type' => 'typo',
		],
		'.landing-block-node-linkcontact-title' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTITLE'),
			'type' => 'typo-link',
		],
		'.landing-block-node-linkcontact-text' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_STYLE_LANDINGBLOCKNODECONTACTTEXT'),
			'type' => 'typo-link',
		],
		'.landing-block-node-contact-icon-container' => [
			'name' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODECONTACTIMG'),
			'type' => 'color',
		],
	],
	'groups' => [
		'contact' => Loc::getMessage('LANDING_BLOCK_7_CONTACTS_4_COLS_NODES_LANDINGBLOCKNODE_CARD'),
	],
];