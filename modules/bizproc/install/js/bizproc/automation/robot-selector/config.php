<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/robot-selector.bundle.css',
	'js' => 'dist/robot-selector.bundle.js',
	'rel' => [
		'main.core.events',
		'main.popup',
		'bizproc.automation',
		'ui.entity-catalog',
		'main.core',
	],
	'skip_core' => false,
];