<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/uploader.bundle.css',
	'js' => 'dist/uploader.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'main.core.minimal',
	],
	'skip_core' => true,
	'lang_additional' => array(
		'isCloud' => \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') ? 'Y' : 'N',
		"phpPostMaxSize" => CUtil::Unformat(ini_get("post_max_size")),
		"phpUploadMaxFilesize" => CUtil::Unformat(ini_get("upload_max_filesize")),
	)
];