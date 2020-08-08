<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/media.bundle.js',
	],
	'css' => [
		'./dist/media.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.progressbarjs.uploader',
		'ui.vue.vuex',
		'im.model',
		'im.const',
		'ui.vue.components.audioplayer',
		'ui.vue.directives.lazyload',
		'ui.icons',
		'ui.vue.components.socialvideo',
		'im.lib.utils',
		'ui.vue',
	],
	'skip_core' => true,
];