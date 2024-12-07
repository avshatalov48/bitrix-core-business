<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/task.bundle.css',
	'js' => 'dist/task.bundle.js',
	'rel' => [
		'main.core',
		'bizproc.types',
	],
	'skip_core' => false,
];