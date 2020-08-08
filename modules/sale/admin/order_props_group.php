<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Loader::includeModule('sale');

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$sTableID = "tbl_sale_order_props_group";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_person_type_id"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (intval($filter_person_type_id)>0)
	$arFilter["PERSON_TYPE_ID"] = $filter_person_type_id;
else
	Unset($arFilter["PERSON_TYPE_ID"]);

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		unset($arFields["PERSON_TYPE_ID"]);

		if (!CSaleOrderPropsGroup::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("ERROR_UPDATE_REC")." (".$CR_ID.", ".$arFields["PERSON_TYPE_ID"].", ".$arFields["NAME"].", ".$arFields["SORT"].")", $ID);

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
		$dbResultList = CSaleOrderPropsGroup::GetList(
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
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				@set_time_limit(0);

				$DB->StartTransaction();

				if (!CSaleOrderPropsGroup::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SOPGAN_DELETE_ERROR"), $ID);
				}

				$DB->Commit();

				break;
		}
	}
}

$arFilter['=PERSON_TYPE.ENTITY_REGISTRY_TYPE'] = 'ORDER';

$dbRes = \Bitrix\Sale\Internals\OrderPropsGroupTable::getList([
	'filter' => $arFilter,
	'order' => array($by => $order),
	'runtime' => [
		new \Bitrix\Main\Entity\ReferenceField(
			'PERSON_TYPE',
			'Bitrix\Sale\Internals\PersonType',
			array('=this.PERSON_TYPE_ID' => 'ref.ID')
		),
	]
]);

$dbResultList = new CAdminResult($dbRes, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("PERS_TYPE_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>GetMessage("PERS_TYPE_ID"), "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("PERS_TYPE_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"PERSON_TYPE_ID", "content"=>GetMessage('PERS_TYPE_TYPE'), "sort"=>"PERSON_TYPE_ID", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("PERS_TYPE_SORT"), "sort"=>"SORT", "default"=>true),
	array("id"=>"PROPS", "content"=>GetMessage("SOPGAN_PROPS"), "sort"=>"", "default"=>true),
));

$arPersonTypeList = array();
$dbPersonType = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array());
while ($arPersonType = $dbPersonType->Fetch())
{
	$arPersonTypeList[$arPersonType["ID"]] = Array("ID" => $arPersonType["ID"], "NAME" => htmlspecialcharsEx($arPersonType["NAME"]), "LID" => implode(", ", $arPersonType["LIDS"]));
}

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arPropsGroup = $dbResultList->NavNext(true, "f_"))
{
	$editUrl = "sale_order_props_group_edit.php?ID=".$f_ID."&lang=".LANG.GetFilterParams("filter_");
	$row =& $lAdmin->AddRow($f_ID, $arPropsGroup, $editUrl, GetMessage("SOPGAN_EDIT_PROMT"));

	$row->AddField("ID", "<b><a href='".$editUrl."' title='".GetMessage("SOPGAN_EDIT_PROMT")."'>".$f_ID."</a>");

	$row->AddInputField("NAME", array("size" => "30"));

	$fieldValue = "";
	if (in_array("PERSON_TYPE_ID", $arVisibleColumns))
	{
		$arPersType = $arPersonTypeList[$f_PERSON_TYPE_ID];
		$fieldValue = "[".$arPersType["ID"]."] ".$arPersType["NAME"]." (".htmlspecialcharsEx($arPersType["LID"]).")";
	}
	$row->AddField("PERSON_TYPE_ID", $fieldValue);

	$row->AddInputField("SORT");

	$fieldValue = "";
	if (in_array("PROPS", $arVisibleColumns))
	{
		$numProps = CSaleOrderProps::GetList(
			array(),
			array("PROPS_GROUP_ID" => $f_ID),
			array()
		);
		$numProps = intval($numProps);

		if ($numProps > 0)
			$fieldValue = "<a href=\"sale_order_props.php?lang=".LANG."&set_filter=Y&filter_group=".$f_ID."\">".$numProps."</a>";
		else
			$fieldValue = "0";
	}
	$row->AddField("PROPS", $fieldValue);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SOPGAN_EDIT_PROMT"), "ACTION"=>$lAdmin->ActionRedirect($editUrl), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SOPGAN_DELETE_PROMT"), "ACTION"=>"if(confirm('".GetMessage('SOPGAN_DELETE_PROMT_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SOPGAN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "sale_order_props_group_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SOPGAN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("PROPS_TYPE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array()
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("PT_FILTER_NAME")?>:</td>
		<td>
			<?echo CSalePersonType::SelectBox("filter_person_type_id", $filter_person_type_id, "(".GetMessage("SALE_ALL").")", True, "", "")?>
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
