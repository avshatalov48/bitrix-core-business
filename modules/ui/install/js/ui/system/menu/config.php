<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/menu.bundle.css',
	'js' => 'dist/menu.bundle.js',
	'rel' => [
		'main.popup',
		'ui.icon-set.main',
		'ui.buttons',
		'ui.cnt',
		'ui.icon-set.api.core',
		'ui.icon-set.outline',
		'main.core',
	],
	'skip_core' => false,
];
