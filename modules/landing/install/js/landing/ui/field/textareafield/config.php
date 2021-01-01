<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/textareafield.bundle.css',
	'js' => 'dist/textareafield.bundle.js',
	'rel' => [
		'main.core',
		'landing.ui.field.basefield',
	],
	'skip_core' => false,
];