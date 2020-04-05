<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"CLOUDS_BROWSE" => array(
		"title" => Loc::getMessage('OP_NAME_CLOUDS_BROWSE'),
	),
	"CLOUDS_UPLOAD" => array(
		"title" => Loc::getMessage('OP_NAME_CLOUDS_UPLOAD'),
	),
	"CLOUDS_CONFIG" => array(
		"title" => Loc::getMessage('OP_NAME_CLOUDS_CONFIG'),
	),
);
