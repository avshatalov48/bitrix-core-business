<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/post-form.bundle.css',
	'js' => 'dist/post-form.bundle.js',
	'rel' => [
		'main.popup',
		'ui.entity-selector',
		'ui.buttons',
		'ui.uploader.core',
		'main.core.events',
		'main.core',
		'ui.alerts',
		'ui.notification',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
];