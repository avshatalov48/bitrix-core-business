<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/lottie.bundle.css',
	'js' => 'dist/lottie.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];