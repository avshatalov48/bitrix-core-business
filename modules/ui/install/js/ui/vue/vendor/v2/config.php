<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'/bitrix/js/ui/vue/vendor/v2/dist/'.(defined('VUEJS_DEBUG') && VUEJS_DEBUG? 'dev.': '').'vue.bundle.js',],
	'rel' => [
		'main.polyfill.core'
	],
	'skip_core' => true,
];