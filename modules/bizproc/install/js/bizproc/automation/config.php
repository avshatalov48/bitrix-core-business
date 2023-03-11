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
		'ui.entity-selector',
		'main.date',
		'main.popup',
		'main.core.events',
		'bizproc.automation',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'ui.tour',
	],
	'skip_core' => false,
];