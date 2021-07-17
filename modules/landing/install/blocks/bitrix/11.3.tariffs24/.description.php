<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Main\Localization\Loc;

return [
	'block' => [
		'name' => Loc::getMessage('BLOCK_11_3_NAME'),
		'section' => ['tariffs'],
		'namespace' => 'bitrix',
		'only_for_license' => 'nfr',
	],
	'nodes' => [],
	'style' => [],
];