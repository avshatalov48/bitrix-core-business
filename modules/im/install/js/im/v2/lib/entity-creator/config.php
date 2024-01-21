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
		'main.core',
		'main.core.events',
		'ai.picker',
		'calendar.sliderloader',
		'im.v2.lib.rest',
		'im.v2.application.core',
		'im.v2.const',
	],
	'skip_core' => false,
];