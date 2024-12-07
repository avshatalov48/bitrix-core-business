<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('LNDNGBLCK_66_90_NAME'),
		'section' => ['forms'],
		'system' => true,
		'dynamic' => false,
		'subtype' => 'form',
	],
];