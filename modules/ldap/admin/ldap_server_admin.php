<?
/*
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2005 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/ldap/prolog.php");

$MOD_RIGHT = $APPLICATION->GetGroupRight("ldap");
if($MOD_RIGHT<"R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ldap/include.php");

$err_mess = "File: ".__FILE__."<br>Line: ";
$APPLICATION->SetTitle(GetMessage("LDAP_ADMIN_TITLE"));



$sTableID = "t_ldap_server_admin";
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc"); // sorting initialisation
$lAdmin = new CAdminList($sTableID, $oSort);// list initialisation

//names of all inputs of a filter
$arFilterFields = Array(
	"find_name",
	"find_id",
	"find_active",
);

$lAdmin->InitFilter($arFilterFields); // filter initialisation

$filter = new CAdminFilter(
	$sTableID."_filter_id",
	array(
		"ID",
		GetMessage("LDAP_ADMIN_F_ACT"),
	)
);

$arFilter = Array(
	"?ID"=>$find_id,
	"?NAME"=>$find_name,
	"ACTIVE"=>$find_active
	);


if ($MOD_RIGHT=="W" && $lAdmin->EditAction()) // if save is done from list
{
	foreach($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		$ob = new CLdapServer;
		if(!$ob->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
			{
				$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.". ".$e->GetString(), $ID);
				$DB->Rollback();
			}
		}
		$DB->Commit();
	}
}



// process both group actions and for a single item
if($MOD_RIGHT=="W" && $arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CLdapServer::GetList(Array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);

		switch($_REQUEST['action'])
		{
			case "delete":
				if(!CLdapServer::Delete($ID))
					$lAdmin->AddGroupError(GetMessage("LDAP_ADMIN_DEL_ERR"), $ID);

			break;

			case "activate":
			case "deactivate":
				$ld = new CLdapServer;
				$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
				if (!$ld->Update($ID, $arFields))
					if($e = $APPLICATION->GetException())
						$lAdmin->AddUpdateError(GetMessage("SAVE_ERROR").$ID.". ".$e->GetString(), $ID);
			break;
		}
	}
}




// initialise list - query data
$rsData = CLdapServer::GetList(Array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// set up navigation string
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("LDAP_ADMIN_NAVSTRING")));

$arHeaders = Array();
$arHeaders[] = Array("id"=>"ID", "content"=>"ID", "default"=>true, "sort" => "id");

$arHeaders[] = Array("id"=>"TIMESTAMP_X", "content"=>GetMessage("LDAP_ADMIN_TSTAMP"), "default"=>true, "sort" => "timestamp_x");
$arHeaders[] = Array("id"=>"NAME", "content"=>GetMessage("LDAP_ADMIN_NAME"), "default"=>true, "sort" => "name");
$arHeaders[] = Array("id"=>"ACTIVE", "content"=>GetMessage("LDAP_ADMIN_ACT"), "default"=>true, "sort" => "active");
$arHeaders[] = Array("id"=>"CONVERT_UTF8", "content"=>"UTF-8", "default"=>true, "sort" => "utf8");
$arHeaders[] = Array("id"=>"CODE", "content"=>GetMessage("LDAP_ADMIN_CODE"), "default"=>true, "sort" => "code");
$arHeaders[] = Array("id"=>"SERVER", "content"=>GetMessage("LDAP_ADMIN_SERV"), "default"=>true, "sort" => "server");
$arHeaders[] = Array("id"=>"SYNC", "content"=>GetMessage("LDAP_ADMIN_SYNC"), "sort" => "sync");
$arHeaders[] = Array("id"=>"SYNC_PERIOD", "content"=>GetMessage("LDAP_ADMIN_SYNC_PERIOD"), "sort" => "sync_period");
$arHeaders[] = Array("id"=>"SYNC_LAST", "content"=>GetMessage("LDAP_ADMIN_SYNC_LAST"), "sort" => "sync_last");

$lAdmin->AddHeaders($arHeaders);

// output of list
while($arRes = $rsData->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("NAME",Array("size"=>"35"));
	$row->AddCheckField("CONVERT_UTF8");
	$row->AddCheckField("SYNC");
	$row->AddInputField("SYNC_PERIOD", Array("size"=>"10"));

	$arActions = Array();

	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT" => "Y",
		"TEXT"=>GetMessage("LDAP_ADMIN_CHANGE"),
		"TITLE"=>GetMessage("LDAP_ADMIN_CHANGE"),
		"ACTION"=>$lAdmin->ActionRedirect("ldap_server_edit.php?ID=".$f_ID."&lang=".LANG)
	);

	if ($MOD_RIGHT=="W")
	{
		$arActions[] = array("SEPARATOR" => true);

		$arActions[] = array(
			"ICON" => "delete",
			"TEXT"	=> GetMessage("LDAP_ADMIN_DEL_LINK"),
			"ACTION"=>"if(confirm('".GetMessage('LDAP_ADMIN_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"),
		);
	}

	$row->AddActions($arActions);
}

// list footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);

if ($MOD_RIGHT=="W")
{
	// show group actions form (buttons in footer)
	$lAdmin->AddGroupActionTable(Array(
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		)
	);
}

$aContext = array(
	array(
		"ICON"=> "btn_new",
		"TEXT"=> GetMessage("MAIN_ADD"),
		"LINK"=>"ldap_server_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("MAIN_ADD")
	),
);


$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

?>

<form name="form1" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?$filter->Begin();?>

<tr>
	<td nowrap><?echo GetMessage("LDAP_ADMIN_F_NAME")?>:</td>
	<td nowrap><input type="text" name="find_name" value="<?echo htmlspecialcharsbx($find_name)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>

<tr>
	<td nowrap>ID:</td>
	<td nowrap><input type="text" name="find_id" value="<?echo htmlspecialcharsbx($find_id)?>" size="47"><?=ShowFilterLogicHelp()?></td>
</tr>
<tr>
	<td nowrap><?echo GetMessage("LDAP_ADMIN_F_ACT")?>:</td>
	<td nowrap><?
		$arr = array("reference"=>array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")), "reference_id"=>array("Y","N"));
		echo SelectBoxFromArray("find_active", $arr, htmlspecialcharsbx($find_active), GetMessage("LDAP_ADMIN_F_ACT_ANY"));
		?></td>
</tr>


<?$filter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));$filter->End();?>
</form>


<?$lAdmin->DisplayList();?>


<?require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");?>
