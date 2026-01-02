<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/vote.bundle.css',
	'js' => 'dist/vote.bundle.js',
	'rel' => [
		'im.v2.const',
		'vote.provider.service',
		'vote.component.loader',
		'main.core.events',
		'im.v2.lib.menu',
		'ui.icon-set.api.core',
		'vote.analytics',
		'main.core',
		'ui.vue3.directives.hint',
		'vote.application',
		'main.popup',
		'ui.vue3.components.popup',
	],
	'skip_core' => false,
];

