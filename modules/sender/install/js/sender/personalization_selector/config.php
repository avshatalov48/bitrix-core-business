<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/personalization_selector.bundle.css',
	'js' => 'dist/personalization_selector.bundle.js',
	'rel' => [
		'main.core',
		'ui.entity-selector',
	],
	'skip_core' => false,
];