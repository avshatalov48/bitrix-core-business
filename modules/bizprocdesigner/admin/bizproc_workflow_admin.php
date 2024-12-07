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

$editPage = "";
if (defined("EDIT_PAGE"))
	$editPage = EDIT_PAGE;

$documentType = trim($_REQUEST["document_type"]);
$backUrl = "/".ltrim(trim($_REQUEST["back_url_list"] ?? null), "\\/");

if ($entity == '')
	$fatalErrorMessage .= GetMessage("BPATT_NO_ENTITY_1").". ";
if ($documentType == '')
	$fatalErrorMessage .= GetMessage("BPATT_NO_DOC_TYPE_1").". ";
if ($editPage == '')
	$fatalErrorMessage .= GetMessage("BPATT_NO_EDIT_PAGE").". ";

if ($fatalErrorMessage == '')
{
	$documentType = array($moduleId, $entity, $documentType);

	$bCanUserWrite = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$GLOBALS["USER"]->GetID(),
		$documentType,
		array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray())
	);
	if (!$bCanUserWrite)
		$fatalErrorMessage .= GetMessage("BPATT_NO_PERMS_1").". ";
}

if ($fatalErrorMessage <> '')
{
	$APPLICATION->SetTitle(GetMessage("BPATT_ERROR"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError($fatalErrorMessage);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}


$sTableID = "tbl_bizproc_workflow_templates";

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$arFilterFields = array(
	"filter_name",
	"filter_autoexecute",
);

$lAdmin->InitFilter($arFilterFields);

$arFilter = array("DOCUMENT_TYPE" => $documentType);
if ($filter_name <> '')
	$arFilter["~NAME"] = "%".$filter_name."%";
if (intval($filter_autoexecute) > 0)
	$arFilter["AUTO_EXECUTE"] = intval($filter_autoexecute);

if($lAdmin->EditAction())
{
	foreach($FIELDS as $ID=>$arFields)
	{
		$ID = intval($ID);

		if(!$lAdmin->IsUpdated($ID))
			continue;

		try
		{
			CBPWorkflowTemplateLoader::Update($ID, $arFields);
		}
		catch (Exception $e)
		{
			$lAdmin->AddUpdateError(GetMessage("BPWFADM_ERR", array("#ID#"=>$ID, "#ERROR_TEXT#"=>$e->getMessage())), $ID);
		}
	}
}

if ($arID = $lAdmin->GroupAction())
{
	if ($_REQUEST['action_target'] == 'selected')
	{
		$arID = Array();
		$dbResultList = CBPWorkflowTemplateLoader::GetList(
			array(),
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
				$arErrorsTmp = array();
				CBPDocument::DeleteWorkflowTemplate($ID, $documentType, $arErrorsTmp);
				if (count($arErrorsTmp) > 0)
				{
					foreach ($arErrorsTmp as $e)
						$lAdmin->AddGroupError($e["message"], $ID);
				}
				break;
		}
	}
	if (empty($lAdmin->arGroupErrors) && !empty($_REQUEST["back_url"]))
	{
		LocalRedirect($_REQUEST["back_url"]);
	}
}


$dbResultList = CBPWorkflowTemplateLoader::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "AUTO_EXECUTE", "USER_NAME", "USER_LAST_NAME", "USER_LOGIN", "ACTIVE")
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BPATT_NAV")));

$lAdmin->AddHeaders(array(
	array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("BPATT_NAME"), "sort"=>"NAME", "default"=>true),
	array("id"=>"MODIFIED", "content"=>GetMessage("BPATT_MODIFIED"), "sort"=>"MODIFIED", "default"=>true),
	array("id"=>"USER", "content"=>GetMessage("BPATT_USER"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"ACTIVE", "content"=>GetMessage("BPWFADM_ACT"), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"AUTO_EXECUTE", "content"=>GetMessage("BPATT_AUTO_EXECUTE"), "default"=>true),
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

while ($arResultItem = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID, $arResultItem);

	$row->AddField(
		"ID",
		'<a href="'.$editPage.'?ID='.$f_ID.'&document_type='.urlencode($documentType[2]).'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($backUrl).'" title="'.GetMessage("BPATT_DO_EDIT").'">'.$f_ID.'</a>'
	);
	$row->AddInputField("NAME", Array("SIZE"=>"35"));
	$row->AddField("MODIFIED", $f_MODIFIED);
	$row->AddCheckField("ACTIVE");
	$row->AddField("USER", '[<a href="/bitrix/admin/user_edit.php?ID='.$f_USER_ID.'&document_type='.urlencode($documentType[2]).'&lang='.LANGUAGE_ID.'" title="'.GetMessage("BPATT_USER_PROFILE").'">'.$f_USER_ID.'</a>] ('.$f_USER_LOGIN.') '.$f_USER_NAME." ".$f_USER_LAST_NAME);

	$autoExecuteText = "";
	if ($f_AUTO_EXECUTE == CBPDocumentEventType::None)
		$autoExecuteText .= GetMessage("BPATT_AE_NONE");
	if (($f_AUTO_EXECUTE & CBPDocumentEventType::Create) != 0)
	{
		if ($autoExecuteText <> '')
			$autoExecuteText .= ", ";
		$autoExecuteText .= GetMessage("BPATT_AE_CREATE");
	}
	if (($f_AUTO_EXECUTE & CBPDocumentEventType::Edit) != 0)
	{
		if ($autoExecuteText <> '')
			$autoExecuteText .= ", ";
		$autoExecuteText .= GetMessage("BPATT_AE_EDIT");
	}
	if (($f_AUTO_EXECUTE & CBPDocumentEventType::Delete) != 0)
	{
		if ($autoExecuteText <> '')
			$autoExecuteText .= ", ";
		$autoExecuteText .= GetMessage("BPATT_AE_DELETE");
	}

	$row->AddField("AUTO_EXECUTE", $autoExecuteText);

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("BPATT_DO_EDIT1"),
		"ACTION" => $lAdmin->ActionRedirect($editPage.'?ID='.$f_ID.'&document_type='.urlencode($documentType[2]).'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($backUrl)),
		"DEFAULT" => true
	);
	$arActions[] = array("SEPARATOR" => true);
	$arActions[] = array(
		"ICON" => "delete",
		"TEXT" => GetMessage("BPATT_DO_DELETE1"),
		"ACTION" => "if(confirm('".GetMessage("BPATT_DO_DELETE1_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "document_type=".urlencode($documentType[2])."&lang=".LANGUAGE_ID."&back_url_list=".urlencode($backUrl))
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

$aContext = array();
if ($backUrl <> '')
{
	$aContext[] = array(
		"TEXT" => GetMessage("BPATT_BACK"),
		"ICON" => "",
		"LINK" => $backUrl,
		"TITLE" => GetMessage("BPATT_BACK_TITLE")
	);
}

$arSubMenu = Array();

$arSubMenu[] = array(
	"TEXT"	=> GetMessage("BPATT_SUBMENU1_TEXT_1"),
	"TITLE"	=> GetMessage("BPATT_SUBMENU1_TEXT_TITLE_MSGVER_1"),
	"ACTION"	=> "window.location='/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&init=statemachine&entity=".urlencode(ENTITY)."&document_type=".urlencode($documentType[2]).'&back_url_list='.urlencode($backUrl)."';"
);

$arSubMenu[] = array(
	"TEXT"	=> GetMessage("BPATT_SUBMENU2_TEXT"),
	"TITLE"	=> GetMessage("BPATT_SUBMENU2_TEXT_TITLE_MSGVER_1"),
	"ACTION"	=> "window.location='/bitrix/admin/".MODULE_ID."_bizproc_workflow_edit.php?lang=".LANGUAGE_ID."&entity=".urlencode(ENTITY)."&document_type=".urlencode($documentType[2]).'&back_url_list='.urlencode($backUrl)."';"
);

$aContext[] = array(
	"TEXT"=>GetMessage("BPATT_DO_CREATE_TEMPLATE"),
	"TITLE"=>GetMessage("BPATT_DO_CREATE_TEMPLATE1"),
	"ICON"=>"btn_new",
	"MENU"=>$arSubMenu
);

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("BPATT_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
	<input type="hidden" name="document_type" value="<?= htmlspecialcharsbx($documentType[2]) ?>">
	<input type="hidden" name="back_url_list" value="<?= htmlspecialcharsbx($backUrl) ?>">

<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		//GetMessage("BPATT_F_NAME"),
		GetMessage("BPATT_F_AUTOEXECUTE"),
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("BPATT_F_NAME") ?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="47"></td>
	</tr>
	<tr>
		<td><?= GetMessage("BPATT_F_AUTOEXECUTE") ?>:</td>
		<td><select name="filter_autoexecute">
				<option value="">(<?= GetMessage("BPATT_ANY") ?>)</option>
				<option value="<?= CBPDocumentEventType::Create ?>"<?= ($filter_autoexecute == CBPDocumentEventType::Create ? " selected" : "") ?>><?= GetMessage("BPATT_F_CREATE") ?></option>
				<option value="<?= CBPDocumentEventType::Edit ?>"<?= ($filter_autoexecute == CBPDocumentEventType::Edit ? " selected" : "") ?>><?= GetMessage("BPATT_F_EDIT") ?></option>
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
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
