<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/deletedviewform.bundle.css',
	'js' => 'dist/deletedviewform.bundle.js',
	'rel' => [
		'main.core',
		'calendar.sharing.public-v2',
		'calendar.util',
		'main.date',
	],
	'skip_core' => false,
];