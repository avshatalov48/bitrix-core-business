<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	"css" => "dist/ui.switcher.bundle.css",
	"js" => "dist/ui.switcher.bundle.js",
	"lang" => "/bitrix/modules/ui/install/ui.switcher.php",
	'rel' => [
		'main.core',
		'ui.design-tokens',
		'main.core.events',
	],
	'skip_core' => false,
];
