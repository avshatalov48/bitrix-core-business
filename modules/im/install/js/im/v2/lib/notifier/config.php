<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/notifier.bundle.js',
	],
	'rel' => [
		'ui.notification',
		'im.v2.application.core',
		'im.public',
		'im.v2.const',
		'main.core',
	],
	'skip_core' => false,
];