<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => './dist/viewer.bundle.css',
	"js" => [
		"/bitrix/js/ui/viewer/ui.viewer.item.js",
		"/bitrix/js/ui/viewer/ui.viewer.js",
		"/bitrix/js/ui/viewer/dist/viewer.bundle.js",
	],
	'rel' => [
		'ajax',
		'loader',
		'main.popup',
		'ui.icon-set.actions',
		'ui.icon-set.main',
		'ui.icons.generator',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
];
