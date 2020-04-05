<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

CJSCore::Init(array('window', 'lists'));

$jsClass = 'ListsElementEditClass_'.$arResult['RAND_STRING'];
$urlTabBp = CHTTP::urlAddParams(
	$APPLICATION->GetCurPageParam("", array($arResult["FORM_ID"]."_active_tab")),
	array($arResult["FORM_ID"]."_active_tab" => "tab_bp")
);
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
$sectionId = $arResult["SECTION_ID"] ? $arResult["SECTION_ID"] : 0;

$listAction = array();
if (isset($arResult["LIST_COPY_ELEMENT_URL"]))
{
	if($arResult["CAN_ADD_ELEMENT"])
	{
		$listAction[] = array(
			"id" => "copyElement",
			"text" => GetMessage("CT_BLEE_TOOLBAR_COPY_ELEMENT"),
			"url" => $arResult["LIST_COPY_ELEMENT_URL"],
			"action" => 'document.location.href="'.$arResult["LIST_COPY_ELEMENT_URL"].'"',
		);
	}
}

if($arResult["CAN_DELETE_ELEMENT"])
{
	$listAction[] = array(
		"id" => "deleteElement",
		"text" => $arResult["IBLOCK"]["ELEMENT_DELETE"],
		"action" => "BX.Lists['".$jsClass."'].elementDelete('form_".$arResult["FORM_ID"]."',
			'".GetMessage("CT_BLEE_TOOLBAR_DELETE_WARNING")."')",
	);
}

$isBitrix24Template = (SITE_TEMPLATE_ID == "bitrix24");
$pagetitleAlignRightContainer = "lists-align-right-container";
if($isBitrix24Template)
{
	$this->SetViewTarget("pagetitle", 100);
	$pagetitleAlignRightContainer = "";
}
elseif(!IsModuleInstalled("intranet"))
{
	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}
?>
<div class="pagetitle-container pagetitle-align-right-container <?=$pagetitleAlignRightContainer?>">
	<a href="<?=$arResult["LIST_SECTION_URL"]?>" class="lists-list-back">
		<?=GetMessage("CT_BLEE_TOOLBAR_RETURN_LIST_ELEMENT")?>
	</a>
	<?if($listAction):?>
	<span id="lists-title-action" class="webform-small-button webform-small-button-transparent bx-filter-button">
		<span class="webform-small-button-text"><?=GetMessage("CT_BLEE_TOOLBAR_ACTION")?></span>
		<span id="lists-title-action-icon" class="webform-small-button-icon"></span>
	</span>
	<?endif;?>
</div>
<?
if($isBitrix24Template)
{
	$this->EndViewTarget();
}

$tabElement = array();
$cuctomHtml = "";
foreach($arResult["FIELDS"] as $fieldId => $field)
{
	$field["LIST_SECTIONS_URL"] = $arParams["~LIST_SECTIONS_URL"];
	$field["SOCNET_GROUP_ID"] = $socnetGroupId;
	$field["LIST_ELEMENT_URL"] = $arParams["~LIST_ELEMENT_URL"];
	$field["LIST_FILE_URL"] = $arParams["~LIST_FILE_URL"];
	$field["IBLOCK_ID"] = $arResult["IBLOCK_ID"];
	$field["SECTION_ID"] = intval($arParams["~SECTION_ID"]);
	$field["ELEMENT_ID"] = $arResult["ELEMENT_ID"];
	$field["FIELD_ID"] = $fieldId;
	$field["VALUE"] = $arResult["FORM_DATA"]["~".$fieldId];
	$field["COPY_ID"] = $arResult["COPY_ID"];
	$preparedData = \Bitrix\Lists\Field::prepareFieldDataForEditForm($field);
	if($preparedData)
	{
		$tabElement[] = $preparedData;
		if(!empty($preparedData["customHtml"]))
		{
			$cuctomHtml .= $preparedData["customHtml"];
		}
	}
}

$tabSection = array(
	array(
		"id" => "IBLOCK_SECTION_ID",
		"name" => $arResult["IBLOCK"]["SECTIONS_NAME"],
		"type" => "list",
		"items" => $arResult["LIST_SECTIONS"],
		"params" => array("size" => 15),
	),
);

$arTabs = array(
	array("id" => "tab_el", "name" => $arResult["IBLOCK"]["ELEMENT_NAME"], "icon" => "", "fields" => $tabElement),
	array("id" => "tab_se", "name" => $arResult["IBLOCK"]["SECTION_NAME"], "icon" => "", "fields" => $tabSection)
);

if(CModule::IncludeModule("bizproc") && CBPRuntime::isFeatureEnabled() && $arResult["IBLOCK"]["BIZPROC"] != "N")
{
	$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
	if(!$arResult["ELEMENT_FIELDS"] || $arResult["ELEMENT_FIELDS"]["CREATED_BY"] == $GLOBALS["USER"]->GetID())
	{
		$arCurrentUserGroups[] = "author";
	}

	$DOCUMENT_TYPE = "iblock_".$arResult["IBLOCK_ID"];
	CBPDocument::AddShowParameterInit("iblock", "only_users", $DOCUMENT_TYPE);

	$arTab2Fields = array();
	$arTab2Fields[] = array(
		"id" => "BIZPROC_WF_STATUS",
		"name" => GetMessage("CT_BLEE_BIZPROC_PUBLISHED"),
		"type" => "label",
		"value" => $arResult["ELEMENT_FIELDS"]["BP_PUBLISHED"]=="Y"? GetMessage("MAIN_YES"): GetMessage("MAIN_NO")
	);

	$bizProcIndex = 0;
	$arDocumentStates = CBPDocument::GetDocumentStates(
		BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
		($arResult["ELEMENT_ID"] > 0) ? BizProcDocument::getDocumentComplexId(
			$arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]) : null,
		"Y"
	);

	$cuctomHtml .= '<input type="hidden" name="stop_bizproc" id="stop_bizproc" value="">';

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");

	foreach ($arDocumentStates as $arDocumentState)
	{
		$templateId = intval($arDocumentState["TEMPLATE_ID"]);
		$templateConstants = CBPWorkflowTemplateLoader::getTemplateConstants($templateId);

		if(
			empty($arDocumentState["TEMPLATE_PARAMETERS"]) &&
			empty($arDocumentState["ID"]) &&
			empty($templateConstants) &&
			!CIBlockRights::UserHasRightTo($arResult["IBLOCK_ID"], $arResult["IBLOCK_ID"], 'iblock_edit')
		)
		{
			continue;
		}

		$bizProcIndex++;

		if ($arResult["ELEMENT_ID"] > 0)
		{
			$canViewWorkflow = CBPDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::ViewWorkflow,
				$GLOBALS["USER"]->GetID(),
				BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]),
				array("AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates,
					"WorkflowId" => $arDocumentState["ID"])
			);
		}
		else
		{
			$canViewWorkflow = CBPDocument::CanUserOperateDocumentType(
				CBPCanUserOperateOperation::StartWorkflow,
				$GLOBALS["USER"]->GetID(),
				BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
				array("sectionId"=> intval($arResult["SECTION_ID"]), "AllUserGroups" => $arCurrentUserGroups,
					"DocumentStates" => $arDocumentStates, "WorkflowId" => $arDocumentState["ID"])
			);
		}

		if($canViewWorkflow)
		{
			$arTab2Fields[] = array(
				"id" => "BIZPROC_TITLE".$bizProcIndex,
				"name" => $arDocumentState["TEMPLATE_NAME"],
				"type" => "section",
			);

			if(strlen($arDocumentState["ID"]) && CIBlockElementRights::UserHasRightTo($arResult["IBLOCK_ID"],
					$arResult["ELEMENT_ID"], "element_edit") && strlen($arDocumentState["WORKFLOW_STATUS"]))
			{
				$arTab2Fields[] = array(
					"id" => "BIZPROC_STOP".$bizProcIndex,
					"name" => GetMessage("CT_BLEE_BIZPROC_STOP_LABEL"),
					"type" => "label",
					"value" => '<a href="javascript:void(0)"
						onclick="BX.Lists[\''.$jsClass.'\'].completeWorkflow(\''.$arDocumentState["ID"].'\',
						\'stop\')">'.GetMessage("CT_BLEE_BIZPROC_STOP").'</a>'
				);
			}

			$arTab2Fields[] = array(
				"id" => "BIZPROC_NAME".$bizProcIndex,
				"name" => GetMessage("CT_BLEE_BIZPROC_NAME"),
				"type" => "label",
				"value" => htmlspecialcharsbx($arDocumentState["TEMPLATE_NAME"]),
			);

			if($arDocumentState["TEMPLATE_DESCRIPTION"]!='')
				$arTab2Fields[] = array(
					"id" => "BIZPROC_DESC".$bizProcIndex,
					"name" => GetMessage("CT_BLEE_BIZPROC_DESC"),
					"type" => "label",
					"value" => htmlspecialcharsbx($arDocumentState["TEMPLATE_DESCRIPTION"]),
				);

			if(strlen($arDocumentState["STATE_MODIFIED"]))
				$arTab2Fields[] = array(
					"id" => "BIZPROC_DATE".$bizProcIndex,
					"name" => GetMessage("CT_BLEE_BIZPROC_DATE"),
					"type" => "label",
					"value" => htmlspecialcharsbx($arDocumentState["STATE_MODIFIED"]),
				);

			if(strlen($arDocumentState["STATE_NAME"]))
			{
				$backUrl = CHTTP::urlAddParams(
					$APPLICATION->GetCurPageParam("", array($arResult["FORM_ID"]."_active_tab")),
					array($arResult["FORM_ID"]."_active_tab" => "tab_bp")
				);
				$url = CHTTP::urlAddParams(str_replace(
					array("#list_id#", "#document_state_id#", "#group_id#"),
					array($arResult["IBLOCK_ID"], $arDocumentState["ID"], $arParams["SOCNET_GROUP_ID"]),
					$arParams["~BIZPROC_LOG_URL"]
				),
					array("back_url" => $backUrl),
					array("skip_empty" => true, "encode" => true)
				);

				if(strlen($arDocumentState["ID"]))
				{
					$arTab2Fields[] = array(
						"id" => "BIZPROC_STATE".$bizProcIndex,
						"name" => GetMessage("CT_BLEE_BIZPROC_STATE"),
						"type" => "label",
						"value" => '<a href="'.htmlspecialcharsbx($url).'">'.(strlen($arDocumentState["STATE_TITLE"])?
								$arDocumentState["STATE_TITLE"] : $arDocumentState["STATE_NAME"]).'</a>',
					);

					$canDeleteWorkflow = CBPDocument::CanUserOperateDocumentType(
						CBPCanUserOperateOperation::CreateWorkflow,
						$GLOBALS["USER"]->GetID(),
						BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
						array("UserGroups" => $arCurrentUserGroups)
					);

					if ($canDeleteWorkflow)
					{
						$arTab2Fields[] = array(
							"id" => "BIZPROC_DELETE".$bizProcIndex,
							"name" => GetMessage("CT_BLEE_BIZPROC_DELETE_LABEL"),
							"type" => "label",
							"value" => '<a href="javascript:void(0)"
								onclick="BX.Lists[\''.$jsClass.'\'].completeWorkflow(\''.$arDocumentState["ID"].'\',
								\'delete\')">'.GetMessage("CT_BLEE_BIZPROC_DELETE").'</a>'
						);
					}
				}
				else
				{
					$arTab2Fields[] = array(
						"id" => "BIZPROC_STATE".$bizProcIndex,
						"name" => GetMessage("CT_BLEE_BIZPROC_STATE"),
						"type" => "label",
						"value" => (strlen($arDocumentState["STATE_TITLE"]) ?
							$arDocumentState["STATE_TITLE"] : $arDocumentState["STATE_NAME"]),
					);
				}
			}

			$arWorkflowParameters = $arDocumentState["TEMPLATE_PARAMETERS"];
			if(!is_array($arWorkflowParameters))
				$arWorkflowParameters = array();
			$formName = $arResult["form_id"];
			$bVarsFromForm = $arResult["VARS_FROM_FORM"];
			if(strlen($arDocumentState["ID"]) <= 0 && $templateId > 0)
			{
				$arParametersValues = array();
				$keys = array_keys($arWorkflowParameters);
				foreach ($keys as $key)
				{
					$v = ($bVarsFromForm ? $_REQUEST["bizproc".$templateId."_".$key] :
						$arWorkflowParameters[$key]["Default"]);
					if (!is_array($v))
					{
						$arParametersValues[$key] = $v;
					}
					else
					{
						$keys1 = array_keys($v);
						foreach ($keys1 as $key1)
						{
							$arParametersValues[$key][$key1] = $v[$key1];
						}
					}
				}

				foreach ($arWorkflowParameters as $parameterKey => $arParameter)
				{
					$parameterKeyExt = "bizproc".$templateId."_".$parameterKey;

					$html = $documentService->GetFieldInputControl(
						BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"],$arResult["IBLOCK_ID"]),
						$arParameter,
						array("Form" => "start_workflow_form1", "Field" => $parameterKeyExt),
						$arParametersValues[$parameterKey],
						false,
						true
					);

					$arTab2Fields[] = array(
						"id" => $parameterKeyExt.$bizProcIndex,
						"required" => $arParameter["Required"],
						"name" => $arParameter["Name"],
						"title" => $arParameter["Description"],
						"type" => "label",
						"value" => $html,
					);
				}

				if(!empty($templateConstants) &&
					CIBlockRights::UserHasRightTo($arResult["IBLOCK_ID"], $arResult["IBLOCK_ID"], 'iblock_edit'))
				{
					$listTemplateId = array();
					$listTemplateId[$templateId]['ID'] = $templateId;
					$listTemplateId[$templateId]['NAME'] = $arDocumentState["TEMPLATE_NAME"];
					$arTab2Fields[] = array(
						"id" => "BIZPROC_CONSTANTS".$bizProcIndex,
						"name" => GetMessage("CT_BLEE_BIZPROC_CONSTANTS_LABLE"),
						"type" => "label",
						"value" => '<a href="javascript:void(0)" id="lists-fill-constants-'.$bizProcIndex.'"
							onclick="BX.Lists[\''.$jsClass.'\'].fillConstants('.CUtil::PhpToJSObject($listTemplateId).');">'.
							GetMessage("CT_BLEE_BIZPROC_CONSTANTS_FILL").'</a>',
					);
				}
			}

			$arEvents = CBPDocument::GetAllowableEvents($GLOBALS["USER"]->GetID(), $arCurrentUserGroups, $arDocumentState);
			if(count($arEvents))
			{
				$html = '';
				$html .= '<input type="hidden" name="bizproc_id_'.$bizProcIndex.'" value="'.$arDocumentState["ID"].'">';
				$html .= '<input type="hidden" name="bizproc_template_id_'.$bizProcIndex.'" value="'.
					$arDocumentState["TEMPLATE_ID"].'">';
				$html .= '<select name="bizproc_event_'.$bizProcIndex.'">';
				$html .= '<option value="">'.GetMessage("CT_BLEE_BIZPROC_RUN_CMD_NO").'</option>';
				foreach ($arEvents as $e)
				{
					$html .= '<option value="'.htmlspecialcharsbx($e["NAME"]).'"'.($_REQUEST["bizproc_event_".
						$bizProcIndex] == $e["NAME"]? " selected": "").'>'.htmlspecialcharsbx($e["TITLE"]).'</option>';
				}
				$html .='</select>';

				$arTab2Fields[] = array(
					"id" => "BIZPROC_RUN_CMD".$bizProcIndex,
					"name" => GetMessage("CT_BLEE_BIZPROC_RUN_CMD"),
					"type" => "label",
					"value" => $html,
				);
			}

			if(strlen($arDocumentState["ID"]))
			{
				$arTasks = CBPDocument::GetUserTasksForWorkflow($GLOBALS["USER"]->GetID(), $arDocumentState["ID"]);
				if(count($arTasks) > 0)
				{
					$html = '';
					foreach($arTasks as $arTask)
					{
						$backUrl = CHTTP::urlAddParams(
							$APPLICATION->GetCurPageParam("", array($arResult["FORM_ID"]."_active_tab")),
							array($arResult["FORM_ID"]."_active_tab" => "tab_bp")
						);

						$url = CHTTP::urlAddParams(str_replace(
								array("#list_id#", "#section_id#", "#element_id#", "#task_id#", "#group_id#"),
								array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]),
									$arResult["ELEMENT_ID"], $arTask["ID"], $arParams["SOCNET_GROUP_ID"]),
								$arParams["~BIZPROC_TASK_URL"]
							),
							array("back_url" => $backUrl),
							array("skip_empty" => true, "encode" => true)
						);

						$html .= '<a href="'.htmlspecialcharsbx($url).'" title="'.strip_tags(
								$arTask["DESCRIPTION"]).'">'.$arTask["NAME"].'</a><br />';
					}

					$arTab2Fields[] = array(
						"id" => "BIZPROC_TASKS".$bizProcIndex,
						"name" => GetMessage("CT_BLEE_BIZPROC_TASKS"),
						"type" => "label",
						"value" => $html,
					);
				}
			}
		}
	}

	if(!$bizProcIndex)
	{
		$arTab2Fields[] = array(
			"id" => "BIZPROC_NO",
			"name" => GetMessage("CT_BLEE_BIZPROC_NA_LABEL"),
			"type" => "label",
			"value" => GetMessage("CT_BLEE_BIZPROC_NA")
		);
	}

	$cuctomHtml .= '<input type="hidden" name="bizproc_index" value="'.$bizProcIndex.'">';

	if($arResult["ELEMENT_ID"])
	{
		$bStartWorkflowPermission = CBPDocument::CanUserOperateDocument(
			CBPCanUserOperateOperation::StartWorkflow,
			$USER->GetID(),
			BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]),
			array("AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates,
				"WorkflowId" => $arDocumentState["TEMPLATE_ID"])
		);
		if($bStartWorkflowPermission)
		{
			$arTab2Fields[] = array(
				"id" => "BIZPROC_NEW",
				"name" => GetMessage("CT_BLEE_BIZPROC_NEW"),
				"type" => "section",
			);

			$backUrl = CHTTP::urlAddParams(
				$APPLICATION->GetCurPageParam("", array($arResult["FORM_ID"]."_active_tab")),
				array($arResult["FORM_ID"]."_active_tab" => "tab_bp")
			);

			$url = CHTTP::urlAddParams(str_replace(
					array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
					array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), $arResult["ELEMENT_ID"],
						$arParams["SOCNET_GROUP_ID"]),
					$arParams["~BIZPROC_WORKFLOW_START_URL"]
				),
				array("back_url" => $backUrl, "sessid" => bitrix_sessid()),
				array("skip_empty" => true, "encode" => true)
			);

			$arTab2Fields[] = array(
				"id" => "BIZPROC_NEW_START",
				"name" => GetMessage("CT_BLEE_BIZPROC_START"),
				"type" => "custom",
				"colspan" => true,
				"value" => '<a href="'.htmlspecialcharsbx($url).'">'.GetMessage("CT_BLEE_BIZPROC_START").'</a>',
			);
		}
	}

	$arTabs[] = array("id"=>"tab_bp", "name"=>GetMessage("CT_BLEE_BIZPROC_TAB"), "icon"=>"", "fields"=>$arTab2Fields);
}

if(isset($arResult["RIGHTS"]))
{
	ob_start();
	IBlockShowRights(
		/*$entity_type=*/'element',
		/*$iblock_id=*/$arResult["IBLOCK_ID"],
		/*$id=*/$arResult["ELEMENT_ID"],
		/*$section_title=*/"",
		/*$variable_name=*/"RIGHTS",
		/*$arPossibleRights=*/$arResult["TASKS"],
		/*$arActualRights=*/$arResult["RIGHTS"],
		/*$bDefault=*/true,
		/*$bForceInherited=*/$arResult["ELEMENT_ID"] <= 0
	);
	$rights_html = ob_get_contents();
	ob_end_clean();

	$rights_fields = array(
		array(
			"id"=>"RIGHTS",
			"name"=>GetMessage("CT_BLEE_ACCESS_RIGHTS"),
			"type"=>"custom",
			"colspan"=>true,
			"value"=>$rights_html,
		),
	);
	$arTabs[] = array(
		"id"=>"tab_rights",
		"name"=>GetMessage("CT_BLEE_TAB_ACCESS"),
		"icon"=>"",
		"fields"=>$rights_fields,
	);
}

$cuctomHtml .= '<input type="hidden" name="action" id="action" value="">';
if(!$arParams["CAN_EDIT"])
	$cuctomHtml .= '<input type="button" value="'.GetMessage("CT_BLEE_FORM_CANCEL").
		'" name="cancel" onclick="window.location=\''.htmlspecialcharsbx(CUtil::addslashes(
				$arResult["~LIST_SECTION_URL"])).'\'" title="'.GetMessage("CT_BLEE_FORM_CANCEL_TITLE").'" />';


$APPLICATION->IncludeComponent(
	"bitrix:main.interface.form",
	"",
	array(
		"FORM_ID"=>$arResult["FORM_ID"],
		"TABS"=>$arTabs,
		"BUTTONS"=>array(
			"standard_buttons" => $arParams["CAN_EDIT"],
			"back_url"=>$arResult["BACK_URL"],
			"custom_html"=>$cuctomHtml,
		),
		"DATA"=>$arResult["FORM_DATA"],
		"SHOW_SETTINGS"=>"N",
		"THEME_GRID_ID"=>$arResult["GRID_ID"],
	),
	$component, array("HIDE_ICONS" => "Y")
);
?>

<div id="lists-notify-admin-popup" style="display:none;">
	<div id="lists-notify-admin-popup-content" class="lists-notify-admin-popup-content">
	</div>
</div>

<script type="text/javascript">
	BX.ready(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsElementEditClass({
			randomString: '<?=$arResult['RAND_STRING']?>',
			urlTabBp: '<?=$urlTabBp?>',
			iblockTypeId: '<?=$arParams["IBLOCK_TYPE_ID"]?>',
			iblockId: '<?=$arResult["IBLOCK_ID"]?>',
			elementId: '<?=$arResult["ELEMENT_ID"]?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			sectionId: '<?= $sectionId ?>',
			isConstantsTuned: <?= $arResult["isConstantsTuned"] ? 'true' : 'false' ?>,
			elementUrl: '<?= $arResult["ELEMENT_URL"] ?>',
			listAction: <?=\Bitrix\Main\Web\Json::encode($listAction)?>
		});

		BX.message({
			CT_BLEE_BIZPROC_SAVE_BUTTON: '<?=GetMessageJS("CT_BLEE_BIZPROC_SAVE_BUTTON")?>',
			CT_BLEE_BIZPROC_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLEE_BIZPROC_CANCEL_BUTTON")?>',
			CT_BLEE_BIZPROC_CONSTANTS_FILL_TITLE: '<?=GetMessageJS("CT_BLEE_BIZPROC_CONSTANTS_FILL_TITLE")?>',
			CT_BLEE_BIZPROC_NOTIFY_TITLE: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_TITLE")?>',
			CT_BLEE_BIZPROC_SELECT_STAFF_SET_RESPONSIBLE: '<?=GetMessageJS("CT_BLEE_BIZPROC_SELECT_STAFF_SET_RESPONSIBLE")?>',
			CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_ONE: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_ONE")?>',
			CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_TWO: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_ADMIN_TEXT_TWO")?>',
			CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE")?>',
			CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE_BUTTON: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_ADMIN_MESSAGE_BUTTON")?>',
			CT_BLEE_BIZPROC_NOTIFY_ADMIN_BUTTON_CLOSE: '<?=GetMessageJS("CT_BLEE_BIZPROC_NOTIFY_ADMIN_BUTTON_CLOSE")?>',
			CT_BLEE_DELETE_POPUP_TITLE: '<?=GetMessageJS("CT_BLEE_DELETE_POPUP_TITLE")?>',
			CT_BLEE_DELETE_POPUP_ACCEPT_BUTTON: '<?=GetMessageJS("CT_BLEE_DELETE_POPUP_ACCEPT_BUTTON")?>',
			CT_BLEE_DELETE_POPUP_CANCEL_BUTTON: '<?=GetMessageJS("CT_BLEE_DELETE_POPUP_CANCEL_BUTTON")?>'
		});

		if(BX["viewElementBind"])
		{
			BX.viewElementBind(
				'form_<?=$arResult["FORM_ID"]?>',
				{showTitle: true},
				{attr: 'data-bx-viewer'}
			);
		}
	});
</script>