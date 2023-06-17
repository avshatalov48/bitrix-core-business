<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/market-content.bundle.css',
	'js' => 'dist/market-content.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.lib.market',
		'im.v2.component.elements',
	],
	'skip_core' => false,
];