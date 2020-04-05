<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"js" => [
		"/bitrix/js/ui/webpacker/example/simple/ui.webpacker.example.simple.js",
	],
	"rel" => [
		"ui.webpacker",
		"ui.webpacker.example.popup"
	],
	"options" => [
		"webpacker" => [
			"callMethod" => "window.WebPackerExample.init",
			"properties" => [
				"myFavoriteColor" => "yellow",
				"milk" => true,
			]
		]
	]
];