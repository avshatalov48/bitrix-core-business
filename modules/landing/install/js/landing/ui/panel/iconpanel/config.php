<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/iconpanel.bundle.css',
	'js' => 'dist/iconpanel.bundle.js',
	'rel' => [
		'landing.ui.panel.content',
		'landing.ui.button.sidebarbutton',
		'landing.loc',
		'main.core',
	],
	'skip_core' => false,
];