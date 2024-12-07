<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/channel-container.bundle.css',
	'js' => 'dist/channel-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.items.channel',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.logger',
		'im.v2.lib.promo',
		'im.v2.lib.create-chat',
	],
	'skip_core' => true,
];