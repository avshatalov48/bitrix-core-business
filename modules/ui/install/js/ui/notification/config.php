<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	"css" => "/bitrix/js/ui/notification/ui.notification.css",
	"js" => [
		"/bitrix/js/ui/notification/ui.notification.balloon.js",
		"/bitrix/js/ui/notification/ui.notification.stack.js",
		"/bitrix/js/ui/notification/ui.notification.center.js",
	],
	"bundle_js" => "ui_notification"
);