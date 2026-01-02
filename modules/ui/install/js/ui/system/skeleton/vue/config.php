<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/vue.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'ui.system.skeleton',
	],
	'skip_core' => true,
];
