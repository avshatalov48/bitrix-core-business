<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/user.bundle.js',
	],
	'rel' => [
		'main.core',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.const',
	],
	'skip_core' => false,
];