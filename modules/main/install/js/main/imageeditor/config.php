<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/main/imageeditor/js/locale.js',
		'/bitrix/js/main/imageeditor/js/editor.js'
	],

	'css' => [
		'/bitrix/js/main/imageeditor/css/style.css'
	],

	'rel' => [
		'main.polyfill.promise'
	],

	'bundle_js' => 'main_imageeditor',
	'bundle_css' => 'main_imageeditor'
];