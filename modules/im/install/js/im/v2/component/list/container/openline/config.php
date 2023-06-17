<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/openline-container.bundle.css',
	'js' => 'dist/openline-container.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.element-list.openline',
		'im.v2.component.search2.search-input',
		'im.v2.component.search2.search-result',
		'im.v2.lib.logger',
	],
	'skip_core' => true,
];