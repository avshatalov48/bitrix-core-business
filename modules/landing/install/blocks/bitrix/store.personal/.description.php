<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_CATALOG_PEROSNAL_NAME'),
		'section' => array('store'),
		'system' => true,
		'subtype' => 'menu',
		'subtype_params' => array(
			'selector' => '.landing-block-node-menu-list-item-link',
			'source' => 'personal',
		),
		'namespace' => 'bitrix',
	),
	'nodes' => array(),
	'style' => array(),
);