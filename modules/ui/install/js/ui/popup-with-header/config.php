<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/popup-with-header.bundle.css',
	'js' => 'dist/popup-with-header.bundle.js',
	'rel' => [
		'main.core.events',
		'main.loader',
		'ui.progressround',
		'ui.popupcomponentsmaker',
		'ui.icon-set.api.core',
		'main.popup',
		'ui.popup-with-header',
		'main.core',
		'ui.buttons',
		'ui.info-helper',
	],
	'skip_core' => false,
];