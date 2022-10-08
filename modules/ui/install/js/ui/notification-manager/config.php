<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/notification-manager.bundle.css',
	'js' => 'dist/notification-manager.bundle.js',
	'rel' => [
		'pull.client',
		'main.core.events',
		'main.core',
		'ui.notification',
		'ui.buttons',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];
