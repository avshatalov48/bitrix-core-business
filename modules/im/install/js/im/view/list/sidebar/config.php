<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/sidebar.bundle.js',
	],
	'css' => [
		'./dist/sidebar.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.components.list',
		'im.view.list.item.sidebar',
		'ui.vue',
		'im.lib.logger',
	],
	'skip_core' => true,
];