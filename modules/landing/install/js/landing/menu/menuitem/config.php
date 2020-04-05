<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/menuitem.bundle.css',
	'js' => 'dist/menuitem.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.form.menuitemform',
	],
	'skip_core' => false,
];