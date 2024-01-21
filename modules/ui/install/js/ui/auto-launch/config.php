<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/auto-launch.bundle.js',
	'rel' => [
		'main.core.collections',
		'main.core.z-index-manager',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
