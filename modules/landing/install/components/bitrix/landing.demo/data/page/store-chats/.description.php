<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-chats',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_CHATS--NAME'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' => array(
		'RULE' => NULL,
	),
	
	'disable_import' => 'Y',
	'site_group' => 'Y',
	'site_group_items' => [
		0 => [
			'code' => 'store-chats-dark',
			'page' => 'mainpage',
			'color' => '#000000',
		],
		1 => [
			'code' => 'store-chats-light',
			'page' => 'mainpage',
			'color' => '#F6F6F9',
		],
	],
);