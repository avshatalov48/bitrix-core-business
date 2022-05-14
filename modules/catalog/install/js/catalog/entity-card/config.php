<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/entity-card.bundle.css',
	'js' => 'dist/entity-card.bundle.js',
	'rel' => [
		'ui.entity-editor',
		'ui.notification',
		'ui.hint',
		'translit',
		'main.core.events',
		'main.popup',
		'main.core',
		'catalog.store-use',
	],
	'skip_core' => false,
];