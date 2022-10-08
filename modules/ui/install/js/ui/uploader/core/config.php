<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$postMaxSize = \CUtil::unformat(ini_get('post_max_size'));
$uploadMaxFileSize = \CUtil::unformat(ini_get('upload_max_filesize'));
$maxFileSize = min($postMaxSize, $uploadMaxFileSize);

$megabyte = 1024 * 1024;
$chunkMaxSize = $maxFileSize;
$chunkMinSize = $maxFileSize > $megabyte ? $megabyte : $maxFileSize;

$cloud = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') && defined('BX24_HOST_NAME');
if ($cloud)
{
	$chunkMinSize = 5 * $megabyte;
	$chunkMaxSize = 100 * $megabyte;
}

$defaultChunkSize = 10 * $megabyte;
$defaultChunkSize = min(max($chunkMinSize, $defaultChunkSize), $chunkMaxSize);

$imageExtensions = \Bitrix\UI\FileUploader\Configuration::getImageExtensions();

return [
	'js' => 'dist/ui.uploader.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
	'lang' => [
		'/bitrix/modules/ui/FileUploader/UserErrors.php',
	],
	'settings' => [
		'chunkMinSize' => $chunkMinSize,
		'chunkMaxSize' => $chunkMaxSize,
		'defaultChunkSize' => $defaultChunkSize,
		'imageExtensions' => $imageExtensions,
	],
];
