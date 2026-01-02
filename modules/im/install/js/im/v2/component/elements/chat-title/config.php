<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.api.vue',
		'im.v2.application.core',
		'ui.vue3',
		'im.v2.lib.text-highlighter',
		'im.v2.lib.copilot',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.esc-manager',
		'im.v2.lib.permission',
	],
	'skip_core' => false,
];
