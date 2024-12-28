<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/interface.bundle.css',
	'js' => 'dist/interface.bundle.js',
	'rel' => [
		'main.date',
		'calendar.sharing.analytics',
		'ui.entity-selector',
		'ui.avatar',
		'ui.icon-set.api.core',
		'ui.dialogs.messagebox',
		'ui.buttons',
		'ui.icon-set.actions',
		'main.qrcode',
		'ui.design-tokens',
		'ui.switcher',
		'spotlight',
		'ui.tour',
		'ui.cnt',
		'ui.info-helper',
		'calendar.util',
		'main.core',
		'main.popup',
		'main.loader',
		'main.core.events',
	],
	'skip_core' => false,
];
