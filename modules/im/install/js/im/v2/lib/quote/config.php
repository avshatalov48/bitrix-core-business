<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
    die();
}

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

return [
	'js' => './dist/quote.bundle.js',
	'css' => './dist/quote.bundle.css',
	'rel' => [
		'main.core',
		'main.core.events',
		'im.v2.lib.copilot',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.date-formatter',
		'im.v2.lib.parser',
	],
	'skip_core' => false,
];