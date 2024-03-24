<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/user-status.bundle.js',
	],
	'rel' => [
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.utils',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];