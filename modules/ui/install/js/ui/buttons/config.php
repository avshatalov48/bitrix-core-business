<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/ui.buttons.bundle.css',
	'js' => 'dist/ui.buttons.bundle.js',
	'rel' => [
		'ui.design-tokens.air',
		'ui.icon-set.api.core',
		'ui.buttons',
		'main.core.events',
		'main.popup',
		'ui.switcher',
		'main.core',
		'ui.cnt',
		'ui.icon-set.outline',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
