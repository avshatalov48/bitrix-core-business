<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

IncludeModuleLangFile(__FILE__);
/*
$bizprocModulePermissions = $APPLICATION->GetGroupRight("bizproc");
if ($bizprocModulePermissions < "R")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
*/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$errorMessage = "";

$ID = trim($_REQUEST["ID"]);
$adminMode = (strtoupper($_REQUEST["admin_mode"]) == "Y");

$arWorkflowState = CBPStateService::GetWorkflowState($ID);

if (!is_array($arWorkflowState) || count($arWorkflowState) <= 0)
{
	$APPLICATION->SetTitle(GetMessage("BPABL_INVALID_WF"));
	CAdminMessage::ShowMessage(GetMessage("BPABL_INVALID_WF").". ");
}
else
{
	$bCanView = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::ViewWorkflow,
		$GLOBALS["USER"]->GetID(),
		$arWorkflowState["DOCUMENT_ID"],
		array("WorkflowId" => $ID, "DocumentStates" => array($ID => $arWorkflowState), "UserGroups" => $GLOBALS["USER"]->GetUserGroupArray())
	);
	if (!$bCanView)
	{
		$APPLICATION->SetTitle(GetMessage("BPABL_ERROR"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
		echo ShowError(GetMessage("BPABL_NO_PERMS"));
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
		die();
	}

	$backUrl = "/".ltrim(trim($_REQUEST["back_url"]), "\\/");
	if (strlen($backUrl) <= 0)
		$backUrl = CBPDocument::GetDocumentAdminPage($arWorkflowState["DOCUMENT_ID"]);

	$aMenu = array(
		array(
			"TEXT" => GetMessage("BPABL_BACK"),
			"LINK" => $backUrl,
			"ICON" => "btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("BPABL_TITLE")));

	$aTabs = array(
			array("DIV" => "edit1", "TAB" => GetMessage("BPABL_WF_TAB"), "ICON" => "bizproc", "TITLE" => GetMessage("BPABL_TAB_TITLE"))
		);

	$tabControl = new CAdminTabControl("tabControl", $aTabs);

	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
		<tr>
			<td align="right" valign="top" width="50%"><?= GetMessage("BPABL_NAME") ?>:</td>
			<td width="50%" valign="top"><?= htmlspecialcharsbx($arWorkflowState["TEMPLATE_NAME"]) ?></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="50%"><?= GetMessage("BPABL_DESCRIPTION") ?>:</td>
			<td width="50%" valign="top"><?= htmlspecialcharsbx($arWorkflowState["TEMPLATE_DESCRIPTION"]) ?></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="50%"><?= GetMessage("BPABL_ID") ?>:</td>
			<td width="50%" valign="top"><?= htmlspecialcharsbx($arWorkflowState["ID"]) ?></td>
		</tr>
		<tr>
			<td width="40%"><?= GetMessage("BPABL_STATE_MODIFIED") ?>:</td>
			<td width="60%"><?= htmlspecialcharsbx($arWorkflowState["STATE_MODIFIED"]) ?></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="50%"><?= GetMessage("BPABL_STATE_NAME") ?>:</td>
			<td width="50%" valign="top"><?
			if (strlen($arWorkflowState["STATE_NAME"]) > 0)
			{
				if (strlen($arWorkflowState["STATE_TITLE"]) > 0)
					echo htmlspecialcharsbx($arWorkflowState["STATE_TITLE"])." (".htmlspecialcharsbx($arWorkflowState["STATE_NAME"]).")";
				else
					echo htmlspecialcharsbx($arWorkflowState["STATE_NAME"]);
			}
			else
			{
				echo "&nbsp;";
			}
			?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr class="heading">
			<td colspan="2"><?= GetMessage("BPABL_LOG") ?>:</td>
		</tr>
		<tr>
			<td colspan="2">
				<?
				if ($adminMode)
				{
					$arWorkflowTrack = CBPTrackingService::DumpWorkflow($ID);
					foreach ($arWorkflowTrack as $track)
					{
						echo $track["PREFIX"];

						$strMessageTemplate = "";
						switch ($track["TYPE"])
						{
							case 1:
								$strMessageTemplate = GetMessage("BPABL_TYPE_1");
								break;
							case 2:
								$strMessageTemplate = GetMessage("BPABL_TYPE_2");
								break;
							case 3:
								$strMessageTemplate = GetMessage("BPABL_TYPE_3");
								break;
							case 4:
								$strMessageTemplate = GetMessage("BPABL_TYPE_4");
								break;
							case 5:
								$strMessageTemplate = GetMessage("BPABL_TYPE_5");
								break;
							default:
								$strMessageTemplate = GetMessage("BPABL_TYPE_6");
						}

						$name = (strlen($track["ACTION_TITLE"]) > 0 ? $track["ACTION_TITLE"]." (".$track["ACTION_NAME"].")" : $track["ACTION_NAME"]);

						switch ($track["EXECUTION_STATUS"])
						{
							case CBPActivityExecutionStatus::Initialized:
								$status = GetMessage("BPABL_STATUS_1");
								break;
							case CBPActivityExecutionStatus::Executing:
								$status = GetMessage("BPABL_STATUS_2");
								break;
							case CBPActivityExecutionStatus::Canceling:
								$status = GetMessage("BPABL_STATUS_3");
								break;
							case CBPActivityExecutionStatus::Closed:
								$status = GetMessage("BPABL_STATUS_4");
								break;
							case CBPActivityExecutionStatus::Faulting:
								$status = GetMessage("BPABL_STATUS_5");
								break;
							default:
								$status = GetMessage("BPABL_STATUS_6");
						}

						switch ($track["EXECUTION_RESULT"])
						{
							case CBPActivityExecutionResult::None:
								$result = GetMessage("BPABL_RES_1");
								break;
							case CBPActivityExecutionResult::Succeeded:
								$result = GetMessage("BPABL_RES_2");
								break;
							case CBPActivityExecutionResult::Canceled:
								$result = GetMessage("BPABL_RES_3");
								break;
							case CBPActivityExecutionResult::Faulted:
								$result = GetMessage("BPABL_RES_4");
								break;
							case CBPActivityExecutionResult::Uninitialized:
								$result = GetMessage("BPABL_RES_5");
								break;
							default:
								$status = GetMessage("BPABL_RES_6");
						}

						$note = ((strlen($track["ACTION_NOTE"]) > 0) ? ": ".$track["ACTION_NOTE"] : "");

						$note = CBPTrackingService::parseStringParameter($note);

						echo str_replace(
							array("#ACTIVITY#", "#STATUS#", "#RESULT#", "#NOTE#"),
							array($name, $status, $result, $note),
							$strMessageTemplate
						);
						echo "<br />";
					}
					echo "<br><a href=\"".htmlspecialcharsbx($APPLICATION->GetCurPageParam("admin_mode=N", array("admin_mode")))."\">".GetMessage("BPABL_RES2SIMPLEMODE")."</a>";
				}
				else
				{
					$dbResult = CBPTrackingService::GetList(
						array("ID" => "ASC"),
						array("WORKFLOW_ID" => $ID, "TYPE" => array(
							CBPTrackingType::Report,
							CBPTrackingType::Custom,
							CBPTrackingType::FaultActivity,
							CBPTrackingType::Error
						)),
						false,
						false,
						array("ID", "MODIFIED", "ACTION_NOTE")
					);
					while ($arResult = $dbResult->GetNext())
						echo "<i>".$arResult["MODIFIED"]."</i><br>".CBPTrackingService::parseStringParameter($arResult["ACTION_NOTE"])."<br><br>";

					echo "<a href=\"".htmlspecialcharsbx($APPLICATION->GetCurPageParam("admin_mode=Y", array("admin_mode")))."\">".GetMessage("BPABL_RES2ADMINMODE")."</a>";
				}
				?>
			</td>
		</tr>
	<?
	//$tabControl->Buttons();
	?>
	<?
	$tabControl->End();
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");