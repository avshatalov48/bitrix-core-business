<?php

use Bitrix\Main\Config\Option;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/qrauthorization.bundle.css',
	'js' => 'dist/qrauthorization.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'main.loader',
		'pull.client',
		'main.qrcode',
		'ui.icon-set.main',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
	'settings' => [
		'ttl' => Option::get('main', 'qr-authorization-ttl', 60),
	],
];
