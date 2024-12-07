<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/entityrelation.bundle.css',
	'js' => 'dist/entityrelation.bundle.js',
	'rel' => [
		'main.core.events',
		'main.loader',
		'im.public',
		'main.core',
	],
	'skip_core' => false,
];