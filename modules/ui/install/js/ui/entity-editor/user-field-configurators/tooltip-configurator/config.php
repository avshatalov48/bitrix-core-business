<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/tooltip-configurator.bundle.css',
	'js' => 'dist/tooltip-configurator.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];
