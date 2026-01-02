<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/mobile-promoter.bundle.css',
	'js' => 'dist/mobile-promoter.bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'ui.analytics',
		'ui.lottie',
		'main.qrcode',
	],
	'skip_core' => false,
];
