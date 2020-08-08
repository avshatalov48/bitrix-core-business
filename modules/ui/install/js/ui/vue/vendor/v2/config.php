<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (defined('VUEJS_DEBUG') && VUEJS_DEBUG)
{
	$js = './dev/dist/dev.vue.bundle.js';
	$rel = [
		'main.polyfill.core',
		'ui.vue.devtools'
	];
}
else
{
	$js = './prod/dist/vue.bundle.js';
	$rel = [
		'main.polyfill.core',
	];
}

return [
	'js' => $js,
	'rel' => $rel,
	'skip_core' => true,
];