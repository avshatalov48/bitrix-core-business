<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/index.bundle.css',
	'js' => 'dist/index.bundle.js',
	'rel' => [
		'ui.form-elements.view',
		'ui.form-elements.field',
		'main.core',
		'main.core.events',
		'ui.section',
	],
	'skip_core' => false,
];