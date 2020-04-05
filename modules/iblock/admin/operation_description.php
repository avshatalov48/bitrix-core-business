<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"IBLOCK_ADMIN_DISPLAY" => array(
		"title" => Loc::getMessage('OP_NAME_IBLOCK_ADMIN_DISPLAY'),
		"description" => Loc::getMessage('OP_DESC_IBLOCK_ADMIN_DISPLAY'),
	),
	"IBLOCK_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_IBLOCK_EDIT'),
		"description" => Loc::getMessage('OP_DESC_IBLOCK_EDIT'),
	),
	"IBLOCK_DELETE" => array(
		"title" => Loc::getMessage('OP_NAME_IBLOCK_DELETE'),
		"description" => Loc::getMessage('OP_DESC_IBLOCK_DELETE'),
	),
	"IBLOCK_RIGHTS_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_IBLOCK_RIGHTS_EDIT'),
		"description" => Loc::getMessage('OP_DESC_IBLOCK_RIGHTS_EDIT'),
	),
	"IBLOCK_EXPORT" => array(
		"title" => Loc::getMessage('OP_NAME_IBLOCK_EXPORT'),
		"description" => Loc::getMessage('OP_DESC_IBLOCK_EXPORT'),
	),
	"SECTION_READ" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_READ'),
		"description" => Loc::getMessage('OP_DESC_SECTION_READ'),
	),
	"SECTION_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_EDIT'),
		"description" => Loc::getMessage('OP_DESC_SECTION_EDIT'),
	),
	"SECTION_DELETE" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_DELETE'),
		"description" => Loc::getMessage('OP_DESC_SECTION_DELETE'),
	),
	"SECTION_ELEMENT_BIND" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_ELEMENT_BIND'),
		"description" => Loc::getMessage('OP_DESC_SECTION_ELEMENT_BIND'),
	),
	"SECTION_SECTION_BIND" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_SECTION_BIND'),
		"description" => Loc::getMessage('OP_DESC_SECTION_SECTION_BIND'),
	),
	"SECTION_RIGHTS_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_SECTION_RIGHTS_EDIT'),
		"description" => Loc::getMessage('OP_DESC_SECTION_RIGHTS_EDIT'),
	),
	"ELEMENT_READ" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_READ'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_READ'),
	),
	"ELEMENT_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_EDIT'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_EDIT'),
	),
	"ELEMENT_EDIT_ANY_WF_STATUS" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_EDIT_ANY_WF_STATUS'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_EDIT_ANY_WF_STATUS'),
	),
	"ELEMENT_EDIT_PRICE" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_EDIT_PRICE'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_EDIT_PRICE'),
	),
	"ELEMENT_DELETE" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_DELETE'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_DELETE'),
	),
	"ELEMENT_BIZPROC_START" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_BIZPROC_START'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_BIZPROC_START'),
	),
	"ELEMENT_RIGHTS_EDIT" => array(
		"title" => Loc::getMessage('OP_NAME_ELEMENT_RIGHTS_EDIT'),
		"description" => Loc::getMessage('OP_DESC_ELEMENT_RIGHTS_EDIT'),
	),
);
