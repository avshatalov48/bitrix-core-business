<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/sending.bundle.js',
	],
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.lib.utils',
		'im.v2.lib.logger',
		'im.v2.lib.rest',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.provider.service.message',
	],
	'skip_core' => false,
];
