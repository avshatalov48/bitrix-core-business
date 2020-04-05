<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/menuform.bundle.css',
	'js' => 'dist/menuform.bundle.js',
	'rel' => [
		'main.core',
		'landing.loc',
		'landing.env',
		'landing.main',
		'landing.ui.form.baseform',
		'landing.ui.form.menuitemform',
		'ui.draganddrop.draggable',
	],
	'skip_core' => false,
];