<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"js" => "/bitrix/js/main/polyfill/core/dist/polyfill.bundle.js",
	'rel' => [
		'core-js/es6',
		'core-js/es7',
		'core-js/web',
	],
	'skip_core' => true,
];