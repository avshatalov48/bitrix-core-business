<?php

use Bitrix\Im\Integration\Disk\Documents;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$getAvailableBlocks = static fn() => ['main', 'info', 'brief', 'file', 'fileUnsorted', 'task', 'meeting', 'market'];
$isLinksAvailable = static function() {
	return \Bitrix\Main\Config\Option::get('im', 'im_link_url_migration', 'N') === 'Y';
};

$isFilesMigrationFinished = static function() {
	return \Bitrix\Main\Config\Option::get('im', 'im_link_file_migration', 'N') === 'Y';
};

$canShowBriefs = static function() {
	return Documents::getResumesOfCallStatus() === Documents::ENABLED;
};

return [
	'css' => 'dist/sidebar.bundle.css',
	'js' => 'dist/sidebar.bundle.js',
	'rel' => [
		'im.v2.lib.logger',
		'im.v2.lib.user',
		'im.v2.lib.call',
		'im.public',
		'ui.vue3.directives.hint',
		'im.v2.provider.service',
		'im.v2.lib.parser',
		'ui.vue3.components.socialvideo',
		'ui.vue3',
		'ui.vue3.components.audioplayer',
		'ui.viewer',
		'ui.label',
		'ui.icons',
		'im.v2.model',
		'ui.notification',
		'rest.client',
		'ui.vue3.vuex',
		'im.v2.application.core',
		'main.date',
		'im.v2.lib.date-formatter',
		'im.v2.lib.market',
		'main.core.events',
		'im.v2.lib.menu',
		'im.v2.lib.utils',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.entity-creator',
		'im.v2.component.entity-selector',
		'main.core',
	],
	'skip_core' => false,
	'settings' => [
		'blocks' => $getAvailableBlocks(),
		'linksAvailable' => $isLinksAvailable(),
		'filesMigrated' => $isFilesMigrationFinished(),
		'canShowBriefs' => $canShowBriefs()
	]
];