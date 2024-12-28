<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => './dist/video-player.bundle.js',
	'css' => './dist/video-player.bundle.css',
	'rel' => [
		'ui.video-js',
		'ui.icon-set.actions',
		'ls',
	],
];
