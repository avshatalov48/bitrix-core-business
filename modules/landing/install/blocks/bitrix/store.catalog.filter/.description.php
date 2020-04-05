<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_FLT_NAME'),
		'section' => array('store'),
		'type' => 'null',
		'html' => false,
		'namespace' => 'bitrix',
	),
	'nodes' => array(
		'bitrix:catalog.smart.filter' => array(
			'type' => 'component',
			'extra' => array(
				'editable' => array(),
			),
		),
	),
	'style' => array(
		'block' => array(
			'type' => array('block-default-wo-background'),
		),
		'nodes' => array(
			'.bx-sidebar-block' => array(
				'name' => 'Inner block',
				'type' => array('padding-top', 'padding-bottom'),
			),
		),
	),
);