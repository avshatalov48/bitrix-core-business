<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/audioplayer.bundle.css',
	'js' => 'dist/audioplayer.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.fonts.opensans',
		'main.polyfill.intersectionobserver',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.local-storage',
		'im.v2.lib.utils',
		'im.v2.component.elements.avatar',
		'im.v2.lib.analytics',
	],
	'skip_core' => true,
];
