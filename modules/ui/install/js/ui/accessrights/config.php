<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/accessrights.bundle.css',
	'js' => 'dist/accessrights.bundle.js',
	'rel' => [
		'main.loader',
		'ui.notification',
		'ui.switcher',
		'main.popup',
		'main.core.events',
		'ui.entity-selector',
		'main.core',
		'ui.hint',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'ui.icon-set.main',
		'ui.icon-set.actions',
		'ui.vue.components.hint',
	],
	'skip_core' => false,
];
