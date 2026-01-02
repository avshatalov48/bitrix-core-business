<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$isPhoneInviteAvailable = Loader::includeModule("bitrix24") && Option::get('bitrix24', 'phone_invite_allowed', 'N') === 'Y';
$isInviteLinkAvailable = Loader::includeModule("bitrix24") && Option::get("socialservices", "new_user_registration_network", "N") === 'Y';

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'im.v2.provider.service.chat',
		'ui.entity-selector',
		'im.v2.lib.channel',
		'intranet.invitation-input',
		'im.v2.application.core',
		'im.v2.lib.helpdesk',
		'im.v2.component.elements.scroll-with-gradient',
		'ui.info-helper',
		'im.v2.lib.permission',
		'im.v2.lib.feature',
		'im.v2.component.elements.button',
		'im.v2.lib.rest',
		'im.v2.lib.utils',
		'intranet.languages',
		'main.core',
		'ui.vue3.directives.hint',
		'main.popup',
		'im.v2.component.elements.popup',
		'main.core.events',
		'im.v2.lib.sound-notification',
		'im.v2.provider.service.sending',
		'im.public',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.component.search',
		'im.v2.lib.notifier',
	],
	'settings' => [
		'isPhoneInviteAvailable' => $isPhoneInviteAvailable,
		'isInviteLinkAvailable' => $isInviteLinkAvailable,
	],
	'skip_core' => false,
];
