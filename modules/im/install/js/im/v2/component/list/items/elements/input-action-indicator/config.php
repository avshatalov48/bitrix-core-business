<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/input-action-indicator.bundle.css',
	'js' => 'dist/input-action-indicator.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.lottie',
	],
	'skip_core' => true,
];