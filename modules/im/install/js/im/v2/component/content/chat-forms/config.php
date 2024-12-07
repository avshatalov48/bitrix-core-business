<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.lib.feature',
		'im.v2.component.animation',
		'ui.entity-selector',
		'main.core',
		'im.v2.component.elements',
		'im.v2.application.core',
		'im.v2.lib.permission',
		'ui.forms',
		'im.v2.lib.create-chat',
		'ui.notification',
		'main.core.events',
		'main.popup',
		'im.public',
		'im.v2.lib.analytics',
		'im.v2.provider.service',
		'im.v2.const',
		'im.v2.lib.confirm',
	],
	'skip_core' => false,
];
