<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/reaction.bundle.css',
	'js' => 'dist/reaction.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.reactions-select',
		'ui.lottie',
		'im.v2.component.elements',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.lib.user',
		'im.v2.lib.logger',
	],
	'skip_core' => false,
];