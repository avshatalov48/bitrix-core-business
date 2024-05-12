<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule('bizproc');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

IncludeModuleLangFile(__FILE__);

$errorMessage = "";

$allowAdminAccess = $USER->IsAdmin();

$taskId = intval($_REQUEST["id"]);
$userId = intval($_REQUEST["uid"]);
if (!$allowAdminAccess || $userId <= 0)
		$userId = $USER->GetID();

$arTask = false;
if ($taskId > 0)
{
	$dbTask = CBPTaskService::GetList(
		array(),
		array("ID" => $taskId, "USER_ID" => $userId),
		false,
		false,
		array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS", "USER_ID", 'STATUS', 'USER_STATUS',)
	);
	$arTask = $dbTask->GetNext();
}

if (!$arTask)
{
	$workflowId = trim($_REQUEST["workflow_id"]);

	if ($workflowId <> '')
	{
		$dbTask = CBPTaskService::GetList(
			array(),
			array("WORKFLOW_ID" => $workflowId, "USER_ID" => $userId),
			false,
			false,
			array("ID", "WORKFLOW_ID", "ACTIVITY", "ACTIVITY_NAME", "MODIFIED", "OVERDUE_DATE", "NAME", "DESCRIPTION", "PARAMETERS", "USER_ID", 'STATUS', 'USER_STATUS',)
		);
		$arTask = $dbTask->GetNext();
	}
}

if (!$arTask)
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$APPLICATION->SetTitle(GetMessage("BPAT_NO_TASK_MSGVER_1"));
	CAdminMessage::ShowMessage(GetMessage("BPAT_NO_TASK_MSGVER_1").". ");
}
else
{
	$arTask["PARAMETERS"]["DOCUMENT_ID"] = CBPStateService::GetStateDocumentId($arTask['WORKFLOW_ID']);
	$backUrl = !empty($_REQUEST["back_url"]) ? "/".ltrim(trim($_REQUEST["back_url"]), "\\/") : '';
	if ($backUrl == '')
		$backUrl = "/bitrix/admin/bizproc_task_list.php?lang=".LANGUAGE_ID;
	if ($backUrl == '' && !empty($arTask["PARAMETERS"]["DOCUMENT_ID"]))
		$backUrl = CBPDocument::GetDocumentAdminPage($arTask["PARAMETERS"]["DOCUMENT_ID"]);

	$backUrl = CHTTP::urlDeleteParams($backUrl, array('mode'));

	$showType = "Form";

	if ($arTask['STATUS'] > CBPTaskStatus::Running || $arTask['USER_STATUS'] > CBPTaskUserStatus::Waiting)
	{
		$showType = "Success";
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
	{
		if ($_POST["action"] == "doTask")
		{
			$arErrorsTmp = array();
			if (CBPDocument::PostTaskForm($arTask, $userId, $_REQUEST + $_FILES, $arErrorsTmp, $USER->GetFormattedName(false)))
			{
				$showType = "Success";
				if ($backUrl <> '')
				{
					LocalRedirect($backUrl);
					die();
				}
			}
			else
			{
				foreach ($arErrorsTmp as $e)
					$errorMessage .= $e["message"].".<br />";
			}
		}
		elseif (
			$_POST["action"] == "delegate"
			&& $showType == 'Form'
			&& $allowAdminAccess
			&& !empty($_POST['delegate_to'])
			&& $arTask["USER_ID"] != $_POST['delegate_to']
		)
		{
			$errors = array();
			CBPDocument::delegateTasks($arTask["USER_ID"], $_POST['delegate_to'], $arTask['ID'], $errors);
			if ($errors)
				$errorMessage .= $errors[0].'.';
			else
				LocalRedirect($backUrl);
		}
	}

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	$aMenu = array(
		array(
			"TEXT" => GetMessage("BPAT_BACK"),
			"LINK" => $backUrl,
			"ICON" => "btn_list",
		)
	);

	if ($showType == 'Form' && $allowAdminAccess)
	{
		$aMenu[] = array(
			"TEXT"=> GetMessage('BPAT_ACTION_DELEGATE'),
			'ONCLICK' => 'bizprocShowDelegateDialog();'
		);
	}

	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	$APPLICATION->SetTitle(str_replace("#ID#", $taskId, GetMessage("BPAT_TITLE_1")));

	if ($errorMessage <> '')
		CAdminMessage::ShowMessage($errorMessage);

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");

	if (empty($arTask["PARAMETERS"]["DOCUMENT_ID"]))
	{
		CAdminMessage::ShowMessage(GetMessage('BPAT_NO_STATE'));
		$showType = 'Success';
	}
	else
	{
		try
		{
			$documentType = $documentService->GetDocumentType($arTask["PARAMETERS"]["DOCUMENT_ID"]);
			if (!array_key_exists("BP_AddShowParameterInit_".$documentType[0]."_".$documentType[1]."_".$documentType[2], $GLOBALS))
			{
				$GLOBALS["BP_AddShowParameterInit_".$documentType[0]."_".$documentType[1]."_".$documentType[2]] = 1;
				CBPDocument::AddShowParameterInit($documentType[0], "only_users", $documentType[2], $documentType[1]);
			}
		}
		catch (Exception $e)
		{
			CAdminMessage::ShowMessage(GetMessage('BPAT_NO_STATE'));
			$showType = 'Success';
		}
	}

	list($taskForm, $taskFormButtons) = array("", "");
	if ($showType != "Success")
		list($taskForm, $taskFormButtons) = CBPDocument::ShowTaskForm($arTask, $userId, "", ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "doTask") ? $_REQUEST : null);

	?>
	<form method="post" name="task_delegate" action="<?= GetPagePath(false, true) ?>">
		<input type="hidden" name="action" value="delegate">
		<input type="hidden" name="id" value="<?= intval($arTask["ID"]) ?>">
		<input type="hidden" name="workflow_id" value="<?= htmlspecialcharsbx($arTask["WORKFLOW_ID"]) ?>">
		<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($backUrl) ?>">
		<?= bitrix_sessid_post() ?>
		<?
		if ($allowAdminAccess)
			echo '<input type="hidden" name="uid" value="'.intval($arTask["USER_ID"]).'">';
		?>
		<input type="hidden" name="delegate_to" onchange="submit()">
	</form>
	<script>
		function bizprocShowDelegateDialog()
		{
			window.open('/bitrix/admin/user_search.php?lang=<?=LANGUAGE_ID?>&FN=task_delegate&FC=delegate_to',
				'',
				'scrollbars=yes,resizable=yes,width=760,height=500,top='
				+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5)
			);
		}
	</script>

	<form method="post" name="task_form1" action="<?= GetPagePath(false, true) ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="doTask">
		<input type="hidden" name="id" value="<?= intval($arTask["ID"]) ?>">
		<input type="hidden" name="workflow_id" value="<?= htmlspecialcharsbx($arTask["WORKFLOW_ID"]) ?>">
		<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($backUrl) ?>">
		<?= bitrix_sessid_post() ?>
		<?
		if ($allowAdminAccess)
			echo '<input type="hidden" name="uid" value="'.intval($arTask["USER_ID"]).'">';

		$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("BPAT_TAB_1"), "ICON" => "bizproc", "TITLE" => GetMessage("BPAT_TAB_TITLE_1"))
		);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);

		$tabControl->Begin();
		$tabControl->BeginNextTab();
		?>
			<?if ($allowAdminAccess):?>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("BPAT_USER") ?>:</td>
				<td width="60%" valign="top">
					<?
					$dbUserTmp = CUser::GetByID($arTask["USER_ID"]);
					$arUserTmp = $dbUserTmp->GetNext();
					$str = $arUserTmp? CUser::FormatName(COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID), $arUserTmp, true) : GetMessage('BPAT_USER_NOT_FOUND');
					$str .= " [".$arTask["USER_ID"]."]";
					echo $str;
					?>
				</td>
			</tr>
			<?endif;?>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("BPAT_NAME") ?>:</td>
				<td width="60%" valign="top"><?= $arTask["NAME"] ?></td>
			</tr>
			<tr>
				<td align="right" valign="top" width="40%"><?= GetMessage("BPAT_DESCR") ?>:</td>
				<td width="60%" valign="top"><?= nl2br($arTask["DESCRIPTION"]) ?></td>
			</tr>
			<?if ($arTask["PARAMETERS"]["DOCUMENT_URL"] <> ''):?>
			<tr>
				<td align="right" valign="top" width="40%">&nbsp;</td>
				<td width="60%" valign="top"><a href="<?= $arTask["PARAMETERS"]["DOCUMENT_URL"] ?>" target="_blank"><?= GetMessage("BPAT_GOTO_DOC") ?></a></td>
			</tr>
			<?endif;?>
			<?= $taskForm; ?>
		<?
		$tabControl->Buttons();
		?>
			<?= $taskFormButtons ?>
		<?
		$tabControl->End();

		?>
	</form>
	<?
}
?>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
