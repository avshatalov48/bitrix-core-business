<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/secret_block.bundle.css',
	'js' => 'dist/secret_block.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];