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
		'ui.hint',
		'bizproc.condition',
		'main.core.events',
		'ui.entity-selector',
		'main.date',
		'main.popup',
		'bizproc.globals',
		'bizproc.automation',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'ui.tour',
	],
	'skip_core' => false,
];