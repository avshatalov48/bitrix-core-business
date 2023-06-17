<?
require_once(__DIR__."/../include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/prolog.php");
define("HELP_FILE", "settings/wizard_list.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/wizard.php");

if(!$USER->CanDoOperation('edit_php') && !$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_php');

IncludeModuleLangFile(__FILE__);

$sTableID = "package_list";
$oSort = new CAdminSorting($sTableID, "sort", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

if(($arID = $lAdmin->GroupAction()) && $isAdmin)
{
	if (isset($_REQUEST['action_target']) && $_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$rsData = CWizardUtil::GetWizardList(false, true);
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
			if(!CWizardUtil::DeleteWizard($ID))
				$lAdmin->AddGroupError(GetMessage("MAIN_WIZARD_DELETE_ERROR"), $ID);
			break;
		case "export":
			?>
			<script type="text/javascript">
				exportWizard('<?=CUtil::JSEscape($ID)?>');
			</script>
			<?
			break;
		/*case "copy":
			CWizardUtil::CopyWizard($ID, $ID."_copy");
			break;*/
		}
	}
}

$rsData = new CDBResult;
$rsData->InitFromArray(CWizardUtil::GetWizardList(false, true));
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("PAGES"), false));

$lAdmin->AddHeaders(
	Array(
		Array("id"=>"ID", "content"=>"ID", "default"=>true),
		Array("id"=>"NAME", "content"=> GetMessage("MAIN_WIZARD_ADMIN_NAME"), "default"=>true),
		Array("id"=>"DESCRIPTION", "content"=> GetMessage("MAIN_WIZARD_ADMIN_DESC"), "default"=>true),
		Array("id"=>"VERSION", "content"=> GetMessage("MAIN_WIZARD_ADMIN_VERSION"), "default"=>true),
	)
);

while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$idTmp = $f_ID;
	$arID = explode(":", $f_ID);
	if (count($arID) > 2)
		$idTmp = $arID[1].":".$arID[2];

	$row->AddField("ID", $idTmp);

	$arActions = Array();
	if ($isAdmin)
	{
		$startType = (array_key_exists("START_TYPE",$arRes) ? $arRes["START_TYPE"] : "POPUP");
		$startType = mb_strtoupper($startType);

		if ($startType == "POPUP")
			$arActions[] = array("DEFAULT" => "Y", "ICON"=>"install", "TEXT" => GetMessage("MAIN_WIZARD_ADMIN_INSTALL"), "ACTION"=>"WizardWindow.Open('".$f_ID."','".bitrix_sessid()."')");
		else if ($startType == "WINDOW")
			$arActions[] = Array(
				"DEFAULT" => "Y", 
				"ICON"=>"install", 
				"TEXT" => GetMessage("MAIN_WIZARD_ADMIN_INSTALL"), 
				"ACTION"=>"window.open('wizard_install.php?lang=".LANGUAGE_ID."&wizardName=".$f_ID."&".bitrix_sessid_get()."');"
			);
	}

	if (count($arID) <= 2)
		$arActions[] = array("ICON"=>"export", "TEXT"=>GetMessage("MAIN_WIZARD_ADMIN_DOWNLOAD"), "ACTION"=>"exportWizard('".$f_ID."')");

	if ($isAdmin && (count($arID) <= 2))
	{
		$arActions[] = Array("SEPARATOR"=>true);
		$arActions[] = Array(
			"ICON"=>"delete", 
			"TEXT"=>GetMessage("MAIN_ADMIN_MENU_DELETE"), 
			"ACTION"=>"if(confirm('".GetMessage('MAIN_ADMIN_MENU_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	}

	$row->AddActions($arActions);
}

/*
$groupAction = Array(
	"copy" => GetMessage("MAIN_ADMIN_MENU_COPY"),
	"delete" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
);
$lAdmin->AddGroupActionTable($groupAction);
*/

$arContext = array(
	array(
		"TEXT"	=> GetMessage("MAIN_WIZARD_ADMIN_LOAD"),
		"LINK"	=> "wizard_load.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_WIZARD_ADMIN_LOAD_TITLE"),
		"ICON"	=> "btn_new"
	),
);
$lAdmin->AddAdminContextMenu($arContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("MAIN_WIZARD_ADMIN_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");?>

<script type="text/javascript">
<!--
function exportWizard(val)
{
	window.open("wizard_export.php?ID="+val+"&<?=bitrix_sessid_get()?>");
}
//-->
</script>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>