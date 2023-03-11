<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/debugger.bundle.css',
	'js' => 'dist/debugger.bundle.js',
	'rel' => [
		'main.popup',
		'ui.buttons',
		'ui.buttons.icons',
		'ui.entity-selector',
		'bp_field_type',
		'ui.layout-form',
		'ui.hint',
		'main.date',
		'main.loader',
		'bizproc.condition',
		'ui.fonts.robotomono',
		'bizproc.automation',
		'bizproc.debugger',
		'pull.client',
		'bizproc.local-settings',
		'main.core.events',
		'main.core',
		'ui.tour',
		'ui.dialogs.messagebox',
		'ui.fonts.opensans',
		'ui.design-tokens',
	],
	'skip_core' => false,
];