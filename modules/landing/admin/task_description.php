<?php
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return array(
	'LANDING_RIGHT_DENIED' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_DENIED')
	),
	'LANDING_RIGHT_READ' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_READ')
	),
	'LANDING_RIGHT_EDIT' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_EDIT')
	),
	'LANDING_RIGHT_SETT' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_SETT')
	),
	'LANDING_RIGHT_PUBLIC' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_PUBLIC')
	),
	'LANDING_RIGHT_DELETE' => array(
		'title' => Loc::getMessage('LANDING_OP_NAME_DELETE')
	)
);