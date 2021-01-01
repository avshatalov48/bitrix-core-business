<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/metrika.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];