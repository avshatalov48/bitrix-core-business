<?php

use Bitrix\Landing\Hook\Page\B24button;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$values = [];
$buttons = B24button::getButtonsData();
if (!empty($buttons))
{
	foreach ($buttons as $button)
	{
		$values[$button['ID']] = $button['NAME'];
	}
}
else
{
	$values['N'] = Loc::getMessage('LANDING_CMP_OL_NO_BUTTONS');
}

$arComponentParameters = [
	'PARAMETERS' => [
		'BUTTON_ID' => [
			'NAME' => Loc::getMessage('LANDING_CMP_BUTTON_ID'),
			'TYPE' => 'LIST',
			'VALUES' => $values,
		],
		'SITE_TYPE' => [
			'NAME' => Loc::getMessage('LANDING_PARAMS_SITE_TYPE'),
			'TYPE' => 'STRING'
		],
	],
];