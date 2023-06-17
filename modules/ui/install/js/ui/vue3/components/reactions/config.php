<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' =>[
		'./dist/reactions.bundle.js',
	],
	'css' => [
		'./dist/reactions.bundle.css',
	],
	'rel' => [
		'ui.fonts.opensans',
		'ui.vue3',
		'main.core',
		'main.core.events',
		'ui.reactions-select',
		'ui.lottie',
	],
	'skip_core' => false,
];