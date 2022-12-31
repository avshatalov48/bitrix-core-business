<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/entity-catalog.bundle.css',
	'js' => 'dist/entity-catalog.bundle.js',
	'rel' => [
		'ui.vue3',
		'ui.vue3.components.hint',
		'ui.feedback.form',
		'ui.icons',
		'ui.advice',
		'ui.vue3.pinia',
		'main.popup',
		'main.core.events',
		'main.core',
		'ui.forms',
	],
	'skip_core' => false,
];