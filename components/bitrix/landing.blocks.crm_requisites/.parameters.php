<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentParameters = [
	'PARAMETERS' => [
		'REQUISITE' => [
			'NAME' => GetMessage('LNDNG_BLPHB_REQUISITE'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => \Bitrix\Main\Loader::includeModule('landing') ? \Bitrix\Landing\Connector\Crm::getMyRequisitesPlainList() : [],
			'DEFAULT' => '',
		],
	],
];
