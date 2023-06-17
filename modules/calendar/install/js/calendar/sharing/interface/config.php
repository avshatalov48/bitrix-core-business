<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/interface.bundle.css',
	'js' => 'dist/interface.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'main.popup',
		'main.loader',
		'main.qrcode',
		'ui.design-tokens',
		'calendar.util',
		'ui.switcher',
		'spotlight',
		'ui.tour',
		'ui.cnt',
	],
	'skip_core' => false,
];