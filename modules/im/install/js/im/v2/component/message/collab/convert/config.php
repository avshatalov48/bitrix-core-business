<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/convert.bundle.css',
	'js' => 'dist/convert.bundle.js',
	'rel' => [
		'main.core',
		'ui.vue3.components.rich-loc',
		'im.v2.component.message.base',
	],
	'skip_core' => false,
];
