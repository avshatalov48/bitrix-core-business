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
		'ui.entity-selector',
		'im.v2.application.core',
		'im.v2.provider.service',
		'main.popup',
		'im.v2.component.elements',
		'main.core.events',
		'im.public',
		'im.v2.const',
		'im.v2.component.search.chat-search-input',
		'im.v2.component.search.chat-search',
	],
	'skip_core' => true,
];