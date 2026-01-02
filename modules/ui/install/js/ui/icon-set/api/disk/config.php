<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/disk-icon.bundle.js',
	'css' => 'dist/disk-icon.bundle.css',
	'rel' => [
		'main.core',
		'ui.icon-set.api.core',
		'ui.icon-set.disk',
	],
	'skip_core' => false,
];
