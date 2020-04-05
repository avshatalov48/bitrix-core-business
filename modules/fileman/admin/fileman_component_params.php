<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");
if (!$USER->CanDoOperation('fileman_view_file_structure') && !$USER->CanDoOperation('edit_other_settings'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

CModule::IncludeModule('fileman');

if (isset($_REQUEST['component_params_manager']))
{
	CComponentParamsManager::ProcessRequest();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");?>