<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/layout-form.bundle.css',
	'js' => 'dist/layout-form.bundle.js',
	'rel' => [
		'main.core',
		'ui.design-tokens',
		'ui.forms',
		'main.core.events',
	],
	'skip_core' => false,
];
