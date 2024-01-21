<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/interface.bundle.css',
	'js' => 'dist/interface.bundle.js',
	'rel' => [
		'main.loader',
		'main.qrcode',
		'ui.design-tokens',
		'main.date',
		'calendar.sharing.analytics',
		'ui.entity-selector',
		'main.core',
		'calendar.util',
		'ui.icon-set.api.core',
		'main.popup',
		'ui.dialogs.messagebox',
		'ui.buttons',
		'main.core.events',
		'ui.icon-set.actions',
		'ui.switcher',
		'spotlight',
		'ui.tour',
		'ui.cnt',
	],
	'skip_core' => false,
];