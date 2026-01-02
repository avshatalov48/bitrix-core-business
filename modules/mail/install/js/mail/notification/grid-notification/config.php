<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/mailbox-grid-notification.bundle.css',
	'js' => 'dist/mailbox-grid-notification.bundle.js',
	'rel' => [
		'main.core',
		'ui.banner-dispatcher',
		'main.popup',
	],
	'skip_core' => false,
];
