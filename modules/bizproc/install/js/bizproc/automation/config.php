<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/automation.bundle.css',
	'js' => 'dist/automation.bundle.js',
	'rel' => [
		'ui.alerts',
		'bizproc.condition',
		'ui.icon-set.main',
		'ui.draganddrop.draggable',
		'ui.entity-selector',
		'main.core.events',
		'main.popup',
		'ui.buttons',
		'main.date',
		'ui.forms',
		'ui.icon-set.actions',
		'ui.hint',
		'bizproc.globals',
		'bizproc.automation',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'ui.tour',
	],
	'skip_core' => false,
];
