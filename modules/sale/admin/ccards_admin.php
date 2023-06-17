<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2006 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

\Bitrix\Main\Loader::includeModule('sale');

if(!CBXFeatures::IsFeatureEnabled('SaleCCards'))
{
	require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_admin_after.php");

	ShowError(GetMessage("SALE_FEATURE_NOT_ALLOW"));

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_sale_ccards";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_user_id",
	"filter_login",
	"filter_user",
	"filter_active"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();

if (intval($filter_user_id) > 0) $arFilter["USER_ID"] = intval($filter_user_id);
if ($filter_login <> '') $arFilter["USER_LOGIN"] = $filter_login;
if ($filter_user <> '') $arFilter["%USER_USER"] = $filter_user;
if ($filter_active <> '') $arFilter["ACTIVE"] = $filter_active;

if ($lAdmin->EditAction() && $saleModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$ID = intval($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$DB->StartTransaction();
		if (!CSaleUserCards::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(str_replace("#ID#", $ID, GetMessage("SCA_ERROR_UPDATE")), $ID);

			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSaleUserCards::GetList(
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

				if (!CSaleUserCards::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SCA_ERROR_DELETE")), $ID);
				}
				else
				{
					$DB->Commit();
				}

				break;

			case "activate":
			case "deactivate":

				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				if (!CSaleUserCards::Update($ID, $arFields))
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(str_replace("#ID#", $ID, GetMessage("SCA_ERROR_UPDATE")), $ID);
				}

				break;
		}
	}
}

$dbResultList = CSaleUserCards::GetList(
		array($by => $order),
		$arFilter,
		false,
		false,
		array("*")
	);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SCA_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"id", "default"=>true),
	array("id"=>"USER_ID", "content"=>GetMessage("SCA_USER"), "sort"=>"user_id", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("SCA_ACT"), "sort"=>"active", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage("SCA_SORT"), "sort"=>"sort", "default"=>true),
	array("id"=>"CURRENCY", "content"=>GetMessage("SCA_CURRENCY"), "sort"=>"currency", "default"=>true),
	array("id"=>"CARD_TYPE", "content"=>GetMessage("SCA_TYPE"), "sort"=>"card_type", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arCCard = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arCCard, "sale_ccards_edit.php?ID=".$f_ID."&lang=" . LANGUAGE_ID . GetFilterParams("filter_"), GetMessage("SCA_UPDATE_ALT"));

	$row->AddField("ID", $f_ID);

	$fieldValue  = "[<a href=\"/bitrix/admin/user_edit.php?ID=".$f_USER_ID."&lang=" . LANGUAGE_ID . "\">".$f_USER_ID."</a>] ";
	$fieldValue .= htmlspecialcharsEx($arCCard["USER_NAME"].(($arCCard["USER_NAME"] == '' || $arCCard["USER_LAST_NAME"] == '') ? "" : " ").$arCCard["USER_LAST_NAME"])."<br>";
	$fieldValue .= htmlspecialcharsEx($arCCard["USER_LOGIN"])."&nbsp;&nbsp;&nbsp; ";
	$fieldValue .= "<a href=\"mailto:".htmlspecialcharsbx($arCCard["USER_EMAIL"])."\">".htmlspecialcharsEx($arCCard["USER_EMAIL"])."</a>";
	$row->AddField("USER_ID", $fieldValue);

	$row->AddCheckField("ACTIVE");
	$row->AddInputField("SORT");

	$row->AddField("CURRENCY", $f_CURRENCY);
	$row->AddField("CARD_TYPE", $f_CARD_TYPE);

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SCA_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("sale_ccards_edit.php?ID=".$f_ID."&lang=" . LANGUAGE_ID . GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SCA_DELETE_ALT1"), "ACTION"=>"if(confirm('".GetMessage('SCA_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SCAN_ADD_NEW"),
			"LINK" => "sale_ccards_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SCAN_ADD_NEW_ALT"),
			"ICON"	=> "btn_new"
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage("SCA_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SCA_USER_ID"),
		GetMessage("SCA_USER_LOGIN"),
		GetMessage("SCA_ACTIVE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("SCA_USER") ?>:</td>
		<td>
			<input type="text" name="filter_user" size="50" value="<?= htmlspecialcharsbx($filter_user) ?>">&nbsp;<?=ShowFilterLogicHelp()?>
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SCA_USER_ID") ?>:</td>
		<td>
			<input type="text" name="filter_user_id" size="5" value="<?= htmlspecialcharsbx($filter_user_id) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SCA_USER_LOGIN") ?>:</td>
		<td>
			<input type="text" name="filter_login" size="50" value="<?= htmlspecialcharsbx($filter_login) ?>">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("SCA_ACTIVE") ?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?= htmlspecialcharsex("(".GetMessage("SCA_ALL").")"); ?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SCA_YES")) ?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?= htmlspecialcharsex(GetMessage("SCA_NO")) ?></option>
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
if (!CSaleUserCards::CheckPassword())
	CAdminMessage::ShowMessage(array("DETAILS"=>GetMessage("SCA_NO_VALID_PASSWORD"), "TYPE"=>"ERROR", "MESSAGE"=>GetMessage("SCA_ATTENTION")));
?>

<?
$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>