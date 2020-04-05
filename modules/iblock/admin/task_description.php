<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"IBLOCK_DENY" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_DENY'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_DENY'),
	),
	"IBLOCK_READ" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_READ'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_READ'),
	),
	"IBLOCK_ELEMENT_ADD" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_ELEMENT_ADD'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_ELEMENT_ADD'),
	),
	"IBLOCK_ADMIN_READ" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_ADMIN_READ'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_ADMIN_READ'),
	),
	"IBLOCK_ADMIN_ADD" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_ADMIN_ADD'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_ADMIN_ADD'),
	),
	"IBLOCK_LIMITED_EDIT" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_LIMITED_EDIT'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_LIMITED_EDIT'),
	),
	"IBLOCK_FULL_EDIT" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_FULL_EDIT'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_FULL_EDIT'),
	),
	"IBLOCK_FULL" => array(
		"title" => Loc::getMessage('TASK_NAME_IBLOCK_FULL'),
		"description" => Loc::getMessage('TASK_DESC_IBLOCK_FULL'),
	),
	"IBLOCK" => array(
		"title" => Loc::getMessage('TASK_BINDING_IBLOCK'),
	),
);
