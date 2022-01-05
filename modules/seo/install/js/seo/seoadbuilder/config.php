<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/seoadbuilder.bundle.css',
	'js' => 'dist/seoadbuilder.bundle.js',
	'rel' => [
		'main.popup',
		'ui.buttons',
		'seo.ads.login',
		'catalog.product-selector',
		'main.core.events',
		'ui.textcrop',
		'main.core',
		'ui.entity-selector',
	],
	'skip_core' => false,
];