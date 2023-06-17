<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-container.bundle.css',
	'js' => 'dist/recent-container.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'im.v2.component.list.element-list.recent',
		'im.v2.component.search.search-input',
		'im.v2.component.search.search-result',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'im.v2.component.elements',
		'im.v2.const',
	],
	'skip_core' => false,
];