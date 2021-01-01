<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/separatorpanel.bundle.css',
	'js' => 'dist/separatorpanel.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.panel.content',
		'landing.pageobject',
		'landing.loc',
	],
	'skip_core' => false,
];