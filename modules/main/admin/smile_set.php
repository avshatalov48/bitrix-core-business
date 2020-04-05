<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!$USER->CanDoOperation('edit_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__); 

$sTableID = "tbl_smile_set";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array();

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if ($arID = $lAdmin->GroupAction())
{
	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		if ($_REQUEST['action'] == 'delete')
		{
			$arSmileSet = CSmileSet::getById($ID);
			if (!in_array($arSmileSet["STRING_ID"], Array('bitrix_main')))
			{
				CSmileSet::delete($ID);
			}
		}
	}
}
if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = IntVal($ID);
		if($ID <= 0)
			continue;

		CSmileSet::update($ID, Array(
			'LANG' => Array(
				LANGUAGE_ID => $arFields['NAME']
			),
			'STRING_ID' => $arFields['STRING_ID'],
			'SORT' => $arFields['SORT'],
		));
	}
}

if (isset($_REQUEST['GALLERY_ID']))
{
	$arFilter['PARENT_ID'] = intval($_REQUEST['GALLERY_ID']);
}
else
{
	LocalRedirect("smile_gallery.php?lang=".LANG);
}

$dbResultList = CSmileSet::getList(Array(
	'SELECT' => Array('ID', 'STRING_ID', 'NAME', 'SORT', 'SMILE_COUNT'),
	'FILTER' => $arFilter,
	'ORDER' => array($by => $order),
	'NAV_PARAMS' => array("nPageSize"=>CAdminResult::GetNavSize($sTableID)),
	'RETURN_RES' => 'Y'
));

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SMILE_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("SMILE_ID"), "sort"=>"ID", "default"=>false),
	array("id"=>"NAME", "content"=>GetMessage("SMILE_NAME"), "default"=>true),
	array("id"=>"STRING_ID", "content"=>GetMessage("SMILE_STRING_ID"), "default"=>false),
	array("id"=>"SORT","content"=>GetMessage("SMILE_SORT"), "sort"=>"SORT", "default"=>true, "align"=>"right"),
	array("id"=>"SMILE_COUNT","content"=>GetMessage("SMILE_SMILE_COUNT"), "sort"=>"SMILE_COUNT", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arForum = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arForum);

	$row->AddField("ID", $f_ID);
	$row->AddField("SORT", $f_SORT);
	$row->AddViewField("NAME", '<a title="'.GetMessage("SMILE_EDIT_DESCR").'" href="'."smile.php?SET_ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").'">'.(strlen($f_NAME)>0?$f_NAME: GetMessage('SMILE_SET_NAME', Array('#ID#' => $f_ID))).'</a>');
	$row->AddViewField("SMILE_COUNT", $f_SMILE_COUNT);

	$row->AddInputField("NAME", array("size"=>20));
	$row->AddInputField("STRING_ID", array("size"=>20));
	$row->AddInputField("SORT", array("size"=>5));

	if (in_array($f_STRING_ID, Array('bitrix_main')))
	{
		$row->AddField("STRING_ID", $f_STRING_ID);
		$arActions = Array(
			array("ICON"=>"edit", "TEXT"=>GetMessage("SMILE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("smile_set_edit.php?GALLERY_ID=".$arFilter['PARENT_ID']."&ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true),
		);
	}
	else
	{
		$row->AddInputField("STRING_ID", array("size"=>20));
		$arActions = Array(
			array("ICON"=>"edit", "TEXT"=>GetMessage("SMILE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("smile_set_edit.php?GALLERY_ID=".$arFilter['PARENT_ID']."&ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true),
			array("SEPARATOR" => true),
			array("ICON"=>"delete", "TEXT"=>GetMessage("SMILE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SMILE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "GALLERY_ID=".$arFilter['PARENT_ID']))
		);
	}

	$row->AddActions($arActions);
}


$aContext = array(

	array(
		"TEXT" => GetMessage("SMILE_BTN_BACK"),
		"LINK" => "/bitrix/admin/smile_gallery.php?&lang=".LANG,
		"ICON" => "btn_list",
	),
	array(
		"TEXT" => GetMessage("SMILE_BTN_ADD_NEW"),
		"LINK" => "smile_set_edit.php?GALLERY_ID=".$arFilter['PARENT_ID']."&lang=".LANG,
		"TITLE" => GetMessage("SMILE_BTN_ADD_NEW_ALT"),
		"ICON" => "btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SMILE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>