<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/element/file/dist/file.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/element/file/dist/file.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.directives.lazyload',
		'ui.icons',
		'ui.vue',
		'im.model',
	],
	'skip_core' => true,
];