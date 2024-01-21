<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sidepanel.menu.bundle.css',
	'js' => 'dist/sidepanel.menu.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'main.popup',
		'main.core.events',
		'main.core',
	],
	'skip_core' => false,
];
