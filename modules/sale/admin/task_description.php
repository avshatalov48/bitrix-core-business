<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"STATUS" => array(
		"title" => Loc::getMessage("TASK_BINDING_STATUS"),
	),

	"SALE_STATUS_NONE" => array(
		"title" => Loc::getMessage('TASK_NAME_SALE_STATUS_NONE'),
		"description" => Loc::getMessage('TASK_DESC_SALE_STATUS_NONE'),
	),
	"SALE_STATUS_ALL" => array(
		"title" => Loc::getMessage('TASK_NAME_SALE_STATUS_ALL'),
		"description" => Loc::getMessage('TASK_DESC_SALE_STATUS_ALL'),
	),
);
