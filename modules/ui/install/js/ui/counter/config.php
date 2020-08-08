<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

// An extension is a synonym. Protection against blocking scripts by AdBlock (Opera / Ticket #126480).
return [
	"css" => [
		"/bitrix/js/ui/cnt/ui.cnt.css",
	],
	"js" => "/bitrix/js/ui/cnt/ui.cnt.js",
	"rel" => "ui.fonts.opensans"
];