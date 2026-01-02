<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/create-chat-promo.bundle.css',
	'js' => 'dist/create-chat-promo.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.const',
		'im.v2.component.elements.popup',
		'ui.lottie',
		'im.v2.component.elements.button',
	],
	'skip_core' => true,
];