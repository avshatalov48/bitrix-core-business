<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/switcher/ui.switcher.css",
	"js" => "/bitrix/js/ui/switcher/dist/ui.switcher.bundle.js",
	"lang" => "/bitrix/modules/ui/install/ui.switcher.php",
	'rel' => [
		'main.core',
		'ui.design-tokens',
	],
	'skip_core' => false,
];