<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/field.bundle.css',
	'js' => 'dist/field.bundle.js',
	'rel' => [
		'main.core.collections',
		'ui.form-elements.view',
		'main.core.events',
		'ui.section',
		'ui.form-elements.field',
		'ui.tabs',
		'main.core',
	],
	'skip_core' => false,
];
