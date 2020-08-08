<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'src/ui.label.css',
	'js' => 'dist/label.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];