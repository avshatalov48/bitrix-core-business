<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/dynamiccardsform.bundle.css',
	'js' => 'dist/dynamiccardsform.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.form.baseform',
		'landing.env',
		'landing.ui.field.sourcefield',
		'landing.loc',
		'landing.main',
	],
	'skip_core' => false,
];