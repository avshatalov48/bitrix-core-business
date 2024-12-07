<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/banner-dispatcher.bundle.css',
	'js' => 'dist/banner-dispatcher.bundle.js',
	'rel' => [
		'ui.auto-launch',
		'main.core',
	],
	'skip_core' => false,
];
