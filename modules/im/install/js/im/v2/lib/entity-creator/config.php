<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/entity-creator.bundle.js',
	],
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'rest.client',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => true,
];