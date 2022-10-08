<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/address_widget.bundle.css',
	'js' => 'dist/address_widget.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'location.widget',
		'location.core',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
