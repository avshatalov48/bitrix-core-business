<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'script.js',
	'rel' => [
		'ui.alerts',
		'ui.entity-selector',
		'main.popup',
		'bizproc.types',
		'bizproc.task',
		'ui.hint',
		'bizproc.workflow.faces',
		'bizproc.workflow.faces.summary',
		'ui.cnt',
		'main.core',
		'ui.notification',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
