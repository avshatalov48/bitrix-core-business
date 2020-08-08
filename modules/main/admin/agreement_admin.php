<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
use Bitrix\Main\Localization\Loc;

require_once(dirname(__FILE__)."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/agreement_admin.php");

Loc::loadMessages(__FILE__);

$canEdit = $USER->CanDoOperation('edit_other_settings');
$canView = $USER->CanDoOperation('view_other_settings');
if(!$canEdit && !$canView)
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

global $adminPage;
$adminPage->hideTitle();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");


$APPLICATION->IncludeComponent(
	"bitrix:main.userconsent.list",
	"",
	array(
		'PATH_TO_LIST' => BX_ROOT . '/admin/agreement_admin.php?lang=' . LANGUAGE_ID,
		'PATH_TO_ADD' => BX_ROOT . '/admin/agreement_edit.php?ID=0&lang=' . LANGUAGE_ID,
		'PATH_TO_EDIT' => BX_ROOT . '/admin/agreement_edit.php?ID=#id#&lang=' . LANGUAGE_ID,
		'PATH_TO_CONSENT_LIST' => BX_ROOT .
			'/admin/agreement_consents.php?AGREEMENT_ID=#id#&apply_filter=Y&lang=' . LANGUAGE_ID,
		'CAN_EDIT' => $canEdit,
		'ADMIN_MODE' => true
	)
);

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
