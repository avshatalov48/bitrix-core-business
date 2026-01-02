<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/invite.bundle.js',
	],
	'rel' => [
		'main.sidepanel',
		'im.v2.application.core',
		'main.core',
		'im.v2.lib.notifier',
	],
	'skip_core' => false,
];