<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'block' => array(
		'name' => Loc::getMessage('LD_BLOCK_STORE_BREADCRUMB'),
		'section' => array('store'),
		'type' => 'store',
		'html' => false,
	),
	'nodes' => array(),
);