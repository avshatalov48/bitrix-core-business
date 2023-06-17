<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/messenger.bundle.css',
	'js' => 'dist/messenger.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.component.messenger',
		'im.v2.provider.pull',
		'im.v2.const',
	],
	'skip_core' => true,
];