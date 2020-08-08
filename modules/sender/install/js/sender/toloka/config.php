<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/toloka.bundle.css',
	'js' => 'dist/toloka.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.popup',
	],
	'skip_core' => true,
];