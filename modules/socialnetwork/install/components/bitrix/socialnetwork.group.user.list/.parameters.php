<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

$arComponentParameters = array(
	'PARAMETERS' => array(

		'PATH_TO_USER' => array(
			"TYPE" => "STRING",
			"NAME" => Loc::getMessage('SOCIALNETWORK_GROUP_USER_LIST_PARAMS_PATH_TO_USER'),
			"DEFAULT" => '/company/personal/user/#user_id#/',
			'PARENT' => 'BASE'
		),

	),
);
