<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/base-message.bundle.css',
	'js' => 'dist/base-message.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.vue3.components.reactions',
		'im.v2.application.core',
		'im.v2.lib.utils',
		'im.v2.lib.parser',
		'im.v2.component.message.reaction',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements',
		'im.v2.const',
	],
	'skip_core' => false,
];