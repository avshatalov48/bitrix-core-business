<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "settings/userfield_admin.php");

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);

$back_url = $_REQUEST["back_url"];
if(mb_substr($back_url, 0, 1) <> '/')
	$back_url = '';

$sTableID = "tbl_user_type";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);

$FilterArr = Array(
	"find","find_type",
	"find_entity_id",
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
	"ENTITY_ID" => ($find!="" && $find_type == "ENTITY_ID"? $find:$find_entity_id),
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

if($lAdmin->EditAction())
{
	$obUserField = new CUserTypeEntity;
	foreach($FIELDS as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		//Rights check
		if($USER_FIELD_MANAGER->GetRights(false, $ID) < "W")
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

	$obUserField = new CUserTypeEntity;
	foreach($arID as $ID)
	{
		if($ID == '')
			continue;
		$ID = intval($ID);
		//Rights check
		if($USER_FIELD_MANAGER->GetRights(false, $ID) < "W")
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
	if($USER_FIELD_MANAGER->GetRights(false, $f_ID) < "W")
		continue;

	$row =& $lAdmin->AddRow($f_ID, $arRes);

	$arUserType = $USER_FIELD_MANAGER->GetUserType($f_USER_TYPE_ID);
	$row->AddViewField("USER_TYPE_ID", htmlspecialcharsbx($arUserType["DESCRIPTION"]));
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
		"ACTION"=>$lAdmin->ActionRedirect("userfield_edit.php?ID=".$f_ID)
	);
	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT"=>GetMessage("MAIN_DELETE"),
		"ACTION"=>"if(confirm('".GetMessageJS('USERTYPE_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", 'back_url='.urlencode($back_url).'&list_url='.urlencode($list_url))
	);

	$row->AddActions($arActions);
endwhile;

$lAdmin->AddGroupActionTable(Array(
	"delete"=>true,
));

$aContext = array();

// backurl button
if ($back_url <> '')
{
	//$aContext[] = array("SEPARATOR" => true);
	$aContext[] = array(
		"TEXT"=>GetMessage('USERTYPE_BACK_URL_BUTTON'),
		"LINK"=>$back_url,
		"TITLE"=>GetMessage('USERTYPE_BACK_URL_BUTTON'),
		"ICON"=>"btn_list"
	);
}

// add button
$add_url =  "userfield_edit.php?lang=".LANG;

if ($find_type === 'ENTITY_ID' && !empty($find))
{
	$add_url .= '&ENTITY_ID='.urlencode($find);

	if ($back_url <> '')
	{
		$add_url .= '&back_url='.urlencode($APPLICATION->GetCurPageParam()).'&list_url='.urlencode($APPLICATION->GetCurPageParam());
	}
}

$aContext[] = array(
	"TEXT"=>GetMessage("MAIN_ADD"),
	"LINK"=>$add_url,
	"TITLE"=>GetMessage("USERTYPE_ADD_TITLE"),
	"ICON"=>"btn_new"
);

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("USERTYPE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		GetMessage("USERTYPE_ENTITY_ID"),
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
	<td><b><?=GetMessage("USERTYPE_F_FIND")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialcharsbx($find)?>">
		<?
		$arr = array(
			"reference" => array(
				"ID",
				GetMessage("USERTYPE_ENTITY_ID"),
			),
			"reference_id" => array(
				"ID",
				"ENTITY_ID",
			)
		);
		echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
		?>
	</td>
</tr>
<tr>
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialcharsbx($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_ENTITY_ID").":"?></td>
	<td><input type="text" name="find_entity_id" size="47" value="<?echo htmlspecialcharsbx($find_entity_id)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_FIELD_NAME").":"?></td>
	<td><input type="text" name="find_field_name" size="47" value="<?echo htmlspecialcharsbx($find_field_name)?>"></td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_USER_TYPE_ID")?>:</td>
	<td>
		<?
		$typeList = array();
		foreach($USER_FIELD_MANAGER->GetUserType() as $arUserType)
		{
			$typeList[$arUserType["USER_TYPE_ID"]] = $arUserType["DESCRIPTION"];
		}
		\Bitrix\Main\Type\Collection::sortByColumn(
			$typeList,
			array('DESCRIPTION' => SORT_ASC),
			'',
			null,
			true
		);
		$arr = array(
			"reference" => array_values($typeList),
			"reference_id" =>array_keys($typeList)
		);
		echo SelectBoxFromArray("find_user_type_id", $arr, $find_user_type_id, GetMessage("MAIN_ALL"), "");
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("USERTYPE_XML_ID").":"?></td>
	<td><input type="text" name="find_xml_id" size="47" value="<?echo htmlspecialcharsbx($find_xml_id)?>"></td>
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
		<?
		$arr = array(
			"reference" => array(
				GetMessage("USER_TYPE_FILTER_N"),
				GetMessage("USER_TYPE_FILTER_I"),
				GetMessage("USER_TYPE_FILTER_E"),
				GetMessage("USER_TYPE_FILTER_S"),
			),
			"reference_id" => array(
				"N",
				"I",
				"E",
				"S",
			),
		);
		echo SelectBoxFromArray("find_show_filter", $arr, $find_show_filter, GetMessage("MAIN_ALL"), "");
		?>
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