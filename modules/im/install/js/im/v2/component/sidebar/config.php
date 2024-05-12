<?php

use Bitrix\Im\Integration\Disk\Documents;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

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
		'im.v2.lib.local-storage',
		'im.v2.lib.rest',
		'ui.vue3.directives.lazyload',
		'ui.label',
		'im.v2.lib.menu',
		'main.date',
		'ui.vue3.directives.hint',
		'ui.vue3.components.socialvideo',
		'ui.viewer',
		'ui.icons',
		'im.v2.model',
		'ui.notification',
		'rest.client',
		'ui.vue3.vuex',
		'im.v2.lib.market',
		'im.v2.lib.entity-creator',
		'im.v2.component.entity-selector',
		'im.v2.lib.call',
		'im.v2.lib.permission',
		'im.v2.lib.confirm',
		'im.v2.provider.service',
		'im.v2.lib.logger',
		'main.core',
		'im.v2.lib.parser',
		'im.v2.lib.text-highlighter',
		'main.core.events',
		'im.public',
		'im.v2.lib.utils',
		'im.v2.lib.date-formatter',
		'im.v2.component.elements',
		'im.v2.const',
		'im.v2.lib.user',
		'im.v2.application.core',
	],
	'skip_core' => false,
	'settings' => [
		'linksAvailable' => $isLinksAvailable(),
		'filesMigrated' => $isFilesMigrationFinished(),
		'canShowBriefs' => $canShowBriefs()
	]
];