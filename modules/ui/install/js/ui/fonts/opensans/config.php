<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (defined('AIR_SITE_TEMPLATE'))
{
	return [];
}

return [
	"css" => "/bitrix/js/ui/fonts/opensans/ui.font.opensans.css",
	"rel" => [
		"ui.design-tokens"
	],
];
