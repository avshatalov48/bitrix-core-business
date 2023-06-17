<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
	'CATALOG_DENIED' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_DENIED'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_DENIED'),
	],
	'CATALOG_VIEW' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_VIEW'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_VIEW'),
	],
	'CATALOG_READ' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_READ'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_READ'),
	],
	'CATALOG_PRICE_EDIT' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_PRICE_EDIT'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_PRICE_EDIT'),
	],
	'CATALOG_STORE_EDIT' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_STORE_EDIT'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_STORE_EDIT'),
	],
	'CATALOG_EXPORT_IMPORT' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_EXPORT_IMPORT'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_EXPORT_IMPORT'),
	],
	'CATALOG_FULL_ACCESS' => [
		'title' => Loc::getMessage('TASK_NAME_CATALOG_FULL_ACCESS'),
		'description' => Loc::getMessage('TASK_DESC_CATALOG_FULL_ACCESS'),
	],
];
