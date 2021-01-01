<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/balloonform.bundle.css',
	'js' => 'dist/balloonform.bundle.js',
	'rel' => [
		'landing.ui.form.baseform',
		'main.core',
	],
	'skip_core' => false,
];