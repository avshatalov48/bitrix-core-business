<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/bundle.css',
	'js' => 'dist/bundle.js',
	'rel' => [
		'main.core',
		'main.popup',
		'main.loader',
		'pull.client',
		'main.qrcode',
	],
	'skip_core' => false,
];
