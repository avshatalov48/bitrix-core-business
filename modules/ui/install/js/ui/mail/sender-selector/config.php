<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/sender-selector.bundle.css',
	'js' => 'dist/sender-selector.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'main.loader',
		'ui.entity-selector',
		'ui.mail.provider-showcase',
		'ui.mail.sender-editor',
		'ui.icon-set.api.core',
		'ui.icon-set.actions',
	],
	'skip_core' => false,
];