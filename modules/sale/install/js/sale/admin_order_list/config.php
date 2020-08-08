<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/admin_order_list.bundle.css',
	'js' => 'dist/admin_order_list.bundle.js',
	'rel' => [
		'main.polyfill.core',
	],
	'skip_core' => true,
];