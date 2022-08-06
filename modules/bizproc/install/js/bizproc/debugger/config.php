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
		'bizproc.debugger',
		'ui.fonts.robotomono',
		'ui.dialogs.messagebox',
		'bizproc.automation',
		'main.core',
		'pull.client',
		'main.core.events',
		'ui.fonts.opensans',
		'ui.design-tokens',
	],
	'skip_core' => false,
];