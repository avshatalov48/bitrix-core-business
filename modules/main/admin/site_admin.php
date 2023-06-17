<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/sites/site_admin.php");

if(!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings') && !$USER->CanDoOperation('lpa_template_edit'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_site";

$oSort = new CAdminSorting($sTableID, "SORT", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

if ($lAdmin->EditAction() && $isAdmin)
{
	foreach ($FIELDS as $ID=>$arFields)
	{
		if (!$lAdmin->IsUpdated($ID))
		{
			continue;
		}

		$DB->StartTransaction();

		$ob = new CLang;
		if ($ob->Update($ID, $arFields))
		{
			$DB->Commit();
		}
		else
		{
			$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.": ".$ob->LAST_ERROR, $ID);
			$DB->Rollback();
		}
	}
}

if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CLang::GetList('', '', Array());
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if($ID == '')
			continue;

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$ob = new CLang;
			$DB->StartTransaction();
			if (!$ob->Delete($ID))
			{
				$DB->Rollback();
				if($ex = $APPLICATION->GetException())
					$er = $ex->GetString();
				else
					$er = GetMessage("DELETE_ERROR");

				$lAdmin->AddGroupError($er, $ID);
			}
			else
			{
				$DB->Commit();
			}
			break;
		case "activate":
		case "deactivate":
			$ob = new CLang;
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if(!$ob->Update($ID, $arFields))
				$lAdmin->AddGroupError(GetMessage("EDIT_ERROR").$ob->LAST_ERROR, $ID);
			break;
		}
	}
}

$APPLICATION->SetTitle(GetMessage("TITLE"));

global $by, $order;

$langs = CLang::GetList($by, $order, Array());
$rsData = new CAdminResult($langs, $sTableID);
$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES"), false));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"ACTIVE","content"=>GetMessage('ACTIVE'), "sort"=>"active", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('SORT'), "sort"=>"sort", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"DIR",	"content"=>GetMessage("DIR"), "sort"=>"dir",	"default"=>true),
	array("id"=>"DEF", "content"=>GetMessage("DEF"), "sort"=>"def", "default"=>true),
));
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes, "site_edit.php?LID=".urlencode($arRes['ID'])."&lang=".LANGUAGE_ID, GetMessage("SITE_EDIT"));
	$row->AddViewField("ID", '<a href="site_edit.php?lang='.LANGUAGE_ID.'&amp;LID='.urlencode($arRes['ID']).'" title="'.GetMessage("SITE_EDIT_TITLE").'">'.$f_ID.'</a>');
	$row->AddCheckField("ACTIVE");
	$row->AddInputField("SORT");
	$row->AddInputField("NAME");
	$row->AddInputField("DIR");
	$row->AddCheckField("DEF");
	$arActions = Array();

	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("CHANGE"), "ACTION"=>$lAdmin->ActionRedirect("site_edit.php?LID=".urlencode($arRes['ID'])), "DEFAULT"=>true);

	if($isAdmin)
	{
		$arActions[] = array("ICON"=>"copy", "TEXT"=>GetMessage("COPY"), "ACTION"=>$lAdmin->ActionRedirect("site_edit.php?COPY_ID=".urlencode($arRes['ID'])));
		$arActions[] = array("SEPARATOR"=>true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("DELETE"), "ACTION"=>"if(confirm('".CUtil::JSEscape(GetMessage('CONFIRM_DEL'))."')) ".$lAdmin->ActionDoGroup(urlencode($arRes['ID']), "delete"));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
	"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
	"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	));

$aContext = array(
	array(
		"TEXT"	=> GetMessage("ADD_SITE"),
		"LINK"	=> "site_edit.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("ADD_SITE_TITLE"),
		"ICON"	=> "btn_new"
	),
);
$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();
?>
<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
