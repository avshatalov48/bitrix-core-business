<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule('bizproc');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

IncludeModuleLangFile(__FILE__);

$fatalErrorMessage = "";

$moduleId = "";
if (defined("MODULE_ID"))
	$moduleId = MODULE_ID;

$entity = "";
if (defined("ENTITY"))
	$entity = ENTITY;

$viewDocumentUrl = "";
if (defined("VIEW_DOCUMENT_URL"))
	$viewDocumentUrl = VIEW_DOCUMENT_URL;

$documentId = trim($_REQUEST["document_id"]);

if ($entity == '')
	$fatalErrorMessage .= GetMessage("BPADH_NO_ENTITY").". ";
if ($documentId == '')
	$fatalErrorMessage .= GetMessage("BPADH_NO_DOC_ID").". ";

if ($fatalErrorMessage == '')
{
	$documentId = array($moduleId, $entity, $documentId);

	$bCanUserWrite = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::WriteDocument,
		$GLOBALS["USER"]->GetID(),
		$documentId,
		array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray())
	);
	if (!$bCanUserWrite)
		$fatalErrorMessage .= GetMessage("BPADH_NO_PERMS").". ";
}

if ($fatalErrorMessage <> '')
{
	$APPLICATION->SetTitle(GetMessage("BPADH_ERROR"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	echo ShowError($fatalErrorMessage);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


$sTableID = "tbl_bizproc_document_history";

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_modified_1",
	"filter_modified_2",
	"filter_user_id",
	"filter_user_id1"
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array("DOCUMENT_ID" => $documentId);
if ($filter_modified_1 <> '')
	$arFilter[">=MODIFIED"] = $filter_modified_1;
if ($filter_modified_2 <> '')
	$arFilter["<=MODIFIED"] = $filter_modified_2;
if (intval($filter_user_id) > 0)
	$arFilter["USER_ID"] = $filter_user_id;
if (intval($filter_user_id1) > 0)
	$arFilter["USER_ID"] = $filter_user_id1;

$history = new CBPHistoryService();

if (($arID = $lAdmin->GroupAction()))
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = Array();
		$dbResultList = $history->GetHistoryList(
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
				CBPHistoryService::Delete($ID, $documentId);
				break;

			case "recover":
				try
				{
					if (CBPHistoryService::RecoverDocumentFromHistory($ID))
					{
						$lAdmin->AddActionSuccessMessage(GetMessage("BPADH_RECOVERY_SUCCESS"));
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("BPADH_RECOVERY_ERROR"), $ID);
					}
				}
				catch (Exception $e)
				{
					$lAdmin->AddGroupError($e->getMessage(), $ID);
				}
				break;
		}
	}
}


$dbResultList = $history->GetHistoryList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "DOCUMENT_ID", "NAME", "MODIFIED", "USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "USER_SECOND_NAME")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BPADH_TITLE")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("BPADH_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"MODIFIED", "content"=>GetMessage("BPADH_MODIFIED"), "sort"=>"MODIFIED", "default"=>true),
	array("id"=>"USER", "content"=>GetMessage("BPADH_AUTHOR"), "sort"=>"USER_ID", "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arResultItem = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arResultItem);

	$row->AddField(
		"ID",
		(($viewDocumentUrl <> '') ? '<a href="'.str_replace(array("#ID#"), array($f_ID), $viewDocumentUrl).'" title="'.GetMessage("BPADH_VIEW_DOC").'">' : "").$f_ID.(($viewDocumentUrl <> '') ? '</a>' : "")
	);
	$row->AddField("NAME", $f_NAME);
	$row->AddField("MODIFIED", $f_MODIFIED);
	$row->AddField("USER", '[<a href="/bitrix/admin/user_edit.php?ID='.$f_USER_ID.'&lang='.LANGUAGE_ID.'" title="'.GetMessage("BPADH_USER_PROFILE").'">'.$f_USER_ID.'</a>] '.$f_USER_NAME.($f_USER_NAME <> '' && $f_USER_LAST_NAME <> '' ? " " : "").$f_USER_LAST_NAME.($f_USER_NAME == '' && $f_USER_LAST_NAME == '' ? $f_USER_LOGIN : ""));

	$arActions = Array();
	if ($viewDocumentUrl <> '')
	{
		$arActions[] = array(
			"ICON" => "edit",
			"TEXT" => GetMessage("BPADH_VIEW_DOC"),
			"ACTION" => $lAdmin->ActionRedirect(str_replace(array("#ID#"), array($f_ID), $viewDocumentUrl)),
			"DEFAULT" => true
		);
		$arActions[] = array("SEPARATOR" => true);
	}
	$arActions[] = array(
		"ICON" => "",
		"TEXT" => GetMessage("BPADH_RECOVERY_DOC"),
		"ACTION" => "if(confirm('".GetMessage("BPADH_RECOVERY_DOC_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "recover", "document_id=".urlencode($documentId[2])."&view_document_url=".urlencode($viewDocumentUrl))
	);
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array(
		"ICON" => "delete",
		"TEXT" => GetMessage("BPADH_DELETE_DOC"),
		"ACTION" => "if(confirm('".GetMessage("BPADH_DELETE_DOC_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "document_id=".urlencode($documentId[2])."&view_document_url=".urlencode($viewDocumentUrl))
	);

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

$lAdmin->CheckListMode();

/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("BPADH_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="document_id" value="<?= htmlspecialcharsbx($documentId[2]) ?>">
	<input type="hidden" name="view_document_url" value="<?= htmlspecialcharsbx($viewDocumentUrl) ?>">

<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		GetMessage("BPADH_F_MODIFIED"),
		GetMessage("BPADH_F_AUTHOR"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("BPADH_F_MODIFIED") ?>:</td>
		<td><?echo CalendarPeriod("filter_modified_1", htmlspecialcharsbx($filter_modified_1), "filter_modified_2", htmlspecialcharsbx($filter_modified_2), "find_form", "Y")?></td>
	</tr>
	<tr>
		<td><?= GetMessage("BPADH_F_AUTHOR") ?>:</td>
		<td><input type="text" name="filter_user_id" value="<?echo htmlspecialcharsex($filter_user_id)?>" size="3">&nbsp;<?
			$dbGrRes = $history->GetHistoryList(
				array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC", "USER_LOGIN" => "ASC"),
				array("DOCUMENT_ID" => $documentId),
				array("USER_ID", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "USER_SECOND_NAME")
			);
			?><select name="filter_user_id1">
				<option value="">(<?= GetMessage("BPADH_F_AUTHOR_ANY") ?>)</option><?
				while ($arGrRes = $dbGrRes->GetNext())
					echo "<option value='".$arGrRes["USER_ID"]."'".($filter_user_id1 == $arGrRes["USER_ID"] ? " selected" : "").">(".htmlspecialcharsex($arGrRes["USER_LOGIN"].") ".CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), array("NAME" => $arGrRes["USER_NAME"], "LAST_NAME" => $arGrRes["USER_LAST_NAME"], "SECOND_NAME" => $arGrRes["USER_SECOND_NAME"])))."</option>";
			?></select>
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
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
