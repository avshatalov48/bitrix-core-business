<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	"SEO_SETTINGS" => array(
		"title" => Loc::getMessage('OP_NAME_SEO_SETTINGS'),
	),
	"SEO_TOOLS" => array(
		"title" => Loc::getMessage('OP_NAME_SEO_TOOLS'),
	),
	"SEO_TOOLS_SITE" => array(
		"title" => Loc::getMessage('OP_NAME_SEO_TOOLS_SITE'),
	),
);
