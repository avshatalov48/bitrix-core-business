<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/short-qr-auth.bundle.css',
	'js' => 'dist/short-qr-auth.bundle.js',
	'rel' => [
		'main.core',
		'main.qrcode',
		'ui.buttons',
		'pull.client',
		'main.loader',
	],
	'skip_core' => false,
];
