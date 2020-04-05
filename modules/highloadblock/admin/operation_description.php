<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	'HL_ELEMENT_READ' => array(
		'title' => Loc::getMessage('OP_NAME_HL_ELEMENT_READ'),
		'description' => Loc::getMessage('OP_DESC_HL_ELEMENT_READ'),
	),
	'HL_ELEMENT_WRITE' => array(
		'title' => Loc::getMessage('OP_NAME_HL_ELEMENT_WRITE'),
		'description' => Loc::getMessage('OP_DESC_HL_ELEMENT_WRITE'),
	),
	'HL_ELEMENT_DELETE' => array(
		'title' => Loc::getMessage('OP_NAME_HL_ELEMENT_DELETE'),
		'description' => Loc::getMessage('OP_DESC_HL_ELEMENT_DELETE'),
	),
);
