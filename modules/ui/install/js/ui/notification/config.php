<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/notification/ui.notification.css",
	"js" => [
		"/bitrix/js/ui/notification/ui.notification.balloon.js",
		"/bitrix/js/ui/notification/ui.notification.stack.js",
		"/bitrix/js/ui/notification/ui.notification.center.js",
	],
	"bundle_js" => "ui_notification",
	"bundle_css" => "ui_notification",
	"rel" => [
		"ui.design-tokens",
	],
];