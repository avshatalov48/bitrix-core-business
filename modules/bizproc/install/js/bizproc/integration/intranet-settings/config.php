<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/index.bundle.css',
	'js' => 'dist/index.bundle.js',
	'rel' => [
		'main.core.events',
		'ui.form-elements.view',
		'ui.form-elements.field',
		'main.core',
		'ui.section',
	],
	'skip_core' => false,
];