<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/messenger.bundle.css',
	'js' => 'dist/messenger.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'planner',
		'ui.fontawesome4',
		'im.integration.viewer',
		'ui.design-tokens',
		'ui.fonts.opensans',
		'im.v2.css.tokens',
		'im.v2.css.icons',
		'im.v2.css.classes',
		'im.v2.lib.counter',
		'im.v2.lib.esc-manager',
		'im.v2.lib.logger',
		'im.v2.lib.init',
		'im.v2.lib.call',
		'im.v2.lib.theme',
		'im.v2.lib.desktop',
		'im.v2.lib.layout',
		'im.v2.const',
		'im.v2.component.content.chat',
		'im.v2.component.content.chat-forms.forms',
		'im.v2.component.content.market',
		'im.v2.component.content.notification',
		'im.v2.component.content.openlines',
		'im.v2.component.content.openlinesV2',
		'im.v2.component.content.settings',
		'im.v2.component.list.container.channel',
		'im.v2.component.list.container.collab',
		'im.v2.component.list.container.copilot',
		'im.v2.component.list.container.openline',
		'im.v2.component.list.container.recent',
		'im.v2.component.list.container.task',
	],
	'skip_core' => true,
	'settings' => [
		'isLinesOperator' => \Bitrix\Im\Integration\Imopenlines\User::isOperator(),
	]
];
