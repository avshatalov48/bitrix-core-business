<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ui.icon-set.vue.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.api.core',
	],
	'skip_core' => false,
];