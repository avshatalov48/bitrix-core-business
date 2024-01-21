<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/nested-switcher.bundle.css',
	'js' => 'dist/nested-switcher.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.form-elements.view',
		'ui.section',
		'ui.switcher',
	],
	'skip_core' => false,
];