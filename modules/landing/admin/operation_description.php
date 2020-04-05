<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	'LANDING_READ' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_READ')
	),
	'LANDING_EDIT' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_EDIT')
	),
	'LANDING_SETT' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_SETT')
	),
	'LANDING_PUBLIC' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_PUBLIC')
	),
	'LANDING_DELETE' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_DELETE')
	)
);