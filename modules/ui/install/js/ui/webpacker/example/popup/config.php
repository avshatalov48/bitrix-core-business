<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"js" => "/bitrix/js/ui/webpacker/example/popup/ui.webpacker.example.popup.js",
	"css" => "/bitrix/js/ui/webpacker/example/popup/ui.webpacker.example.popup.css",
	"layout" => "/bitrix/js/ui/webpacker/example/popup/ui.webpacker.example.popup.html",
	"lang" => "/bitrix/modules/ui/install/index.php",
	"options" => [
		"webpacker" => [
			"useAllLangs" => true,
			"useLangCamelCase" => true,
			"deleteLangPrefixes" => ["UI_"],
		]
	]
];