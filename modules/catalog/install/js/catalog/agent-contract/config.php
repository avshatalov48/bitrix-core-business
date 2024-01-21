<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/agent-contract.bundle.css',
	'js' => 'dist/agent-contract.bundle.js',
	'rel' => [
		'main.core.events',
		'main.core',
		'ui.dialogs.messagebox',
	],
	'skip_core' => false,
];