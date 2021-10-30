<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue',
		'ui.forms',
		'ui.info-helper',
		'ui.sidepanel-content',
		'ui.layout-form',
		'ui.dialogs.messagebox',
		'sidepanel',
		'ui.sidepanel.layout',
	],
	'skip_core' => false,
];