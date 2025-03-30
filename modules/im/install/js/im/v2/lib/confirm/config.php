<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.popup',
		'ui.dialogs.messagebox',
		'ui.lottie',
		'im.v2.lib.channel',
		'main.core',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];
