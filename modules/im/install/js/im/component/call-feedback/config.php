<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/call-feedback.bundle.css',
	'js' => 'dist/call-feedback.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'ui.vue',
		'ui.forms',
		'main.popup',
		'im.lib.logger',
	],
	'skip_core' => true,
];