<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/widget.bundle.css',
	'js' => 'dist/widget.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.image-stack-steps',
		'ui.label',
		'main.date',
		'main.polyfill.intersectionobserver',
		'ui.design-tokens',
		'ui.icons',
		'ui.icon-set.main',
	],
	'skip_core' => false,
];
