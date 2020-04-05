<?php
define('ADMIN_MODULE_NAME', 'translate');
require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_before.php';

/**
 * @global \CMain $APPLICATION
 * @global \CUser $USER
 */

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

if (!\Bitrix\Translate\Permission::canView($USER))
{
	$APPLICATION->AuthForm(\Bitrix\Main\Localization\Loc::getMessage('ACCESS_DENIED'));
}

if (!\Bitrix\Translate\Permission::canEditSource($USER))
{
	$APPLICATION->AuthForm(\Bitrix\Main\Localization\Loc::getMessage('TR_FILE_VIEW_PHPERROR'));
}

define('HELP_FILE', 'translate_list.php');

$APPLICATION->SetTitle(\Bitrix\Main\Localization\Loc::getMessage("TRANS_TITLE"));

require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';


/** @global \CMain $APPLICATION */
$APPLICATION->IncludeComponent('bitrix:translate.edit', '', ['VIEW_MODE' => 'SourceEdit']);


require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
