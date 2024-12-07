<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/summary.bundle.css',
	'js' => 'dist/summary.bundle.js',
	'rel' => [
		'main.core',
		'main.date',
		'bizproc.workflow.timeline',
		'ui.design-tokens',
		'ui.icons',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
