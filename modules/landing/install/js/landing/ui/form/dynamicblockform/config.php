<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/dynamicblockform.bundle.css',
	'js' => 'dist/dynamicblockform.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.form.baseform',
		'landing.env',
		'landing.loc',
	],
	'skip_core' => false,
];