<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_STORE.CART_NAME'),
		'section' => array('store'),
		'type' => 'null',
		'html' => false,
	),
	'nodes' => array(),
);