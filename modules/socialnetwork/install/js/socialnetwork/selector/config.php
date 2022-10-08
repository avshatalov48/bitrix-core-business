<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => [
		'/bitrix/js/socialnetwork/selector/socialnetwork.selector.css',
		'/bitrix/js/socialnetwork/selector/callback.css'
	],
	'js' => [
		'/bitrix/js/socialnetwork/selector/socialnetwork.selector.js'
	],
	'rel' => ['ui.design-tokens', 'ui.selector']
];