<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/messenger.bundle.css',
	'js' => 'dist/messenger.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.component.navigation',
		'im.v2.component.list.container.recent',
		'im.v2.component.list.container.openline',
		'im.v2.component.content.chat',
		'im.v2.component.content.create-chat',
		'im.v2.component.content.openline',
		'im.v2.component.content.notification',
		'im.v2.component.content.market',
		'im.v2.lib.logger',
		'im.v2.lib.init',
		'im.v2.const',
		'im.v2.lib.call',
		'im.v2.lib.theme',
		'ui.fonts.opensans',
	],
	'skip_core' => true,
];