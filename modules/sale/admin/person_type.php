<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");


$sTableID = "tbl_sale_person_type";


$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);


$arFilterFields = array(
	"filter_lang",
	"filter_active",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if(!is_array($filter_lang) && strlen($filter_lang) > 0)
	$filter_lang = Array($filter_lang);

if (!empty($filter_lang) && !in_array("NOT_REF", $filter_lang))
	$arFilter["LID"] = $filter_lang;
else
	Unset($arFilter["LID"]);
if (strlen($filter_active)>0) $arFilter["ACTIVE"] = Trim($filter_active);



if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CSalePersonType::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SPTAN_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}

		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSalePersonType::GetList($by, $order, $arFilter);
		while ($arResult = $dbResultList->Fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSalePersonType::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SPTAN_ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSalePersonType::GetList($by, $order, $arFilter);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();


$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("PERS_TYPE_NAV")));


$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("PERS_TYPE_ID"), "sort"=>"id", "default"=>true),
	array("id"=>"NAME","content"=>GetMessage("PERS_TYPE_NAME"), "sort"=>"name", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("PERS_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"LID", "content"=>GetMessage('PERS_TYPE_LID'), "sort"=>false, "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("PERS_TYPE_SORT"), "sort"=>"sort", "default"=>true),
	array("id"=>"PROPS", "content"=>GetMessage("PERS_PROPS"), "sort"=>"", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arLangs = array();
$dbLangsList = CLang::GetList(($b = "sort"), ($o = "asc"));
while ($arLang = $dbLangsList->Fetch())
	$arLangs[$arLang["LID"]] = "[".htmlspecialcharsbx($arLang["LID"])."]&nbsp;".htmlspecialcharsbx($arLang["NAME"]);

while ($arPersonType = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arPersonType, "sale_person_type_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_"), GetMessage("SPTAN_UPDATE_ALT"));

	$row->AddField("ID","<a href=\""."sale_person_type_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_")."\">".$f_ID."</a>");
	$row->AddInputField("NAME", array("size" => "30"));
	$LIDS = "";
	foreach($f_LIDS as $v)
		$LIDS .= $arLangs[$v]."<br />";
	$row->AddField("LID", $LIDS);
	$row->AddInputField("SORT", array("size" => "3"));
	$row->AddCheckField("ACTIVE");
	
	$fieldValue = "";
	if (in_array("PROPS", $arVisibleColumns))
	{
		$numProps = CSaleOrderProps::GetList(
			array(),
			array("PERSON_TYPE_ID" => $f_ID),
			array()
		);
		$numProps = IntVal($numProps);

		if ($numProps > 0)
			$fieldValue = "<a href=\"sale_order_props.php?lang=".LANG."&set_filter=Y&filter_person_type_id=".$f_ID."\" title=\"".GetMessage("SPTAN_VIEW_PROPS")."\">".$numProps."</a>";
		else
			$fieldValue = "0";
	}
	$row->AddField("PROPS", $fieldValue);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SPTAN_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_person_type_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SPTAN_DELETE_ALT1"), "ACTION"=>"if(confirm('".GetMessage('PERS_TYPE_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
	}

	$row->AddActions($arActions);
}


$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);


$lAdmin->AddGroupActionTable(
	array(
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE")
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SPTAN_ADD_NEW"),
			"LINK" => "sale_person_type_edit.php?lang=".LANG.GetFilterParams("filter_", false),
			"TITLE" => GetMessage("SPTAN_ADD_NEW_ALT"),
			"ICON" => "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}


$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("PERSON_TYPE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(GetMessage("SALE_FIELD_ACTIVE"),)
);

$oFilter->Begin();
?>
	<tr>
		<td valign="top"><?echo GetMessage("LANG_FILTER_NAME")?>:</td>
		<td style="padding-left:10px;"><?echo CSite::SelectBoxMulti("filter_lang", $filter_lang, GetMessage("SPT_ALL")) ?>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_FIELD_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>

<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>

<?

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
