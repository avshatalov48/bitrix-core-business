<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/rc_editor.bundle.css',
	'js' => 'dist/rc_editor.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];