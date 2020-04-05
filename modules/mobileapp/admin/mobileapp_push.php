<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage mobileapp
 * @copyright 2001-2014 Bitrix
 */
use Bitrix\Main\Localization\Loc;

/**
 * Bitrix vars
 * @global CAllUser $USER
 * @global CAllMain $APPLICATION
 */

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("mobileapp");
Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage("MOBILEAPP_PUSH_NOTIFICATIONS"));


if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

CUtil::InitJSCore(Array('ajax', 'window', "popup"));
$lAdmin = new CAdminList("applist");
$lAdmin->ShowActionTable();

$aContext = array(
	array(
		"TEXT" => Loc::getMessage("MOBILEAPP_REMOVE"),
		"TITLE" => GetMessage("MOBILEAPP_ADD_APP"),
		"LINK" => "mobile_app_edit.php?lang=" . LANG,
		"ICON" => "btn_new",
	)
);



$lAdmin->AddAdminContextMenu($aContext);
$data = array(
	"NAME" => "applist",
	"TITLE" => GetMessage("SEC_PANEL_SCANNER_TITLE"),
	"HEADERS" => array(),
	"ITEMS" => array()
);

$appTable = new CPullPush();
$tableMap = $appTable->getMap();
$fieldsCount = count($tableMap);

for ($i = 0; $i < $fieldsCount; $i++)
{
	/**
	 * @var $field Bitrix\Main\Entity\ScalarField
	 */
	$field = $tableMap[$i];
	$name = $field->getName();
	if($name === "CONFIG")
	{
		continue;
	}
	else
	{
		$data["HEADERS"][] = array(
			"id" => $field->getName(),
			"content" => $field->getTitle(),
			"align" => "left",
			"default" => true,
		);
	}

}

$data["ITEMS"] = $appTable->getList()->fetchAll();
$lAdmin->AddHeaders($data["HEADERS"]);

$rsData = new CDBResult;
$rsData->InitFromArray($data["ITEMS"]);
$rsData = new CAdminResult($rsData, "applist");

$j = 0;
while ($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($j++, $arRes);
	foreach ($arRes as $key => $value)
	{
		$row->AddViewField($key, is_array($value)?count($value):$value);
		$arActions = array(
			array(
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage("MOBILEAPP_EDIT_APP"),
				"ACTION" => $lAdmin->ActionRedirect("mobile_app_edit.php?ID=" . $f_ID)
			),
			array(
				"ICON" => "delete",
				"TEXT" => GetMessage("MOBILEAPP_REMOVE_APP"),
				"ACTION"=>"if(confirm('" . GetMessage('MOBILEAPP_REMOVE_APP_CONFIRM') . "')) " . $lAdmin->ActionDoGroup($f_ID, "delete")
			)
		);

		$row->AddActions($arActions);
	}
}


$lAdmin->CheckListMode();
$lAdmin->Display();


require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin.php"); ?>


