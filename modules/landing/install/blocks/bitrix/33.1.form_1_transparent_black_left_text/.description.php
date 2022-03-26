<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33_1'),
		'section' => array('forms'),
		'dynamic' => false,
		'subtype' => 'form',
	),
	'cards' => array(
		'.landing-block-node-card-contact' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CARD_CONTACT'),
			'label' => array(
				'.landing-block-node-contact-text',
				'.landing-block-card-contact-icon',
			),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'groups' => array(
		'contact' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CARD_CONTACT'),
	),
	'nodes' => array(
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_BGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
			'create2xByDefault' => false,
		),
		'.landing-block-node-main-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_SUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-card-contact-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-contact-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-contact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_LINK'),
			'type' => 'link',
		),
		'.landing-block-card-linkcontact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_LINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay'),
		),
		'nodes' => array(
			'.landing-block-node-main-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_TITLE'),
				'type' => ['typo', 'animation', 'heading'],
			),
			'.landing-block-card-contact-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_ICON'),
				'type' => 'color',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_SUBTITLE'),
				'type' => ['typo', 'heading'],
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_TEXT'),
				'type' => array('typo', 'animation'),
			),
			'.landing-block-node-contact-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-contact-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_LINK'),
				'type' => 'typo',
			),
			'.landing-block-card-linkcontact-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_LINK'),
				'type' => 'typo-link',
			),
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_BGIMG'),
				'type' => 'background-attachment',
			),
			'.landing-block-node-row' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33_1_NODE_BLOCK'),
				'type' => 'align-items',
			),
		),
	),
);