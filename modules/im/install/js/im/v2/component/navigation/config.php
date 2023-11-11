<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

return [
	'css' => 'dist/navigation.bundle.css',
	'js' => 'dist/navigation.bundle.js',
	'rel' => [
		'ui.vue3.directives.hint',
		'ui.dialogs.messagebox',
		'im.v2.lib.slider',
		'im.v2.lib.call',
		'im.v2.lib.desktop-api',
		'im.v2.lib.phone',
		'im.v2.lib.desktop',
		'main.core',
		'im.v2.component.elements',
		'im.v2.lib.utils',
		'im.v2.lib.logger',
		'im.v2.lib.rest',
		'ui.buttons',
		'ui.feedback.form',
		'ui.fontawesome4',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.market',
	],
	'skip_core' => false,
	'settings' => [
		'v2' => \Bitrix\Im\Settings::isBetaActivated(),
		'force_beta' => \Bitrix\Im\Settings::isForceBetaActivatedForCurrentUser()
	]
];