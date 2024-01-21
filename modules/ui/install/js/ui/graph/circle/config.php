<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "dist/ui.circle.bundle.css",
	"js" => "dist/ui.circle.bundle.js",
	'rel' => [
		'main.core',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];
