<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_FORM_33_3'),
		'section' => array('forms'),
		'dynamic' => false,
		'subtype' => 'form',
	),
	'nodes' => array(
		'.landing-block-node-bgimg' => array(
			'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.3_NODE_BGIMG'),
			'type' => 'img',
			'editInStyle' => true,
			'allowInlineEdit' => false,
			'dimensions' => array('width' => 1920, 'height' => 1080),
			'create2xByDefault' => false,
		),
	),
	'style' => array(
		'block' => array(
			'type' => ['background', 'block-default-background-overlay'],
		),
		'nodes' => array(
			'.landing-block-node-bgimg' => array(
				'name' => Loc::getMessage('LANDING_BLOCK_FORM_33.3_NODE_BGIMG'),
				'type' => 'background-attachment',
			),
		),
	),
	'assets' => array(
		'ext' => array('landing_form'),
	),
);