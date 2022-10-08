<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js'  => [
		'/bitrix/js/report/js/activitywidget/activitywidget.js',
	],
	'css' => [
		'/bitrix/js/report/js/activitywidget/css/activitywidget.css',
	],
	'rel' => ['ui.design-tokens', 'ui.fonts.opensans', 'popup',],
	'bundle_js' => 'activitywidget',
	'bundle_css' => 'activitywidget',
];