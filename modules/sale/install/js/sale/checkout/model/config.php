<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/model.bundle.css',
	'js' => 'dist/model.bundle.js',
	'rel' => [
		'ui.vue',
		'ui.vue.vuex',
		'main.core',
		'sale.checkout.const',
	],
	'skip_core' => false,
];