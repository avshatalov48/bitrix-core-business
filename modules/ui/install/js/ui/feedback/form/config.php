<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/form.bundle.css',
	'js' => 'dist/form.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];