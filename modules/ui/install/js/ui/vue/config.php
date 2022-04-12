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
		'main.core',
		'main.core.events',
		'ui.vue.devtools'
	];
	$settings = [
		'localizationDebug' => defined('VUEJS_LOCALIZATION_DEBUG') && VUEJS_LOCALIZATION_DEBUG,
	];
}
else
{
	$js = './vue2/prod/dist/vue.bundle.js';
	$rel = [
		'main.core',
		'main.core.events',
	];
	$settings = [];
}

return [
	'js' => $js,
	'rel' => $rel,
	'settings' => $settings,
	'skip_core' => false,
];