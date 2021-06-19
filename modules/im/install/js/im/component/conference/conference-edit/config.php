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
		'calendar.planner',
		'calendar.util',
		'main.core.events',
		'ui.vue.components.hint',
		'im.const',
		'ui.entity-selector',
	],
	'skip_core' => false,
];