<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule('bizproc');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

IncludeModuleLangFile(__FILE__);

$fatalErrorMessage = "";
$actionErrorMessage = '';

$sTableID = "tbl_bizproc_task_list";

$oSort = new CAdminSorting($sTableID, "ID", "DESC");
$lAdmin = new CAdminList($sTableID, $oSort);

$allowAdminAccess = $USER->IsAdmin();

$arFilterFields = array(
	"filter_modified_1",
	"filter_modified_2",
	"filter_name",
	'filter_descr',
	'filter_status',
	'filter_workflow_template_id'
);
if ($allowAdminAccess)
	$arFilterFields[] = "filter_user_id";

$lAdmin->InitFilter($arFilterFields);

$arFilter = array('USER_STATUS' => CBPTaskUserStatus::Waiting);
if (!$allowAdminAccess)
	$arFilter["USER_ID"] = $USER->GetID();
elseif ($filter_user_id <> '')
	$arFilter["USER_ID"] = $filter_user_id;
if ($filter_modified_1 <> '')
	$arFilter[">=MODIFIED"] = $filter_modified_1;
if ($filter_modified_2 <> '')
	$arFilter["<=MODIFIED"] = $filter_modified_2;
if ($filter_name <> '')
	$arFilter["~NAME"] = "%".$filter_name."%";
if ($filter_descr <> '')
	$arFilter["~DESCRIPTION"] = "%".$filter_descr."%";
if (!empty($filter_status))
{
	if ($filter_status == 2)
		unset($arFilter['USER_STATUS']);
	else
		$arFilter['USER_STATUS'] = array(CBPTaskUserStatus::Ok, CBPTaskUserStatus::Yes, CBPTaskUserStatus::No, CBPTaskUserStatus::Cancel);
}
if (!empty($filter_workflow_template_id))
{
	$arFilter['WORKFLOW_TEMPLATE_ID'] = (int)$filter_workflow_template_id;
}

if ($allowAdminAccess && !empty($_REQUEST['action']) && check_bitrix_sessid())
{
	$ids = (isset($_REQUEST['ID']) && is_array($_REQUEST['ID'])) ? $_REQUEST['ID'] : array();
	if ($ids)
	{
		$errors = array();
		$action = $_REQUEST['action'];
		$status = 0;
		if (mb_strpos($action, 'set_status_') === 0)
		{
			$status = mb_substr($action, mb_strlen('set_status_'));
			$action = 'set_status';
		}

		foreach ($ids as $id)
		{
			list($taskId, $userId) = explode('_', $id);

			if ($action == 'set_status' && $status > 0)
				CBPDocument::setTasksUserStatus($userId, $status, $taskId, $errors);
			elseif ($action == 'delegate' && !empty($_REQUEST['delegate_to']))
				CBPDocument::delegateTasks($userId, $_REQUEST['delegate_to'], $taskId, $errors);
		}

		if ($errors)
			foreach ($errors as $error)
			{
				$actionErrorMessage .= $error.PHP_EOL;
			}

		unset($ids, $errors, $action, $status, $taskId, $userId);
	}
}

if ($actionErrorMessage)
{
	$lAdmin->BeginPrologContent();
	CAdminMessage::ShowMessage($actionErrorMessage);
	$lAdmin->EndPrologContent();
}


$arAddHeaders = array(
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
	array("id" => "DOCUMENT_NAME", "content" => GetMessage("BPATL_DOCUMENT_NAME"), "default" => false, "sort" => "DOCUMENT_NAME"),
	array("id" => "NAME", "content" => GetMessage("BPATL_NAME"), "sort" => "NAME", "default" => true),
	array("id" => "DESCRIPTION", "content" => GetMessage("BPATL_DESCR"), "default" => true, "sort" => "DESCRIPTION"),
	array("id" => "DESCRIPTION_FULL", "content" => GetMessage("BPATL_DESCR_FULL"), "default" => false, "sort" => "DESCRIPTION"),
	array("id" => "MODIFIED", "content" => GetMessage("BPATL_MODIFIED"), "sort" => "MODIFIED", "default" => true),
	array("id" => "OVERDUE_DATE", "content" => GetMessage("BPATL_OVERDUE_DATE"), "default" => false, "sort" => "OVERDUE_DATE"),
	array("id" => "WORKFLOW_STARTED", "content" => GetMessage("BPATL_STARTED"), "default" => false, "sort" => "WORKFLOW_STARTED"),
	array("id" => "WORKFLOW_STARTED_BY", "content" => GetMessage("BPATL_STARTED_BY"), "default" => false, "sort" => "WORKFLOW_STARTED_BY"),
	array("id" => "WORKFLOW_NAME", "content" => GetMessage("BPATL_WORKFLOW_NAME"), "default" => true, "sort" => "WORKFLOW_TEMPLATE_NAME"),
	array("id" => "WORKFLOW_STATE", "content" => GetMessage("BPATL_WORKFLOW_STATE"), "default" => true, "sort" => "WORKFLOW_STATE"),
);
if ($allowAdminAccess)
	$arAddHeaders[] = array("id" => "USER", "content" => GetMessage("BPATL_USER"), "default" => true, "sort" => "USER_ID");

$lAdmin->AddHeaders($arAddHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

$arSelectFields = array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS", 'DOCUMENT_NAME', 'WORKFLOW_STARTED', 'WORKFLOW_STARTED_BY', 'OVERDUE_DATE', 'WORKFLOW_TEMPLATE_NAME', 'WORKFLOW_STATE');
if (in_array("USER", $arVisibleColumns) && $allowAdminAccess)
	$arSelectFields[] = "USER_ID";

$dbResultList = CBPTaskService::GetList(
	array($by => $order),
	$arFilter,
	false,
	false,
	$arSelectFields
);

$dbResultList = new CAdminResult($dbResultList, $sTableID);
$dbResultList->NavStart();

$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("BPATL_NAV")));

while ($arResultItem = $dbResultList->NavNext(true, "f_"))
{
	$row =& $lAdmin->AddRow($f_ID.'_'.$f_USER_ID, $arResultItem);

	$s = $allowAdminAccess ? "&uid=".intval($arResultItem["USER_ID"]) : "";
	$row->AddField(
		"ID",
		'<a href="bizproc_task.php?id='.$f_ID.$s.'&back_url='.urlencode($APPLICATION->GetCurPageParam("lang=".LANGUAGE_ID, array("lang"))).'" title="'.GetMessage("BPATL_VIEW_MSGVER_1").'">'.$f_ID.'</a>'
	);
	$row->AddField("NAME", $f_NAME);

	$description = $f_DESCRIPTION;
	if (mb_strlen($description) > 100)
		$description = mb_substr($description, 0, 97)."...";

	$row->AddField("DESCRIPTION", $description);
	$row->AddField("DESCRIPTION_FULL", $f_DESCRIPTION);
	$row->AddField("MODIFIED", $f_MODIFIED);
	$row->AddField("WORKFLOW_NAME", $f_WORKFLOW_TEMPLATE_NAME);
	$row->AddField("WORKFLOW_STATE", $f_WORKFLOW_STATE);
	$row->AddField("WORKFLOW_STARTED", FormatDateFromDB($f_WORKFLOW_STARTED));

	if (intval($f_STARTED_BY ?? 0) > 0)
	{
		$dbUserTmp = CUser::GetByID($f_STARTED_BY);
		$arUserTmp = $dbUserTmp->fetch();
		$row->AddField("WORKFLOW_STARTED_BY",
			CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $arUserTmp, true)
			." [".$f_STARTED_BY."]"
		);
	}

	if (in_array("USER", $arVisibleColumns))
	{
		$dbUserTmp = CUser::GetByID($arResultItem["USER_ID"]);
		if ($arUserTmp = $dbUserTmp->GetNext())
		{
			$str = CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $arUserTmp, true);
			$str .= " [".$arResultItem["USER_ID"]."]";
		}
		else
			$str = str_replace("#USER_ID#", $arResultItem["USER_ID"], GetMessage("BPATL_USER_NOT_FOUND"));
		$row->AddField("USER", $str);
	}

	$arActions = Array();
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => GetMessage("BPATL_VIEW_MSGVER_1"),
		"ACTION" => $lAdmin->ActionRedirect('bizproc_task.php?id='.$f_ID.$s.'&back_url='.urlencode($APPLICATION->GetCurPageParam("lang=".LANGUAGE_ID, array("lang"))).''),
		"DEFAULT" => true
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

if ($allowAdminAccess && isset($arFilter['USER_STATUS']) && $arFilter['USER_STATUS'] == 0)
{
	$lAdmin->AddGroupActionTable(
		array(
			'set_status_'.CBPTaskUserStatus::Yes => GetMessage("BPATL_GROUP_ACTION_YES"),
			'set_status_'.CBPTaskUserStatus::No => GetMessage("BPATL_GROUP_ACTION_NO"),
			'set_status_'.CBPTaskUserStatus::Ok => GetMessage("BPATL_GROUP_ACTION_OK"),
			'delegate' => GetMessage('BPATL_GROUP_ACTION_DELEGATE'),
			'delegate_dialog' => array(
				'type' => 'html',
				'value' => '<div id="action_delegate_to" style="display:none">
					<input type="text" name="delegate_to" size="3" name=""/>
					<input type="button" OnClick="window.open(\'/bitrix/admin/user_search.php?lang='
					.LANGUAGE_ID.'&FN=form_'.$sTableID.'&FC=delegate_to\',
					\'\', \'scrollbars=yes,resizable=yes,width=760,height=500,top=\'+Math.floor((screen.height - 560)/2-14)
					+\',left=\'+Math.floor((screen.width - 760)/2-5));" value=" ... "></div>'
			)
		),
		array(
			'select_onchange' => 'BX("action_delegate_to").style.display = (this.value == "delegate"? "block":"none");',
			'disable_action_target' => true,
		)
	);
}

if (($bizprocModulePermissions ?? '') >= "W")
{
	$aContext = array(
//		array(
//			"TEXT" => GetMessage("SONET_ADD_NEW"),
//			"ICON" => "btn_new",
//			"LINK" => "socnet_subject_edit.php?lang=".LANG,
//			"TITLE" => GetMessage("SONET_ADD_NEW_ALT")
//		),
	);
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->AddAdminContextMenu(array(), false);
$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/
$APPLICATION->SetTitle(GetMessage("BPATL_TITLE_1"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">

<?
$ar = array(
	GetMessage("BPATL_F_MODIFIED"),
	GetMessage("BPATL_F_NAME"),
	GetMessage("BPATL_DESCR"),
	GetMessage("BPATL_FILTER_STATUS"),
	GetMessage("BPATL_WORKFLOW_NAME"),
);
if ($allowAdminAccess)
	$ar[] = GetMessage("BPATL_USER_ID");

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$ar
);

$oFilter->SetDefaultRows(Array("filter_modified_1", 'filter_name'));
$oFilter->AddPreset(array(
	"ID" => "filter_running",
	"NAME" => GetMessage("BPATL_FILTER_STATUS_RUNNING_1"),
	"FIELDS" => array("filter_status" => 0),
));
$oFilter->AddPreset(array(
	"ID" => "filter_complete",
	"NAME" => GetMessage("BPATL_FILTER_STATUS_COMPLETE_1"),
	"FIELDS" => array("filter_status" => 1),
));
$oFilter->AddPreset(array(
	"ID" => "filter_all",
	"NAME" => GetMessage("BPATL_FILTER_STATUS_ALL"),
	"FIELDS" => array("filter_status" => 2),
));

$oFilter->Begin();
?>
	<tr>
		<td><?= GetMessage("BPATL_F_MODIFIED") ?>:</td>
		<td><?echo CalendarPeriod("filter_modified_1", htmlspecialcharsbx($filter_modified_1), "filter_modified_2", htmlspecialcharsbx($filter_modified_2), "find_form", "Y")?></td>
	</tr>
	<tr>
		<td><?= GetMessage("BPATL_F_NAME") ?>:</td>
		<td><input type="text" name="filter_name" value="<?echo htmlspecialcharsex($filter_name)?>" size="30">
		</td>
	</tr>
	<tr>
		<td><?= GetMessage("BPATL_DESCR") ?>:</td>
		<td><input type="text" name="filter_descr" value="<?echo htmlspecialcharsex($filter_descr)?>" size="30">
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("BPATL_FILTER_STATUS")?>:</td>
		<td>
			<select name="filter_status" >
				<option value="0"<?if($filter_status=="0")echo" selected"?>><?echo GetMessage("BPATL_FILTER_STATUS_RUNNING_1")?></option>
				<option value="1"<?if($filter_status=="1")echo" selected"?>><?echo GetMessage("BPATL_FILTER_STATUS_COMPLETE_1")?></option>
				<option value="2"<?if($filter_status=="2")echo" selected"?>><?echo GetMessage("BPATL_FILTER_STATUS_ALL")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=GetMessage("BPATL_WORKFLOW_NAME")?>:</td>
		<td>
			<select name="filter_workflow_template_id">
				<option value=""><?echo GetMessage("BPATL_FILTER_STATUS_ALL")?></option>
				<?
				$dbResTmp = CBPTaskService::GetList(
					array("WORKFLOW_TEMPLATE_NAME" => "ASC"),
					array(),
					array("WORKFLOW_TEMPLATE_TEMPLATE_ID", "WORKFLOW_TEMPLATE_NAME"),
					false,
					array("WORKFLOW_TEMPLATE_TEMPLATE_ID", "WORKFLOW_TEMPLATE_NAME")
				);
				while ($arResTmp = $dbResTmp->GetNext()):?>
					<option value="<?=$arResTmp["WORKFLOW_TEMPLATE_TEMPLATE_ID"]?>"><?=$arResTmp["WORKFLOW_TEMPLATE_NAME"]?></option>
				<?endwhile;?>
			</select>
		</td>
	</tr>

<?
if ($allowAdminAccess)
{
	?><tr>
		<td><?= GetMessage("BPATL_USER_ID") ?>:</td>
		<td><?echo FindUserID(
				"filter_user_id",
				$filter_user_id,
				"",
				"find_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr><?
}
?>
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
