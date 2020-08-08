<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
CModule::IncludeModule("sale");
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

function GetRights($ID)
{
	global $USER;
	static $cache = array();
	if(!array_key_exists($ID, $cache))
	{
		if($USER->IsAdmin())
			$RIGHTS = "X";
		else
		{
			$RIGHTS = "D";
			$ar = CUserTypeEntity::GetByID($ID);
			if($ar)
			{
				$db_events = GetModuleEvents("main", "OnUserTypeRightsCheck");
				while($arEvent = $db_events->Fetch())
				{
					$res = ExecuteModuleEvent($arEvent, $ar["ENTITY_ID"]);
					if($res > $RIGHTS)
						$RIGHTS = $res;
				}
			}
		}
		$cache[$ID] = $RIGHTS;
	}
	return $cache[$ID];
}

$sTableID = "tbl_user_type_order";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find",
	"find_person_id",
	"find_id",
	"find_field_name",
	"find_user_type_id",
	"find_xml_id",
	"find_multiple",
	"find_mandatory",
	"find_show_filter",
	"find_show_in_list",
	"find_edit_in_list",
	"find_is_searchable",
);

$lAdmin->InitFilter($FilterArr);

$arFilter = array(
	"ID" => ($find!="" && $find_type == "ID"? $find:$find_id),
	"FIELD_NAME" => $find_field_name,
	"USER_TYPE_ID" => $find_user_type_id,
	"XML_ID" => $find_xml_id,
	"MULTIPLE" => $find_multiple,
	"MANDATORY" => $find_mandatory,
	"SHOW_FILTER" => $find_show_filter,
	"SHOW_IN_LIST" => $find_show_in_list,
	"EDIT_IN_LIST" => $find_edit_in_list,
	"IS_SEARCHABLE" => $find_is_searchable,
);
if(intval($find_person_id)>0)
	$arFilter["ENTITY"] = "SALE_ORDER_".$find_person_id;
else
	$arFilter["ENTITY_ID"] = "SALE_ORDER_%";

$dbRes = CSalePersonType::GetList(
	array("NAME" => "ASC"),
	array(),
	false,
	false,
	array("ID", "NAME", "LID")
);
while ($arRes = $dbRes->Fetch())
	$arPerson[$arRes["ID"]] = $arRes;


if($lAdmin->EditAction())
{
	$obUserField  = new CUserTypeEntity;
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		//Rights check
		if(GetRights($ID)<"W")
			continue;
		//Update
		$DB->StartTransaction();
		$ID = intval($ID);
		if(!$obUserField->Update($ID, $arFields))
		{
			if($e = $APPLICATION->GetException())
				$lAdmin->AddGroupError(GetMessage("USERTYPE_UPDATE_ERROR")." ".$e->GetString(), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}

if($arID = $lAdmin->GroupAction())
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CUserTypeEntity::GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}

	$obUserField  = new CUserTypeEntity;
	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
	   	$ID = intval($ID);
		//Rights check
		if(GetRights($ID)<"W")
			continue;
		//Do action
		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!$obUserField->Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(GetMessage("USERTYPE_DEL_ERROR"), $ID);
			}
			$DB->Commit();
			break;
		}
	}
}
                        
$rsData = CUserTypeEntity::GetList(array($by=>$order), $arFilter);
$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("USERTYPE_NAV")));

$lAdmin->AddHeaders(array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "PERSON",
		"content" => GetMessage("USERTYPE_PERSON"),
		"default" => true,
	),
	array(
		"id" => "ENTITY_ID",
		"content" => GetMessage("USERTYPE_ENTITY_ID"),
		"sort" => "ENTITY_ID",
		"default" => true,
	),
	array(
		"id" => "FIELD_NAME",
		"content" => GetMessage("USERTYPE_FIELD_NAME"),
		"sort" => "FIELD_NAME",
		"default" => true,
	),
	array(
		"id" => "USER_TYPE_ID",
		"content" => GetMessage("USERTYPE_USER_TYPE_ID"),
		"sort" => "USER_TYPE_ID",
		"default" => true,
	),
	array(
		"id" => "XML_ID",
		"content" => GetMessage("USERTYPE_XML_ID"),
		"sort" => "XML_ID",
		"default" => false,
	),
	array(
		"id" => "SORT",
		"content" => GetMessage("USERTYPE_SORT"),
		"sort" => "SORT",
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "MULTIPLE",
		"content" => GetMessage("USERTYPE_MULTIPLE"),
		"default" => false,
	),
	array(
		"id" => "MANDATORY",
		"content" => GetMessage("USERTYPE_MANDATORY"),
		"default" => false,
	),
	array(
		"id" => "SHOW_FILTER",
		"content" => GetMessage("USERTYPE_SHOW_FILTER"),
		"default" => false,
	),
	array(
		"id" => "SHOW_IN_LIST",
		"content" => GetMessage("USERTYPE_SHOW_IN_LIST"),
		"default" => false,
	),
	array(
		"id" => "EDIT_IN_LIST",
		"content" => GetMessage("USERTYPE_EDIT_IN_LIST"),
		"default" => false,
	),
	array(
		"id" => "IS_SEARCHABLE",
		"content" => GetMessage("USERTYPE_IS_SEARCHABLE"),
		"default" => false,
	),
));

while($arRes = $rsData->NavNext(true, "f_")):
	//Rights check
	if(GetRights($f_ID)<"W")
		continue;

	$row =& $lAdmin->AddRow($f_ID, $arRes);
	
	$arUserType = $USER_FIELD_MANAGER->GetUserType($f_USER_TYPE_ID);
	$row->AddViewField("USER_TYPE_ID", htmlspecialchars($arUserType["DESCRIPTION"]));
	$personID = intval(mb_substr($f_ENTITY_ID, (mb_strlen("SALE_ORDER_"))));
	
	$row->AddViewField("PERSON", htmlspecialchars("[".$personID."] ".$arPerson[$personID]["NAME"]));
	$row->AddInputField("SORT", array("size"=>5));
	$row->AddViewField("MULTIPLE", $f_MULTIPLE=="Y"?GetMessage("MAIN_YES"):GetMessage("MAIN_NO"));
	$row->AddCheckField("MANDATORY");
	$row->AddSelectField("SHOW_FILTER", array(
		"N"=>GetMessage("USER_TYPE_FILTER_N"),
		"I"=>GetMessage("USER_TYPE_FILTER_I"),
		"E"=>GetMessage("USER_TYPE_FILTER_E"),
		"S"=>GetMessage("USER_TYPE_FILTER_S"),
	));
	$row->AddCheckField("SHOW_IN_LIST");
	$row->AddCheckField("EDIT_IN_LIST");
	$row->AddCheckField("IS_SEARCHABLE");
	$row->AddInputField("XML_ID", array("size"=>10));

	$arActions = Array();
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>GetMessage("MAIN_EDIT"),
		"ACTION"=>$lAdmin->ActionRedirect("userfield_edit.php?ID=".$f_ID."&back_url=%2Fbitrix%2Fadmin%2Fsale_order_uf_props.php?lang=".LANG)
	);
	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_DELETE"),
		"ACTION"=>"if(confirm('".GetMessage('USERTYPE_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
	);

	$row->AddActions($arActions);
endwhile;

$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);
$lAdmin->AddGroupActionTable(Array(
	"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
));

$aContext = array(
	array(
		"TEXT"=>GetMessage("MAIN_ADD"),
		"LINK"=>"userfield_edit.php?lang=".LANG,
		"TITLE"=>GetMessage("USERTYPE_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
$lAdmin->AddAdminContextMenu($aContext);
if ($saleModulePermissions == "W")
{
	$arDDMenu = array();

	$arDDMenu[] = array(
		"TEXT" => "<b>".GetMessage("SOPAN_4NEW_PROMT")."</b>",
		"ACTION" => false
	);

	foreach($arPerson as $arRes)
	{
		$arDDMenu[] = array(
			"TEXT" => "[".$arRes["ID"]."] ".$arRes["NAME"]." (".$arRes["LID"].")",
			"ACTION" => "window.location = 'userfield_edit.php?lang=ru&ENTITY_ID=SALE_ORDER_".$arRes["ID"]."&lang=".LANG."&back_url=%2Fbitrix%2Fadmin%2Fsale_order_uf_props.php?lang=".LANG."';"
		);
		
	}

	$aContext = array(
		array(
			"TEXT" => GetMessage("SOPAN_ADD_NEW"),
			"ICON" => "btn_new",
			"TITLE" => GetMessage("SOPAN_ADD_NEW_ALT"),
			"MENU" => $arDDMenu
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("USERTYPE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("USER_TYPE_ID"),
		GetMessage("USERTYPE_PERSON"),
		GetMessage("USERTYPE_FIELD_NAME"),
		GetMessage("USERTYPE_USER_TYPE_ID"),
		GetMessage("USERTYPE_XML_ID"),
		GetMessage("USERTYPE_MULTIPLE"),
		GetMessage("USERTYPE_MANDATORY"),
		GetMessage("USERTYPE_SHOW_FILTER"),
		GetMessage("USERTYPE_SHOW_IN_LIST"),
		GetMessage("USERTYPE_EDIT_IN_LIST"),
		GetMessage("USERTYPE_IS_SEARCHABLE"),
	)
);
$arrYN = array(
	"reference" => array(GetMessage("MAIN_YES"), GetMessage("MAIN_NO")),
	"reference_id" => array("Y", "N")
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_PERSON")?>:</td>
	<td>
		<?
		$arr = array("reference"=>array(), "reference_id"=>array());
		foreach($arPerson as $arRes)
		{
			$arr["reference"][] = "[".$arRes["ID"]."] ".$arRes["NAME"];
			$arr["reference_id"][] = $arRes["ID"];
		}
		echo SelectBoxFromArray("find_person_id", $arr, $find_person_id, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_FIELD_NAME").":"?></td>
	<td><input type="text" name="find_field_name" size="47" value="<?echo htmlspecialchars($find_field_name)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_USER_TYPE_ID")?>:</td>
	<td>
		<?
		$arUserTypes = $USER_FIELD_MANAGER->GetUserType();
		$arr = array("reference"=>array(), "reference_id"=>array());
		foreach($arUserTypes as $arUserType)
		{
			$arr["reference"][] = $arUserType["DESCRIPTION"];
			$arr["reference_id"][] = $arUserType["USER_TYPE_ID"];
		}
		echo SelectBoxFromArray("find_user_type_id", $arr, $find_user_type_id, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_XML_ID").":"?></td>
	<td><input type="text" name="find_xml_id" size="47" value="<?echo htmlspecialchars($find_xml_id)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_MULTIPLE")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_multiple", $arrYN, $find_multiple, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_MANDATORY")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_mandatory", $arrYN, $find_mandatory, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_SHOW_FILTER")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_show_filter", $arrYN, $find_show_filter, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_SHOW_IN_LIST")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_show_in_list", $arrYN, $find_show_in_list, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_EDIT_IN_LIST")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_edit_in_list", $arrYN, $find_edit_in_list, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_IS_SEARCHABLE")?>:</td>
	<td>
		<?=SelectBoxFromArray("find_is_searchable", $arrYN, $find_is_searchable, GetMessage("MAIN_ALL"), "");?>
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>

<?$lAdmin->DisplayList();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>