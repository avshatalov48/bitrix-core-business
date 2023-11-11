<?php

use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/analytics.bundle.js',
	'rel' => [
		'main.core',
	],
	'settings' => [
		'collectData' => ModuleManager::isModuleInstalled('bitrix24'),
	],
];