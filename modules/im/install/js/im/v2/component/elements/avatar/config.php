<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'im.v2.lib.utils',
		'im.v2.lib.channel',
		'ui.avatar',
		'im.v2.lib.copilot',
		'main.core',
		'im.v2.const',
	],
	'skip_core' => false,
];
