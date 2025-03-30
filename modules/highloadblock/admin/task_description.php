<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

return [
	'HBLOCK_DENIED' => [
		'title' => Loc::getMessage('TASK_NAME_HBLOCK_DENIED'),
		'description' => Loc::getMessage('TASK_DESC_HBLOCK_DENIED'),
	],
	'HBLOCK_READ' => [
		'title' => Loc::getMessage('TASK_NAME_HBLOCK_READ'),
		'description' => Loc::getMessage('TASK_DESC_HBLOCK_READ'),
	],
	'HBLOCK_WRITE' => [
		'title' => Loc::getMessage('TASK_NAME_HBLOCK_WRITE'),
		'description' => Loc::getMessage('TASK_DESC_HBLOCK_WRITE'),
	],
];
