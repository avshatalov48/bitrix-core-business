<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/graph/circle/ui.circle.css",
	"js" => "/bitrix/js/ui/graph/circle/ui.circle.bundle.js",
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
