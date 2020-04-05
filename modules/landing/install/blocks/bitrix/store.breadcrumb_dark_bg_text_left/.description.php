<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB3'),
		'section' => array('store'),
		'type' => 'store',
		'html' => false,
	),
	'nodes' => array(
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB3_LANDINGBLOCKNODEBGIMG'),
			'type' => 'img',
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 500),
		),
		'.landing-block-node-title' => array(
			'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB3_LANDINGBLOCKNODE_TITLE'),
			'type' => 'text',
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-background-overlay-paddings-x'),
		),
		'nodes' => array(
			'.landing-block-node-title' => array(
				'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB3_LANDINGBLOCKNODE_TITLE'),
				'type' => 'typo',
			),
			'.landing-breadcrumb-container' => array(
				'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB3_LANDINGBLOCKNODE_NAV_CONTAINER'),
				'type' => 'typo',
			),
		),
	),
);