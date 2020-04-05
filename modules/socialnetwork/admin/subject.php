<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main\Loader;

Loader::includeModule('socialnetwork');

$socialnetworkModulePermissions = $APPLICATION->GetGroupRight("socialnetwork");
if ($socialnetworkModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/prolog.php");

$sTableID = "tbl_socnet_subject";

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_site_id",
	"filter_name",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array();
if (strlen($filter_site_id) > 0 && $filter_site_id != "NOT_REF")
	$arFilter["SITE_ID"] = $filter_site_id;
if (strlen($filter_name) > 0)
	$arFilter["~NAME"] = "%".$filter_name."%";

if ($lAdmin->EditAction() && $socialnetworkModulePermissions >= "W")
{
	foreach ($FIELDS as $ID => $arFields)
	{
		$DB->StartTransaction();
		$ID = IntVal($ID);

		if (!$lAdmin->IsUpdated($ID))
			continue;

		$arBlogSubjectTmp = CSocNetGroupSubject::GetByID($ID);

		foreach ($arFields as $key => $value)
		{
			unset($arFields[$key]);
			$arFields[ltrim($key, "=")] = $value;
		}

		if (!CSocNetGroupSubject::Update($ID, $arFields))
		{
			if ($ex = $APPLICATION->GetException())
				$lAdmin->AddUpdateError($ex->GetString(), $ID);
			else
				$lAdmin->AddUpdateError(GetMessage("SONET_ERROR_UPDATE"), $ID);

			$DB->Rollback();
		}

		//BXClearCache(True, "/".$arFields["SITE_ID"]."/socialnetwork/");
		//BXClearCache(True, "/".$arBlogSubjectTmp["SITE_ID"]."/socialnetwork/");

		$DB->Commit();
	}
}

if (($arID = $lAdmin->GroupAction()) && $socialnetworkModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$dbResultList = CSocNetGroupSubject::GetList(
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

				$arBlogSubjectTmp = CSocNetGroupSubject::GetByID($ID);
				if (!CSocNetGroupSubject::Delete($ID))
				{
					$DB->Rollback();

					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(GetMessage("SONET_DELETE_ERROR"), $ID);
				}

				//BXClearCache(True, "/".$arBlogSubjectTmp["SITE_ID"]."/socialnetwork/");

				$DB->Commit();

				break;
		}
	}
}

$dbResultList = CSocNetGroupSubject::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "SITE_ID", "NAME", "SORT")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SONET_SUBJECT_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("SONET_SUBJECT_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"SITE_ID", "content"=>GetMessage('SONET_SUBJECT_SITE_ID'), "sort"=>"SITE_ID", "default"=>true),
	array("id"=>"SORT", "content"=>GetMessage('SONET_SUBJECT_SORT'), "sort"=>"SORT", "default"=>true),	
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSites = array();
$dbSitesList = CSite::GetList(($b = "sort"), ($o = "asc"));
while ($arSite = $dbSitesList->Fetch())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."]&nbsp;".$arSite["NAME"];

while ($arSubject = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arSubject);

	$row->AddField("ID", '<a href="/bitrix/admin/socnet_subject_edit.php?ID='.$f_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("SONET_UPDATE_ALT").'">'.$f_ID.'</a>');
	$row->AddInputField("NAME", array("size" => "35"));
	
	$f_SITE_ID = '';
	$rsSubjectSite = CSocNetGroupSubject::GetSite($f_ID);
	while($arSubjectSite = $rsSubjectSite->Fetch())
		$f_SITE_ID .= ($f_SITE_ID!=""?" / ":"").htmlspecialcharsbx($arSubjectSite["LID"]);

	$row->AddViewField("SITE_ID", $f_SITE_ID);
	$row->AddInputField("SORT", array("size" => "3"));	

	$arActions = Array();
	$arActions[] = array("ICON"=>"edit", "TEXT"=>GetMessage("SONET_UPDATE_ALT"), "ACTION"=>$lAdmin->ActionRedirect("socnet_subject_edit.php?ID=".$f_ID."&lang=".LANG."&".GetFilterParams("filter_").""), "DEFAULT"=>true);
	if ($socialnetworkModulePermissions >= "U")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>GetMessage("SONET_DELETE_ALT"), "ACTION"=>"if(confirm('".GetMessage('SONET_DELETE_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete"));
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

if ($socialnetworkModulePermissions >= "W")
{
	$aContext = array(
		array(
			"TEXT" => GetMessage("SONET_ADD_NEW"),
			"ICON" => "btn_new",
			"LINK" => "socnet_subject_edit.php?lang=".LANG,
			"TITLE" => GetMessage("SONET_ADD_NEW_ALT")
		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("SONET_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("SONET_SUBJECT_NAME"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?echo GetMessage("SONET_FILTER_SITE_ID")?>:</td>
		<td><?echo CLang::SelectBox("filter_site_id", $filter_site_id, GetMessage("SONET_SPT_ALL")) ?>
	</tr>
	<tr>
		<td><?echo GetMessage("SONET_SUBJECT_NAME")?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsbx($filter_name)?>" size="40"><?=ShowFilterLogicHelp()?></td>
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
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>