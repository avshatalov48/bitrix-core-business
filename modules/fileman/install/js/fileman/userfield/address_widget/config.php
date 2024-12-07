<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/address_widget.bundle.css',
	'js' => 'dist/address_widget.bundle.js',
	'rel' => [
		'main.core.events',
		'location.core',
		'location.widget',
		'main.core',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
