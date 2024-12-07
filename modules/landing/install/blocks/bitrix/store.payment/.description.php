<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LANDING_BLOCK_STORE.PAY_NAME'),
		'section' => array('store'),
		'system' => true,
		'html' => false,
		'namespace' => 'bitrix',
	),
	'nodes' => array(),
);