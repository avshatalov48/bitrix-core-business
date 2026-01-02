<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/task.bundle.js',
	'css' => 'dist/task.bundle.css',
	'rel' => [
		'main.core',
		'im.v2.application.core',
		'im.v2.provider.pull',
		'im.v2.css.classes',
		'im.v2.css.icons',
		'im.v2.css.tokens',
		'im.v2.lib.logger',
		'im.v2.provider.service.chat',
		'im.v2.lib.theme',
		'main.core.events',
		'im.v2.component.textarea',
		'im.v2.component.dialog.chat',
		'im.v2.lib.message-notifier',
		'im.v2.component.elements.loader',
		'im.v2.component.animation',
		'im.v2.component.content.elements',
		'im.v2.const',
		'im.v2.lib.demo',
	],
	'skip_core' => false,
];