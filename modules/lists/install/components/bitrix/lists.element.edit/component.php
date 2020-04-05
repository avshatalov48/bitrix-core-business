<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLEE_MODULE_NOT_INSTALLED"));
	return;
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");
$APPLICATION->AddHeadScript('/bitrix/js/iblock/iblock_edit.js');

$IBLOCK_ID = is_array($arParams["~IBLOCK_ID"])? 0: intval($arParams["~IBLOCK_ID"]);
$ELEMENT_ID = is_array($arParams["~ELEMENT_ID"])? 0: intval($arParams["~ELEMENT_ID"]);
$SECTION_ID = is_array($arParams["~SECTION_ID"])? 0: intval($arParams["~SECTION_ID"]);

if (
	intval($arParams["~SOCNET_GROUP_ID"]) > 0
	&& CModule::IncludeModule("socialnetwork")
)
{
	$arSonetGroup = CSocNetGroup::GetByID(intval($arParams["~SOCNET_GROUP_ID"]));
	if (
		is_array($arSonetGroup)
		&& $arSonetGroup["CLOSED"] == "Y"
		&& !CSocNetUser::IsCurrentUserModuleAdmin()
		&& (
			$arSonetGroup["OWNER_ID"] != $GLOBALS["USER"]->GetID()
			|| COption::GetOptionString("socialnetwork", "work_with_closed_groups", "N") != "Y"
		)
	)
	{
		$arResult["IS_SOCNET_GROUP_CLOSED"] = true;
	}
}

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	$IBLOCK_ID,
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
		case CListPermissions::WRONG_IBLOCK_TYPE:
			ShowError(GetMessage("CC_BLEE_WRONG_IBLOCK_TYPE"));
			return;
		case CListPermissions::WRONG_IBLOCK:
			ShowError(GetMessage("CC_BLEE_WRONG_IBLOCK"));
			return;
		case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
			ShowError(GetMessage("CC_BLEE_LISTS_FOR_SONET_GROUP_DISABLED"));
			return;
		default:
			ShowError(GetMessage("CC_BLEE_UNKNOWN_ERROR"));
			return;
	}
}
elseif(
	(
		$ELEMENT_ID > 0
		&& $lists_perm < CListPermissions::CAN_READ
		&& !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_read")
	) || (
		$ELEMENT_ID == 0
		&& $lists_perm < CListPermissions::CAN_READ
		&& !CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_element_bind")
	)
)
{
	ShowError(GetMessage("CC_BLEE_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] =	(
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		(
			$ELEMENT_ID > 0
			&& (
				$lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_edit")
			)
		)
		|| (
			$ELEMENT_ID == 0
			&& (
				$lists_perm >= CListPermissions::CAN_WRITE
				|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_element_bind")
			)
		)
	)
);
$arResult["CAN_EDIT_RIGHTS"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		(
			$ELEMENT_ID > 0
			&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_rights_edit")
		)
		|| (
			$ELEMENT_ID == 0
			&& CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "element_rights_edit")
		)
	)
);
$arResult["CAN_ADD_ELEMENT"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		($lists_perm > CListPermissions::CAN_READ)
		|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $SECTION_ID, "section_element_bind")
	)

);
$arResult["CAN_DELETE_ELEMENT"] =
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& $ELEMENT_ID > 0
	&& ($lists_perm >= CListPermissions::CAN_WRITE
		|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_delete"));

$arResult["CAN_FULL_EDIT"] = (
	!$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& $ELEMENT_ID > 0
	&& (
		$lists_perm >= CListPermissions::IS_ADMIN
		|| CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit")
	)
);

$arResult["IBLOCK_PERM"] = $lists_perm;
$arResult["USER_GROUPS"] = $USER->GetUserGroupArray();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));
$arResult["~IBLOCK"] = $arIBlock;
$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
$arResult["IBLOCK_ID"] = $arIBlock["ID"];

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_list_elements_".$arResult["IBLOCK_ID"];
if ($ELEMENT_ID)
	$arResult["FORM_ID"] = "lists_element_edit_".$arResult["IBLOCK_ID"];
else
	$arResult["FORM_ID"] = "lists_element_add_".$arResult["IBLOCK_ID"];

$bBizproc = CModule::IncludeModule("bizproc") && CBPRuntime::isFeatureEnabled() && ($arIBlock["BIZPROC"] != "N");

$arResult["~LISTS_URL"] = str_replace(
	array("#group_id#"),
	array($arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_URL"] = CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], 0, $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => ""));
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_SECTION_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arParams["~SECTION_ID"]), $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
);
if((isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0) || !isset($_GET["list_section_id"]))
	$arResult["~LIST_SECTION_URL"] = CHTTP::urlAddParams($arResult["~LIST_SECTION_URL"], array("list_section_id" => ""));

$arResult["LIST_SECTION_URL"] = htmlspecialcharsbx($arResult["~LIST_SECTION_URL"]);

if ($ELEMENT_ID > 0)
{
	$copy_id = 0;
	$arResult["LIST_COPY_ELEMENT_URL"] = CHTTP::urlAddParams(str_replace(
			array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
			array($arResult["IBLOCK_ID"], intval($arResult["SECTION_ID"]), 0, $arParams["SOCNET_GROUP_ID"]),
			$arParams["~LIST_ELEMENT_URL"]
		),
		array("copy_id" => $ELEMENT_ID),
		array("skip_empty" => true, "encode" => true)
	);
	if(isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0)
		$arResult["LIST_COPY_ELEMENT_URL"] = CHTTP::urlAddParams($arResult["LIST_COPY_ELEMENT_URL"], array("list_section_id" => ""));
}
else
{
	if (isset($_REQUEST["copy_id"]) && $_REQUEST["copy_id"] > 0)
		$copy_id = intval($_REQUEST["copy_id"]);
}

$arResult["COPY_ID"] = $copy_id;

$obList = new CList($arIBlock["ID"]);

$arResult["FIELDS"] = $obList->GetFields();
if($bBizproc)
	$arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "CREATED_BY", "BP_PUBLISHED");
else
	$arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID");

$arProps = array();
foreach($arResult["FIELDS"] as $FIELD_ID => $arField)
{
	if($obList->is_field($FIELD_ID))
		$arSelect[] = $FIELD_ID;
	else
		$arProps[] = $FIELD_ID;

	if($FIELD_ID == "CREATED_BY")
		$arSelect[] = "CREATED_USER_NAME";

	if($FIELD_ID == "MODIFIED_BY")
		$arSelect[] = "USER_NAME";
}

$rsElement = CIBlockElement::GetList(
	array(),
	array(
		"IBLOCK_ID" => $arResult["IBLOCK_ID"],
		"=ID" => ($copy_id? $copy_id: $arParams["ELEMENT_ID"]),
		"SHOW_NEW" => ($arResult["CAN_FULL_EDIT"] ? "Y" : "N")
	),
	false,
	false,
	$arSelect
);
$arResult["ELEMENT"] = $rsElement->GetNextElement();

if(is_object($arResult["ELEMENT"]))
	$arResult["ELEMENT_FIELDS"] = $arResult["ELEMENT"]->GetFields();
else
	$arResult["ELEMENT_FIELDS"] = array();

if(is_object($arResult["ELEMENT"]) && !$copy_id)
	$arResult["ELEMENT_ID"] = intval($arResult["ELEMENT_FIELDS"]["ID"]);
else
	$arResult["ELEMENT_ID"] = 0;

$arResult["ELEMENT_PROPS"] = array();
if(is_object($arResult["ELEMENT"]) && count($arProps))
{
	$rsProperties = CIBlockElement::GetProperty(
		$arResult["IBLOCK_ID"],
		$copy_id? $copy_id: $arParams["ELEMENT_ID"],
		array(
			"sort"=>"asc",
			"id"=>"asc",
			"enum_sort"=>"asc",
			"value_id"=>"asc",
		),
		array(
			"ACTIVE"=>"Y",
			"EMPTY"=>"N",
		)
	);
	while($arProperty = $rsProperties->Fetch())
	{
		$prop_id = $arProperty["ID"];
		if(!array_key_exists($prop_id, $arResult["ELEMENT_PROPS"]))
		{
			$arResult["ELEMENT_PROPS"][$prop_id] = $arProperty;
			unset($arResult["ELEMENT_PROPS"][$prop_id]["DESCRIPTION"]);
			unset($arResult["ELEMENT_PROPS"][$prop_id]["VALUE_ENUM_ID"]);
			unset($arResult["ELEMENT_PROPS"][$prop_id]["VALUE_ENUM"]);
			unset($arResult["ELEMENT_PROPS"][$prop_id]["VALUE_XML_ID"]);
			$arResult["ELEMENT_PROPS"][$prop_id]["FULL_VALUES"] = array();
			$arResult["ELEMENT_PROPS"][$prop_id]["VALUES_LIST"] = array();
		}

		$arResult["ELEMENT_PROPS"][$prop_id]["FULL_VALUES"][$arProperty["PROPERTY_VALUE_ID"]] = array(
			"VALUE" => $arProperty["VALUE"],
			"DESCRIPTION" => $arProperty["DESCRIPTION"],
		);
		$arResult["ELEMENT_PROPS"][$prop_id]["VALUES_LIST"][$arProperty["PROPERTY_VALUE_ID"]] = $arProperty["VALUE"];
	}
}

$section_id = intval($arParams["~SECTION_ID"]);
$arSection = false;
if($section_id)
{
	$rsSection = CIBlockSection::GetList(array(), array(
		"IBLOCK_ID" => $arIBlock["ID"],
		"ID" => $section_id,
		"GLOBAL_ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "N",
	));
	$arSection = $rsSection->GetNext();
}
$arResult["SECTION"] = $arSection;
if($arResult["SECTION"])
{
	$arResult["SECTION_ID"] = $arResult["SECTION"]["ID"];
	$arResult["SECTION_PATH"] = array();
	$rsPath = CIBlockSection::GetNavChain($arResult["IBLOCK_ID"], $arResult["SECTION_ID"]);
	while($arPath = $rsPath->Fetch())
	{
		$arResult["SECTION_PATH"][] = array(
			"NAME" => htmlspecialcharsex($arPath["NAME"]),
			"URL" => str_replace(
					array("#list_id#", "#section_id#", "#group_id#"),
					array($arIBlock["ID"], intval($arPath["ID"]), $arParams["SOCNET_GROUP_ID"]),
					$arParams["LIST_URL"]
			),
		);
	}
}
else
{
	$arResult["SECTION_ID"] = false;
}


$tab_name = $arResult["FORM_ID"]."_active_tab";

//Assume there was no error
$bVarsFromForm = false;
$arResult["BACK_URL"] = $arResult["~LIST_SECTION_URL"];

$arResult["EXTERNAL_CONTEXT"] = isset($_REQUEST["external_context"]) ? $_REQUEST["external_context"] : "";
if(!empty($arResult["EXTERNAL_CONTEXT"]))
{
	$arResult["BACK_URL"] = CHTTP::urlAddParams($APPLICATION->getCurPageParam(),
		array("external_context_canceled" => "y"));
	if(!empty($_REQUEST['external_context_canceled']))
	{
		$arResult['EXTERNAL_EVENT'] = array(
			'NAME' => 'onElementCreate',
			'IS_CANCELED' => true,
			'PARAMS' => array(
				'isCanceled' => true,
				'context' => $arResult['EXTERNAL_CONTEXT']
			)
		);
		$this->includeComponentTemplate('event');
		return;
	}
	$externalFieldId = isset($_REQUEST["fieldId"]) ? $_REQUEST["fieldId"] : "";
	$externalDefaultValue = isset($_REQUEST["defaultValue"]) ? $_REQUEST["defaultValue"] : "";
	if($externalFieldId && $externalDefaultValue)
	{
		foreach($arResult["FIELDS"] as $fieldId => $field)
		{
			if($fieldId == $externalFieldId)
				$arResult["FIELDS"][$fieldId]['DEFAULT_VALUE'] = $externalDefaultValue;
		}
	}
}

//Form submitted
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& check_bitrix_sessid()
	&& !$arResult["IS_SOCNET_GROUP_CLOSED"]
	&& (
		$arParams["CAN_EDIT"]
		|| (
			$ELEMENT_ID > 0
			&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_delete")
		) /*|| (
			CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_bizproc_start")
		)*/
	)
)
{

	$obList->ActualizeDocumentAdminPage(str_replace(
		array("#list_id#", "#group_id#"),
		array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
		$arParams["~LIST_ELEMENT_URL"]
	));

	if(
		$arResult["ELEMENT_ID"]
		&& isset($_POST["action"])
		&& $_POST["action"]==="delete"
	)
	{
		if(
			$lists_perm >= CListPermissions::CAN_WRITE
			|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ELEMENT_ID, "element_delete")
		)
		{
			$DB->StartTransaction();
			$APPLICATION->ResetException();
			$obElement = new CIBlockElement;
			if(!$obElement->Delete($arResult["ELEMENT_ID"]))
			{
				$DB->Rollback();
				if($ex = $APPLICATION->GetException())
					ShowError(GetMessage("CC_BLEE_DELETE_ERROR")." ".$ex->GetString());
				else
					ShowError(GetMessage("CC_BLEE_DELETE_ERROR")." ".GetMessage("CC_BLEE_UNKNOWN_ERROR"));
				$bVarsFromForm = true;
			}
			else
			{
				$DB->Commit();
				LocalRedirect($arResult["~LIST_SECTION_URL"]);
			}
		}
		else
		{
			ShowError(GetMessage("CC_BLEE_DELETE_ERROR")." ".GetMessage("CC_BLEE_UNKNOWN_ERROR"));
			$bVarsFromForm = true;
			LocalRedirect($arResult["~LIST_SECTION_URL"]);
		}
	}
	elseif(
		(isset($_POST["save"]) || isset($_POST["apply"]))
		&& $arParams["CAN_EDIT"]
	)
	{
		$strError = "";

		//Gather fields for update
		$arElement = array(
			"IBLOCK_ID" => $arResult["IBLOCK_ID"],
			"IBLOCK_SECTION_ID" => $_POST["IBLOCK_SECTION_ID"],
			"NAME" => $_POST["NAME"],
		);
		$arProps = array();
		$additionalActions = array();
		foreach($arResult["FIELDS"] as $FIELD_ID => $arField)
		{
			if($FIELD_ID == "PREVIEW_PICTURE" || $FIELD_ID == "DETAIL_PICTURE")
			{
				$arElement[$FIELD_ID] = $_FILES[$FIELD_ID];
				if(isset($_POST[$FIELD_ID."_del"]) && $_POST[$FIELD_ID."_del"]=="Y")
					$arElement[$FIELD_ID]["del"] = "Y";
			}
			elseif($FIELD_ID == "PREVIEW_TEXT" || $FIELD_ID == "DETAIL_TEXT")
			{
				if(
					isset($arField["SETTINGS"])
					&& is_array($arField["SETTINGS"])
					&& $arField["SETTINGS"]["USE_EDITOR"] == "Y"
				)
					$arElement[$FIELD_ID."_TYPE"] = "html";
				else
					$arElement[$FIELD_ID."_TYPE"] = "text";

				$arElement[$FIELD_ID] = $_POST[$FIELD_ID];
			}
			elseif($obList->is_field($FIELD_ID))
			{
				$arElement[$FIELD_ID] = $_POST[$FIELD_ID];
			}
			elseif($arField["PROPERTY_TYPE"] == "F")
			{
				if(isset($_POST[$FIELD_ID."_del"]))
					$arDel = $_POST[$FIELD_ID."_del"];
				else
					$arDel = array();

				$arProps[$arField["ID"]] = array();
				if(!empty($_FILES[$FIELD_ID]))
					CFile::ConvertFilesToPost($_FILES[$FIELD_ID], $arProps[$arField["ID"]]);
				foreach($arProps[$arField["ID"]] as $file_id => $arFile)
				{
					if(
						isset($arDel[$file_id])
						&& (
							(!is_array($arDel[$file_id]) && $arDel[$file_id]=="Y")
							|| (is_array($arDel[$file_id]) && $arDel[$file_id]["VALUE"]=="Y")
						)
					)
					{
						if(!$arFile["VALUE"]["error"])
							continue;
						if(isset($arProps[$arField["ID"]][$file_id]["VALUE"]))
							$arProps[$arField["ID"]][$file_id]["VALUE"]["del"] = "Y";
						else
							$arProps[$arField["ID"]][$file_id]["del"] = "Y";
					}
				}
			}
			elseif($arField["PROPERTY_TYPE"] == "N")
			{
				if(is_array($_POST[$FIELD_ID]) && !array_key_exists("VALUE", $_POST[$FIELD_ID]))
				{
					$arProps[$arField["ID"]] = array();
					foreach($_POST[$FIELD_ID] as $key=>$value)
					{
						if(is_array($value))
						{
							if(strlen($value["VALUE"]))
							{
								$value = str_replace(" ", "", str_replace(",", ".", $value["VALUE"]));
								if (!is_numeric($value))
								{
									$strError .= GetMessage('CC_BLEE_VALIDATE_FIELD_ERROR', array('#NAME#'=>$arField['NAME']))."<br />";
								}
								$arProps[$arField["ID"]][$key] = doubleval($value);
							}

						}
						else
						{
							if(strlen($value))
							{
								$value = str_replace(" ", "", str_replace(",", ".", $value));
								if (!is_numeric($value))
								{
									$strError .= GetMessage('CC_BLEE_VALIDATE_FIELD_ERROR', array('#NAME#'=>$arField['NAME']))."<br />";
								}
								$arProps[$arField["ID"]][$key] = doubleval($value);
							}
						}
					}
				}
				else
				{
					if(is_array($_POST[$FIELD_ID]))
					{
						if(strlen($_POST[$FIELD_ID]["VALUE"]))
						{
							$value = str_replace(" ", "", str_replace(",", ".", $_POST[$FIELD_ID]["VALUE"]));
							if (!is_numeric($value))
							{
								$strError .= GetMessage('CC_BLEE_VALIDATE_FIELD_ERROR', array('#NAME#'=>$arField['NAME']))."<br />";
							}
							$arProps[$arField["ID"]] = doubleval($_POST[$FIELD_ID]["VALUE"]);
						}
					}
					else
					{
						if(strlen($_POST[$FIELD_ID]))
						{
							$value = str_replace(" ", "", str_replace(",", ".", $_POST[$FIELD_ID]));
							if (!is_numeric($value))
							{
								$strError .= GetMessage('CC_BLEE_VALIDATE_FIELD_ERROR', array('#NAME#'=>$arField['NAME']))."<br />";
							}
							$arProps[$arField["ID"]] = doubleval($_POST[$FIELD_ID]);
						}
					}
				}
			}
			elseif($arField["PROPERTY_TYPE"] == "E")
			{
				$arField["VALUE"] = $_POST[$FIELD_ID];
				$additionalActions[$arField["ID"]] = $arField;
				$arProps[$arField["ID"]] = $_POST[$FIELD_ID];
			}
			else
			{
				$arProps[$arField["ID"]] = $_POST[$FIELD_ID];
			}
		}

		$arElement["MODIFIED_BY"] = $USER->GetID();
		unset($arElement["TIMESTAMP_X"]);

		if(count($arProps))
		{
			$arElement["PROPERTY_VALUES"] = $arProps;
			$oldPropertyValues = array();
			if($arResult["ELEMENT_ID"] > 0)
			{
				$ignoreProperty = array("F", "L", "N");
				//We have to read properties from database in order not to delete its values
				$dbPropV = CIBlockElement::GetProperty(
					$arResult["IBLOCK_ID"],
					$arResult["ELEMENT_ID"],
					"sort", "asc",
					array("ACTIVE"=>"Y")
				);
				while($arPropV = $dbPropV->Fetch())
				{
					if(!in_array($arPropV["PROPERTY_TYPE"], $ignoreProperty))
					{
						if(is_array($arProps[$arPropV["ID"]]) && empty($arProps[$arPropV["ID"]])
							|| is_string($arProps[$arPropV["ID"]]) && strlen($arProps[$arPropV["ID"]]) <= 0)
						{
							if(!array_key_exists($arPropV["ID"], $arElement["PROPERTY_VALUES"]))
								$arElement["PROPERTY_VALUES"][$arPropV["ID"]] = array();

							$arElement["PROPERTY_VALUES"][$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]] = array(
								"VALUE" => $arPropV["VALUE"],
								"DESCRIPTION" => $arPropV["DESCRIPTION"],
							);
						}
					}
					if($arPropV["USER_TYPE"] == "DiskFile")
					{
						$oldPropertyValues[$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]]["VALUE"] = $arPropV["VALUE"];
					}
				}
			}
		}

		if(
			$arResult["IBLOCK"]["RIGHTS_MODE"] === 'E'
			&& $arResult["CAN_EDIT_RIGHTS"]
		)
		{
			if(is_array($_POST["RIGHTS"]))
				$postRights = CIBlockRights::Post2Array($_POST["RIGHTS"]);
			else
				$postRights = array();

			if($ELEMENT_ID)
				$objectRights = new CIBlockElementRights($arResult["IBLOCK_ID"], $ELEMENT_ID);
			else
				$objectRights = new CIBlockSectionRights($arResult["IBLOCK_ID"], $SECTION_ID);
			$rights = $objectRights->GetRights();
			$arElement["RIGHTS"] = array();
			foreach($rights as $rightId => $right)
			{
				if(array_key_exists($rightId, $postRights))
					$arElement["RIGHTS"][$rightId] = $right;
			}
			foreach($postRights as $rightId => $right)
				$arElement["RIGHTS"][$rightId] = $right;

		}

		//---BP---
		$arResult["isConstantsTuned"] = false;
		if($bBizproc)
		{
			$documentType = BizProcDocument::generateDocumentComplexType($arParams["IBLOCK_TYPE_ID"], $arResult["IBLOCK_ID"]);
			$arDocumentStates = CBPDocument::GetDocumentStates(
				$documentType,
				($arResult["ELEMENT_ID"] > 0) ? BizProcDocument::getDocumentComplexId(
					$arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]) : null,
				"Y"
			);

			$templatesOnStartup = false;
			$arCurrentUserGroups = $USER->GetUserGroupArray();
			if(!$arResult["ELEMENT_FIELDS"] || $arResult["ELEMENT_FIELDS"]["CREATED_BY"] == $USER->GetID())
			{
				$arCurrentUserGroups[] = "author";
			}

			if($arResult["ELEMENT_ID"])
			{
				$canWrite = CBPDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]),
					array("AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates)
				);
			}
			else
			{
				$canWrite = CBPDocument::CanUserOperateDocumentType(
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					$documentType,
					array("AllUserGroups" => $arCurrentUserGroups, "DocumentStates" => $arDocumentStates)
				);
			}

			if(!$canWrite)
				$strError = GetMessage("CC_BLEE_ACCESS_DENIED_STATUS");

			if(!$strError)
			{
				$arBizProcParametersValues = array();
				foreach ($arDocumentStates as $arDocumentState)
				{
					if(strlen($arDocumentState["ID"]) <= 0)
					{
						$templatesOnStartup = true;
						$arErrorsTmp = array();

						$arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]] = CBPDocument::StartWorkflowParametersValidate(
							$arDocumentState["TEMPLATE_ID"],
							$arDocumentState["TEMPLATE_PARAMETERS"],
							$documentType,
							$arErrorsTmp
						);

						foreach($arErrorsTmp as $e)
							$strError .= $e["message"]."<br />";
					}
				}
				$templates = array_merge(
					\CBPWorkflowTemplateLoader::SearchTemplatesByDocumentType($documentType, CBPDocumentEventType::Create),
					\CBPWorkflowTemplateLoader::SearchTemplatesByDocumentType($documentType, CBPDocumentEventType::Edit)
				);
				foreach($templates as $template)
				{
					if(!CBPWorkflowTemplateLoader::isConstantsTuned($template["ID"]))
					{
						$strError .= GetMessage('CC_BLEE_IS_CONSTANTS_TUNED')."<br />";
						$arResult["isConstantsTuned"] = true;
						break;
					}
				}
			}
		}

		if(!$strError)
		{
			$obElement = new CIBlockElement;

			if($arResult["ELEMENT_ID"])
			{
				$res = $obElement->Update($arResult["ELEMENT_ID"], $arElement, false, true, true);
				if(!$res)
					$strError = $obElement->LAST_ERROR;
			}
			else
			{
				$res = $obElement->Add($arElement, false, true, true);
				if($res)
					$arResult["ELEMENT_ID"] = $res;
				else
					$strError = $obElement->LAST_ERROR;
			}
		}

		if(!$strError && $bBizproc)
		{
			/* Find the new or modified field. */
			$changedFields = array();
			if($ELEMENT_ID && $templatesOnStartup)
			{
				$changedFields = CLists::checkChangedFields(
					$arResult["IBLOCK_ID"],
					$arResult["ELEMENT_ID"],
					$arSelect,
					$arResult["ELEMENT_FIELDS"],
					$arResult["ELEMENT_PROPS"]
				);
			}

			$arBizProcWorkflowId = array();
			foreach($arDocumentStates as $arDocumentState)
			{
				if(strlen($arDocumentState["ID"]) <= 0)
				{
					$arErrorsTmp = array();

					$arBizProcWorkflowId[$arDocumentState["TEMPLATE_ID"]] = CBPDocument::StartWorkflow(
						$arDocumentState["TEMPLATE_ID"],
						BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]),
						array_merge($arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]], array(
							CBPDocument::PARAM_TAGRET_USER => "user_".intval($GLOBALS["USER"]->GetID()),
							CBPDocument::PARAM_MODIFIED_DOCUMENT_FIELDS => $changedFields
						)),
						$arErrorsTmp
					);

					foreach($arErrorsTmp as $e)
						$strError .= $e["message"]."<br />";
				}
			}

			$bizprocIndex = intval($_REQUEST["bizproc_index"]);
			if($bizprocIndex > 0)
			{
				for($i = 1; $i <= $bizprocIndex; $i++)
				{
					$bpId = trim($_REQUEST["bizproc_id_".$i]);
					$bpTemplateId = intval($_REQUEST["bizproc_template_id_".$i]);
					$bpEvent = trim($_REQUEST["bizproc_event_".$i]);

					if(strlen($bpEvent) > 0)
					{
						if(strlen($bpId) > 0)
						{
							if(!array_key_exists($bpId, $arDocumentStates))
								continue;
						}
						else
						{
							if(!array_key_exists($bpTemplateId, $arDocumentStates))
								continue;
							$bpId = $arBizProcWorkflowId[$bpTemplateId];
						}

						$arErrorTmp = array();
						CBPDocument::SendExternalEvent(
							$bpId,
							$bpEvent,
							array("Groups" => $arCurrentUserGroups, "User" => $GLOBALS["USER"]->GetID()),
							$arErrorTmp
						);

						foreach ($arErrorsTmp as $e)
							$strWarning .= $e["message"]."<br />";
					}
				}
			}

			$arDocumentStates = null;
			/*if($arElement["NAME"])
			{
				CBPDocument::AddDocumentToHistory(
					BizProcDocument::getDocumentComplexId($arParams["IBLOCK_TYPE_ID"], $arResult["ELEMENT_ID"]),
					$arElement["NAME"],
					$GLOBALS["USER"]->GetID()
				);
			}*/
		}

		if(!$strError)
		{
			//Successfull update

			$url = CHTTP::urlAddParams(str_replace(
				array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
				array($arResult["IBLOCK_ID"], intval($_POST["IBLOCK_SECTION_ID"]), $arResult["ELEMENT_ID"], $arParams["SOCNET_GROUP_ID"]),
				$arParams["~LIST_ELEMENT_URL"]
			),
				array($tab_name => $_POST[$tab_name]),
				array("skip_empty" => true, "encode" => true)
			);
			if(isset($_GET["list_section_id"]) && strlen($_GET["list_section_id"]) == 0)
				$url = CHTTP::urlAddParams($url, array("list_section_id" => ""));

			if(isset($arResult['EXTERNAL_CONTEXT']) && $arResult['EXTERNAL_CONTEXT'] !== '')
			{
				$arResult['EXTERNAL_EVENT'] = array(
					'NAME' => 'onElementCreate',
					'IS_CANCELED' => false,
					'PARAMS' => array(
						'isCanceled' => false,
						'context' => $arResult['EXTERNAL_CONTEXT'],
						'elementInfo' => array(
							'iblockTypeId' => $arParams['IBLOCK_TYPE_ID'],
							'iblockId' => $arResult['IBLOCK_ID'],
							'socnetGroupId' => $arParams['SOCNET_GROUP_ID'],
							'elementId' => $arResult["ELEMENT_ID"],
							'elementUrl' => $url,
							'elementName' => $arElement['NAME']
						),
					)
				);
				$this->includeComponentTemplate('event');
				return;
			}

			//And go to proper page
			if(isset($_POST["save"]))
			{
				LocalRedirect($arResult["~LIST_SECTION_URL"]);
			}
			elseif(
				$lists_perm < CListPermissions::CAN_READ
				&& !CIBlockElementRights::UserHasRightTo($arResult["IBLOCK_ID"], $arResult["ELEMENT_ID"], "element_read")
			)
			{
				LocalRedirect($arResult["~LIST_SECTION_URL"]);
			}
			else
			{
				LocalRedirect($url);
			}
		}
		else
		{
			ShowError($strError);
			$bVarsFromForm = true;
		}
	}
	else
	{
		//Go to list section page
		LocalRedirect($arResult["~LIST_SECTION_URL"]);
	}
}

$arResult["ELEMENT_URL"] = str_replace(
	array("#list_id#", "#section_id#", "#element_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], intval($arParams["~SECTION_ID"]), $arResult["ELEMENT_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["LIST_ELEMENT_URL"]
);

$data = array();
if($bVarsFromForm)
{//There was an error so display form values
	$data["NAME"] = $_POST["NAME"];
	$data["IBLOCK_SECTION_ID"] = $_POST["IBLOCK_SECTION_ID"];
}
elseif($arResult["ELEMENT_ID"] || $copy_id)
{//Edit existing field
	$data["NAME"] = $arResult["ELEMENT_FIELDS"]["NAME"];
	$data["IBLOCK_SECTION_ID"] = $arResult["ELEMENT_FIELDS"]["IBLOCK_SECTION_ID"];
}
else
{//New one
	$data["NAME"] = GetMessage("CC_BLEE_FIELD_NAME_DEFAULT");
	$data["IBLOCK_SECTION_ID"] = $arResult["SECTION_ID"]? $arResult["SECTION_ID"]: "";
}

foreach($arResult["FIELDS"] as $FIELD_ID => $arField)
{
	if($obList->is_field($FIELD_ID))
	{
		if($FIELD_ID == "ACTIVE_FROM")
		{
			if($bVarsFromForm)
				$data[$FIELD_ID] = $_POST[$FIELD_ID];
			elseif($arResult["ELEMENT_ID"] || $copy_id)
				$data[$FIELD_ID] = $arResult["ELEMENT_FIELDS"]["~".$FIELD_ID];
			elseif($arField["DEFAULT_VALUE"] === "=now")
				$data[$FIELD_ID] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "FULL");
			elseif($arField["DEFAULT_VALUE"] === "=today")
				$data[$FIELD_ID] = ConvertTimeStamp(time()+CTimeZone::GetOffset(), "SHORT");
			else
				$data[$FIELD_ID] = "";
		}
		elseif($FIELD_ID == "PREVIEW_PICTURE" || $FIELD_ID == "DETAIL_PICTURE")
		{
			if($arResult["ELEMENT_ID"])
				$data[$FIELD_ID] = $arResult["ELEMENT_FIELDS"]["~".$FIELD_ID];
			else
				$data[$FIELD_ID] = "";
		}
		else
		{
			if($bVarsFromForm)
				$data[$FIELD_ID] = $_POST[$FIELD_ID];
			elseif($arResult["ELEMENT_ID"] || $copy_id)
				$data[$FIELD_ID] = $arResult["ELEMENT_FIELDS"]["~".$FIELD_ID];
			else
				$data[$FIELD_ID] = $arField["DEFAULT_VALUE"];
		}
	}
	elseif(is_array($arField["PROPERTY_USER_TYPE"]) && array_key_exists("GetPublicEditHTML", $arField["PROPERTY_USER_TYPE"]))
	{
		if($bVarsFromForm)
		{
			$data[$FIELD_ID] = $_POST[$FIELD_ID];
		}
		elseif($arResult["ELEMENT_ID"] || $copy_id)
		{
			if(isset($arResult["ELEMENT_PROPS"][$arField["ID"]]))
			{
				$data[$FIELD_ID] = $arResult["ELEMENT_PROPS"][$arField["ID"]]["FULL_VALUES"];
				if($arField["MULTIPLE"] == "Y")
					$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
			else
			{
				$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
		}
		else
		{
			$data[$FIELD_ID] = array(
				"n0" => array(
					"VALUE" => $arField["DEFAULT_VALUE"] ? $arField["DEFAULT_VALUE"] : "",
					"DESCRIPTION" => "",
				)
			);
		}
	}
	elseif($arField["PROPERTY_TYPE"] == "L")
	{
		if($bVarsFromForm)
		{
			$data[$FIELD_ID] = $_POST[$FIELD_ID];
		}
		elseif($arResult["ELEMENT_ID"] || $copy_id)
		{
			if(isset($arResult["ELEMENT_PROPS"][$arField["ID"]]))
				$data[$FIELD_ID] = $arResult["ELEMENT_PROPS"][$arField["ID"]]["VALUES_LIST"];
			else
				$data[$FIELD_ID] = array();
		}
		else
		{
			$data[$FIELD_ID] = array();
			$prop_enums = CIBlockProperty::GetPropertyEnum($arField["ID"]);
			while($ar_enum = $prop_enums->Fetch())
				if($ar_enum["DEF"] == "Y")
					$data[$FIELD_ID][] =$ar_enum["ID"];
		}
	}
	elseif($arField["PROPERTY_TYPE"] == "F")
	{
		if($arResult["ELEMENT_ID"])
		{
			if(isset($arResult["ELEMENT_PROPS"][$arField["ID"]]))
			{
				$data[$FIELD_ID] = $arResult["ELEMENT_PROPS"][$arField["ID"]]["FULL_VALUES"];
				if($arField["MULTIPLE"] == "Y")
					$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
			else
			{
				$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
		}
		else
		{
			$data[$FIELD_ID] = array(
				"n0" => array("VALUE" => "", "DESCRIPTION" => ""),
			);
		}
	}
	elseif($arField["PROPERTY_TYPE"] == "G" || $arField["PROPERTY_TYPE"] == "E")
	{
		if($bVarsFromForm)
		{
			$data[$FIELD_ID] = $_POST[$FIELD_ID];
		}
		elseif($arResult["ELEMENT_ID"] || $copy_id)
		{
			if(isset($arResult["ELEMENT_PROPS"][$arField["ID"]]))
				$data[$FIELD_ID] = $arResult["ELEMENT_PROPS"][$arField["ID"]]["VALUES_LIST"];
			else
				$data[$FIELD_ID] = array();
		}
		else
		{
			$data[$FIELD_ID] = array($arField["DEFAULT_VALUE"]);
		}
	}
	else//if($arField["PROPERTY_TYPE"] == "S" || $arField["PROPERTY_TYPE"] == "N")
	{
		if($bVarsFromForm)
		{
			$data[$FIELD_ID] = $_POST[$FIELD_ID];
		}
		elseif($arResult["ELEMENT_ID"] || $copy_id)
		{
			if(isset($arResult["ELEMENT_PROPS"][$arField["ID"]]))
			{
				$data[$FIELD_ID] = $arResult["ELEMENT_PROPS"][$arField["ID"]]["FULL_VALUES"];
				if($arField["MULTIPLE"] == "Y")
					$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
			else
			{
				$data[$FIELD_ID]["n0"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
		}
		else
		{
			$data[$FIELD_ID] = array(
				"n0" => array("VALUE" => $arField["DEFAULT_VALUE"], "DESCRIPTION" => ""),
			);
			if($arField["MULTIPLE"] == "Y")
			{
				if(is_array($arField["DEFAULT_VALUE"]) || strlen($arField["DEFAULT_VALUE"]))
					$data[$FIELD_ID]["n1"] = array("VALUE" => "", "DESCRIPTION" => "");
			}
		}
	}
}

$arResult["LIST_SECTIONS"] = array(
	"" => GetMessage("CC_BLEE_UPPER_LEVEL"),
);
$rsSections = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$arResult["IBLOCK_ID"], "CHECK_PERMISSIONS"=>"N"));
while($arSection = $rsSections->Fetch())
	$arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];

if(
	$arResult["IBLOCK"]["RIGHTS_MODE"] == 'E'
	&& $arResult["CAN_EDIT_RIGHTS"]
)
{
	$arResult["RIGHTS"] = array();
	if($arResult["ELEMENT_ID"])
		$objectRights = new CIBlockElementRights($arResult["IBLOCK_ID"], $arResult["ELEMENT_ID"]);
	else
		$objectRights = new CIBlockSectionRights($arResult["IBLOCK_ID"], intval($data["IBLOCK_SECTION_ID"]));

	$arResult["RIGHTS"] = $objectRights->GetRights(array("parents" => array($data["IBLOCK_SECTION_ID"])));
	$arResult["TASKS"] = CIBlockRights::GetRightsList();
}

$arResult["VARS_FROM_FORM"] = $bVarsFromForm;
$arResult["FORM_DATA"] = array();
foreach($data as $key => $value)
{
	$arResult["FORM_DATA"]["~".$key] = $value;
	if(is_array($value))
	{
		foreach($value as $key1 => $value1)
		{
			if(is_array($value1))
			{
				foreach($value1 as $key2 => $value2)
					if(!is_array($value2))
						$value[$key1][$key2] = htmlspecialcharsbx($value2);
			}
			else
			{
				$value[$key1] = htmlspecialcharsbx($value1);
			}
		}
		$arResult["FORM_DATA"][$key] = $value;
	}
	else
	{
		$arResult["FORM_DATA"][$key] = htmlspecialcharsbx($value);
	}
}

$arResult['RAND_STRING'] = $this->randString();

$this->IncludeComponentTemplate();

if($arResult["ELEMENT_ID"])
	$APPLICATION->SetTitle($arResult["IBLOCK"]["ELEMENT_NAME"].": ".$arResult["ELEMENT_FIELDS"]["NAME"]);
else
	$APPLICATION->SetTitle($arResult["IBLOCK"]["ELEMENT_NAME"]);

$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], $arResult["~LIST_URL"]);
if($arResult["SECTION"])
{
	foreach($arResult["SECTION_PATH"] as $arPath)
	{
		$APPLICATION->AddChainItem($arPath["NAME"], $arPath["URL"]);
	}
}


?>