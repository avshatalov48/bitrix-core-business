<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/actionpanel/css/style.css",
	"js" => [
		"/bitrix/js/ui/actionpanel/panel.js",
		"/bitrix/js/ui/actionpanel/item.js"
	],
	"bundle_js" => "ui_actionpanel",
	"bundle_css" => "ui_actionpanel"
];