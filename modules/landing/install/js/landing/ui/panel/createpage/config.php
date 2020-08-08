<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/createpage.bundle.css',
	'js' => 'dist/createpage.bundle.js',
	'rel' => [
		'main.core',
		'main.loader',
		'landing.ui.panel.content',
		'landing.loc',
		'landing.backend',
		'landing.env',
		'landing.sliderhacks',
		'translit',
	],
	'skip_core' => false,
];