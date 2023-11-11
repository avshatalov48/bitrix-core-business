<?php
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");
define("HELP_FILE", "settings/culture_admin.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

use Bitrix\Main;
use Bitrix\Main\Localization\CultureTable;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$tableID = "tbl_culture";
$sorting = new CAdminSorting($tableID, "name", "asc");
$adminList = new CAdminList($tableID, $sorting);

/** @var $request Main\HttpRequest */
$request = Main\Context::getCurrent()->getRequest();

if($adminList->EditAction() && $isAdmin)
{
	foreach($request["FIELDS"] as $ID => $arFields)
	{
		if(!$adminList->IsUpdated($ID))
			continue;

		$result = CultureTable::update($ID, $arFields);
		if(!$result->isSuccess())
		{
			$adminList->AddUpdateError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
		}
	}
}

if(($arID = $adminList->GroupAction()) && $isAdmin)
{
	if($request['action_target'] == 'selected')
	{
		$arID = array();
		$data = CultureTable::getList();
		while($culture = $data->fetch())
			$arID[] = $culture['ID'];
	}

	foreach($arID as $ID)
	{
		if(intval($ID) <= 0)
			continue;

		switch($request['action_button'])
		{
			case "delete":
				$result = CultureTable::delete($ID);
				if(!$result->isSuccess())
				{
					$adminList->AddGroupError("(ID=".$ID.") ".implode("<br>", $result->getErrorMessages()), $ID);
				}
				break;
		}
	}
}

$APPLICATION->SetTitle(Loc::getMessage("TITLE"));

$sortBy = mb_strtoupper($sorting->getField());
if(!CultureTable::getEntity()->hasField($sortBy))
{
	$sortBy = "NAME";
}

$sortOrder = mb_strtoupper($sorting->getOrder());
if($sortOrder <> "DESC")
{
	$sortOrder = "ASC";
}

$nav = new \Bitrix\Main\UI\AdminPageNavigation("nav-culture");

$cultureList = CultureTable::getList(array(
	'order' => array($sortBy => $sortOrder),
	'count_total' => true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
));

$nav->setRecordCount($cultureList->getCount());

$adminList->setNavigation($nav, Loc::getMessage("PAGES"));

$adminList->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>Loc::getMessage("NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"CODE", "content"=>Loc::getMessage("culture_code"), "sort"=>"CODE", "default"=>true),
	array("id"=>"FORMAT_DATE", "content"=>Loc::getMessage("culture_date"), "sort"=>"FORMAT_DATE", "default"=>true),
	array("id"=>"FORMAT_DATETIME", "content"=>Loc::getMessage("culture_datetime"), "sort"=>"FORMAT_DATETIME", "default"=>true),
	array("id"=>"FORMAT_NAME", "content"=>Loc::getMessage("culture_name"), "sort"=>"FORMAT_NAME", "default"=>true),
	array("id"=>"CHARSET", "content"=>Loc::getMessage("culture_charset"), "sort"=>"CHARSET", "default"=>true),
	array("id"=>"WEEK_START", "content"=>Loc::getMessage("culture_week"), "sort"=>"WEEK_START", "default"=>false),
	array("id"=>"DIRECTION", "content"=>Loc::getMessage("culture_direction"), "sort"=>"DIRECTION", "default"=>false),
));

$days = array(Loc::getMessage("culture_su"), Loc::getMessage("culture_mo"), Loc::getMessage("culture_tu"), Loc::getMessage("culture_we"), Loc::getMessage("culture_th"), Loc::getMessage("culture_fr"), Loc::getMessage("culture_sa"));

while($culture = $cultureList->fetch())
{
	$id = htmlspecialcharsbx($culture["ID"]);
	$name = htmlspecialcharsbx($culture["NAME"]);

	$row = &$adminList->AddRow($id, $culture, "culture_edit.php?ID=".$id."&lang=".LANGUAGE_ID, Loc::getMessage("LANG_EDIT_TITLE"));
	$row->AddViewField("ID", $id);
	$row->AddField("NAME", '<a href="culture_edit.php?ID='.$id.'&amp;lang='.LANGUAGE_ID.'" title="'.Loc::getMessage("LANG_EDIT_TITLE").'">'.$name.'</a>', $name);
	$row->AddInputField("CODE");
	$row->AddInputField("FORMAT_DATE");
	$row->AddInputField("FORMAT_DATETIME");
	$row->AddInputField("FORMAT_NAME");
	$row->AddViewField("WEEK_START", $days[$culture["WEEK_START"]]);
	$row->AddInputField("CHARSET");
	$row->AddViewField("DIRECTION", ($culture["DIRECTION"] == CultureTable::LEFT_TO_RIGHT? Loc::getMessage("culture_left_to_right") : Loc::getMessage("culture_right_to_left")));

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("CHANGE"), "ACTION"=>$adminList->ActionRedirect("culture_edit.php?ID=".$id));
	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("COPY"), "ACTION"=>$adminList->ActionRedirect("culture_edit.php?COPY_ID=".$id));
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("DELETE"), "ACTION"=>"if(confirm('".Loc::getMessage('CONFIRM_DEL')."')) ".$adminList->ActionDoGroup($id, "delete"));
	}

	$row->AddActions($arActions);
}

$adminList->AddGroupActionTable(array(
	"delete"=>true,
));

$aContext = array(
	array(
		"TEXT"	=> Loc::getMessage("ADD_LANG"),
		"LINK"	=> "culture_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> Loc::getMessage("ADD_LANG_TITLE"),
		"ICON"	=> "btn_new"
	),
);
$adminList->AddAdminContextMenu($aContext);

$adminList->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$adminList->DisplayList();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
