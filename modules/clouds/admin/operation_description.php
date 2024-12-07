<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

return [
	'CLOUDS_BROWSE' => [
		'title' => Loc::getMessage('OP_NAME_CLOUDS_BROWSE'),
	],
	'CLOUDS_UPLOAD' => [
		'title' => Loc::getMessage('OP_NAME_CLOUDS_UPLOAD'),
	],
	'CLOUDS_CONFIG' => [
		'title' => Loc::getMessage('OP_NAME_CLOUDS_CONFIG'),
	],
];
