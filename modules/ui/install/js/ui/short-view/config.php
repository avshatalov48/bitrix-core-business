<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/short.view.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.buttons',
		'ui.switcher',
	],
	'skip_core' => false,
];
