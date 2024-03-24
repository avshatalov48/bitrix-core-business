<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/sidepanel-content/ui.sidepanel-content.css",
	'js' => 'dist/ui.sidepanel-content.js',
	'rel' => [
		'main.core',
		'main.sidepanel',
		'ui.sidepanel.layout',
		'ui.helper',
	],
	'skip_core' => false,
];
