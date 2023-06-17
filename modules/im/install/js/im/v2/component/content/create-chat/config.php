<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/create-chat-content.bundle.css',
	'js' => 'dist/create-chat-content.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.public',
		'im.v2.lib.logger',
		'im.v2.const',
		'im.v2.provider.service',
		'im.v2.application.core',
		'ui.forms',
		'im.v2.component.elements',
		'im.v2.component.animation',
		'ui.entity-selector',
		'ui.notification',
	],
	'skip_core' => true,
];