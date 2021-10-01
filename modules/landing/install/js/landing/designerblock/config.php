<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/designerblock.bundle.css',
	'js' => 'dist/designerblock.bundle.js',
	'rel' => [
		'landing.backend',
		'landing.env',
		'landing.metrika',
		'landing.ui.highlight',
		'landing.loc',
		'landing.ui.panel.content',
		'main.core',
	],
	'skip_core' => false,
];
