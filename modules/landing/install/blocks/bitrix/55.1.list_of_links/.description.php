<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_55_1-NAME'),
		'section' => array('tiles'),
	),
	'cards' => array(
		'.landing-block-node-list-item' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_55_1-LINK'),
			'label' => ['.landing-block-node-link-text'],
		),
	),
	'nodes' => array(
		'.landing-block-node-link' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_55_1-LINK'),
			'type' => 'link',
			'group' => 'link',
			'skipContent' => true,
		),
		'.landing-block-node-link-text' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_55_1-LINK_TEXT'),
			'type' => 'text',
			'group' => 'link',
			'allowInlineEdit' => false,
			'textOnly' => true,
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default', 'block-border'),
		),
		'nodes' => array(
			'.landing-block-node-list-container' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_55_1-LIST'),
				'type' => 'row-align',
			),
			'.landing-block-node-list-item' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_55_1-LINK'),
				'type' => ['border-color', 'border-width', 'animation', 'padding-top', 'padding-bottom'],
			),
			'.landing-block-node-link' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_55_1-LINK'),
				'type' => 'typo-link',
			),
		),
	),
);