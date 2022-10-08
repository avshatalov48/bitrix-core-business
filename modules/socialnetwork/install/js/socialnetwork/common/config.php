<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\UserToGroupTable;

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/socialnetwork/install/js/socialnetwork/common/config.php');

return array(
	'js' => './dist/common.bundle.js',
	'css' => '/bitrix/js/socialnetwork/common/socialnetwork.common.css',
	'rel' => [
		'popup',
		'intranet_theme_picker',
		'ui.popupcomponentsmaker',
		'ui.icons',
		'ui.buttons',
		'ui.fonts.opensans',
	],
	'lang_additional' => [
		'USER_SONET_ADMIN' => (\CSocNetUser::isCurrentUserModuleAdmin() ? 'Y' : 'N'),
		'USER_TO_GROUP_ROLE_OWNER' => UserToGroupTable::ROLE_OWNER,
		'USER_TO_GROUP_ROLE_MODERATOR' => UserToGroupTable::ROLE_MODERATOR,
		'USER_TO_GROUP_ROLE_USER' => UserToGroupTable::ROLE_USER,
		'USER_TO_GROUP_ROLE_REQUEST' => UserToGroupTable::ROLE_REQUEST,
		'USER_TO_GROUP_INITIATED_BY_USER' => UserToGroupTable::INITIATED_BY_USER,
		'USER_TO_GROUP_INITIATED_BY_GROUP' => UserToGroupTable::INITIATED_BY_GROUP,
	],
);