<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/crm.form.bundle.css',
	'js' => 'dist/crm.form.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.vue',
		'main.polyfill.promise',
	],
	'skip_core' => true,
];