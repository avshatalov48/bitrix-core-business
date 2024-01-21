<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/membership-request-panel.bundle.css',
	'js' => 'dist/membership-request-panel.bundle.js',
	'rel' => [
		'pull.client',
		'main.core',
		'main.popup',
		'main.loader',
		'main.core.events',
	],
	'skip_core' => false,
];