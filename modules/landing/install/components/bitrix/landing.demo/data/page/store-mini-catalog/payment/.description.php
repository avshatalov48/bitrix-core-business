<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return array(
	'code' => 'store-mini-catalog/payment',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_MINI_CATALOG_PAYMENT_TXT_1'),
	'description' => NULL,
	'type' => 'store',
	'version' => 2,
	'fields' =>array(
			'RULE' => NULL,
			'ADDITIONAL_FIELDS' =>array(
					'THEME_CODE' => 'event',
					'THEME_CODE_TYPO' => 'event',
					'VIEW_USE' => 'N',
					'VIEW_TYPE' => 'no',
				),
		),
	'layout' =>array(),
	'items' =>array(
			0 =>array(
					'code' => 'store.payment',
					'cards' =>array(),
					'nodes' =>array(),
					'style' =>array(),
					'attrs' =>array(),
				),
		),
);