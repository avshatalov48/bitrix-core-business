<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.popup',
		'ui.entity-selector',
		'im.public',
		'im.v2.application.core',
		'im.v2.provider.service',
		'im.v2.const',
		'im.v2.component.search.search-result',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];