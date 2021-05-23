<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (
	defined('VUEJS_DEBUG') && VUEJS_DEBUG
	&& (!defined('VUEJS_DEBUG_DISABLE') || !VUEJS_DEBUG_DISABLE)
)
{
	$js = './vue2/dev/dist/vue.bundle.js';
	$rel = [
		'main.polyfill.core',
		'ui.vue.devtools'
	];
}
else
{
	$js = './vue2/prod/dist/vue.bundle.js';
	$rel = [
		'main.polyfill.core',
	];
}

return [
	'js' => $js,
	'rel' => $rel,
	'skip_core' => true,
];