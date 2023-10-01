<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/ast-processor.bundle.css',
	'js' => 'dist/ast-processor.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];