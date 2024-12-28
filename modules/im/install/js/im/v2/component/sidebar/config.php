<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sidebar.bundle.css',
	'js' => 'dist/sidebar.bundle.js',
	'rel' => [
		'im.v2.lib.local-storage',
		'im.v2.lib.channel',
		'ui.vue3.directives.lazyload',
		'ui.label',
		'im.v2.lib.layout',
		'main.date',
		'ui.vue3.directives.hint',
		'im.v2.lib.rest',
		'ui.promo-video-popup',
		'ui.manual',
		'im.v2.lib.promo',
		'im.v2.lib.feature',
		'ui.viewer',
		'ui.icons',
		'im.v2.model',
		'ui.notification',
		'rest.client',
		'ui.vue3.vuex',
		'im.v2.lib.market',
		'im.v2.lib.entity-creator',
		'im.v2.lib.analytics',
		'im.v2.component.entity-selector',
		'im.v2.lib.menu',
		'im.v2.lib.call',
		'im.v2.lib.permission',
		'im.v2.lib.confirm',
		'im.v2.provider.service',
		'im.v2.lib.logger',
		'im.v2.lib.parser',
		'im.v2.lib.text-highlighter',
		'main.core',
		'im.v2.lib.utils',
		'im.v2.lib.user',
		'im.v2.application.core',
		'im.public',
		'im.v2.const',
		'im.v2.component.elements',
		'main.core.events',
		'im.v2.lib.date-formatter',
	],
	'skip_core' => false,
];
