<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/conference-create.bundle.css',
	'js' => 'dist/conference-create.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'ui.vue',
		'im.lib.logger',
		'im.lib.clipboard',
		'ui.vue.components.hint',
	],
	'skip_core' => false,
];