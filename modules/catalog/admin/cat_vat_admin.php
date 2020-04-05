<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_vat')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
CModule::IncludeModule("catalog");
$bReadOnly = !$USER->CanDoOperation('catalog_vat');

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_catalog_vat";

$oSort = new CAdminSorting($sTableID, "C_SORT", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter",
	"filter_id",
	"filter_active",
	"filter_name",
	"filter_rate",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (strlen($filter_id) > 0) $arFilter["ID"] = $filter_id;
if (strlen($filter_active) > 0) $arFilter["ACTIVE"] = $filter_active;
if (strlen($filter_name) > 0) $arFilter["%NAME"] = $filter_name;
if (strlen($filter_rate) > 0) $arFilter["RATE"] = $filter_rate;

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)$ID;

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CCatalogVat::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_VAT")), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && !$bReadOnly)
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = array();
		$dbResultList = CCatalogVat::GetListEx(
			array($by => $order),
			$arFilter,
			false,
			false,
			array('ID')
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
				$DB->StartTransaction();
				if (!CCatalogVat::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_DELETE_VAT")), $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
			case "activate":
			case "deactivate":
				$arFields = array(
					"ACTIVE" => (($_REQUEST['action'] == "activate") ? "Y" : "N")
				);
				if (!CCatalogVat::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("ERROR_UPDATE_VAT")), $ID);
				}
				break;
		}
	}
}

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"C_SORT", "content"=>GetMessage("CVAT_SORT"), "sort"=>"C_SORT", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("CVAT_ACTIVE"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("CVAT_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"RATE", "content"=>GetMessage("CVAT_RATE"), "sort"=>"RATE", "default"=>true),
));

$arSelectFields = $lAdmin->GetVisibleHeaderColumns();
if (!in_array('ID', $arSelectFields))
	$arSelectFields[] = 'ID';

$arSelectFields = array_values($arSelectFields);

$arNavParams = (isset($_REQUEST["mode"]) && 'excel' == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);

$dbResultList = CCatalogVat::GetListEx(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams,
	$arSelectFields
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("CVAT_NAV")));

while ($arVAT = $dbResultList->Fetch())
{
	$arVAT['ID'] = (int)$arVAT['ID'];
	$row =& $lAdmin->AddRow($arVAT['ID'], $arVAT);

	$row->AddField("ID", $arVAT['ID']);

	if ($bReadOnly)
	{
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("NAME", false);
		$row->AddViewField("C_SORT", false);
	}
	else
	{
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("NAME", array("size" => 30));
		$row->AddInputField("C_SORT", array("size" => 5));
		$row->AddInputField("RATE", array("size" => 5));
	}

	$row->AddViewField("RATE", doubleval($arVAT['RATE'])." %");

	$arActions = array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("CVAT_EDIT_ALT"),
		"ACTION" => $lAdmin->ActionRedirect("/bitrix/admin/cat_vat_edit.php?ID=".$arVAT['ID']."&lang=".LANGUAGE_ID."&".GetFilterParams("filter_").""),
		"DEFAULT" => true
	);

	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => GetMessage("CVAT_DELETE_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS('CVAT_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($arVAT['ID'], "delete")
		);
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

if (!$bReadOnly)
{
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
		)
	);
}

if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CVAT_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "/bitrix/admin/cat_vat_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("CVAT_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("CVAT_PAGE_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("CVAT_ACTIVE"),
		GetMessage("CVAT_NAME"),
		GetMessage("CVAT_RATE")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td>ID:</td>
		<td>
			<input type="text" name="filter_id" size="5" value="<?=htmlspecialcharsbx($filter_id)?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("CVAT_FILTER_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=htmlspecialcharsbx("(".GetMessage("CVAT_ALL").")") ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("CVAT_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=htmlspecialcharsbx(GetMessage("CVAT_NO")) ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("CVAT_FILTER_NAME") ?>:</td>
		<td>
			<input type="text" name="filter_name" size="30" value="<?=htmlspecialcharsbx($filter_name)?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("CVAT_FILTER_RATE") ?>:</td>
		<td>
			<input type="text" name="filter_rate" size="30" value="<?=htmlspecialcharsbx($filter_rate)?>">%
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