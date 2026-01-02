<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/side-panel.bundle.css',
	'js' => 'dist/side-panel.bundle.js',
	'rel' => [
		'fx',
		'main.pageobject',
		'clipboard',
		'ui.fonts.opensans',
		'popup',
		'ui.design-tokens.air',
		'ui.icon-set.actions',
		'ui.icon-set.main',
		'ui.icon-set.outline',
	],
	'skip_core' => false,
];
