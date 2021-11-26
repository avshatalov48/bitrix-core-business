<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

return [
	'js' => './dist/renderparts.bundle.js',
	'lang_additional' => [
		'SONET_RENDERPARTS_JS_DESTINATION_ALL' => (
			ModuleManager::isModuleInstalled('intranet')
				? Loc::getMessage('SONET_RENDERPARTS_JS_DESTINATION_ALL')
				: Loc::getMessage('SONET_RENDERPARTS_JS_DESTINATION_ALL_BUS')
		),
	],
	'rel' => [
		'main.core',
		'tasks.comment-renderer',
	],
	'skip_core' => false,
];
