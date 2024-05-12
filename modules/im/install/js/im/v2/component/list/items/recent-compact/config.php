<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-compact.bundle.css',
	'js' => 'dist/recent-compact.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.utils',
		'im.v2.provider.service',
		'im.v2.lib.menu',
		'im.public',
		'im.v2.css.tokens',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.component.elements',
	],
	'skip_core' => true,
];