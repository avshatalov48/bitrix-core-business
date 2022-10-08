<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	"js" => "/bitrix/js/seo/ads/client_selector/dist/client_selector.bundle.js",
	"css" => "/bitrix/js/seo/ads/client_selector/dist/client_selector.bundle.css",
	'rel' => [
		'main.core',
		'main.loader',
		'ui.design-tokens',
	],
	'skip_core' => false,
);