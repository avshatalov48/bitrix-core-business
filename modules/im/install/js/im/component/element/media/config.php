<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/im/component/element/media/dist/media.bundle.js',
	],
	'css' => [
		'/bitrix/js/im/component/element/media/dist/media.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.progressbarjs.uploader',
		'ui.vue.vuex',
		'im.model',
		'im.utils',
		'im.const',
		'ui.vue.components.audioplayer',
		'ui.vue.directives.lazyload',
		'ui.icons',
		'ui.vue.components.socialvideo',
		'ui.vue',
	],
	'skip_core' => true,
];