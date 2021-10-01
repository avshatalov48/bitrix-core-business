<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

class ListExportExcelComponent extends CBitrixComponent
{
	protected $listsPerm;
	protected $arIBlock = array();

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));
		Loc::loadMessages(__FILE__);
	}

	protected function checkModules()
	{
		if (!Loader::includeModule('lists'))
		{
			throw new SystemException(Loc::getMessage('CC_BLL_MODULE_NOT_INSTALLED'));
		}

		$this->arResult['BIZPROC'] = Loader::includeModule('bizproc');
		$this->arResult['DISK'] = Loader::includeModule('disk');
	}

	public function onPrepareComponentParams($params)
	{
		if(!Loader::includeModule('lists'))
			return $params;

		$this->arIBlock = CIBlock::GetArrayByID($params["IBLOCK_ID"]);
		$this->arResult["IBLOCK"] = $this->arIBlock;
		$this->arResult["IBLOCK_ID"] = $this->arIBlock["ID"];
		$this->arResult["GRID_ID"] = "lists_list_elements_".$this->arResult["IBLOCK_ID"];
		$this->arResult["FILTER_ID"] = "lists_list_elements_".$this->arResult["IBLOCK_ID"];
		$this->arResult["ANY_SECTION"] = isset($_GET["list_section_id"]) && $_GET["list_section_id"] == '';
		$sectionUpperUrl = CHTTP::urlAddParams(str_replace(array("#list_id#", "#section_id#", "#group_id#"),
			array($this->arResult["IBLOCK_ID"], 0, $params["SOCNET_GROUP_ID"]),
			$params['LIST_URL']), array('list_section_id' => ""));
		$this->arResult["SECTIONS"] = array(
			array(
				"NAME" => GetMessage("CC_BLL_UPPER_LEVEL"),
				"NAME_HTML" => '<a href="'.$sectionUpperUrl .'">'.GetMessage("CC_BLL_UPPER_LEVEL").'</a>',
			)
		);
		$this->arResult["SECTION_ID"] = false;
		$this->arResult["PARENT_SECTION_ID"] = false;
		$this->arResult["LIST_SECTIONS"] = array();
		$this->arResult["SECTION_PATH"] = array();
		if (isset($_GET["list_section_id"]))
			$sectionId = intval($_GET["list_section_id"]);
		else
			$sectionId = intval($params["SECTION_ID"]);

		$rsSections = CIBlockSection::GetList(
			array("left_margin" => "asc"),
			array("IBLOCK_ID" => $this->arIBlock["ID"], "GLOBAL_ACTIVE" => "Y", "CHECK_PERMISSIONS" => "Y")
		);
		while ($arSection = $rsSections->GetNext())
		{

			if($sectionId && !$this->arResult["SECTION"])
			{
				while(count($this->arResult["SECTION_PATH"]) && $arSection["DEPTH_LEVEL"] <= $this->arResult["SECTION_PATH"]
					[count($this->arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
					array_pop($this->arResult["SECTION_PATH"]);

				if(!count($this->arResult["SECTION_PATH"])|| $arSection["DEPTH_LEVEL"] > $this->arResult["SECTION_PATH"]
					[count($this->arResult["SECTION_PATH"])-1]["DEPTH_LEVEL"])
					array_push($this->arResult["SECTION_PATH"], $arSection);
			}

			if($arSection["ID"] == $sectionId)
			{
				$this->arResult["SECTION"] = $arSection;
				$this->arResult["SECTION_ID"] = intval($arSection["ID"]);
				$this->arResult["PARENT_SECTION_ID"] = $arSection["IBLOCK_SECTION_ID"];
			}

			$this->arResult["LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ",
					$arSection["DEPTH_LEVEL"]).$arSection["NAME"];
			$this->arResult["~LIST_SECTIONS"][$arSection["ID"]] = str_repeat(" . ",
					$arSection["DEPTH_LEVEL"]).$arSection["~NAME"];

			$sectionUrl = CHTTP::URN2URI(CHTTP::urlAddParams(str_replace(array("#list_id#", "#section_id#", "#group_id#"),
				array($this->arResult["IBLOCK_ID"], 0, $params["SOCNET_GROUP_ID"]),
				$params['LIST_URL']), array('list_section_id' => $arSection["ID"])));

			$this->arResult["SECTIONS"][$arSection["ID"]] = array(
				"ID" => $arSection["ID"],
				"NAME" => $arSection["NAME"],
				"LIST_URL" => str_replace(
					array("#list_id#", "#section_id#", "#group_id#"),
					array($arSection["IBLOCK_ID"], $arSection["ID"], $params["SOCNET_GROUP_ID"]),
					$params['LIST_URL']
				),
				"PARENT_ID" => intval($arSection["IBLOCK_SECTION_ID"]),
				"SECTION_URL" => $sectionUrl,
				"NAME_HTML_LABLE" => '<a href="'.$sectionUrl .'">'.htmlspecialcharsbx($arSection["~NAME"]).'</a>',
				"NAME_HTML" => '<a href="'.$sectionUrl .'">'.htmlspecialcharsbx(str_repeat(
							" . ", ($arSection["DEPTH_LEVEL"])).$arSection["~NAME"]).'</a>',
				"DEPTH_LEVEL" => intval($arSection["DEPTH_LEVEL"])
			);
		}

		$this->arResult["IS_SOCNET_GROUP_CLOSED"] = false;
		if (
			intval($params["~SOCNET_GROUP_ID"]) > 0
			&& CModule::IncludeModule("socialnetwork")
		)
		{
			$arSonetGroup = CSocNetGroup::GetByID(intval($params["~SOCNET_GROUP_ID"]));
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
				$this->arResult["IS_SOCNET_GROUP_CLOSED"] = true;
			}
		}

		return $params;
	}

	public function executeComponent()
	{
		try
		{
			$this->checkModules();
			$this->checkPermissions();

			$this->setFrameMode(false);
			global $APPLICATION;

			$this->createDataExcel();

			$APPLICATION->RestartBuffer();
			header("Content-Type: application/vnd.ms-excel");
			header("Content-Disposition: filename=list_".$this->arIBlock["ID"].".xls");
			$this->IncludeComponentTemplate();
			$r = $APPLICATION->EndBufferContentMan();
			echo $r;
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
			die();
		}
		catch (SystemException $exception)
		{
			ShowError($exception->getMessage());
		}
	}

	protected function checkPermissions()
	{
		global $USER;
		$this->listsPerm = CListPermissions::checkAccess(
			$USER,
			$this->arParams['IBLOCK_TYPE_ID'],
			$this->arResult['IBLOCK_ID'],
			$this->arParams['SOCNET_GROUP_ID']
		);
		if($this->listsPerm < 0)
		{
			switch($this->listsPerm)
			{
				case CListPermissions::WRONG_IBLOCK_TYPE:
					throw new SystemException(Loc::getMessage('CC_BLL_WRONG_IBLOCK_TYPE'));
				case CListPermissions::WRONG_IBLOCK:
					throw new SystemException(Loc::getMessage('CC_BLL_WRONG_IBLOCK'));
				case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
					throw new SystemException(Loc::getMessage('CC_BLL_LISTS_FOR_SONET_GROUP_DISABLED'));
				default:
					throw new SystemException(Loc::getMessage('CC_BLL_UNKNOWN_ERROR'));
			}
		}
		elseif(
			$this->listsPerm < CListPermissions::CAN_READ
			&& !(
				CIBlockRights::UserHasRightTo($this->arResult['IBLOCK_ID'],$this->arResult['IBLOCK_ID'],'element_read')
				|| CIBlockSectionRights::UserHasRightTo(
					$this->arResult["IBLOCK_ID"], $this->arResult["SECTION_ID"], "section_element_bind")
			)
		)
		{
			throw new SystemException(Loc::getMessage("CC_BLL_ACCESS_DENIED"));
		}

		if(!(
			!$this->arResult["IS_SOCNET_GROUP_CLOSED"]
			&& ($this->listsPerm > CListPermissions::CAN_READ
				|| CIBlockSectionRights::UserHasRightTo($this->arResult["IBLOCK_ID"],
					$this->arResult["SECTION_ID"], "element_read")
				|| CIBlockSectionRights::UserHasRightTo($this->arResult["IBLOCK_ID"],
					$this->arResult["SECTION_ID"], "section_element_bind")
			)
		))
		{
			throw new SystemException(Loc::getMessage("CC_BLL_ACCESS_DENIED"));
		}
	}

	protected function createDataExcel()
	{
		$iblockId = $this->arIBlock["ID"];
		$obList = new CList($iblockId);
		$gridOptions = new CGridOptions($this->arResult["GRID_ID"]);
		$gridColumns = $gridOptions->GetVisibleColumns();
		$gridSort = $gridOptions->GetSorting(array("sort" => array("name" => "asc")));

		$this->arResult["ELEMENTS_HEADERS"] = array();
		$arSelect = array("ID", "IBLOCK_ID");
		$arProperties = array();

		$this->arResult["FIELDS"] = $arListFields = $obList->GetFields();
		$filterable = array("ID" => "");
		$dateFilter = array();
		$customFilter = array();
		foreach ($arListFields as $fieldId => $arField)
		{
			if (!count($gridColumns) || in_array($fieldId, $gridColumns))
			{
				if (mb_substr($fieldId, 0, 9) == "PROPERTY_")
					$arProperties[] = $fieldId;
				else
					$arSelect[] = $fieldId;
			}

			if ($fieldId == "CREATED_BY")
				$arSelect[] = "CREATED_USER_NAME";

			if ($fieldId == "MODIFIED_BY")
				$arSelect[] = "USER_NAME";

			$this->arResult["ELEMENTS_HEADERS"][$fieldId] = $arField["NAME"];

			$preparedField = Bitrix\Lists\Field::prepareFieldDataForFilter($arField);
			$filterable[$preparedField["id"]] = $preparedField["filterable"];
			if(!empty($preparedField["dateFilter"]))
				$dateFilter[$preparedField["id"]] = true;
			if(!empty($preparedField["customFilter"]))
				$customFilter[$preparedField["id"]] = $preparedField["customFilter"];
		}

		if (!count($gridColumns) || in_array("IBLOCK_SECTION_ID", $gridColumns))
		{
			$arSelect[] = "IBLOCK_SECTION_ID";
			$this->arResult["ELEMENTS_HEADERS"]["IBLOCK_SECTION_ID"] = Loc::getMessage("CC_BLL_COLUMN_SECTION");
		}

		$arFilter = array();
		$filterOption = new \Bitrix\Main\UI\Filter\Options($this->arResult["FILTER_ID"]);
		$filterData = $filterOption->getFilter();
		global $DB;
		foreach($filterData as $key => $value)
		{
			if (is_array($value))
			{
				if (empty($value))
					continue;
			}
			elseif($value == '')
				continue;

			if(mb_substr($key, -5) == "_from")
			{
				$new_key = mb_substr($key, 0, -5);
				$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "more") ? ">" : ">=";
			}
			elseif(mb_substr($key, -3) == "_to")
			{
				$new_key = mb_substr($key, 0, -3);
				$op = (!empty($filterData[$new_key."_numsel"]) && $filterData[$new_key."_numsel"] == "less") ? "<" : "<=";
				if(array_key_exists($new_key, $dateFilter))
				{
					$dateFormat = $DB->dateFormatToPHP(Csite::getDateFormat());
					$dateParse = date_parse_from_format($dateFormat, $value);
					if(!mb_strlen($dateParse["hour"]) && !mb_strlen($dateParse["minute"]) && !mb_strlen($dateParse["second"]))
					{
						$timeFormat = $DB->dateFormatToPHP(CSite::getTimeFormat());
						$value .= " ".date($timeFormat, mktime(23, 59, 59, 0, 0, 0));
					}
				}
			}
			elseif($key == 'list_section_id')
			{
				$this->arResult["ANY_SECTION"] = false;
				$this->arResult["SECTION_ID"] = $value;
			}
			else
			{
				$op = "";
				$new_key = $key;
			}

			if($key == "CREATED_BY" || $key == "MODIFIED_BY")
			{
				if(!intval($value))
				{
					$userId = array();
					$userQuery = CUser::GetList(
						"ID",
						"ASC",
						array("NAME" => $value),
						array("FIELDS" => array("ID"))
					);
					while($user = $userQuery->fetch())
						$userId[] = $user["ID"];
					if(!empty($userId))
						$value = $userId;
				}
			}

			if(array_key_exists($new_key, $filterable))
			{
				if($op == "")
					$op = $filterable[$new_key];
				$arFilter[$op.$new_key] = $value;
			}

			if($key == "FIND")
			{
				$op = "?";
				$arFilter[$op."SEARCHABLE_CONTENT"] = $value;
			}
		}
		foreach($customFilter as $fieldId => $callback)
		{
			$filtered = false;
			call_user_func_array($callback, array(
				$this->arResult["FIELDS"][$fieldId],
				array(
					"VALUE" => $fieldId,
					"FILTER_ID" => $this->arResult["FILTER_ID"],
				),
				&$arFilter,
				&$filtered,
			));
		}

		$arFilter["IBLOCK_ID"] = $this->arIBlock["ID"];
		if(
			!$this->arResult["IS_SOCNET_GROUP_CLOSED"]
			&& (
				$this->listsPerm >= CListPermissions::IS_ADMIN
				|| CIBlockRights::UserHasRightTo($iblockId, $iblockId, "iblock_edit")
			)
		)
		{
			$arFilter["SHOW_NEW"] = "Y";
		}
		$arFilter["CHECK_PERMISSIONS"] = $this->listsPerm >= CListPermissions::CAN_READ ? "N" : "Y";
		if(!$this->arResult["ANY_SECTION"])
		{
			$listChildSection = array();
			CLists::getChildSection($this->arResult["SECTION_ID"], $this->arResult["SECTIONS"], $listChildSection);
			$arFilter["SECTION_ID"] = $listChildSection;
		}

		$this->arResult["EXCEL_COLUMN_NAME"] = array();
		$this->arResult["EXCEL_CELL_VALUE"] = array();
		$count = 0;
		$comments = in_array("COMMENTS", $gridColumns) && CModule::includeModule("forum");
		$listValues = array();

		$rsElements = CIBlockElement::GetList(
			$gridSort["sort"], $arFilter, false, false, $arSelect);
		$regexp = '/<a.*?href="(.*?)".*?>(.*?)<\/a>/';
		while($obElement = $rsElements->GetNextElement())
		{
			$data = $obElement->GetFields();
			if(!is_array($data))
				continue;

			if(!is_array($listValues[$data["ID"]]))
				$listValues[$data["ID"]] = array();
			foreach($data as $fieldId => $fieldValue)
				$listValues[$data["ID"]][$fieldId] = $fieldValue;

			if(!empty($arProperties))
			{
				$propertyValuesObject = \CIblockElement::getPropertyValues(
					$data["IBLOCK_ID"], array("ID" => $data["ID"], "SHOW_NEW" => "Y"));
				while($propertyValues = $propertyValuesObject->fetch())
				{
					foreach($propertyValues as $propertyId => $propertyValue)
					{
						if($propertyId == "IBLOCK_ELEMENT_ID")
							continue;
						$listValues[$data["ID"]]['PROPERTY_'.$propertyId] = $propertyValue;
					}
				}
			}

			$iblockSectionId = 0;
			if (
				!empty($data["IBLOCK_SECTION_ID"]) &&
				array_key_exists($data["IBLOCK_SECTION_ID"], $this->arResult["SECTIONS"])
			)
			{
				$iblockSectionId = $data["IBLOCK_SECTION_ID"];
			}

			foreach($this->arResult["FIELDS"] as $fieldId => $field)
			{
				switch($field["TYPE"])
				{
					case "S:DiskFile":
						$field["CONTROL_SETTINGS"]["MODE"] = "EXCEL_EXPORT";
						break;
					case "S:map_yandex":
						$field["CONTROL_SETTINGS"]["MODE"] = "CSV_EXPORT";
						break;
					case "S:ECrm":
						$field["CONTROL_SETTINGS"]["MODE"] = "EXCEL_EXPORT";
						break;
				}
				$valueKey = (mb_substr($fieldId, 0, 9) == "PROPERTY_") ? $fieldId : "~".$fieldId;
				$field["ELEMENT_ID"] = $data["ID"];
				$field["VALUE"] = $listValues[$data["ID"]][$valueKey];
				$field["LIST_FILE_URL"] = $this->arParams["~LIST_FILE_URL"];
				$field["SECTION_ID"] = $iblockSectionId;
				$data[$fieldId] = \Bitrix\Lists\Field::renderField($field);
			}

			if ($iblockSectionId)
			{
				$iblockSectionId = $data["IBLOCK_SECTION_ID"];
				$sectionName = array();
				$data["IBLOCK_SECTION_ID"] = $this->arResult["SECTIONS"][0]["NAME_HTML"];
				$parentId = $this->arResult["SECTIONS"][$iblockSectionId]["PARENT_ID"];
				if($parentId)
				{
					for($cnt = 1; $cnt < $this->arResult["SECTIONS"][$iblockSectionId]["DEPTH_LEVEL"]; $cnt++)
					{
						foreach($this->arResult["SECTIONS"] as $sectionId => $sectionData)
						{
							if($sectionId == $parentId)
							{
								$sectionName[] = $sectionData["NAME_HTML"];
								if($sectionData["PARENT_ID"])
									$parentId = $sectionData["PARENT_ID"];
							}
						}
					}
					krsort($sectionName);
					foreach($sectionName as $name)
						$data["IBLOCK_SECTION_ID"] .= "<br>".$name;
				}
				$data["IBLOCK_SECTION_ID"] .= "<br>".$this->arResult["SECTIONS"][$iblockSectionId]["NAME_HTML"];
			}

			if(in_array("BIZPROC", $gridColumns))
				$data["BIZPROC"] = $this->getArrayBizproc($data);

			if($comments)
				$countComments = $this->getCommentsProcess($data["ID"]);

			if (empty($gridColumns))
			{
				$gridColumns = array_keys($arListFields);
			}
			if (in_array("IBLOCK_SECTION_ID", $arSelect) && !in_array("IBLOCK_SECTION_ID", $gridColumns))
			{
				$gridColumns[] = "IBLOCK_SECTION_ID";
			}
			foreach ($gridColumns as $position => $id)
			{
				if($id == "COMMENTS")
				{
					if($comments)
						$data[$id] = $countComments;
					else
						continue;
				}
				if(is_array($data[$id]))
					continue;
				if(preg_match_all($regexp, $data[$id], $matches, PREG_SET_ORDER))
				{
					foreach($matches as $match)
					{
						$fullLink = CHTTP::URN2URI($match[1]);
						$data[$id] = str_replace($match[1], $fullLink, $data[$id]);
					}
				}
				$this->arResult["EXCEL_CELL_VALUE"][$count][$position] = is_array($data[$id]) ?
					implode('/', $data[$id]) : $data[$id];
				$this->arResult["EXCEL_COLUMN_NAME"][$position] = $this->arResult["ELEMENTS_HEADERS"][$id];
			}
			$count++;
		}
	}

	protected function getArrayBizproc($data = array())
	{
		if(!$this->arResult["BIZPROC"])
			return '';

		$currentUserId = $GLOBALS["USER"]->GetID();
		$html = "";
		if ($this->arResult["IBLOCK"]["BIZPROC"] == "Y")
		{
			$this->arResult["ELEMENTS_HEADERS"]["BIZPROC"] = Loc::getMessage("CC_BLL_COLUMN_BIZPROC");

			$arDocumentStates = CBPDocument::GetDocumentStates(
				BizprocDocument::generateDocumentComplexType(
					$this->arParams["IBLOCK_TYPE_ID"], $this->arResult["IBLOCK_ID"]),
				BizprocDocument::getDocumentComplexId($this->arParams["IBLOCK_TYPE_ID"], $data["ID"])
			);

			$userGroups = $GLOBALS["USER"]->GetUserGroupArray();
			if ($data["~CREATED_BY"] == $currentUserId)
				$userGroups[] = "Author";

			$arUserGroupsForBP = CUser::GetUserGroup($currentUserId);

			$ii = 0;
			foreach ($arDocumentStates as $workflowId => $workflowState)
			{
				$canViewWorkflow = BizprocDocument::canUserOperateDocument(
					CBPCanUserOperateOperation::ViewWorkflow,
					$currentUserId,
					$data["ID"],
					array(
						"IBlockPermission" => $this->listsPerm,
						"AllUserGroups" => $arUserGroupsForBP,
						"DocumentStates" => $arDocumentStates,
						"WorkflowId" => $workflowId,
					)
				);
				if (!$canViewWorkflow)
					continue;
				if ($workflowState["TEMPLATE_NAME"] <> '')
					$html .= "".$workflowState["TEMPLATE_NAME"].":\r\n";
				else
					$html .= "".(++$ii).":\r\n";

				$html .= "".($workflowState["STATE_TITLE"] <> '' ?
						$workflowState["STATE_TITLE"] : $workflowState["STATE_NAME"])."\r\n";
			}
		}

		return $html;
	}

	protected function getCommentsProcess($elementId)
	{
		$countComments = 0;

		$this->arResult["ELEMENTS_HEADERS"]["COMMENTS"] = Loc::getMessage("CC_BLL_COMMENTS");

		if(!$this->arResult["BIZPROC"] || !$elementId)
			return $countComments;

		$documentStates = CBPDocument::GetDocumentStates(
			BizprocDocument::generateDocumentComplexType(
				$this->arParams["IBLOCK_TYPE_ID"], $this->arResult["IBLOCK_ID"]),
			BizprocDocument::getDocumentComplexId($this->arParams["IBLOCK_TYPE_ID"], $elementId)
		);

		if(!empty($documentStates))
			$state = current($documentStates);
		else
			return $countComments;

		$query = CForumTopic::getList(array(), array("@XML_ID" => 'WF_'.$state["ID"]));
		while ($row = $query->fetch())
			$countComments = $row["POSTS"];

		return $countComments;
	}
}