<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/notification-panel.bundle.css',
	'js' => 'dist/notification-panel.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.api.core',
		'main.core.events',
		'main.popup',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
