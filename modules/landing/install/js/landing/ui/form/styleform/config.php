<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/styleform.bundle.css',
	'js' => 'dist/styleform.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.form.baseform',
		'landing.ui.highlight',
		'landing.ui.field.basefield',
		'landing.ui.component.internal',
		'ui.design-tokens',
	],
	'skip_core' => false,
];