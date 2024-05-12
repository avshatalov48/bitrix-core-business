<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

\Bitrix\Main\Loader::includeModule('ui');

CJSCore::Init(array('window', 'lists'));
Bitrix\Main\UI\Extension::load(["ui.buttons", "ui.dialogs.messagebox"]);

$jsClass = 'ListsElementEditClass_'.$arResult['RAND_STRING'];
$urlTabBp = CHTTP::urlAddParams(
	$APPLICATION->GetCurPageParam("", array($arResult["FORM_ID"]."_active_tab")),
	array($arResult["FORM_ID"]."_active_tab" => "tab_bp")
);
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ?: 0;
$sectionId = $arResult["SECTION_ID"] ?: 0;

$listAction = array();
if (isset($arResult["LIST_COPY_ELEMENT_URL"]))
{
	if($arResult["CAN_ADD_ELEMENT"])
	{
		$listAction[] = [
			"text" => GetMessage("CT_BLEE_TOOLBAR_COPY_ELEMENT"),
			"href" => $arResult["LIST_COPY_ELEMENT_URL"]
		];
	}
}

if (CLists::isEnabledLockFeature($arResult["IBLOCK_ID"]) &&
	$arResult["ELEMENT_ID"] && ($arResult["CAN_FULL_EDIT"] ||
	!CIBlockElement::WF_IsLocked($arResult["ELEMENT_ID"], $lockedBy, $dateLock)))
{
	$listAction[] = [
		"text" => GetMessage("CT_BLEE_UN_LOCK_ELEMENT"),
		"onclick" => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['".$jsClass."'].unLock();"
		),
	];
}

if($arResult["CAN_DELETE_ELEMENT"])
{
	$listAction[] = [
		"text" => $arResult["IBLOCK"]["ELEMENT_DELETE"],
		"onclick" => new \Bitrix\UI\Buttons\JsCode(
			"BX.Lists['".$jsClass."'].elementDelete('form_".$arResult["FORM_ID"]."',
			'".GetMessage("CT_BLEE_TOOLBAR_DELETE_WARNING")."')"
		),
	];
}

if(!IsModuleInstalled("intranet"))
{
	\Bitrix\Main\UI\Extension::load([
		'ui.design-tokens',
		'ui.fonts.opensans',
	]);

	$APPLICATION->SetAdditionalCSS("/bitrix/js/lists/css/intranet-common.css");
}

\Bitrix\UI\Toolbar\Facade\Toolbar::deleteFavoriteStar();
\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
		'link' => $arResult["LIST_SECTION_URL"],
		'color' => \Bitrix\UI\Buttons\Color::LINK,
		'text' => GetMessage("CT_BLEE_TOOLBAR_RETURN_LIST_ELEMENT"),
		'classList' => ['lists-list-back'],
	]
);

if ($listAction)
{
	$settingsButton = new Bitrix\UI\Buttons\SettingsButton([
		'menu' => [
			'items' => $listAction,
		],
	]);
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($settingsButton);
}

$tabElement = array();
$cuctomHtml = "";
foreach($arResult["FIELDS"] as $fieldId => $field)
{
	$field["LIST_SECTIONS_URL"] = $arParams["~LIST_SECTIONS_URL"] ?? null;
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
		"name" => $arResult["~IBLOCK"]["SECTIONS_NAME"],
		"type" => "list",
		"items" => $arResult["LIST_SECTIONS"],
		"params" => array("size" => 15),
	),
);

$arTabs = array(
	array("id" => "tab_el", "name" => $arResult["~IBLOCK"]["ELEMENT_NAME"], "icon" => "", "fields" => $tabElement),
	array("id" => "tab_se", "name" => $arResult["~IBLOCK"]["SECTION_NAME"], "icon" => "", "fields" => $tabSection)
);

if (
	CModule::IncludeModule("bizproc")
	&& CLists::isBpFeatureEnabled($arParams["IBLOCK_TYPE_ID"])
	&& $arResult["IBLOCK"]["BIZPROC"] != "N"
)
{
	if ($arResult["ELEMENT_ID"] > 0)
	{
		$complexDocumentId = BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"],
			$arResult["ELEMENT_ID"]);

		ob_start();
		$APPLICATION->IncludeComponent(
			"bitrix:bizproc.document",
			"frame",
			[
				'MODULE_ID' => 'lists',
				'ENTITY' => $complexDocumentId[1],
				'DOCUMENT_TYPE' => 'iblock_' . $arResult["IBLOCK_ID"],
				'DOCUMENT_ID' => $complexDocumentId[2],
				'LAZYLOAD' => 'Y',
			],
			$component, ["HIDE_ICONS" => "Y"]
		);

		$arTabs[] = [
			"id" => "tab_bp",
			"name" => GetMessage("CT_BLEE_BIZPROC_TAB"),
			"icon" => "",
			"fields" => [
				[
					"id" => "BIZPROC",
					"type" => "custom",
					"colspan" => true,
					"value" => ob_get_clean(),
				],
			],
		];
	}
	else
	{
		$bizprocTabFields = [];

		$bizProcIndex = 0;
		$arDocumentStates = CBPWorkflowTemplateLoader::GetDocumentTypeStates(
			BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
			CBPDocumentEventType::Create
		);

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$documentService = $runtime->GetService("DocumentService");

		foreach ($arDocumentStates as $arDocumentState)
		{
			$templateId = (int)$arDocumentState["TEMPLATE_ID"];
			$templateConstants = CBPWorkflowTemplateLoader::getTemplateConstants($templateId);

			if (
				empty($arDocumentState["TEMPLATE_PARAMETERS"])
				&& empty($arDocumentState["ID"])
				&& empty($templateConstants)
				&& !CIBlockRights::UserHasRightTo($arResult["IBLOCK_ID"], $arResult["IBLOCK_ID"], 'iblock_edit')
			)
			{
				continue;
			}

			$bizProcIndex++;

			$canViewWorkflow = CBPDocument::CanUserOperateDocumentType(
				CBPCanUserOperateOperation::StartWorkflow,
				$GLOBALS["USER"]->GetID(),
				BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]),
				[
					"sectionId" => (int)$arResult["SECTION_ID"],
					"DocumentStates" => $arDocumentStates,
				]
			);

			if ($canViewWorkflow)
			{
				$bizprocTabFields[] = [
					"id" => "BIZPROC_TITLE" . $bizProcIndex,
					"name" => $arDocumentState["TEMPLATE_NAME"],
					"type" => "section",
				];

				$bizprocTabFields[] = [
					"id" => "BIZPROC_NAME" . $bizProcIndex,
					"name" => GetMessage("CT_BLEE_BIZPROC_NAME"),
					"type" => "label",
					"value" => htmlspecialcharsbx($arDocumentState["TEMPLATE_NAME"]),
				];

				if ($arDocumentState["TEMPLATE_DESCRIPTION"] != '')
				{
					$bizprocTabFields[] = [
						"id" => "BIZPROC_DESC" . $bizProcIndex,
						"name" => GetMessage("CT_BLEE_BIZPROC_DESC"),
						"type" => "label",
						"value" => htmlspecialcharsbx($arDocumentState["TEMPLATE_DESCRIPTION"]),
					];
				}

				$arWorkflowParameters = $arDocumentState["TEMPLATE_PARAMETERS"];
				if (!is_array($arWorkflowParameters))
				{
					$arWorkflowParameters = [];
				}
				$bVarsFromForm = $arResult["VARS_FROM_FORM"];
				if ($templateId > 0)
				{
					$parametersValues = [];
					$keys = array_keys($arWorkflowParameters);
					foreach ($keys as $key)
					{
						$v = $bVarsFromForm
							? $_REQUEST["bizproc" . $templateId . "_" . $key]
							: $arWorkflowParameters[$key]["Default"]
						;
						if (!is_array($v))
						{
							$parametersValues[$key] = $v;
						}
						else
						{
							foreach (array_keys($v) as $subKey)
							{
								$parametersValues[$key][$subKey] = $v[$subKey];
							}
						}
					}

					foreach ($arWorkflowParameters as $parameterKey => $arParameter)
					{
						$parameterKeyExt = "bizproc" . $templateId . "_" . $parameterKey;

						$html = $documentService->GetFieldInputControl(
							BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"],
								$arResult["IBLOCK_ID"]),
							$arParameter,
							["Form" => "start_workflow_form1", "Field" => $parameterKeyExt],
							$parametersValues[$parameterKey] ?? null,
							false,
							true
						);

						$bizprocTabFields[] = [
							"id" => $parameterKeyExt . $bizProcIndex,
							"required" => $arParameter["Required"],
							"name" => $arParameter["Name"],
							"title" => $arParameter["Description"],
							"type" => "label",
							"value" => '<div>' . $html . '</div>',
						];
					}

					if (
						!empty($templateConstants)
						&& CIBlockRights::UserHasRightTo($arResult["IBLOCK_ID"], $arResult["IBLOCK_ID"], 'iblock_edit')
					)
					{
						$listTemplateId = [];
						$listTemplateId[$templateId]['ID'] = $templateId;
						$listTemplateId[$templateId]['NAME'] = htmlspecialcharsbx($arDocumentState["TEMPLATE_NAME"]);
						$bizprocTabFields[] = [
							"id" => "BIZPROC_CONSTANTS" . $bizProcIndex,
							"name" => GetMessage("CT_BLEE_BIZPROC_CONSTANTS_LABLE"),
							"type" => "label",
							"value" => '<a href="javascript:void(0)" id="lists-fill-constants-'
								. $bizProcIndex
								. '"
							onclick="BX.Lists[\''
								. $jsClass
								. '\'].fillConstants('
								. CUtil::PhpToJSObject($listTemplateId)
								. ');">'
								. GetMessage("CT_BLEE_BIZPROC_CONSTANTS_FILL")
								. '</a>',
						];
					}
				}
			}
		}

		if (!$bizProcIndex)
		{
			$bizprocTabFields[] = [
				"id" => "BIZPROC_NO",
				"name" => GetMessage("CT_BLEE_BIZPROC_NA_LABEL"),
				"type" => "label",
				"value" => GetMessage("CT_BLEE_BIZPROC_NA"),
			];
		}

		$arTabs[] = [
			"id" => "tab_bp",
			"name" => GetMessage("CT_BLEE_BIZPROC_TAB"),
			"icon" => "",
			"fields" => $bizprocTabFields,
		];
	}
}

if (isset($arResult["RIGHTS"]))
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
	$rights_html = ob_get_clean();

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

$lockStatus = CLists::isEnabledLockFeature($arResult["IBLOCK_ID"]) && $arResult["ELEMENT_ID"];
if ($lockStatus)
{
	$APPLICATION->IncludeComponent(
		"bitrix:lists.lock.status.widget",
		"",
		[
			"ELEMENT_ID" => $arResult["ELEMENT_ID"],
			"ELEMENT_NAME" => $arResult["IBLOCK"]["ELEMENT_NAME"]
		],
		$component, ["HIDE_ICONS" => "Y"]
	);
}

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

<script>
	BX.ready(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsElementEditClass({
			formId: '<?= CUtil::JSEscape(htmlspecialcharsbx($arResult['FORM_ID'])) ?>',
			randomString: '<?=$arResult['RAND_STRING']?>',
			urlTabBp: '<?=$urlTabBp?>',
			iblockTypeId: '<?=$arParams["IBLOCK_TYPE_ID"]?>',
			iblockId: '<?=$arResult["IBLOCK_ID"]?>',
			elementId: '<?=$arResult["ELEMENT_ID"]?>',
			socnetGroupId: '<?=$socnetGroupId?>',
			sectionId: '<?= $sectionId ?>',
			isConstantsTuned: <?= !empty($arResult["isConstantsTuned"]) ? 'true' : 'false' ?>,
			elementUrl: '<?= $arResult["ELEMENT_URL"] ?>',
			sectionUrl: '<?= $arResult["LIST_SECTION_URL"] ?>',
			lockStatus: <?=($lockStatus ? 'true' : 'false')?>
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
