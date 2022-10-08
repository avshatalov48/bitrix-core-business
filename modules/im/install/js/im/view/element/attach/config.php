<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/attach.bundle.js',
	],
	'css' => [
		'./dist/attach.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.icons.disk',
		'ui.vue.directives.lazyload',
		'im.model',
		'im.lib.utils',
		'ui.vue',
	],
	'skip_core' => true,
];