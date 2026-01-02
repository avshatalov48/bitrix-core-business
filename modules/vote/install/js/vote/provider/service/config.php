<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/vote-service.bundle.js',
	'rel' => [
		'main.core',
		'ui.notification',
		'vote.application',
	],
	'skip_core' => false,
];

