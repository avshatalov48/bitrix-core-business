<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/imageuploader.bundle.css',
	'js' => 'dist/imageuploader.bundle.js',
	'rel' => [
		'main.core',
		'landing.imagecompressor',
		'landing.backend',
	],
	'skip_core' => false,
];