<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

ClearVars("l_");

$inputTypes = Bitrix\Sale\Internals\Input\Manager::getTypes();

$sTableID = "tbl_sale_order_props";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_person_type_id",
	"filter_type",
	"filter_user",
	"filter_group",
	"filter_code",
	"filter_active",
	"filter_util",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (IntVal($filter_person_type_id)>0) $arFilter["PERSON_TYPE_ID"] = IntVal($filter_person_type_id);
if (strlen($filter_type)>0) $arFilter["TYPE"] = Trim($filter_type);
if (strlen($filter_user)>0) $arFilter["USER_PROPS"] = Trim($filter_user);
if (IntVal($filter_group)>0) $arFilter["PROPS_GROUP_ID"] = IntVal($filter_group);
if (strlen($filter_code)>0) $arFilter["CODE"] = Trim($filter_code);
if (strlen($filter_active)>0) $arFilter["ACTIVE"] = Trim($filter_active);
if (strlen($filter_util)>0) $arFilter["UTIL"] = Trim($filter_util);

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		if (!CSaleOrderProps::Update($ID, $arFields))
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
		$dbResultList = CSaleOrderProps::GetList(
				array($by => $order),
				$arFilter,
				false,
				false,
				array("ID")
			);
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

				if (CSaleOrderProps::Delete($ID))
				{
					if (\Bitrix\Main\Loader::includeModule('crm'))
					{
						$property = \Bitrix\Crm\Order\Matcher\Internals\OrderPropsMatchTable::getByPropertyId($ID);

						if (!empty($property))
						{
							\Bitrix\Crm\Order\Matcher\Internals\OrderPropsMatchTable::delete($property['ID']);
						}
					}
				}
				else
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SOPAN_ERROR_DELETE"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = \Bitrix\Sale\Property::getList([
	'filter' => $arFilter,
	'order' => [$by => $order]
]);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_PRLIST")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"PERSON_TYPE_ID","content"=>GetMessage("SALE_PERSON_TYPE"), "sort"=>"PERSON_TYPE_ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage('SALE_FIELD_NAME'),	"sort"=>"NAME", "default"=>true),
	array("id"=>"CODE", "content"=>GetMessage('SALE_FIELD_CODE'),	"sort"=>"CODE", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SALE_FIELD_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('SALE_FIELD_SORT'),	"sort"=>"SORT", "default"=>true),
	array("id"=>"TYPE", "content"=>GetMessage("SALE_FIELD_TYPE"),  "sort"=>"TYPE", "default"=>true),
	array("id"=>"REQUIRED", "content"=>GetMessage("SALE_REQUIED"),  "sort"=>"REQUIRED", "default"=>true),
	array("id"=>"MULTIPLE", "content"=>GetMessage("SALE_MULTIPLE"),  "sort"=>"MULTIPLE", "default"=>true),
	array("id"=>"PROPS_GROUP_ID", "content"=>GetMessage("SALE_GROUP"),  "sort"=>"PROPS_GROUP_ID", "default"=>true),
	array("id"=>"USER_PROPS", "content"=>GetMessage("SALE_USER"),  "sort"=>"USER_PROPS", "default"=>true),
	array("id"=>"UTIL", "content"=>GetMessage("SALE_FIELD_UTIL"),  "sort"=>"UTIL", "default"=>true),
));

$arPersonTypeList = array();
$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
while ($arPersonType = $dbPersonType->Fetch())
{
	$arPersonTypeList[$arPersonType["ID"]] = Array("ID" => $arPersonType["ID"], "NAME" => htmlspecialcharsEx($arPersonType["NAME"]), "LID" => implode(", ", $arPersonType["LIDS"]));
}

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
while ($arOrderProp = $dbResultList->NavNext(true, "f_"))
{
	$editUrl = "sale_order_props_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_");
	$row =& $lAdmin->AddRow($f_ID, $arOrderProp, $editUrl, GetMessage("SALE_EDIT_DESCR"));
	$row->AddField("ID", "<b><a href='".$editUrl."' title='".GetMessage("SALE_EDIT_DESCR")."'>".$f_ID."</a>");

	$fieldValue = "";
	if (in_array("PERSON_TYPE_ID", $arVisibleColumns))
	{
		$fieldValue  = "[".$arPersonTypeList[$f_PERSON_TYPE_ID]["ID"]."] ";
		$fieldValue .= $arPersonTypeList[$f_PERSON_TYPE_ID]["NAME"]." ";
		$fieldValue .= "(".htmlspecialcharsEx($arPersonTypeList[$f_PERSON_TYPE_ID]["LID"]).")";
	}
	$row->AddField("PERSON_TYPE_ID", $fieldValue);

	$row->AddInputField("NAME");
	$row->AddInputField("SORT");
	$row->AddInputField("CODE");
	$row->AddField('TYPE', "[$f_TYPE] ".$inputTypes[$f_TYPE]['NAME']);
	$row->AddCheckField("ACTIVE");
	$row->AddCheckField("REQUIRED");
	$row->AddCheckField("MULTIPLE");
	$row->AddCheckField("UTIL");
	$row->AddCheckField("USER_PROPS");

	$fieldValue = "";
	if (in_array("PROPS_GROUP_ID", $arVisibleColumns))
	{
		$arPropsGroup = CSaleOrderPropsGroup::GetByID($f_PROPS_GROUP_ID);
		$fieldValue = htmlspecialcharsEx($arPropsGroup["NAME"]);
	}
	$row->AddField("PROPS_GROUP_ID", $fieldValue);


	$arActions = Array();

	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SALE_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect($editUrl), "DEFAULT"=>true);

	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SALE_DELETE_DESCR"), "ACTION"=>"if(confirm('".GetMessage('SALE_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
	$arDDMenu = array();

	$arDDMenu[] = array(
		"TEXT" => GetMessage("SOPAN_4NEW_PROMT"),
		"ACTION" => false
	);

	foreach($arPersonTypeList as $arRes)
	{
		$arDDMenu[] = array(
			"TEXT" => "[".$arRes["ID"]."] ".$arRes["NAME"]." (".$arRes["LID"].")",
			"ACTION" => "window.location = 'sale_order_props_edit.php?lang=".LANG."&PERSON_TYPE_ID=".$arRes["ID"].GetFilterParams("filter_")."';"
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


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SALE_SECTION_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SALE_F_TYPE"),
		GetMessage("SALE_F_USER"),
		GetMessage("SALE_F_GROUP"),
		GetMessage("SALE_F_CODE"),
		GetMessage("SALE_FIELD_ACTIVE"),
		GetMessage("SALE_FIELD_UTIL"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SALE_F_PERS_TYPE");?>:</td>
		<td>
			<select name="filter_person_type_id">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				foreach($arPersonTypeList as $val)
				{
					?><option value="<?echo $val["ID"]?>"<?if (IntVal($filter_person_type_id)==IntVal($val["ID"])) echo " selected"?>>[<?echo $val["ID"] ?>] <?echo $val["NAME"]?> (<?echo htmlspecialcharsEx($val["LID"]) ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_TYPE")?>:</td>
		<td>
			<select name="filter_type">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				foreach ($inputTypes as $name => $type):
					?><option value="<?=$name?>"<?= $filter_type == $name ? ' selected' : ''?>>[<?=$name?>] <?=$type['NAME']?></option><?
				endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_USER")?>:</td>
		<td>
			<select name="filter_user">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<option value="Y"<?if ($filter_user=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_user=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_GROUP");?>:</td>
		<td>
			<select name="filter_group">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<?
				$l = CSaleOrderPropsGroup::GetList(Array("PERSON_TYPE_ID" => "ASC","SORT" => "ASC", "NAME" => "ASC"));
				while ($arL = $l->Fetch()):
					?><option value="<?echo $arL["ID"]?>"<?if (IntVal($filter_group)==IntVal($arL["ID"])) echo " selected"?>>[<?echo $arL["ID"] ?>] <?echo htmlspecialcharsbx($arL["NAME"])?> <?if (!empty($arPersonTypeList[$arL["PERSON_TYPE_ID"]])) echo "(".$arPersonTypeList[$arL["PERSON_TYPE_ID"]]["NAME"]." (".htmlspecialcharsEx($arPersonTypeList[$arL["PERSON_TYPE_ID"]]["LID"]).")".")";?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("SALE_F_CODE")?>:</td>
		<td>
			<input type="text" name="filter_code" value="<?=htmlspecialcharsbx($filter_code)?>">
		</td>
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
	<tr>
		<td><?echo GetMessage("SALE_FIELD_UTIL")?>:</td>
		<td>
			<select name="filter_util">
				<option value="">(<?echo GetMessage("SALE_ALL")?>)</option>
				<option value="Y"<?if ($filter_util=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
				<option value="N"<?if ($filter_util=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
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