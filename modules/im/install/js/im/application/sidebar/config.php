<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/sidebar.bundle.js',
	],
	'css' => [
		'./dist/sidebar.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'im.application.core',
		'ui.vue',
		'ui.vue.vuex',
		'im.view.list.recent',
		'im.view.list.sidebar',
	],
	'skip_core' => true,
];