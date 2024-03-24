<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/group-settings.bundle.css',
	'js' => 'dist/group-settings.bundle.js',
	'rel' => [
		'pull.client',
		'socialnetwork.group-privacy',
		'ui.avatar-editor',
		'socialnetwork.logo',
		'main.popup',
		'ui.buttons',
		'ui.label',
		'ui.switcher',
		'main.core',
		'main.core.events',
		'ui.entity-selector',
		'socialnetwork.controller',
		'ui.alerts',
		'ui.sidepanel-content',
		'ui.dialogs.messagebox',
		'ui.icon-set.main',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
];