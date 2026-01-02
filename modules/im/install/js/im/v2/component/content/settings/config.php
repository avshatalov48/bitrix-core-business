<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/settings-content.bundle.css',
	'js' => 'dist/settings-content.bundle.js',
	'rel' => [
		'im.v2.lib.logger',
		'im.v2.lib.helpdesk',
		'im.v2.lib.feedback',
		'im.v2.component.dialog.chat',
		'im.v2.lib.theme',
		'im.v2.lib.demo',
		'im.v2.lib.confirm',
		'ui.forms',
		'im.v2.component.elements.hint',
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'im.v2.lib.utils',
		'im.v2.lib.desktop-api',
		'im.v2.const',
		'im.v2.lib.feature',
		'im.v2.provider.service.settings',
	],
	'skip_core' => false,
];
