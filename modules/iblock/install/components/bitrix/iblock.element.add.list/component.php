<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
$this->setFrameMode(false);

if (CModule::IncludeModule("iblock"))
{
	if($arParams["IBLOCK_ID"] > 0)
		$bWorkflowIncluded = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "WORKFLOW") == "Y" && CModule::IncludeModule("workflow");
	else
		$bWorkflowIncluded = CModule::IncludeModule("workflow");

	if (!$bWorkflowIncluded)
	{
		if ($arParams["STATUS_NEW"] != "N" && $arParams["STATUS_NEW"] != "NEW") $arParams["STATUS_NEW"] = "ANY";
	}

	if(!is_array($arParams["STATUS"]))
	{
		if($arParams["STATUS"] === "INACTIVE")
			$arParams["STATUS"] = array("INACTIVE");
		else
			$arParams["STATUS"] = array("ANY");
	}

	$arGroups = $USER->GetUserGroupArray();

	// check whether current user has access to view list
	if ($USER->IsAdmin() || is_array($arGroups) && is_array($arParams["GROUPS"]) && count(array_intersect($arGroups, $arParams["GROUPS"])) > 0)
	{
		$bAllowAccess = true;
	}
	elseif ($USER->GetID() > 0 && $arParams["ELEMENT_ASSOC"] != "N")
	{
		$bAllowAccess = true;
	}
	else
	{
		$bAllowAccess = false;
	}

	// if user has access
	if ($bAllowAccess)
	{
		$arResult["CAN_EDIT"] = $arParams["ALLOW_EDIT"] == "Y" ? "Y" : "N";
		$arResult["CAN_DELETE"] = $arParams["ALLOW_DELETE"] == "Y" ? "Y" : "N";

		if ($USER->GetID())
		{
			$arResult["NO_USER"] = "N";

			// get list of iblock properties and list of iblock property ids
			$rsIBLockPropertyList = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
			$arIBlockPropertyList = array();
			$arPropertyIDs = array();
			$i = 0;
			while ($arProperty = $rsIBLockPropertyList->GetNext())
			{
				$arIBlockPropertyList[] = $arProperty;
				$arPropertyIDs[] = $arProperty["ID"];
			}

			// set starting filter value
			$arFilter = array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"], "SHOW_NEW" => "Y");
			// check type of user association to iblock elements and add user association to filter

			if ($arParams["ELEMENT_ASSOC"] == "PROPERTY_ID" && intval($arParams["ELEMENT_ASSOC_PROPERTY"]) > 0 && in_array($arParams["ELEMENT_ASSOC_PROPERTY"], $arPropertyIDs))
			{
				$arFilter["PROPERTY_".$arParams["ELEMENT_ASSOC_PROPERTY"]] = $USER->GetID();
			}
			else
			{
				$arFilter["CREATED_BY"] = $USER->GetID();
			}

			// deleteting element
			if (check_bitrix_sessid() && $_REQUEST["delete"] == "Y" && $arResult["CAN_DELETE"])
			{
				$arParams["ID"] = intval($_REQUEST["CODE"]);

				// try to get element with id, for user and for iblock
				$rsElement = CIBLockElement::GetList(array(), array_merge($arFilter, array("ID" => $arParams["ID"])));
				if ($arElement = $rsElement->GetNext())
				{
					// delete one
					$DB->StartTransaction();
					if(!CIBlockElement::Delete($arElement["ID"]))
					{
						$DB->Rollback();
					}
					else
					{
						$DB->Commit();
					}
				}
			}

			if ($bWorkflowIncluded)
			{
				$by = "c_sort";
				$order = "asc";
				$is_filtered = false;
				$rsWFStatus = CWorkflowStatus::GetList($by, $order, array("ACTIVE" => "Y"), $is_filtered);
				$arResult["WF_STATUS"] = array();
				while ($arStatus = $rsWFStatus->GetNext())
				{
					$arResult["WF_STATUS"][$arStatus["ID"]] = $arStatus["TITLE"];
				}
			}
			else
			{
				$arResult["ACTIVE_STATUS"] = array("Y" => GetMessage("IBLOCK_FORM_STATUS_ACTIVE"), "N" => GetMessage("IBLOCK_FORM_STATUS_INACTIVE"));
			}

			// get elements list using generated filter
			$rsIBlockElements = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter);

			$arResult["ELEMENTS_COUNT"] = $rsIBlockElements->SelectedRowsCount();
			//$page_split = intval(COption::GetOptionString("iblock", "RESULTS_PAGEN"));
			$arParams["NAV_ON_PAGE"] = intval($arParams["NAV_ON_PAGE"]);
			$arParams["NAV_ON_PAGE"] = $arParams["NAV_ON_PAGE"] > 0 ? $arParams["NAV_ON_PAGE"] : 10;

			$rsIBlockElements->NavStart($arParams["NAV_ON_PAGE"]);

			// get paging to component result
			if ($arParams["NAV_ON_PAGE"] < $arResult["ELEMENTS_COUNT"])
			{
				$arResult["NAV_STRING"] = $rsIBlockElements->GetPageNavString(GetMessage("IBLOCK_LIST_PAGES_TITLE"), "", true);
			}

			// get current page elements to component result
			$arResult["ELEMENTS"] = array();
			$bCanEdit = false;
			$bCanDelete = false;
			while ($arElement = $rsIBlockElements->NavNext(false))
			{
				$arElement = htmlspecialcharsex($arElement);
				if ($bWorkflowIncluded)
				{
					$PREVIOUS_ID = $arElement['ID'];
					$LAST_ID = CIBlockElement::WF_GetLast($arElement['ID']);
					if ($LAST_ID != $arElement["ID"])
					{
						$rsElement = CIBlockElement::GetByID($LAST_ID);
						$arElement = $rsElement->GetNext();
					}
					$arElement["ID"] = $PREVIOUS_ID;

					$arElement["CAN_EDIT"] = $arResult["CAN_EDIT"] == "Y" ? (in_array($arElement["WF_STATUS_ID"], $arParams["STATUS"]) == true ? "Y" : "N") : "N";
					$arElement["CAN_DELETE"] = $arResult["CAN_DELETE"] == "Y" ? (in_array($arElement["WF_STATUS_ID"], $arParams["STATUS"]) == true ? "Y" : "N") : "N";
				}
				elseif (in_array("INACTIVE", $arParams["STATUS"]) === true)
				{
					$arElement["CAN_EDIT"] = $arResult["CAN_EDIT"] == "Y" ? ($arElement["ACTIVE"] == "Y" ? "N" : "Y") : "N";
					$arElement["CAN_DELETE"] = $arResult["CAN_DELETE"] == "Y" ? ($arElement["ACTIVE"] == "Y" ? "N" : "Y") : "N";
				}
				else
				{
					$arElement["CAN_EDIT"] = $arResult["CAN_EDIT"];
					$arElement["CAN_DELETE"] = $arResult["CAN_DELETE"];
				}

				if (!$bCanEdit && $arResult["CAN_EDIT"] == "Y" && $arElement["CAN_EDIT"] == "Y")
				{
					$bCanEdit = true;
				}

				if (!$bCanDelete && $arResult["CAN_DELETE"] == "Y" && $arElement["CAN_DELETE"] == "Y")
				{
					$bCanDelete = true;
				}

				$arResult["ELEMENTS"][] = $arElement;
			}

			if ($arResult["CAN_EDIT"] == "Y" && !$bCanEdit) $arResult["CAN_EDIT"] = "N";
			if ($arResult["CAN_DELETE"] == "Y" && !$bCanDelete) $arResult["CAN_DELETE"] = "N";
		}
		else
		{
			$arResult["NO_USER"] = "Y";
		}

		$arResult["MESSAGE"] = htmlspecialcharsex($_REQUEST["strIMessage"]);

		$this->IncludeComponentTemplate();
	}
	else
	{
		$APPLICATION->AuthForm("");
	}
}
?>