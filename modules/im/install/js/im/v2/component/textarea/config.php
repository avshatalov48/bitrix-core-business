<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Im\V2\Integration\AI\Restriction;

if (!\Bitrix\Main\Loader::includeModule('im'))
{
	return [];
}

$aiTextRestriction = new Restriction(Restriction::AI_TEXT_TYPE);
$aiImageRestriction = new Restriction(Restriction::AI_IMAGE_TYPE);

return [
	'css' => 'dist/textarea.bundle.css',
	'js' => 'dist/textarea.bundle.js',
	'rel' => [
		'ui.uploader.core',
		'im.v2.lib.draft',
		'im.v2.lib.local-storage',
		'im.v2.lib.sound-notification',
		'rest.client',
		'im.v2.application.core',
		'im.v2.lib.smile-manager',
		'im.v2.lib.rest',
		'im.v2.lib.parser',
		'ui.vue3.directives.hint',
		'im.v2.lib.entity-creator',
		'im.v2.lib.market',
		'im.v2.lib.hotkey',
		'im.v2.lib.textarea',
		'main.core.events',
		'im.v2.lib.utils',
		'im.v2.lib.logger',
		'im.v2.provider.service',
		'main.core',
		'im.v2.const',
		'im.v2.component.elements',
		'im.v2.lib.text-highlighter',
	],
	'skip_core' => false,
	'settings' => [
		'isAiTextBetaAvailable' => $aiTextRestriction->isAvailable(),
		'isAiImageBetaAvailable' => $aiImageRestriction->isAvailable(),
		'maxLength' => \CIMMessenger::MESSAGE_LIMIT,
		'minSearchTokenSize' => \Bitrix\Main\ORM\Query\Filter\Helper::getMinTokenSize(),
	]
];