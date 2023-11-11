<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/quick-access.js.bundle.css',
	'js' => 'dist/quick-access.js.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.component.quick-access',
		'im.v2.const',
		'im.public',
	],
	'skip_core' => true,
];