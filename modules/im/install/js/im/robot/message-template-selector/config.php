<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/message-template-selector.bundle.css',
	'js' => 'dist/message-template-selector.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.buttons',
	],
	'skip_core' => false,
];