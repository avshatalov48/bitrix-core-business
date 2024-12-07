<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/settings.bundle.css',
	'js' => 'dist/settings.bundle.js',
	'rel' => [
		'ui.progressbar',
		'ui.notification',
		'catalog.external-catalog-placement',
		'catalog.tool-availability-manager',
		'ui.label',
		'main.popup',
		'ui.buttons',
		'ui.alerts',
		'ui.form-elements.field',
		'ui.form-elements.view',
		'ui.section',
		'ui.icon-set.crm',
		'ui.icon-set.editor',
		'catalog.store-enable-wizard',
		'main.core',
		'main.core.events',
	],
	'skip_core' => false,
];
