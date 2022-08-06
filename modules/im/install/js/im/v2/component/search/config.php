<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/search.bundle.css',
	'js' => 'dist/search.bundle.js',
	'rel' => [
		'im.v2.component.elements',
		'im.v2.lib.logger',
		'im.v2.const',
		'main.core.events',
		'ui.dexie',
		'ui.vue3',
		'main.core',
	],
	'settings' => [
		'minTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	],
	'skip_core' => false,
];