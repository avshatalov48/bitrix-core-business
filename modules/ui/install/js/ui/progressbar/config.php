<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'ui.progressbar.css',
	'js' => 'dist/progressbar.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];