<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/progressbarjs/uploader/dist/uploader.bundle.js',
	],
	'css' => [
		'/bitrix/js/ui/progressbarjs/uploader/dist/uploader.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.progressbarjs',
	],
	'skip_core' => true,
];