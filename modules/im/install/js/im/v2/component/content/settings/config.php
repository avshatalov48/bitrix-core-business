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
		'ui.feedback.form',
		'im.v2.component.dialog.chat',
		'im.v2.lib.theme',
		'im.v2.lib.user',
		'ui.forms',
		'main.core',
		'im.v2.application.core',
		'im.v2.lib.rest',
		'im.v2.lib.utils',
		'im.v2.lib.desktop-api',
		'im.v2.lib.confirm',
		'im.v2.const',
		'im.v2.provider.service',
	],
	'skip_core' => false,
];