<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/vote.bundle.css',
	'js' => 'dist/vote.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.message.unsupported',
		'vote.component.message',
	],
	'skip_core' => true,
];
