<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => ["ui.cnt.css", 'dist/cnt.bundle.css'],
	'js' => 'dist/cnt.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'main.core',
		'ui.design-tokens.air',
	],
	'skip_core' => false,
];
