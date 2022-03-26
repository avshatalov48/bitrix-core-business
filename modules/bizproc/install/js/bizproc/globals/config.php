<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => '/bitrix/js/bizproc/globals/dist/globals.bundle.js',
	'rel' => [
		'main.core',
		'sidepanel',
	],
	'skip_core' => false,
];