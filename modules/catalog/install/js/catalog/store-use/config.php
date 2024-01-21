<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/store-use.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.layout-form',
		'main.core.events',
		'ui.buttons',
		'ui.dialogs.messagebox',
		'main.core',
		'main.popup',
	],
	'skip_core' => false,
];
