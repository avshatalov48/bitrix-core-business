<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/cardform.bundle.css',
	'js' => 'dist/cardform.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.form.baseform',
	],
	'skip_core' => false,
];