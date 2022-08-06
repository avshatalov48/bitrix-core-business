<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/share-popup.bundle.css',
	'js' => 'dist/share-popup.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'landing.env',
		'crm.form.embed',
		'landing.pageobject',
	],
	'skip_core' => false,
];