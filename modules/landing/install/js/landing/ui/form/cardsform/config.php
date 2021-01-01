<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/cardsform.bundle.css',
	'js' => 'dist/cardsform.bundle.js',
	'rel' => [
		'landing.ui.form.baseform',
		'landing.ui.collection.formcollection',
		'landing.loc',
		'landing.ui.panel.content',
		'main.core',
		'landing.ui.form.cardform',
		'ui.draganddrop.draggable',
		'landing.pageobject',
		'main.core.events',
		'landing.ui.field.textfield',
	],
	'skip_core' => false,
];