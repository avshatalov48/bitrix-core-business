<?php

use Bitrix\Main\Config\Ini;
use Bitrix\UI\FileUploader\Configuration;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$defaultConfig = \Bitrix\Main\Config\Configuration::getValue('ui');
$settings = [];
if (isset($defaultConfig['uploader']['settings']) && is_array($defaultConfig['uploader']['settings']))
{
	$settings = $defaultConfig['uploader']['settings'];
}

$megabyte = 1024 * 1024;
$cloud = \Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') && defined('BX24_HOST_NAME');
$maxFileSize = min(Ini::getInt('post_max_size'), Ini::getInt('upload_max_filesize'));

$chunkMaxSize = $cloud ? 100 * $megabyte : $maxFileSize;
$chunkMaxSize = isset($settings['chunkMaxSize']) ? Ini::unformatInt($settings['chunkMaxSize']) : $chunkMaxSize;
$chunkMaxSize = min($chunkMaxSize, $maxFileSize);

$chunkMinSize = $cloud ? 5 * $megabyte : $megabyte;
$chunkMinSize = isset($settings['chunkMinSize']) ? Ini::unformatInt($settings['chunkMinSize']) : $chunkMinSize;
$chunkMinSize = min($chunkMinSize, $chunkMaxSize);

$defaultChunkSize = 10 * $megabyte;
$defaultChunkSize = isset($settings['defaultChunkSize']) ? Ini::unformatInt($settings['defaultChunkSize']) : $defaultChunkSize;
$defaultChunkSize = min(max($chunkMinSize, $defaultChunkSize), $chunkMaxSize);

\Bitrix\Main\Loader::includeModule('ui');
$defaultConfig = new Configuration();

return [
	'js' => 'dist/ui.uploader.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
	'lang' => [
		'/bitrix/modules/ui/lib/FileUploader/UserErrors.php',
	],
	'settings' => [
		'chunkMinSize' => $chunkMinSize,
		'chunkMaxSize' => $chunkMaxSize,
		'defaultChunkSize' => $defaultChunkSize,

		'maxFileSize' => $defaultConfig->getMaxFileSize(),
		'minFileSize' => $defaultConfig->getMinFileSize(),
		'imageMinWidth' => $defaultConfig->getImageMinWidth(),
		'imageMinHeight' => $defaultConfig->getImageMinHeight(),
		'imageMaxWidth' => $defaultConfig->getImageMaxWidth(),
		'imageMaxHeight' => $defaultConfig->getImageMaxHeight(),
		'imageMaxFileSize' => $defaultConfig->getImageMaxFileSize(),
		'imageMinFileSize' => $defaultConfig->getImageMinFileSize(),
		'acceptOnlyImages' => $defaultConfig->shouldAcceptOnlyImages(),
		'acceptedFileTypes' => empty($defaultConfig->getAcceptedFileTypes()) ? null : $defaultConfig->getAcceptedFileTypes(),
		'ignoredFileNames' => $defaultConfig->getIgnoredFileNames(),

		'imageExtensions' => Configuration::getImageExtensions(),
	],
];
