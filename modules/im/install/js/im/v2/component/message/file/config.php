<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/file-message.bundle.css',
	'js' => 'dist/file-message.bundle.js',
	'rel' => [
		'im.v2.component.message.unsupported',
		'im.v2.provider.service.uploading',
		'ui.uploader.core',
		'im.v2.component.elements.media-gallery',
		'im.v2.component.elements.videoplayer',
		'im.v2.provider.service.disk',
		'im.v2.lib.menu',
		'im.v2.lib.notifier',
		'ui.icons.disk',
		'im.v2.lib.utils',
		'main.core',
		'im.v2.component.elements.audioplayer',
		'im.v2.component.elements.progressbar',
		'im.v2.component.animation',
		'ui.icon-set.api.vue',
		'im.v2.component.elements.loader',
		'im.v2.provider.service.message',
		'im.v2.lib.feature',
		'im.v2.lib.analytics',
		'im.v2.component.message.elements',
		'im.v2.component.message.base',
		'im.v2.const',
	],
	'skip_core' => false,
];
