<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/menuitemfield.bundle.css',
	'js' => 'dist/menuitemfield.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.field.basefield',
		'landing.ui.form.menuform',
	],
	'skip_core' => false,
];