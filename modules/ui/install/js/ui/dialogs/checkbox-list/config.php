<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bundle.css',
	'js' => 'dist/bundle.js',
	'rel' => [
		'checkbox-list.css',
		'main.popup',
		'ui.design-tokens',
		'ui.vue3',
		'ui.forms',
		'ui.switcher',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];