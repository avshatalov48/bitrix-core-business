<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'code' => 'store_v3/technicalpages',
	'name' => Loc::getMessage('LANDING_DEMO_STORE_TECHNICALPAGES_NAME'),
	'description' => null,
	'type' => 'store',
	'version' => 3,
	'fields' => [
		'RULE' => null,
		'ADDITIONAL_FIELDS' => [],
	],
	'layout' => [],
	'items' => [],
];