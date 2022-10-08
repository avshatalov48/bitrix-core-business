<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sidebarbutton.bundle.css',
	'js' => 'dist/sidebarbutton.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.button.basebutton',
		'landing.loc',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];