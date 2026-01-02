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
		'im.v2.component.elements.attach',
		'im.v2.component.elements.keyboard',
		'ui.lottie',
		'im.v2.component.elements.user-list-popup',
		'im.v2.lib.user',
		'im.v2.lib.logger',
		'ui.reactions-select',
		'im.v2.component.elements.chat-title',
		'im.v2.lib.utils',
		'im.v2.application.core',
		'im.v2.lib.menu',
		'im.v2.provider.service.sending',
		'im.v2.provider.service.message',
		'im.v2.provider.service.uploading',
		'ui.system.menu',
		'im.v2.lib.copilot',
		'im.v2.lib.channel',
		'main.core.events',
		'im.v2.const',
		'im.v2.component.elements.avatar',
		'im.v2.lib.permission',
		'im.v2.component.animation',
		'im.v2.provider.service.comments',
		'main.core',
		'ui.vue3.components.reactions',
		'im.v2.lib.parser',
	],
	'skip_core' => false,
];