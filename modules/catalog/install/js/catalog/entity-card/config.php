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
		'ui.feedback.form',
		'ui.hint',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'translit',
		'main.popup',
		'main.core',
		'main.core.events',
		'catalog.store-use',
	],
	'skip_core' => false,
];