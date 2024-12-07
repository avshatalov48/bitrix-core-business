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
		'ui.feedback.form',
		'ui.hint',
		'ui.fonts.opensans',
		'ui.design-tokens',
		'translit',
		'ui.notification',
		'main.popup',
		'main.core',
		'main.core.events',
		'catalog.tool-availability-manager',
	],
	'skip_core' => false,
];