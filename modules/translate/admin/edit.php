<?php

define('ADMIN_MODULE_NAME', 'translate');

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_before.php';

/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 */

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::showMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

if (!\Bitrix\Translate\Permission::canEdit($USER))
{
	$APPLICATION->AuthForm(\Bitrix\Main\Localization\Loc::getMessage('ACCESS_DENIED'));
}

$request = \Bitrix\Main\HttpContext::getCurrent()->getRequest();

if (($request->isAjaxRequest() || $request->get('AJAX_CALL') !== null) && !defined('ADMIN_SECTION_LOAD_AUTH'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');
}
else
{
	define('HELP_FILE', 'translate_list.php');

	$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage('TRANS_TITLE'));

	require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
}

$APPLICATION->IncludeComponent('bitrix:translate.edit', '', ['SET_TITLE' => 'Y']);

if ($request->isAjaxRequest() || $request->get('AJAX_CALL') !== null)
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin_js.php');
}
else
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}