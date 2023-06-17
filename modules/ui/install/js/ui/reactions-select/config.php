<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/reactions-select.bundle.css',
	'js' => 'dist/reactions-select.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.lottie',
		'main.popup',
	],
	'skip_core' => false,
];