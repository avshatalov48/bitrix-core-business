<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'src/ui.alert.css',
	'js' => 'dist/alert.bundle.js',
	'rel' => [
		'main.core',
		'ui.design-tokens',
	],
	'skip_core' => false,
];