<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bundle.css',
	'js' => 'dist/bundle.js',
	'rel' => [
		'ui.design-tokens',
		'main.popup',
		'ui.vue3',
		'ui.switcher',
		'ui.forms',
		'main.core.events',
		'main.core',
		'checkbox-list.css',
	],
	'skip_core' => false,
];