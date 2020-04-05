<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'/bitrix/js/ui/vue/components/smiles/dist/smiles.bundle.js',
	],
	'css' => [
		'/bitrix/js/ui/vue/components/smiles/dist/smiles.bundle.css',
	],
	'rel' => [
		'main.polyfill.core',
		'ui.vue.directives.lazyload',
		'ui.dexie',
		'ui.vue',
		'rest.client',
	],
	'skip_core' => true,
];