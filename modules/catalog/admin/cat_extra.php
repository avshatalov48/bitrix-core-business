<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");
if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_price')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_extra');

IncludeModuleLangFile(__FILE__);

if ($ex = $APPLICATION->GetException())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$strError = $ex->GetString();
	ShowError($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$sTableID = "tbl_catalog_extra";

$oSort = new CAdminSorting($sTableID, "ID", "asc");

$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	'find_id_start',
	'find_id_end',
	'find_name',
	'find_perc_start',
	'find_perc_end',
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (!empty($find_id_start))
	$arFilter['>=ID'] = $find_id_start;
if (!empty($find_id_end))
	$arFilter['<=ID'] = $find_id_end;
if (!empty($find_name))
	$arFilter["~NAME"] = $find_name;
if (!empty($find_perc_start))
	$arFilter['>=PERCENTAGE'] = $find_perc_start;
if (!empty($find_perc_end))
	$arFilter['<=PERCENTAGE'] = $find_perc_end;

if ($lAdmin->EditAction() && !$bReadOnly)
{
	foreach ($_POST['FIELDS'] as $ID => $arFields)
	{
		$ID = (int)($ID);

		if ($ID <= 0 || !$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CExtra::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("CEN_ERROR_UPDATE"), $ID);

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
		$dbResultList = CExtra::GetList(array($by => $order), $arFilter, false, false, array('ID'));
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
				if (!CExtra::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("EXTRA_DELETE_ERROR"), $ID);
				}
				else
				{
					$DB->Commit();
				}
				break;
		}
	}
}

$arHeaders = array(
	array(
		"id" => "ID",
		"content" => "ID",
		"sort" => "ID",
		"default" => true
	),
	array(
		"id" => "NAME",
		"content" => GetMessage("EXTRA_NAME"),
		"sort" => "NAME",
		"default" => true
	),
	array(
		"id" => "PERCENTAGE",
		"content" => GetMessage('EXTRA_PERCENTAGE'),
		"sort" => "PERCENTAGE",
		"default" => true
	),
);

if (!$bReadOnly)
{
	$arHeaders[] = array(
		"id" => "RECALCULATE",
		"content" => GetMessage("EXTRA_RECALCULATE"),
		"default" => true
	);
}

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arNavParams = (isset($_REQUEST["mode"]) && "excel" == $_REQUEST["mode"]
	? false
	: array("nPageSize" => CAdminResult::GetNavSize($sTableID))
);

$dbResultList = CExtra::GetList(
	array($by => $order),
	$arFilter,
	false,
	$arNavParams
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("cat_extra_nav")));

while ($arExtra = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arExtra);

	$row->AddField("ID", $f_ID);

	if ($bReadOnly)
	{
		$row->AddViewField("NAME", $f_NAME);
		$row->AddViewField("PERCENTAGE", $f_PERCENTAGE);
	}
	else
	{
		$row->AddInputField("NAME", array("size" => "35"));
		$row->AddInputField("PERCENTAGE", array("size" => "10"));
		$row->AddCheckField("RECALCULATE");
		$row->AddViewField("RECALCULATE", '');
	}

	$arActions = array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("CEN_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("/bitrix/admin/cat_extra_edit.php?ID=".$f_ID."&lang=".LANGUAGE_ID), "DEFAULT"=>true);

	if (!$bReadOnly)
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("CEN_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('CEN_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		)
	);
}

if (!$bReadOnly)
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("CEN_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "cat_extra_edit.php?lang=".LANG,
			"TITLE" => GetMessage("CEN_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("EXTRA_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("EXTRA_NAME"),
		GetMessage("EXTRA_PERCENTAGE"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?$oFilter->Begin();?>
<tr>
	<td><? echo "ID" ?>:</td>
	<td>
		<input type="text" name="find_id_start" size="10" value="<?=htmlspecialcharsbx($find_id_start); ?>">
			...
		<input type="text" name="find_id_end" size="10" value="<?=htmlspecialcharsbx($find_id_end); ?>">
	</td>
</tr>
<tr>
	<td><? echo GetMessage("EXTRA_NAME")?>:</td>
	<td><input type="text" name="find_name" size="47" value="<?=htmlspecialcharsbx($find_name); ?>"></td>
</tr>
<tr>
	<td><? echo GetMessage("EXTRA_PERCENTAGE")?>:</td>
	<td>
		<input type="text" name="find_perc_start" value="<?=htmlspecialcharsbx($find_perc_start); ?>" size="15">
			...
		<input type="text" name="find_perc_end" value="<?=htmlspecialcharsbx($find_perc_end); ?>" size="15">
	</td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();

echo BeginNote();
echo GetMessage("EXTRA_NOTES");
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");