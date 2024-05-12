<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sender-editor.bundle.css',
	'js' => 'dist/sender-editor.bundle.js',
	'rel' => [
		'main.core',
		'ui.sidepanel.layout',
		'ui.hint',
		'ui.alerts',
		'ui.buttons',
		'ui.forms',
		'ui.layout-form',
		'ui.sidepanel-content',
		'ui.entity-selector',
		'ui.icon-set.actions',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];