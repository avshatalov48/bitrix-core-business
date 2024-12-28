<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => [
		'./dist/videojs-playlist.min.js',
		'./dist/videojs-playlist-ui.min.js',
	],
	'css' => [
		'./dist/videojs-playlist-ui.css',
	],
	'skip_core' => true,
	'rel' => [
		'ui.video-js',
	],
];
