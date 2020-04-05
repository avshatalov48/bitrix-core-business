<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/main/imageeditor/external/photoeditorsdk/js/photo-editor-sdk.js',
		'/bitrix/js/main/imageeditor/external/photoeditorsdk/js/photo-editor-sdk.ui.desktop-ui.js'
	],

	'css' => [
		'/bitrix/js/main/imageeditor/external/photoeditorsdk/css/photo-editor-sdk.ui.desktop-ui.css',
	],

	'bundle_js' => 'main_imageeditor_photoeditorsdk',
	'bundle_css' => 'main_imageeditor_photoeditorsdk'
];