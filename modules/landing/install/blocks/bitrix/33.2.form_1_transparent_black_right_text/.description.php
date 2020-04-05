<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' =>
		array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2'),
			'section' => array('forms'),
			'subtype' => 'form',
		),
	'cards' => array(
		'.landing-block-node-card-contact' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CARD_CONTACT'),
			'label' => array('.landing-block-node-contact-text', '.landing-block-node-contact-link', '.landing-block-card-linkcontact-link'),
			'presets' => include __DIR__ . '/presets.php',
		),
	),
	'groups' => array(
		'contact' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CARD_CONTACT'),
	),
	'nodes' => array(
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_BGIMG'),
			'type' => 'img',
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'allowInlineEdit' => false,
		),
		'.landing-block-node-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-main-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_TITLE'),
			'type' => 'text',
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_SUBTITLE'),
			'type' => 'text',
		),
		'.landing-block-card-contact-icon' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_ICON'),
			'type' => 'icon',
		),
		'.landing-block-node-contact-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_TEXT'),
			'type' => 'text',
		),
		'.landing-block-node-contact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_LINK'),
			'type' => 'link',
		),
		'.landing-block-card-linkcontact-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_LINK'),
			'type' => 'link',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay'),
		),
		'nodes' => array(
			'.landing-block-node-main-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_TITLE'),
				'type' => array('typo','animation'),
			),
			'.landing-block-card-contact-icon-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.1_NODE_CONTACT_ICON'),
				'type' => 'color',
			),
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_SUBTITLE'),
				'type' => 'typo',
			),
			'.landing-block-node-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_TEXT'),
				'type' => array('typo','animation'),
			),
			'.landing-block-node-contact-text' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_TEXT'),
				'type' => 'typo',
			),
			'.landing-block-node-contact-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_LINK'),
				'type' => 'typo',
			),
			'.landing-block-node-form' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_FORM'),
				'type' => 'animation',
			),
			'.landing-block-card-linkcontact-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_CONTACT_LINK'),
				'type' => 'typo',
			),
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.2_NODE_BGIMG'),
				'type' => 'background-attachment',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);