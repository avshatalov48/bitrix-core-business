<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$requisites = \Bitrix\Landing\Connector\Crm::getMyRequisites();
$values = [
	'empty' => GetMessage('LANDING_BLOCK_CRM_REQ_EMPTY'),
];
$bankValues = [
	'empty' => GetMessage('LANDING_BLOCK_CRM_REQ_EMPTY'),
];
if (\Bitrix\Main\Loader::includeModule('landing'))
{
	$values = array_merge(
		$values,
		\Bitrix\Landing\Connector\Crm::getMyRequisitesPlainList($requisites)
	);
	$bankValues = array_merge(
		$bankValues,
		\Bitrix\Landing\Connector\Crm::getMyRequisitesPlainList($requisites, 'bankRequisites')
	);
}

$requisiteDataItems = [];
$bankRequisiteDataItems = [];
if (!empty($requisites))
{
	foreach ($requisites as $requisite)
	{
		foreach ($requisite['requisites'] as $requisiteItem)
		{
			$data = array_column($requisiteItem['data'], 'title', 'name');
			$requisiteDataItems += $data;
		}
		foreach ($requisite['bankRequisites'] as $requisiteItem)
		{
			$data = array_column($requisiteItem['bankData'], 'title', 'name');
			$bankRequisiteDataItems += $data;
		}
	}
}

$contactsValues = [
	'web' => GetMessage('LANDING_BLOCK_CRM_REQ_CONTACTS_SITE'),
	'phone' => GetMessage('LANDING_BLOCK_CRM_REQ_CONTACTS_PHONE'),
	'email' => GetMessage('LANDING_BLOCK_CRM_REQ_CONTACTS_MAIL'),
];

$arComponentParameters = [
	'PARAMETERS' => [
		'REQUISITE' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_SELECT'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => $values,
		],
		'BANK_REQUISITE' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_SELECT'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'N',
			'VALUES' => $bankValues,
		],
		'HIDE_CONTACTS_DATA' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_HIDE_FIELDS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $contactsValues,
		],
		'HIDE_REQUISITES_DATA' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_HIDE_FIELDS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $requisiteDataItems,
		],
		'HIDE_BANK_DATA' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_HIDE_FIELDS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => $bankRequisiteDataItems,
		],
		'PRIMARY_ICON' => [
			'NAME' => GetMessage('LANDING_BLOCK_CRM_REQ_ICON_COLOR'),
			'TYPE' => 'CHECKBOX',
		],
	],
];
