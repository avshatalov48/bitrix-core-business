<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/market-expired-curtain.bundle.css',
	'js' => 'dist/market-expired-curtain.bundle.js',
	'rel' => [
		'main.core',
		'ui.banner-dispatcher',
		'ui.notification-panel',
		'ui.icon-set.api.core',
		'ui.buttons',
		'ui.analytics',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
