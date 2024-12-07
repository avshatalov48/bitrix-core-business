<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/faces.bundle.css',
	'js' => 'dist/faces.bundle.js',
	'rel' => [
		'bizproc.workflow.faces.summary',
		'ui.image-stack-steps',
		'main.core',
		'ui.design-tokens',
		'ui.icons',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
