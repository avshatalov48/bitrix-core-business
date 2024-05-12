<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/label.bundle.css',
	'js' => 'dist/label.bundle.js',
	'rel' => [
		'main.core',
		'main.loader',
		'ui.icon-set.api.core',
		'ui.icon-set.main',
		'ui.fonts.opensans',
		'ui.design-tokens',
	],
	'skip_core' => false,
];