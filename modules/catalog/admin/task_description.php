<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"CATALOG_DENIED" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_DENIED"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_DENIED")
	),
	"CATALOG_VIEW" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_VIEW"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_VIEW")
	),
	"CATALOG_READ" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_READ"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_READ")
	),
	"CATALOG_PRICE_EDIT" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_PRICE_EDIT"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_PRICE_EDIT")
	),
	"CATALOG_STORE_EDIT" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_STORE_EDIT"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_STORE_EDIT")
	),
	"CATALOG_EXPORT_IMPORT" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_EXPORT_IMPORT"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_EXPORT_IMPORT")
	),
	"CATALOG_FULL_ACCESS" => array(
		"title" => Loc::getMessage("TASK_NAME_CATALOG_FULL_ACCESS"),
		"description" => Loc::getMessage("TASK_DESC_CATALOG_FULL_ACCESS")
	)
);