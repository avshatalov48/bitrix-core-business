<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/vote-creation-form.bundle.css',
	'js' => 'dist/vote-creation-form.bundle.js',
	'rel' => [
		'ui.notification',
		'ui.vue3',
		'main.core.events',
		'main.core',
		'ui.switcher',
		'ui.forms',
		'vote.analytics',
	],
	'skip_core' => false,
];
