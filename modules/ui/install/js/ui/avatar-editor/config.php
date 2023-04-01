<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
use \Bitrix\UI;
return [
	'css' => 'dist/main.avatar-editor.bundle.css',
	'js' => 'dist/main.avatar-editor.bundle.js',
	'rel' => [
		'ui.fonts.opensans',
		'ui.design-tokens',
		'ui.notification',
		'ui.entity-selector',
		'ui.dialogs.messagebox',
		'main.loader',
		'main.core',
		'main.core.events',
		'main.popup',
		'ui.buttons',
		'ui.sidepanel.layout',
	],
	'skip_core' => false,

	'oninit' => static function() {
		return [
			'rel' => [
				'dd'
			],
			'lang_additional' => [
				'UI_AVATAR_MASK_MAX_SIZE' => 1024,
				'UI_AVATAR_MASK_REQUEST_FIELD_NAME' => \Bitrix\UI\Avatar\Mask\Helper::REQUEST_FIELD_NAME,
				'UI_AVATAR_MASK_PATH_ARTICLE' => \Bitrix\UI\Util::getArticleUrlByCode('15968402'),
				//TODO delete this string and its using after testing
				'UI_AVATAR_MASK_IS_AVAILABLE' => \Bitrix\Main\Config\Option::get('ui', 'avatar-editor-availability-delete-after-10.2022', 'N') === 'Y'
			]
		];
	}
];