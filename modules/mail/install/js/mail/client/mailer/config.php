<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/mailer.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'mail.client.filtertoolbar',
		'mail.client.binding',
		'main.core.events',
	],
	'skip_core' => true,
];