<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/index.bundle.css',
	'js' => 'dist/index.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.base',
		'im.v2.component.message.elements',
		'vote.component.vote',
		'vote.analytics',
	],
	'skip_core' => true,
];

