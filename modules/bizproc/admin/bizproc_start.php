<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/prolog.php");

IncludeModuleLangFile(__FILE__);

$fatalErrorMessage = "";
$errorMessage = "";

$moduleId = "";
if (defined("MODULE_ID"))
	$moduleId = MODULE_ID;

$entity = "";
if (defined("ENTITY"))
	$entity = ENTITY;

$documentType = trim($_REQUEST["document_type"]);
$documentId = trim($_REQUEST["document_id"]);

$backUrl = "/".ltrim(trim($_REQUEST["back_url"]), "\\/");

if (strlen($documentType) <= 0)
	$fatalErrorMessage .= GetMessage("BPABS_EMPTY_DOC_TYPE").". ";
if (strlen($entity) <= 0)
	$fatalErrorMessage .= GetMessage("BPABS_EMPTY_ENTITY").". ";
if (strlen($documentId) <= 0)
	$fatalErrorMessage .= GetMessage("BPABS_EMPTY_DOC_ID").". ";

if (strlen($fatalErrorMessage) <= 0)
{
	$documentType = array($moduleId, $entity, $documentType);
	$documentId = array($moduleId, $entity, $documentId);

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();

	$documentService = $runtime->GetService("DocumentService");

	$bCanUserStartDocumentWorkflow = CBPDocument::CanUserOperateDocument(
		CBPCanUserOperateOperation::StartWorkflow,
		$GLOBALS["USER"]->GetID(),
		$documentId,
		array("UserGroups" => $GLOBALS["USER"]->GetUserGroupArray())
	);
	if (!$bCanUserStartDocumentWorkflow)
		$fatalErrorMessage .= GetMessage("BPABS_NO_PERMS").". ";
}

if (strlen($fatalErrorMessage) <= 0)
{
	$showMode = "SelectWorkflow";
	$workflowTemplateId = intval($_REQUEST["workflow_template_id"]);
	$arWorkflowTemplates = array();

	$dbWorkflowTemplate = CBPWorkflowTemplateLoader::GetList(
		array(),
		array("DOCUMENT_TYPE" => $documentType, "ACTIVE"=>"Y"),
		false,
		false,
		array("ID", "NAME", "DESCRIPTION", "MODIFIED", "USER_ID", "PARAMETERS")
	);
	while ($arWorkflowTemplate = $dbWorkflowTemplate->GetNext())
	{
		$arWorkflowTemplates[$arWorkflowTemplate["ID"]] = $arWorkflowTemplate;
		$arWorkflowTemplates[$arWorkflowTemplate["ID"]]["URL"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("workflow_template_id=".$arWorkflowTemplate["ID"].'&'.bitrix_sessid_get(), Array("workflow_template_id", "sessid")));
;
	}

	if ($workflowTemplateId > 0 && check_bitrix_sessid() && strlen($_POST["CancelStartParamWorkflow"]) <= 0
		&& array_key_exists($workflowTemplateId, $arWorkflowTemplates))
	{
		$arWorkflowTemplate = $arWorkflowTemplates[$workflowTemplateId];

		$arWorkflowParameters = array();
		$bCanStartWorkflow = false;

		if (count($arWorkflowTemplate["PARAMETERS"]) <= 0)
		{
			$bCanStartWorkflow = true;
		}
		elseif ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0)
		{
			$arErrorsTmp = array();

			$arRequest = $_REQUEST;

			foreach ($_FILES as $k => $v)
			{
				if (array_key_exists("name", $v))
				{
					if (is_array($v["name"]))
					{
						$ks = array_keys($v["name"]);
						for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
						{
							$ar = array();
							foreach ($v as $k1 => $v1)
								$ar[$k1] = $v1[$ks[$i]];

							$arRequest[$k][] = $ar;
						}
					}
					else
					{
						$arRequest[$k] = $v;
					}
				}
			}

			$arWorkflowParameters = CBPWorkflowTemplateLoader::CheckWorkflowParameters(
				$arWorkflowTemplate["PARAMETERS"],
				$arRequest,
				$documentType,
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				$bCanStartWorkflow = false;

				foreach ($arErrorsTmp as $e)
					$errorMessage .= $e["message"]."<br />";
			}
			else
			{
				$bCanStartWorkflow = true;
			}
		}

		if ($bCanStartWorkflow)
		{
			$arErrorsTmp = array();

			$wfId = CBPDocument::StartWorkflow(
				$workflowTemplateId,
				$documentId,
				$arWorkflowParameters,
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				$showMode = "StartWorkflowError";

				foreach ($arErrorsTmp as $e)
					$errorMessage .= "[".$e["code"]."] ".$e["message"]."<br />";
			}
			else
			{
				$showMode = "StartWorkflowSuccess";
				if (strlen($backUrl) <= 0)
					$backUrl = "/bitrix/admin/bizproc_log.php?ID=#WF#";
				LocalRedirect(str_replace("#WF#", $wfId, $backUrl));
				die();
			}
		}
		else
		{
			$p = ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_POST["DoStartParamWorkflow"]) > 0);
			$keys = array_keys($arWorkflowTemplate["PARAMETERS"]);
			foreach ($keys as $key)
			{
				$v = ($p ? $_REQUEST[$key] : $arWorkflowTemplate["PARAMETERS"][$key]["Default"]);
				if (!is_array($v))
				{
					$arParametersValues[$key] = $v;
				}
				else
				{
					$keys1 = array_keys($v);
					foreach ($keys1 as $key1)
						$arParametersValues[$key][$key1] = $v[$key1];
				}
			}

			$showMode = "WorkflowParameters";
		}
	}
	else
	{
		$showMode = "SelectWorkflow";
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
CBPDocument::AddShowParameterInit(MODULE_ID, "only_users", $documentType[2], $documentType[1]);

if (strlen($fatalErrorMessage) > 0)
{
	$APPLICATION->SetTitle(GetMessage("BPABS_ERROR"));
	CAdminMessage::ShowMessage($fatalErrorMessage);
}
else
{
	if (strlen($backUrl) <= 0)
		$backUrl = CBPDocument::GetDocumentAdminPage($documentId);

	$aMenu = array(
		array(
			"TEXT" => GetMessage("BPABS_BACK"),
			"LINK" => $backUrl,
			"ICON" => "btn_list",
		)
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();

	$APPLICATION->SetTitle(GetMessage("BPABS_TITLE"));

	CAdminMessage::ShowMessage($errorMessage);

	if ($showMode == "StartWorkflowSuccess")
	{
		?>
		<?= str_replace("#TEMPLATE#", $arWorkflowTemplates[$workflowTemplateId]["NAME"], GetMessage("BPABS_MESSAGE_SUCCESS")) ?>
		<?
	}
	elseif ($showMode == "StartWorkflowError")
	{
		?>
		<?= str_replace("#TEMPLATE#", $arWorkflowTemplates[$workflowTemplateId]["NAME"], GetMessage("BPABS_MESSAGE_ERROR")) ?>
		<?
	}
	elseif ($showMode == "WorkflowParameters")
	{
		?>
		<form method="post" name="start_workflow_form1" action="<?= GetPagePath(false, true) ?>" enctype="multipart/form-data">
			<input type="hidden" name="workflow_template_id" value="<?= intval($workflowTemplateId) ?>">
			<input type="hidden" name="document_type" value="<?= htmlspecialcharsbx($documentType[2]) ?>">
			<input type="hidden" name="document_id" value="<?= htmlspecialcharsbx($documentId[2]) ?>">
			<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($backUrl) ?>">

			<?= bitrix_sessid_post() ?>
			<?
			$aTabs = array(
				array("DIV" => "edit1", "TAB" => GetMessage("BPABS_TAB"), "ICON" => "bizproc", "TITLE" => GetMessage("BPABS_TAB_TITLE"))
			);

			$tabControl = new CAdminTabControl("tabControl", $aTabs);

			$tabControl->Begin();
			$tabControl->BeginNextTab();
			?>
			<tr>
				<td width="40%"><?= GetMessage("BPABS_NAME") ?>:</td>
				<td width="60%">
					<?= $arWorkflowTemplates[$workflowTemplateId]["NAME"] ?>
				</td>
			</tr>
			<?if($arWorkflowTemplates[$workflowTemplateId]["DESCRIPTION"]!=''):?>
			<tr>
				<td class="adm-detail-valign-top"><?= GetMessage("BPABS_DESCRIPTION") ?>:</td>
				<td>
					<?= $arWorkflowTemplates[$workflowTemplateId]["DESCRIPTION"] ?>
				</td>
			</tr>
			<?endif?>
			<?
			foreach ($arWorkflowTemplates[$workflowTemplateId]["PARAMETERS"] as $parameterKey => $arParameter)
			{
				?>
				<tr<?if ($arParameter["Required"]):?> class="adm-detail-required-field"<?endif?>>
					<td><?= htmlspecialcharsbx($arParameter["Name"]) ?>:<?if (strlen($arParameter["Description"]) > 0) echo "<br /><small>".htmlspecialcharsbx($arParameter["Description"])."</small><br />";?></td>
					<td><?
						echo $documentService->GetFieldInputControl(
							$documentType,
							$arParameter,
							array("Form" => "start_workflow_form1", "Field" => $parameterKey),
							$arParametersValues[$parameterKey],
							false,
							true
						);

						/*switch ($arParameter["Type"])
						{
							case "int":
							case "double":
								?><input type="text" name="<?= $parameterKey ?>" size="10" value="<?= $arParametersValues[$parameterKey] ?>" /><?
								break;
							case "string":
								?><input type="text" name="<?= $parameterKey ?>" size="50" value="<?= $arParametersValues[$parameterKey] ?>" /><?
								break;
							case "text":
								?><textarea name="<?= $parameterKey ?>" rows="5" cols="40"><?= $arParametersValues[$parameterKey] ?></textarea><?
								break;
							case "select":
								?><select name="<?= $parameterKey ?><?= $arParameter["Multiple"] ? "[]\" size='5' multiple" : "\"" ?>>
								<?
								if (is_array($arParameter["Options"]) && count($arParameter["Options"]) > 0)
								{
									foreach ($arParameter["Options"] as $key => $value)
									{
										?><option value="<?= htmlspecialcharsbx($key) ?>"<?= (!$arParameter["Multiple"] && $key == $arParametersValues[$parameterKey] || $arParameter["Multiple"] && is_array($arParametersValues[$parameterKey]) && in_array($key, $arParametersValues[$parameterKey])) ? " selected" : "" ?>><?= htmlspecialcharsbx($value) ?></option><?
									}
								}
								?>
								</select><?
								break;
							case "bool":
								?><select name="<?= $parameterKey ?>">
									<option value="Y"<?= ($arParametersValues[$parameterKey] == "Y") ? " selected" : "" ?>><?= GetMessage("BPABS_YES") ?></option>
									<option value="N"<?= ($arParametersValues[$parameterKey] == "N") ? " selected" : "" ?>><?= GetMessage("BPABS_NO") ?></option>
								</select><?
								break;
							case "date":
							case "datetime":
								echo CAdminCalendar::CalendarDate($parameterKey, $arParametersValues[$parameterKey], 19, ($arParameter["Type"] == "date"));
								break;
							case "user":
								?><textarea name="<?= $parameterKey ?>" id="id_<?= $parameterKey ?>" rows="3" cols="40"><?= $arParametersValues[$parameterKey] ?></textarea><input type="button" value="..." onclick="BPAShowSelector('id_<?= $parameterKey ?>', 'user');" /><?
								break;
							default:
								echo GetMessage("BPABS_INVALID_TYPE");
						}*/
					?></td>
				</tr>
				<?
			}
			?>
			<?
			$tabControl->Buttons();
			?>
			<input type="submit" class="adm-btn-save" name="DoStartParamWorkflow" value="<?= GetMessage("BPABS_DO_START") ?>" />
			<input type="submit" name="CancelStartParamWorkflow" value="<?= GetMessage("BPABS_DO_CANCEL") ?>" />
			<?
			$tabControl->End();
			?>
		</form>
		<?
	}
	elseif ($showMode == "SelectWorkflow")
	{
		$aTabs = array(
				array("DIV" => "edit1", "TAB" => GetMessage("BPABS_TAB1"), "ICON" => "bizproc", "TITLE" => GetMessage("BPABS_TAB1_TITLE"))
			);

		$tabControl = new CAdminTabControl("tabControl", $aTabs);

		$tabControl->Begin();
		$tabControl->BeginNextTab();

		if (count($arWorkflowTemplates) > 0)
		{
			foreach ($arWorkflowTemplates as $workflowTemplateId => $arWorkflowTemplate)
			{
				?>
				<tr>
					<td colspan="2">
						<a href="<?= $arWorkflowTemplate["URL"] ?>"><?= $arWorkflowTemplate["NAME"] ?></a><br>
						<?= $arWorkflowTemplate["DESCRIPTION"] ?><br><br>
					</td>
				</tr>
				<?
			}
		}
		else
		{
			?>
			<tr>
				<td colspan="2"><?= GetMessage("BPABS_NO_TEMPLATES") ?></td>
			</tr>
			<?
		}

		$tabControl->End();
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
