<?php
use Bitrix\Main\Localization\Loc;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

CAdminNotify::DeleteByTag('SALE_PAYSYSTEM_CONVERT_ERROR');

$APPLICATION->SetTitle(Loc::getMessage("SALE_CONVERTER_PS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$psConverted = \Bitrix\Main\Config\Option::get('main', '~sale_paysystem_converted');
if ($psConverted == '')
{
	CSalePaySystemAction::convertPsBusVal();
	CSaleExport::migrateToBusinessValues();
}

CAdminMessage::ShowMessage(array(
	"DETAILS" => Loc::getMessage('SALE_CONVERTER_PS_DETAILS'),
	"HTML" => true,
	"TYPE" => 'OK',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>