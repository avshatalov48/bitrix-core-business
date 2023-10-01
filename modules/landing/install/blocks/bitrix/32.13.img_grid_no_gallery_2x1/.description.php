<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block'
	=> array(
		'name' => Loc::getMessage('LANDING_BLOCK_32_13-NAME'),
		'section' => array('image'),
		'dynamic' => false,
	),
	'cards' => array(),
	'nodes' => array(
		'.landing-block-node-img-small' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-IMG2'),
			'type' => 'img',
			'useInDesigner' => false,
			'dimensions' => array('width' => 370),
			'allowInlineEdit' => false,
			'create2xByDefault' => false,
		),
		'.landing-block-node-img-big' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-IMG1'),
			'type' => 'img',
			'useInDesigner' => false,
			'dimensions' => array('width' => 710),
			'allowInlineEdit' => false,
			'create2xByDefault' => false,
		),
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-TXT'),
			'type' => 'text',
		),
	),
	'style' => array(
		'.landing-block-node-img-title' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-TXT'),
			'type' => 'typo',
		),
		'.landing-block-node-img-container-left' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-IMG'),
			'type' => ['animation', 'border-radius'],
		),
		'.landing-block-node-img-container-right-top' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-IMG'),
			'type' => ['animation', 'border-radius'],
		),
		'.landing-block-node-img-container-right-bottom' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_32_13-IMG'),
			'type' => ['animation', 'border-radius'],
		),
	),
);