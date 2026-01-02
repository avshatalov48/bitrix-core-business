<?php

use Bitrix\Im\V2\Application\Navigation\Menu;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-compact.bundle.css',
	'js' => 'dist/recent-compact.bundle.js',
	'rel' => [
		'im.v2.lib.utils',
		'im.v2.provider.service.recent',
		'im.v2.lib.menu',
		'im.v2.css.tokens',
		'ui.design-tokens.air',
		'main.core',
		'ui.icon-set.api.vue',
		'im.public',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.component.elements.avatar',
	],
	'skip_core' => false,
	'settings' => [
		'navigationItems' => Menu::getInstance()->getMenuItems(),
	],
];
