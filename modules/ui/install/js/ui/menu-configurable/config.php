<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'src/menu-configurable.css',
	'js' => 'dist/menu-configurable.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.draganddrop.draggable',
	],
	'skip_core' => false,
];
