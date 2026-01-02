<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/button.bundle.css',
	'js' => 'dist/button.bundle.js',
	'rel' => [
		'main.core',
		'ui.buttons',
		'ui.icon-set.api.core',
	],
	'skip_core' => false,
];
