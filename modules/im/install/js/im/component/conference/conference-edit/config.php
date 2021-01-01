<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/conference-edit.bundle.css',
	'js' => 'dist/conference-edit.bundle.js',
	'rel' => [
		'ui.vue',
		'im.lib.logger',
		'im.lib.clipboard',
		'main.core',
		'ui.entity-selector',
		'calendar.planner',
		'calendar.util',
		'im.const',
		'main.core.events',
	],
	'skip_core' => false,
];