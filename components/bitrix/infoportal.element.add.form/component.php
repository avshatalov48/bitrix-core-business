<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (CModule::IncludeModule("iblock"))
{
	$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
	if(strlen($arParams["EVENT_NAME"]) <= 0)
	$arParams["EVENT_NAME"] = "INFOPORTAL_ADD_ELEMENT";
	
	if($arParams["IBLOCK_ID"] > 0)
		$bWorkflowIncluded = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "WORKFLOW") == "Y" && CModule::IncludeModule("workflow");
	else
		$bWorkflowIncluded = CModule::IncludeModule("workflow");

	$arParams["ID"] = intval($_REQUEST["CODE"]);
	$arParams["MAX_FILE_SIZE"] = intval($arParams["MAX_FILE_SIZE"]);
	$arParams["PREVIEW_TEXT_USE_HTML_EDITOR"] = $arParams["PREVIEW_TEXT_USE_HTML_EDITOR"] === "Y" && CModule::IncludeModule("fileman");
	$arParams["DETAIL_TEXT_USE_HTML_EDITOR"] = $arParams["DETAIL_TEXT_USE_HTML_EDITOR"] === "Y" && CModule::IncludeModule("fileman");
	$arParams["RESIZE_IMAGES"] = $arParams["RESIZE_IMAGES"]==="Y";

	if(!is_array($arParams["PROPERTY_CODES"]))
	{
		$arParams["PROPERTY_CODES"] = array();
	}
	else
	{
		foreach($arParams["PROPERTY_CODES"] as $i=>$k)
			if(strlen($k) <= 0)
				unset($arParams["PROPERTY_CODES"][$i]);
	}
	$arParams["PROPERTY_CODES_REQUIRED"] = is_array($arParams["PROPERTY_CODES_REQUIRED"]) ? $arParams["PROPERTY_CODES_REQUIRED"] : array();
	foreach($arParams["PROPERTY_CODES_REQUIRED"] as $key => $value)
		if(strlen(trim($value)) <= 0)
			unset($arParams["PROPERTY_CODES_REQUIRED"][$key]);

	$arParams["USER_MESSAGE_ADD"] = trim($arParams["USER_MESSAGE_ADD"]);
	if(strlen($arParams["USER_MESSAGE_ADD"]) <= 0)
		$arParams["USER_MESSAGE_ADD"] = GetMessage("IBLOCK_USER_MESSAGE_ADD_DEFAULT");

	$arParams["USER_MESSAGE_EDIT"] = trim($arParams["USER_MESSAGE_EDIT"]);
	if(strlen($arParams["USER_MESSAGE_EDIT"]) <= 0)
		$arParams["USER_MESSAGE_EDIT"] = GetMessage("IBLOCK_USER_MESSAGE_EDIT_DEFAULT");

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

	if(!is_array($arParams["GROUPS"]))
		$arParams["GROUPS"] = array();

	$arGroups = $USER->GetUserGroupArray();

	// check whether current user can have access to add/edit elements
	if ($arParams["ID"] == 0)
	{
		$bAllowAccess = count(array_intersect($arGroups, $arParams["GROUPS"])) > 0 || $USER->IsAdmin();
	}
	else
	{
		// rights for editing current element will be in element get filter
		$bAllowAccess = $USER->GetID() > 0;
	}

	$arResult["ERRORS"] = array();

	if ($bAllowAccess)
	{
		// get iblock sections list
		$rsIBlockSectionList = CIBlockSection::GetList(
			array("left_margin"=>"asc"),
			array(
				"ACTIVE"=>"Y",
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
			),
			false,
			array("ID", "NAME", "DEPTH_LEVEL")
		);
		$arResult["SECTION_LIST"] = array();
		while ($arSection = $rsIBlockSectionList->GetNext())
		{
			$arSection["NAME"] = str_repeat(" . ", $arSection["DEPTH_LEVEL"]).$arSection["NAME"];
			$arResult["SECTION_LIST"][$arSection["ID"]] = array(
				"VALUE" => $arSection["NAME"]
			);
		}

		$COL_COUNT = intval($arParams["DEFAULT_INPUT_SIZE"]);
		if($COL_COUNT < 1)
			$COL_COUNT = 30;
		// customize "virtual" properties
		$arResult["PROPERTY_LIST"] = array();
		$arResult["PROPERTY_LIST_FULL"] = array(
			"NAME" => array(
				"PROPERTY_TYPE" => "S",
				"MULTIPLE" => "N",
				"COL_COUNT" => $COL_COUNT,
			),

			"TAGS" => array(
				"PROPERTY_TYPE" => "S",
				"MULTIPLE" => "N",
				"COL_COUNT" => $COL_COUNT,
			),

			"DATE_ACTIVE_FROM" => array(
				"PROPERTY_TYPE" => "S",
				"MULTIPLE" => "N",
				"USER_TYPE" => "DateTime",
			),

			"DATE_ACTIVE_TO" => array(
				"PROPERTY_TYPE" => "S",
				"MULTIPLE" => "N",
				"USER_TYPE" => "DateTime",
			),

			"IBLOCK_SECTION" => array(
				"PROPERTY_TYPE" => "L",
				"ROW_COUNT" => "8",
				"MULTIPLE" => $arParams["MAX_LEVELS"] == 1 ? "N" : "Y",
				"ENUM" => $arResult["SECTION_LIST"],
			),

			"PREVIEW_TEXT" => array(
				"PROPERTY_TYPE" => ($arParams["PREVIEW_TEXT_USE_HTML_EDITOR"]? "HTML": "T"),
				"MULTIPLE" => "N",
				"ROW_COUNT" => "5",
				"COL_COUNT" => $COL_COUNT,
			),
			"PREVIEW_PICTURE" => array(
				"PROPERTY_TYPE" => "F",
				"FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
				"MULTIPLE" => "N",
			),
			"DETAIL_TEXT" => array(
				"PROPERTY_TYPE" => ($arParams["DETAIL_TEXT_USE_HTML_EDITOR"]? "HTML": "T"),
				"MULTIPLE" => "N",
				"ROW_COUNT" => "5",
				"COL_COUNT" => $COL_COUNT,
			),
			"DETAIL_PICTURE" => array(
				"PROPERTY_TYPE" => "F",
				"FILE_TYPE" => "jpg, gif, bmp, png, jpeg",
				"MULTIPLE" => "N",
			),
		);

		// add them to edit-list
		foreach ($arResult["PROPERTY_LIST_FULL"] as $key => $arr)
		{
			if (in_array($key, $arParams["PROPERTY_CODES"])) $arResult["PROPERTY_LIST"][] = $key;
		}

		// get iblock property list
		$rsIBLockPropertyList = CIBlockProperty::GetList(array("sort"=>"asc", "name"=>"asc"), array("ACTIVE"=>"Y", "IBLOCK_ID"=>$arParams["IBLOCK_ID"]));
		while ($arProperty = $rsIBLockPropertyList->GetNext())
		{
			// get list of property enum values
			if ($arProperty["PROPERTY_TYPE"] == "L")
			{
				$rsPropertyEnum = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				$arProperty["ENUM"] = array();
				while ($arPropertyEnum = $rsPropertyEnum->GetNext())
				{
					$arProperty["ENUM"][$arPropertyEnum["ID"]] = $arPropertyEnum;
				}
			}

			if ($arProperty["PROPERTY_TYPE"] == "T")
			{
				if (empty($arProperty["COL_COUNT"])) $arProperty["COL_COUNT"] = "30";
				if (empty($arProperty["ROW_COUNT"])) $arProperty["ROW_COUNT"] = "5";
			}

			if(strlen($arProperty["USER_TYPE"]) > 0 )
			{
				$arUserType = CIBlockProperty::GetUserType($arProperty["USER_TYPE"]);
				if(array_key_exists("GetPublicEditHTML", $arUserType))
					$arProperty["GetPublicEditHTML"] = $arUserType["GetPublicEditHTML"];
				else
					$arProperty["GetPublicEditHTML"] = false;
			}
			else
			{
				$arProperty["GetPublicEditHTML"] = false;
			}

			// add property to edit-list
			if (in_array($arProperty["ID"], $arParams["PROPERTY_CODES"]))
				$arResult["PROPERTY_LIST"][] = $arProperty["ID"];

			$arResult["PROPERTY_LIST_FULL"][$arProperty["ID"]] = $arProperty;
		}

		// set starting filter value
		$arFilter = array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"], "SHOW_NEW" => "Y");

		// check type of user association to iblock elements and add user association to filter
		if ($arParams["ELEMENT_ASSOC"] == "PROPERTY_ID" && strlen($arParams["ELEMENT_ASSOC_PROPERTY"]) && is_array($arResult["PROPERTY_LIST_FULL"][$arParams["ELEMENT_ASSOC_PROPERTY"]]))
		{
			if ($USER->GetID())
				$arFilter["PROPERTY_".$arParams["ELEMENT_ASSOC_PROPERTY"]] = $USER->GetID();
			else
				$arFilter["ID"] = -1;
		}
		elseif ($USER->GetID())
		{
			$arFilter["CREATED_BY"] = $USER->GetID();
		}
		// additional bugcheck. situation can be found when property ELEMENT_ASSOC_PROPERTY does not exists and user is not registered
		else
		{
			$arFilter["ID"] = -1;
		}

		//check for access to current element
		if ($arParams["ID"] > 0)
		{
			if (empty($arFilter["ID"])) $arFilter["ID"] = $arParams["ID"];

			// get current iblock element

			//echo "<pre>"; print_r($arFilter); echo "</pre>";

			$rsIBlockElements = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter);

			if ($arElement = $rsIBlockElements->Fetch())
			{
				$bAllowAccess = true;

				if ($bWorkflowIncluded)
				{
					$LAST_ID = CIBlockElement::WF_GetLast($arElement['ID']);
					if ($LAST_ID != $arElement["ID"])
					{
						$rsElement = CIBlockElement::GetByID($LAST_ID);
						$arElement = $rsElement->Fetch();
					}

					if (!in_array($arElement["WF_STATUS_ID"], $arParams["STATUS"]))
					{
						echo ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
						$bAllowAccess = false;
					}
				}
				else
				{
					if (in_array("INACTIVE", $arParams["STATUS"]) === true && $arElement["ACTIVE"] !== "N")
					{
						echo ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
						$bAllowAccess = false;
					}
				}
			}
			else
			{
				echo ShowError(GetMessage("IBLOCK_ADD_ELEMENT_NOT_FOUND"));
				$bAllowAccess = false;
			}
		}
		elseif ($arParams["MAX_USER_ENTRIES"] > 0 && $USER->GetID())
		{
			$rsIBlockElements = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter);
			$elements_count = $rsIBlockElements->SelectedRowsCount();
			if ($elements_count >= $arParams["MAX_USER_ENTRIES"])
			{
				echo ShowError(GetMessage("IBLOCK_ADD_MAX_ENTRIES_EXCEEDED"));
				$bHideAuth = true;
				$bAllowAccess = false;
			}
		}
	}

	if ($bAllowAccess)
	{
		// process POST data
		if (check_bitrix_sessid() && (!empty($_REQUEST["iblock_submit"]) || !empty($_REQUEST["iblock_apply"])))
		{
			$SEF_URL = $_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"];
			$arResult["SEF_URL"] = $SEF_URL;

			$arProperties = $_REQUEST["PROPERTY"];

			$arUpdateValues = array();
			$arUpdatePropertyValues = array();

			// process properties list
			foreach ($arParams["PROPERTY_CODES"]  as $i => $propertyID)
			{
				$arPropertyValue = $arProperties[$propertyID];
				// check if property is a real property, or element field
				if (intval($propertyID) > 0)
				{
					// for non-file properties
					if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "F")
					{
						// for multiple properties
						if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y")
						{
							$arUpdatePropertyValues[$propertyID] = array();

							if (!is_array($arPropertyValue))
							{
								$arUpdatePropertyValues[$propertyID][] = $arPropertyValue;
							}
							else
							{
								foreach ($arPropertyValue as $key => $value)
								{
									if (
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "L" && intval($value) > 0
										||
										$arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "L" && !empty($value)
									)
									{
										$arUpdatePropertyValues[$propertyID][] = $value;
									}
								}
							}
						}
						// for single properties
						else
						{
							if ($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] != "L")
								$arUpdatePropertyValues[$propertyID] = $arPropertyValue[0];
							else
								$arUpdatePropertyValues[$propertyID] = $arPropertyValue;
						}
					}
					// for file properties
					else
					{
						$arUpdatePropertyValues[$propertyID] = array();
						foreach ($arPropertyValue as $key => $value)
						{
							$arFile = $_FILES["PROPERTY_FILE_".$propertyID."_".$key];
							$arFile["del"] = $_REQUEST["DELETE_FILE"][$propertyID][$key] == "Y" ? "Y" : "";
							$arUpdatePropertyValues[$propertyID][$key] = $arFile;

							if(($arParams["MAX_FILE_SIZE"] > 0) && ($arFile["size"] > $arParams["MAX_FILE_SIZE"]))
								$arResult["ERRORS"][] = GetMessage("IBLOCK_ERROR_FILE_TOO_LARGE");
						}

						if (count($arUpdatePropertyValues[$propertyID]) == 0)
							unset($arUpdatePropertyValues[$propertyID]);
					}
				}
				else
				{
					// for "virtual" properties
					if ($propertyID == "IBLOCK_SECTION")
					{
						if (!is_array($arProperties[$propertyID]))
							$arProperties[$propertyID] = array($arProperties[$propertyID]);
						$arUpdateValues[$propertyID] = $arProperties[$propertyID];

						if ($arParams["LEVEL_LAST"] == "Y" && is_array($arUpdateValues[$propertyID]))
						{
							foreach ($arUpdateValues[$propertyID] as $section_id)
							{
								$rsChildren = CIBlockSection::GetList(
									array("SORT" => "ASC"),
									array(
										"IBLOCK_ID" => $arParams["IBLOCK_ID"],
										"SECTION_ID" => $section_id,
									),
									false,
									array("ID")
								);
								if ($rsChildren->SelectedRowsCount() > 0)
								{
									$arResult["ERRORS"][] = GetMessage("IBLOCK_ADD_LEVEL_LAST_ERROR");
									break;
								}
							}
						}

						if ($arParams["MAX_LEVELS"] > 0 && count($arUpdateValues[$propertyID]) > $arParams["MAX_LEVELS"])
						{
							$arResult["ERRORS"][] = str_replace("#MAX_LEVELS#", $arParams["MAX_LEVELS"], GetMessage("IBLOCK_ADD_MAX_LEVELS_EXCEEDED"));
						}
					}
					else
					{
						if($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "F")
						{
							$arFile = $_FILES["PROPERTY_FILE_".$propertyID."_0"];
							$arFile["del"] = $_REQUEST["DELETE_FILE"][$propertyID][0] == "Y" ? "Y" : "";
							$arUpdateValues[$propertyID] = $arFile;
							if ($arParams["MAX_FILE_SIZE"] > 0 && $arFile["size"] > $arParams["MAX_FILE_SIZE"])
								$arResult["ERRORS"][] = GetMessage("IBLOCK_ERROR_FILE_TOO_LARGE");
						}
						elseif($arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "HTML")
						{
							if($propertyID == "DETAIL_TEXT")
								$arUpdateValues["DETAIL_TEXT_TYPE"] = "html";
							if($propertyID == "PREVIEW_TEXT")
								$arUpdateValues["PREVIEW_TEXT_TYPE"] = "html";
							$arUpdateValues[$propertyID] = $arProperties[$propertyID][0];
						}
						else
						{
							if($propertyID == "DETAIL_TEXT")
								$arUpdateValues["DETAIL_TEXT_TYPE"] = "text";
							if($propertyID == "PREVIEW_TEXT")
								$arUpdateValues["PREVIEW_TEXT_TYPE"] = "text";
							$arUpdateValues[$propertyID] = $arProperties[$propertyID][0];
						}
					}
				}
			}

			// check required properties
			foreach ($arParams["PROPERTY_CODES_REQUIRED"] as $key => $propertyID)
			{
				$bError = false;
				$propertyValue = intval($propertyID) > 0 ? $arUpdatePropertyValues[$propertyID] : $arUpdateValues[$propertyID];

				//Files check
				if ($arResult["PROPERTY_LIST_FULL"][$propertyID]['PROPERTY_TYPE'] == 'F')
				{
					//New element
					if ($arParams["ID"] <= 0)
					{
						$bError = true;
						if(is_array($propertyValue))
						{
							if(array_key_exists("tmp_name", $propertyValue) && array_key_exists("size", $propertyValue))
							{
								if($propertyValue['size'] > 0)
								{
									$bError = false;
								}
							}
							else
							{
								foreach ($propertyValue as $arFile)
								{
									if ($arFile['size'] > 0)
									{
										$bError = false;
										break;
									}
								}
							}
						}
					}
					//Element field
					elseif (intval($propertyID) <= 0)
					{
						if ($propertyValue['size'] <= 0)
						{
							if (intval($arElement[$propertyID]) <= 0 || $propertyValue['del'] == 'Y')
								$bError = true;
						}
					}
					//Element property
					else
					{
						$dbProperty = CIBlockElement::GetProperty(
							$arElement["IBLOCK_ID"],
							$arParams["ID"],
							"sort", "asc",
							array("ID"=>$propertyID)
						);

						$bCount = 0;
						while ($arProperty = $dbProperty->Fetch())
							$bCount++;

						foreach ($propertyValue as $arFile)
						{
							if ($arFile['size'] > 0)
							{
								$bCount++;
								break;
							}
							elseif ($arFile['del'] == 'Y')
							{
								$bCount--;
							}
						}

						$bError = $bCount <= 0;
					}
				}
				//multiple property
				elseif ($arResult["PROPERTY_LIST_FULL"][$propertyID]["MULTIPLE"] == "Y" || $arResult["PROPERTY_LIST_FULL"][$propertyID]["PROPERTY_TYPE"] == "L")
				{
					if(is_array($propertyValue))
					{
						$bError = true;
						foreach($propertyValue as $value)
						{
							if(strlen($value) > 0)
							{
								$bError = false;
								break;
							}
						}
					}
					elseif(strlen($propertyValue) <= 0)
					{
						$bError = true;
					}
				}
				//single
				elseif (is_array($propertyValue) && array_key_exists("VALUE", $propertyValue))
				{
					if(strlen($propertyValue["VALUE"]) <= 0)
						$bError = true;
				}
				elseif (!is_array($propertyValue))
				{
					if(strlen($propertyValue) <= 0)
						$bError = true;
				}

				if ($bError)
				{
					$arResult["ERRORS"][] = str_replace("#PROPERTY_NAME#", intval($propertyID) > 0 ? $arResult["PROPERTY_LIST_FULL"][$propertyID]["NAME"] : (!empty($arParams["CUSTOM_TITLE_".$propertyID]) ? $arParams["CUSTOM_TITLE_".$propertyID] : GetMessage("IBLOCK_FIELD_".$propertyID)), GetMessage("IBLOCK_ADD_ERROR_REQUIRED"));
				}
			}

			// check captcha
			if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0)
			{
				if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
				{
					$arResult["ERRORS"][] = GetMessage("IBLOCK_FORM_WRONG_CAPTCHA");
				}
			}

			if (count($arResult["ERRORS"]) == 0)
			{
				if ($arParams["ELEMENT_ASSOC"] == "PROPERTY_ID")
					$arUpdatePropertyValues[$arParams["ELEMENT_ASSOC_PROPERTY"]] = $USER->GetID();
				$arUpdateValues["MODIFIED_BY"] = $USER->GetID();

				$arUpdateValues["PROPERTY_VALUES"] = $arUpdatePropertyValues;

				if ($bWorkflowIncluded && strlen($arParams["STATUS_NEW"]) > 0)
				{
					$arUpdateValues["WF_STATUS_ID"] = $arParams["STATUS_NEW"];
					$arUpdateValues["ACTIVE"] = "Y";
				}
				else
				{
					if ($arParams["STATUS_NEW"] == "ANY")
					{
						$arUpdateValues["ACTIVE"] = "N";
					}
					elseif ($arParams["STATUS_NEW"] == "N")
					{
						$arUpdateValues["ACTIVE"] = "Y";
					}
					else
					{
						if ($arParams["ID"] <= 0 ) $arUpdateValues["ACTIVE"] = "N";
						//$arUpdateValues["ACTIVE"] = $arParams["ID"] > 0 ? "Y" : "N";
					}
				}

				// update existing element
				$oElement = new CIBlockElement();
				if ($arParams["ID"] > 0)
				{
					$sAction = "EDIT";

					$bFieldProps = array();
					foreach($arUpdateValues["PROPERTY_VALUES"] as $prop_id=>$v)
					{
						$bFieldProps[$prop_id]=true;
					}
					$dbPropV = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arParams["ID"], "sort", "asc", Array("ACTIVE"=>"Y"));
					while($arPropV = $dbPropV->Fetch())
					{
						if(!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
						{
							if($arPropV["MULTIPLE"] == "Y")
							{
								if(!array_key_exists($arPropV["ID"], $arUpdateValues["PROPERTY_VALUES"]))
									$arUpdateValues["PROPERTY_VALUES"][$arPropV["ID"]] = array();
								$arUpdateValues["PROPERTY_VALUES"][$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]] = array(
									"VALUE" => $arPropV["VALUE"],
									"DESCRIPTION" => $arPropV["DESCRIPTION"],
								);
							}
							else
							{
								$arUpdateValues["PROPERTY_VALUES"][$arPropV["ID"]] = array(
									"VALUE" => $arPropV["VALUE"],
									"DESCRIPTION" => $arPropV["DESCRIPTION"],
								);
							}
						}
					}

					if (!$res = $oElement->Update($arParams["ID"], $arUpdateValues, $bWorkflowIncluded, true, $arParams["RESIZE_IMAGES"]))
					{
						$arResult["ERRORS"][] = $oElement->LAST_ERROR;
					}
				}
				// add new element
				else
				{
					$arUpdateValues["IBLOCK_ID"] = $arParams["IBLOCK_ID"];

					// set activity start date for new element to current date. Change it, if ya want ;-)
					if (strlen($arUpdateValues["DATE_ACTIVE_FROM"]) <= 0)
					{
						$arUpdateValues["DATE_ACTIVE_FROM"] = ConvertTimeStamp(false, "FULL");
					}

					$sAction = "ADD";
					if (!$arParams["ID"] = $oElement->Add($arUpdateValues, $bWorkflowIncluded, true, $arParams["RESIZE_IMAGES"]))
					{
						$arResult["ERRORS"][] = $oElement->LAST_ERROR;
					}
					else
					{
						if($arParams["SEND_EMAIL"] == "Y")
						{
							if(!empty($arUpdateValues["IBLOCK_SECTION"]["0"]))
							{
								$SECTION_ID = $arUpdateValues["IBLOCK_SECTION"]["0"];
							}
							else 
							{
								$SECTION_ID = 0;
							}
						
   					   		$arFields = array(
								"NAME" => $arUpdateValues["NAME"],
								"SUBJECT" => $arParams["SUBJECT"],
								"EMAIL_TO"    => $arParams["EMAIL_TO"],
								"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
								"IBLOCK_ID" => $arParams["IBLOCK_ID"],
								"ID" => $arParams["ID"],
								"SECTION_ID" => $SECTION_ID,
                         	);
	                        if(!empty($arParams["EVENT_MESSAGE_ID"]))
							{
								foreach($arParams["EVENT_MESSAGE_ID"] as $v)
									if(IntVal($v) > 0)
										CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", IntVal($v));
							}
							else
								CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields);

							
						}
					}

					if (!empty($_REQUEST["iblock_apply"]) && strlen($SEF_URL) > 0)
					{
						if (strpos($SEF_URL, "?") === false) $SEF_URL .= "?edit=Y";
						elseif (strpos($SEF_URL, "edit=") === false) $SEF_URL .= "&edit=Y";
						$SEF_URL .= "&CODE=".$arParams["ID"];
					}
				}
			}

			// redirect to element edit form or to elements list
			if (count($arResult["ERRORS"]) == 0)
			{
				if (!empty($_REQUEST["iblock_submit"]))
				{
					if (strlen($arParams["LIST_URL"]) > 0)
					{
						$sRedirectUrl = $arParams["LIST_URL"];
					}
					else
					{
						if (strlen($SEF_URL) > 0)
						{
							$SEF_URL = str_replace("edit=Y", "", $SEF_URL);
							$SEF_URL = str_replace("?&", "?", $SEF_URL);
							$SEF_URL = str_replace("&&", "&", $SEF_URL);
							$sRedirectUrl = $SEF_URL;
						}
						else
						{
							$sRedirectUrl = $APPLICATION->GetCurPageParam("", array("edit", "CODE"), $get_index_page=false);
						}

					}
				}
				else
				{
					if (strlen($SEF_URL) > 0)
						$sRedirectUrl = $SEF_URL;
					else
						$sRedirectUrl = $APPLICATION->GetCurPageParam("edit=Y&CODE=".$arParams["ID"], array("edit", "CODE"), $get_index_page=false);
				}

				$sAction = $sAction == "ADD" ? "ADD" : "EDIT";
				$sRedirectUrl .= (strpos($sRedirectUrl, "?") === false ? "?" : "&")."strIMessage=";
				$sRedirectUrl .= urlencode($arParams["USER_MESSAGE_".$sAction]);

				//echo $sRedirectUrl;
				LocalRedirect($sRedirectUrl);
				exit();
			}
		}

		//prepare data for form

		$arResult["PROPERTY_REQUIRED"] = is_array($arParams["PROPERTY_CODES_REQUIRED"]) ? $arParams["PROPERTY_CODES_REQUIRED"] : array();

		if ($arParams["ID"] > 0)
		{
			// $arElement is defined before in elements rights check
			$rsElementSections = CIBlockElement::GetElementGroups($arElement["ID"]);
			$arElement["IBLOCK_SECTION"] = array();
			while ($arSection = $rsElementSections->GetNext())
			{
				$arElement["IBLOCK_SECTION"][] = array("VALUE" => $arSection["ID"]);
			}

			$arResult["ELEMENT"] = array();
			foreach($arElement as $key => $value)
			{
				$arResult["ELEMENT"]["~".$key] = $value;
				if(!is_array($value) && !is_object($value))
					$arResult["ELEMENT"][$key] = htmlspecialcharsbx($value);
				else
					$arResult["ELEMENT"][$key] = $value;
			}

			//Restore HTML if needed
			if(
				$arParams["DETAIL_TEXT_USE_HTML_EDITOR"]
				&& array_key_exists("DETAIL_TEXT", $arResult["ELEMENT"])
				&& strtolower($arResult["ELEMENT"]["DETAIL_TEXT_TYPE"]) == "html"
			)
				$arResult["ELEMENT"]["DETAIL_TEXT"] = $arResult["ELEMENT"]["~DETAIL_TEXT"];

			if(
				$arParams["PREVIEW_TEXT_USE_HTML_EDITOR"]
				&& array_key_exists("PREVIEW_TEXT", $arResult["ELEMENT"])
				&& strtolower($arResult["ELEMENT"]["PREVIEW_TEXT_TYPE"]) == "html"
			)
				$arResult["ELEMENT"]["PREVIEW_TEXT"] = $arResult["ELEMENT"]["~PREVIEW_TEXT"];


			//$arResult["ELEMENT"] = $arElement;

			// load element properties
			$rsElementProperties = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arElement["ID"], $by="sort", $order="asc");
			$arResult["ELEMENT_PROPERTIES"] = array();
			while ($arElementProperty = $rsElementProperties->Fetch())
			{
				if(!array_key_exists($arElementProperty["ID"], $arResult["ELEMENT_PROPERTIES"]))
					$arResult["ELEMENT_PROPERTIES"][$arElementProperty["ID"]] = array();

				if(is_array($arElementProperty["VALUE"]))
				{
					$htmlvalue = array();
					foreach($arElementProperty["VALUE"] as $k => $v)
					{
						if(is_array($v))
						{
							$htmlvalue[$k] = array();
							foreach($v as $k1 => $v1)
								$htmlvalue[$k][$k1] = htmlspecialcharsbx($v1);
						}
						else
						{
							$htmlvalue[$k] = htmlspecialcharsbx($v);
						}
					}
				}
				else
				{
					$htmlvalue = htmlspecialcharsbx($arElementProperty["VALUE"]);
				}

				$arResult["ELEMENT_PROPERTIES"][$arElementProperty["ID"]][] = array(
					"ID" => htmlspecialcharsbx($arElementProperty["ID"]),
					"VALUE" => $htmlvalue,
					"~VALUE" => $arElementProperty["VALUE"],
					"VALUE_ID" => htmlspecialcharsbx($arElementProperty["PROPERTY_VALUE_ID"]),
					"VALUE_ENUM" => htmlspecialcharsbx($arElementProperty["VALUE_ENUM"]),
				);
			}

			// process element property files
			$arResult["ELEMENT_FILES"] = array();
			foreach ($arResult["PROPERTY_LIST"] as $propertyID)
			{
				$arProperty = $arResult["PROPERTY_LIST_FULL"][$propertyID];
				if ($arProperty["PROPERTY_TYPE"] == "F")
				{
					$arValues = array();
					if (intval($propertyID) > 0)
					{
						foreach ($arResult["ELEMENT_PROPERTIES"][$propertyID] as $arProperty)
						{
							$arValues[] = $arProperty["VALUE"];
						}
					}
					else
					{
						$arValues[] = $arResult["ELEMENT"][$propertyID];
					}

					foreach ($arValues as $value)
					{
						if ($arFile = CFile::GetFileArray($value))
						{
							$arFile["IS_IMAGE"] = CFile::IsImage($arFile["FILE_NAME"], $arFile["CONTENT_TYPE"]);
							$arResult["ELEMENT_FILES"][$value] = $arFile;
						}
					}
				}
			}

			$bShowForm = true;
		}
		else
		{
			$bShowForm = true;
		}

		if ($bShowForm)
		{
			// prepare form data if some errors occured
			if (count($arResult["ERRORS"]) > 0)
			{
				//echo "<pre>",htmlspecialcharsbx(print_r($arUpdateValues, true)),"</pre>";
				foreach ($arUpdateValues as $key => $value)
				{
					if ($key == "IBLOCK_SECTION")
					{
						$arResult["ELEMENT"][$key] = array();
						if(!is_array($value))
						{
							$arResult["ELEMENT"][$key][] = array("VALUE" => htmlspecialcharsbx($value));
						}
						else
						{
							foreach ($value as $vkey => $vvalue)
							{
								$arResult["ELEMENT"][$key][$vkey] = array("VALUE" => htmlspecialcharsbx($vvalue));
							}
						}
					}
					elseif ($key == "PROPERTY_VALUES")
					{
						//Skip
					}
					elseif ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] == "F")
					{
						//Skip
					}
					elseif ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] == "HTML")
					{
						$arResult["ELEMENT"][$key] = $value;
					}
					else
					{
						$arResult["ELEMENT"][$key] = htmlspecialcharsbx($value);
					}
				}

				foreach ($arUpdatePropertyValues as $key => $value)
				{
					if ($arResult["PROPERTY_LIST_FULL"][$key]["PROPERTY_TYPE"] != "F")
					{
						$arResult["ELEMENT_PROPERTIES"][$key] = array();
						if(!is_array($value))
						{
							$value = array(
								array("VALUE" => $value),
							);
						}
						foreach($value as $vv)
						{
							if(is_array($vv))
							{
								if(array_key_exists("VALUE", $vv))
									$arResult["ELEMENT_PROPERTIES"][$key][] = array(
										"~VALUE" => $vv["VALUE"],
										"VALUE" => htmlspecialcharsbx($vv["VALUE"]),
									);
								else
									$arResult["ELEMENT_PROPERTIES"][$key][] = array(
										"~VALUE" => $vv,
										"VALUE" => $vv,
									);
							}
							else
							{
								$arResult["ELEMENT_PROPERTIES"][$key][] = array(
									"~VALUE" => $vv,
									"VALUE" => htmlspecialcharsbx($vv),
								);
							}
						}
					}
				}
			}

			// prepare captcha
			if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0)
			{
				$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
			}

			$arResult["MESSAGE"] = htmlspecialcharsex($_REQUEST["strIMessage"]);

			$this->IncludeComponentTemplate();
		}
	}
	if (!$bAllowAccess && !$bHideAuth)
	{
		//echo ShowError(GetMessage("IBLOCK_ADD_ACCESS_DENIED"));
		$APPLICATION->AuthForm("");
	}
}

?>