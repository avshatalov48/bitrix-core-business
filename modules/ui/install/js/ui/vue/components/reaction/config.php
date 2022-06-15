<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'/bitrix/js/ui/vue/components/reaction/dist/reaction.bundle.js',
	],
	'css' => [
		'/bitrix/js/ui/vue/components/reaction/dist/reaction.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'main.core.events',
	],
	'skip_core' => true,
];