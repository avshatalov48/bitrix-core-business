<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/formsettingsform.bundle.css',
	'js' => 'dist/formsettingsform.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'ui.fonts.opensans',
		'main.core',
		'landing.ui.form.baseform',
		'landing.ui.field.smallswitch',
		'main.core.events',
		'landing.ui.component.link',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];