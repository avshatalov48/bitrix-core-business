<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/grid.bundle.css',
	'js' => ['dist/grid.bundle.js'],
	'rel' => [
		'ui.cnt',
		'main.date',
		'ui.notification',
		'ui.label',
		'main.popup',
		'ui.avatar',
		'main.core',
		'ui.buttons',
	],
	'skip_core' => false,
];
