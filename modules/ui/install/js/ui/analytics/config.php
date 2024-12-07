<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$hasCollectDataOption = Option::get('ui', 'ui_analytics_collect_data', 'N') === 'Y';

return [
	'js' => 'dist/analytics.bundle.js',
	'rel' => [
		'main.core',
	],
	'settings' => [
		'collectData' => ModuleManager::isModuleInstalled('bitrix24') || $hasCollectDataOption,
	],
];