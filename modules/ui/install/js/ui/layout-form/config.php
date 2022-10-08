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
		'ui.design-tokens',
		'ui.forms',
		'main.core.events',
	],
	'skip_core' => false,
];
