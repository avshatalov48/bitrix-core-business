<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$lastUpdate = \CSmile::getLastUpdate()->format(\DateTime::ATOM);

return [
	'css' => 'dist/smile-manager.bundle.css',
	'js' => 'dist/smile-manager.bundle.js',
	'rel' => [
		'main.core',
		'ui.dexie',
		'im.v2.const',
		'im.v2.application.core',
		'rest.client',
		'im.v2.lib.local-storage',
	],
	'skip_core' => false,
	'settings' => [
		'lastUpdate' => $lastUpdate
	]
];