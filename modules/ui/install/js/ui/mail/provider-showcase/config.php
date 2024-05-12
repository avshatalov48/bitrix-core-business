<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/provider-showcase.bundle.css',
	'js' => 'dist/provider-showcase.bundle.js',
	'rel' => [
		'main.core',
		'ui.sidepanel.layout',
		'ui.mail.sender-editor',
		'ui.info-helper',
		'ui.forms',
		'ui.sidepanel-content',
		'ui.buttons',
	],
	'skip_core' => false,
];