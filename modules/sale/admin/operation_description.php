<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"SALE_STATUS_VIEW" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_VIEW'),
	),
	"SALE_STATUS_CANCEL" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_CANCEL'),
	),
	"SALE_STATUS_MARK" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_MARK'),
	),
	"SALE_STATUS_DELIVERY" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_DELIVERY'),
	),
	"SALE_STATUS_DEDUCTION" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_DEDUCTION'),
	),
	"SALE_STATUS_PAYMENT" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_PAYMENT'),
	),
	"SALE_STATUS_TO" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_TO'),
	),
	"SALE_STATUS_UPDATE" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_UPDATE'),
	),
	"SALE_STATUS_DELETE" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_DELETE'),
	),
	"SALE_STATUS_FROM" => array(
		"title" => Loc::getMessage('OP_NAME_SALE_STATUS_FROM'),
	),
);
