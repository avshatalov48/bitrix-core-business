<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/setting-selector.bundle.css',
	'js' => 'dist/setting-selector.bundle.js',
	'rel' => [
		'main.core',
		'ui.icon-set.api.core',
		'ui.entity-selector',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
];
