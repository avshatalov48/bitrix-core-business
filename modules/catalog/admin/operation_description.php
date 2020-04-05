<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"CATALOG_READ" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_READ"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_READ")
	),
	"CATALOG_SETTINGS" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_SETTINGS"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_SETTINGS")
	),
	"CATALOG_PRICE" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_PRICE"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_PRICE")
	),
	"CATALOG_GROUP" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_GROUP"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_GROUP")
	),
	"CATALOG_DISCOUNT" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_DISCOUNT"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_DISCOUNT")
	),
	"CATALOG_VAT" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_VAT"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_VAT")
	),
	"CATALOG_EXTRA" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_EXTRA"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_EXTRA")
	),
	"CATALOG_EXPORT_EDIT" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_EXPORT_EDIT"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_EXPORT_EDIT")
	),
	"CATALOG_EXPORT_EXEC" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_EXPORT_EXEC"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_EXPORT_EXEC")
	),
	"CATALOG_IMPORT_EDIT" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_IMPORT_EDIT"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_IMPORT_EDIT")
	),
	"CATALOG_IMPORT_EXEC" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_IMPORT_EXEC"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_IMPORT_EXEC")
	),
	"CATALOG_PURCHAS_INFO" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_PURCHAS_INFO"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_PURCHAS_INFO")
	),
	"CATALOG_STORE" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_STORE"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_STORE")
	),
	"CATALOG_MEASURE" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_MEASURE"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_MEASURE")
	),
	"CATALOG_VIEW" => array(
		"title" => Loc::getMessage("OP_NAME_CATALOG_VIEW"),
		"description" => Loc::getMessage("OP_DESC_CATALOG_VIEW")
	)
);