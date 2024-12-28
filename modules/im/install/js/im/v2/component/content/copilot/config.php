<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-content.bundle.css',
	'js' => 'dist/copilot-content.bundle.js',
	'rel' => [
		'im.v2.lib.logger',
		'ui.notification',
		'main.core.events',
		'im.v2.component.content.elements',
		'im.v2.component.entity-selector',
		'im.v2.lib.promo',
		'im.v2.lib.analytics',
		'main.popup',
		'im.v2.component.textarea',
		'im.v2.lib.draft',
		'im.v2.component.dialog.chat',
		'main.core',
		'im.v2.component.message-list',
		'im.v2.lib.copilot',
		'im.public',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.theme',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];