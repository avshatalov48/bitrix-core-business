<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => '/bitrix/js/ui/tour/ui.tour.css',
	'js' => '/bitrix/js/ui/tour/dist/tour.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];