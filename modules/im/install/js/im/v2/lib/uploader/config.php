<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/uploader.bundle.js',
	],
	'rel' => [
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
	'settings' => array(
		'isCloud' => \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'),
		"phpPostMaxSize" => \Bitrix\Main\Config\Ini::unformatInt(ini_get("post_max_size")),
		"phpUploadMaxFilesize" => \Bitrix\Main\Config\Ini::unformatInt(ini_get("upload_max_filesize")),
	)
];