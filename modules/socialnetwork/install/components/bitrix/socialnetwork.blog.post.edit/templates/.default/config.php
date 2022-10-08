<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'script.css',
	'js' => 'script.js',
	'rel' => [
		'main.core',
		'ui.entity-selector',
		'ui.design-tokens',
		'ui.fonts.opensans',
	],
	'skip_core' => false,
];