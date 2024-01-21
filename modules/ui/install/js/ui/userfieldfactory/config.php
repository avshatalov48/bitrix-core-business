<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/userfieldfactory.bundle.css',
	'js' => 'dist/userfieldfactory.bundle.js',
	'rel' => [
		'main.core',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.popup',
		'sidepanel',
		'ui.userfield',
	],
	'skip_core' => false,
];
