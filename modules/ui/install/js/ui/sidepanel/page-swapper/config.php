<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/page-swapper.bundle.css',
	'js' => 'dist/page-swapper.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.icon-set.api.core',
		'ui.icon-set.actions',
		'main.loader',
	],
	'skip_core' => false,
];