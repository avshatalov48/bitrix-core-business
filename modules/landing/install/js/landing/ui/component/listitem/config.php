<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/listitem.bundle.css',
	'js' => 'dist/listitem.bundle.js',
	'rel' => [
		'ui.design-tokens',
		'main.core',
		'main.core.events',
		'landing.loc',
		'landing.ui.form.baseform',
		'landing.ui.component.iconbutton',
		'landing.ui.component.internal',
	],
	'skip_core' => false,
];