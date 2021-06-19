<?php

use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$arComponentDescription = [
	'NAME' => Loc::getMessage('SOF_DEFAULT_TEMPLATE_NAME1'),
	'DESCRIPTION' => Loc::getMessage('SOF_DEFAULT_TEMPLATE_DESCRIPTION'),
	'ICON' => '/images/sale_order_full.gif',
	'PATH' => [
		'ID' => 'e-store',
		'CHILD' => [
			'ID' => 'sale_order_checkout',
			'NAME' => Loc::getMessage('SOF_NAME'),
		],
	],
];