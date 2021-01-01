<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "/bitrix/js/ui/layout-form/ui.layout.form.css",
	'js' => "/bitrix/js/ui/layout-form/dist/layout-form.bundle.js",
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
