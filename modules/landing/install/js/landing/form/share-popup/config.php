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
		'landing.features-popup',
		'landing.loc',
		'landing.pageobject',
		'landing.env',
		'crm.form.embed',
		'ui.feedback.form',
		'bitrix24.phoneverify',
	],
	'skip_core' => false,
];