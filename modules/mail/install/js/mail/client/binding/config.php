<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/binding.bundle.js',
	'rel' => [
		'main.core',
		'ui.notification',
		'main.core.events',
	],
	'skip_core' => false,
];