<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => '/bitrix/js/translate/process/css/dialog.css',
	'js' => [
		'/bitrix/js/translate/process/dialog.js',
		'/bitrix/js/translate/process/process.js',
	],
	'rel' => [
		'main.popup',
		'ui.progressbar',
		'ui.buttons',
	],
];

