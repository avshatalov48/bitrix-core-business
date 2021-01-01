<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/listitem.bundle.css',
	'js' => 'dist/listitem.bundle.js',
	'rel' => [
		'main.core.events',
		'landing.loc',
		'landing.ui.form.baseform',
		'landing.ui.component.iconbutton',
		'main.core',
	],
	'skip_core' => false,
];