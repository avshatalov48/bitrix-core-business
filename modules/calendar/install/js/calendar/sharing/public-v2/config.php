<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/public-v2.bundle.css',
	'js' => 'dist/public-v2.bundle.js',
	'rel' => [
		'ui.icons.b24',
		'calendar.util',
		'main.core',
		'main.popup',
		'main.date',
		'ui.bottomsheet',
		'ui.icon-set.actions',
		'main.core.events',
		'ui.design-tokens',
	],
	'skip_core' => false,
];
