<?php


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.lib.date-formatter',
		'ui.vue3',
		'ui.lottie',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'ui.reactions-select',
		'ui.vue3.components.reactions',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.lib.menu',
		'im.v2.lib.parser',
		'im.v2.lib.copilot',
		'im.v2.lib.channel',
		'main.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.component.elements',
		'im.v2.lib.permission',
		'im.v2.component.animation',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];