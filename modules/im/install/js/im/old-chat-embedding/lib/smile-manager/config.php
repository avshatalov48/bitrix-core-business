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
		'rest.client',
		'im.old-chat-embedding.application.core',
		'im.old-chat-embedding.const',
		'im.old-chat-embedding.lib.local-storage',
	],
	'skip_core' => false,
	'settings' => [
		'lastUpdate' => $lastUpdate
	]
];