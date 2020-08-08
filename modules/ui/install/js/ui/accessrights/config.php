<!--"rel" => [""]-->

<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/accessrights/css/style.css",
	"js" => [
		"/bitrix/js/ui/accessrights/grid.js",
		"/bitrix/js/ui/accessrights/section.js",
		"/bitrix/js/ui/accessrights/column.js",
		"/bitrix/js/ui/accessrights/column.item.js",
	],
	"bundle_js" => "ui_accessrights",
	"bundle_css" => "ui_accessrights",
	"rel" => ["ui.switcher"]
];