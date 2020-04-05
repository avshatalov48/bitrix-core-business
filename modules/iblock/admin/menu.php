<?
if(!is_object($GLOBALS["USER_FIELD_MANAGER"]))
	return false;

if (!CModule::IncludeModule("iblock"))
	return false;

foreach (GetModuleEvents("iblock", "OnBeforeIBlockAdminMenu", true) as $arEvent)
{
	return ExecuteModuleEventEx($arEvent, array());
}

IncludeModuleLangFile(__FILE__);

function _get_elements_menu($arType, $arIBlock, $SECTION_ID)
{
	$urlElementAdminPage = CIBlock::GetAdminElementListLink($arIBlock["ID"], array("menu" => null));

	$SECTION_ID = intval($SECTION_ID);
	if($SECTION_ID <= 0)
	{
		return array(
			"text" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
			"url" => htmlspecialcharsbx($urlElementAdminPage."&find_el_y=Y"),
			"more_url" => array(
				$urlElementAdminPage."&find_el_y=Y",
				$urlElementAdminPage."&find_section_section=-1",
				$urlElementAdminPage."&find_section_section=0",
				"iblock_element_edit.php?".$arIBlock["URL_PART"]."&find_section_section=-1",
				"iblock_element_edit.php?".$arIBlock["URL_PART"]."&find_section_section=0",
				"iblock_history_list.php?".$arIBlock["URL_PART"]."&find_section_section=-1",
				"iblock_start_bizproc.php?document_type=iblock_".$arIBlock["ID"],
			),
			"title" => GetMessage("IBLOCK_MENU_ALL_EL"),
			"page_icon" => "iblock_page_icon_elements",
			"skip_chain" => true,
			"items_id" => "menu_iblock_".$arType["ID"]."_".$arIBlock["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);
	}
	else
	{
		return array(
			"text" => htmlspecialcharsex($arIBlock["ELEMENTS_NAME"]),
			"url" => htmlspecialcharsbx($urlElementAdminPage."&find_section_section=".$SECTION_ID),
			"more_url" => array(
				"iblock_element_edit.php?".$arIBlock["URL_PART"]."&find_section_section=".$SECTION_ID,
				$urlElementAdminPage."&find_section_section=".$SECTION_ID,
				"iblock_history_list.php?".$arIBlock["URL_PART"]."&find_section_section=".$SECTION_ID,
			),
			"title" => GetMessage("IBLOCK_MENU_SEC_EL"),
			"page_icon" => "iblock_page_icon_elements",
			"skip_chain" => true,
			"items_id" => "menu_iblock_el_".$arType["ID"]."_".$arIBlock["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);
	}
}

function _get_other_elements_menu($arType, $arIBlock, $arSection, &$more_url)
{
	$urlElementAdminPage = CIBlock::GetAdminElementListLink($arIBlock["ID"], array("menu" => null));
	$more_url[] = $urlElementAdminPage."&find_section_section=".(int)$arSection["ID"];

	if (($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1)
	{
		$rsSections = CIBlockSection::GetList(
			array("left_margin"=>"ASC"),
			array(
				"IBLOCK_ID" => $arIBlock["ID"],
				"SECTION_ID" => $arSection["ID"],
			),
			false,
			array("ID", "IBLOCK_SECTION_ID", "NAME", "LEFT_MARGIN", "RIGHT_MARGIN")
		);
		while($arSubSection = $rsSections->Fetch())
			_get_other_elements_menu($arType, $arIBlock, $arSubSection, $more_url);
	}
}

function _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL, $SECTION_ID, $arSectionsChain = false)
{
	//Determine opened sections
	if($arSectionsChain === false)
	{
		$arSectionsChain = array();
		if(isset($_REQUEST['admin_mnu_menu_id']))
		{
			$menu_id = "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/";
			if(strncmp($_REQUEST['admin_mnu_menu_id'], $menu_id, strlen($menu_id)) == 0)
			{
				$rsSections = CIBlockSection::GetNavChain($arIBlock["ID"], substr($_REQUEST['admin_mnu_menu_id'], strlen($menu_id)), array('ID', 'IBLOCK_ID'));
				while($arSection = $rsSections->Fetch())
					$arSectionsChain[$arSection["ID"]] = $arSection["ID"];
			}
		}
		if(
			isset($_REQUEST["find_section_section"])
			&& (int)$_REQUEST["find_section_section"] > 0
			&& isset($_REQUEST["IBLOCK_ID"])
			&& $_REQUEST["IBLOCK_ID"] == $arIBlock["ID"]
		)
		{
			$rsSections = CIBlockSection::GetNavChain($arIBlock["ID"], $_REQUEST["find_section_section"], array('ID', 'IBLOCK_ID'));
			while($arSection = $rsSections->Fetch())
				$arSectionsChain[$arSection["ID"]] = $arSection["ID"];
		}
	}

	$urlSectionAdminPage = CIBlock::GetAdminSectionListLink($arIBlock["ID"], array("menu" => null));

	$arSections = array();

	if(CIBlock::GetAdminListMode($arIBlock["ID"]) == 'S')
		$arSections[] = _get_elements_menu($arType, $arIBlock, $SECTION_ID);

	$rsSections = CIBlockSection::GetList(
		array("left_margin"=>"ASC"),
		array(
			"IBLOCK_ID" => $arIBlock["ID"],
			"SECTION_ID" => $SECTION_ID,
		),
		false,
		array("ID", "IBLOCK_SECTION_ID", "NAME", "LEFT_MARGIN", "RIGHT_MARGIN")
	);
	$sectionCount = 0;
	$limit = COption::GetOptionInt("iblock", "iblock_menu_max_sections");
	while($arSection = $rsSections->Fetch())
	{
		if(($limit > 0) && ($sectionCount >= $limit))
		{
			$arSections[] = array(
				"text" => GetMessage("IBLOCK_MENU_ALL_OTH"),
				"url" => htmlspecialcharsbx($urlSectionAdminPage."&find_section_section=".(int)$arSection["IBLOCK_SECTION_ID"]),
				"more_url" => array(
					$urlSectionAdminPage."&find_section_section=".(int)$arSection["IBLOCK_SECTION_ID"],
					$urlSectionAdminPage,
					"iblock_section_edit.php?".$arIBlock["URL_PART"],
					"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"],
					"iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"],
				),
				"title" => GetMessage("IBLOCK_MENU_ALL_OTH_TITLE"),
				"icon" => "iblock_menu_icon_sections",
				"page_icon" => "iblock_page_icon_sections",
				"skip_chain" => true,
				"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"],
				"module_id" => "iblock",
				"items" => array()
			);
			_get_other_elements_menu($arType, $arIBlock, $arSection, $arSections[0]["more_url"]);

			break;
		}
		$arSectionTmp = array(
			"text" => htmlspecialcharsex($arSection["NAME"]),
			"url" => htmlspecialcharsbx($urlSectionAdminPage."&find_section_section=".$arSection["ID"]),
			"more_url" => array(
				$urlSectionAdminPage."&find_section_section=".$arSection["ID"],
				"iblock_section_edit.php?".$arIBlock["URL_PART"]."&ID=".$arSection["ID"],
				"iblock_section_edit.php?".$arIBlock["URL_PART"]."&ID=0&find_section_section=".$arSection["ID"],
				"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=".$arSection["ID"],
				"iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=".$arSection["ID"],
			),
			"title" => htmlspecialcharsex($arSection["NAME"]),
			"icon" => "iblock_menu_icon_sections",
			"page_icon" => "iblock_page_icon_sections",
			"skip_chain" => true,
			"dynamic" =>
				(CIBlock::GetAdminListMode($arIBlock["ID"]) == 'S') ||
				(($arSection["RIGHT_MARGIN"] - $arSection["LEFT_MARGIN"]) > 1),
			"items_id" => "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"],
			"module_id" => "iblock",
			"items" => array(),
		);

		if(array_key_exists($arSection["ID"], $arSectionsChain))
		{
			$arSectionTmp["items"] = _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL+1, $arSection["ID"], $arSectionsChain);
		}
		elseif(method_exists($GLOBALS["adminMenu"], "IsSectionActive"))
		{
			if($GLOBALS["adminMenu"]->IsSectionActive("menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"]."/".$arSection["ID"]))
				$arSectionTmp["items"] = _get_sections_menu($arType, $arIBlock, $DEPTH_LEVEL+1, $arSection["ID"], $arSectionsChain);
		}

		$arSections[] = $arSectionTmp;
		$sectionCount++;
	}

	while($arSection = $rsSections->Fetch())
	{
		$urlElementAdminPage = CIBlock::GetAdminElementListLink($arIBlock["ID"], array("menu" => null));
		$arSections[0]["more_url"][] = $urlElementAdminPage."&find_section_section=".(int)$arSection["ID"];
	}

	return $arSections;
}

function _get_iblocks_menu($arType)
{
	global $adminMenu;

	$arIBlocks = array();
	foreach($arType["IBLOCKS"]["S"] as $arIBlock)
	{
		$items_id = "menu_iblock_/".$arType["ID"]."/".$arIBlock["ID"];

		if($arType["SECTIONS"]=='Y')
		{
			if(isset($_REQUEST["IBLOCK_ID"]) && $_REQUEST["IBLOCK_ID"] == $arIBlock["ID"])
			{
				$arItems = _get_sections_menu($arType, $arIBlock, 1, 0);
			}
			elseif(isset($_REQUEST['admin_mnu_menu_id']) && strpos($_REQUEST['admin_mnu_menu_id'], $items_id) !== false)
			{
				$arItems = _get_sections_menu($arType, $arIBlock, 1, 0);
			}
			elseif(method_exists($adminMenu, "IsSectionActive"))
			{
				if(
					$adminMenu->IsSectionActive("menu_iblock_/".$arType["ID"])
					&& $adminMenu->IsSectionActive($items_id)
				)
					$arItems = _get_sections_menu($arType, $arIBlock, 1, 0);
				else
					$arItems = array();
			}
			else
			{
				$arItems = _get_sections_menu($arType, $arIBlock, 1, 0);
			}

			$urlSectionAdminPage = CIBlock::GetAdminSectionListLink($arIBlock["ID"], array("menu" => null));
			$arMenuItem = array(
				"text" => $arIBlock["NAME~"],
				"url" => htmlspecialcharsbx($urlSectionAdminPage."&find_section_section=0"),
				"more_url" => array(
					$urlSectionAdminPage."&find_section_section=0",
					$urlSectionAdminPage."&find_section_section=-1",
					"iblock_section_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=-1",
					"iblock_section_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=0",
					"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=-1",
					"iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=0",
					"iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"]."&find_section_section=-1",
					"iblock_start_bizproc.php?document_type=iblock_".$arIBlock["ID"],
				),
				"title" => $arIBlock["NAME~"],
				"icon" => "iblock_menu_icon_iblocks",
				"page_icon" => "iblock_page_icon_iblocks",
				"skip_chain" => true,
				"module_id" => "iblock",
				"items_id" => $items_id,
				"dynamic" => true,
				"items" => $arItems,
			);

			if (empty($arItems))
			{
				$arMenuItem["more_url"][] = "iblock_element_edit.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"];
				$arMenuItem["more_url"][] = "iblock_history_list.php?IBLOCK_ID=".$arIBlock["ID"]."&type=".$arType["ID"];
			}

			$arIBlocks[] = $arMenuItem;
		}
		else
		{
			$urlElementAdminPage = CIBlock::GetAdminElementListLink($arIBlock["ID"], array("menu" => null));
			$arIBlocks[] = array(
				"text" => $arIBlock["NAME~"],
				"url" => htmlspecialcharsbx($urlElementAdminPage),
				"more_url" => array(
					"iblock_element_edit.php?".$arIBlock["URL_PART"],
					"iblock_history_list.php?".$arIBlock["URL_PART"],
					$urlElementAdminPage,
				),
				"title" => $arIBlock["NAME~"],
				"items_id" => $items_id,
				"icon" => "iblock_menu_icon_iblocks",
				"page_icon" => "iblock_page_icon_iblocks",
				"skip_chain" => true,
				"module_id" => "iblock",
				"items" => array(),
			);
		}
	}
	return $arIBlocks;
}

function _get_iblocks_admin_menu($arType)
{
	$arIBlocks = array();
	foreach($arType["IBLOCKS"]["X"] as $arIBlock)
	{
		$arIBlockTmp = array(
			"text" => $arIBlock["NAME~"],
			"url" => "iblock_edit.php?type=".$arType["ID"]."&lang=".LANGUAGE_ID."&ID=".$arIBlock["ID"]."&admin=Y",
			"more_url" => array(
				"iblock_convert.php?lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
				"iblock_edit.php?type=".$arType["ID"]."&lang=".LANGUAGE_ID."&ID=".$arIBlock["ID"]."&admin=Y",
				"iblock_bizproc_workflow_edit.php?document_type=iblock_".$arIBlock["ID"]."&lang=".LANGUAGE_ID,
				"iblock_bizproc_workflow_admin.php?document_type=iblock_".$arIBlock["ID"]."&lang=".LANGUAGE_ID,
				"iblock_edit.php?".$arIBlock["URL_PART"]."&admin=Y",
				"iblock_property_admin.php?IBLOCK_ID=".$arIBlock["ID"]."&lang=".LANGUAGE_ID."&admin=Y",
				"iblock_edit_property.php?IBLOCK_ID=".$arIBlock["ID"]."&lang=".LANGUAGE_ID."&admin=Y",
			),
			"title" => $arIBlock["NAME~"],
			"items_id" => "menu_iblock_admin_/".$arType["ID"]."/".$arIBlock["ID"],
			"icon" => "iblock_menu_icon_settings",
			"page_icon" => "iblock_page_icon_settings",
			"skip_chain" => true,
			"category" => "global_menu_settings",
			"module_id" => "iblock",
			"items" => false,
		);
		$arIBlocks[] = $arIBlockTmp;
	}
	return $arIBlocks;
}

function _get_types_admin_menu($arTypes, $bUserIsAdmin)
{
	$arResult = array();
	$obt_index = 0;
	foreach($arTypes as $arType)
	{
		if($bUserIsAdmin || !empty($arType["IBLOCKS"]["X"]))
		{
			$arResult[] = array(
				"text" => $arType["NAME~"],
				"url" => "iblock_admin.php?type=".$arType["ID"]."&amp;lang=".LANGUAGE_ID."&amp;admin=Y",
				"more_url" => array(
					"iblock_admin.php?type=".$arType["ID"]."&lang=".LANGUAGE_ID."&admin=Y",
					"iblock_edit.php?type=".$arType["ID"]."&lang=".LANGUAGE_ID."&admin=Y",
				),
				"title" => $arType["NAME~"],
				"parent_menu" => "global_menu_content",
				"sort" => 200 + $obt_index,
				"icon" => "iblock_menu_icon_types",
				"page_icon" => "iblock_page_icon_settings",
				"module_id" => "iblock",
				"items_id" => "menu_iblock_admin_/".$arType["ID"],
				"dynamic" => true,
				"items" => _get_iblocks_admin_menu($arType),
			);
			$obt_index++;
		}
	}
	return $arResult;
}

$aMenu = array();

//Read all necessary data from database

global $USER;
$bUserIsAdmin = $USER->IsAdmin();

$arTypes = array();
$rsTypes = CIBlockType::GetList(array("SORT"=>"ASC"));
while($arType = $rsTypes->Fetch())
{
	$arType = CIBlockType::GetByIDLang($arType["ID"], LANGUAGE_ID);
	$arTypes[$arType["ID"]] = array(
		"ID" => $arType["ID"],
		"NAME" => $arType["NAME"],
		"NAME~" => $arType["NAME"],
		"ELEMENT_NAME" => $arType["ELEMENT_NAME"],
		"SECTIONS" => $arType["SECTIONS"],
		"IBLOCKS" => array(
			"S" => array(),
			"W" => array(),
			"X" => array(),
		),
	);
}

$bHasXRight = false;
$bHasWRight = false;
$bHasSRight = false;
$bHasERight = false;

if ($bUserIsAdmin)
{
	$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"));
	while($arIBlock = $rsIBlocks->Fetch())
	{
		if(!$arIBlock["ELEMENTS_NAME"])
			$arIBlock["ELEMENTS_NAME"] = $arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["ELEMENT_NAME"]?: GetMessage("IBLOCK_MENU_ELEMENTS");

		$arItem = array(
			"ID" => $arIBlock["ID"],
			"NAME" => $arIBlock["NAME"],
			"NAME~" => htmlspecialcharsex($arIBlock["NAME"]),
			"ELEMENTS_NAME" => $arIBlock["ELEMENTS_NAME"],
			"URL_PART" => "type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
		);
		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["X"][] = $arItem;
		$bHasXRight = true;
		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["W"][] = $arItem;
		$bHasWRight = true;
		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["S"][] = $arItem;
		$bHasSRight = true;
		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["E"][] = $arItem;
		$bHasERight = true;
	}
}
else
{
	$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("MIN_PERMISSION" => "X"));
	while($arIBlock = $rsIBlocks->Fetch())
	{
		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["X"][] = array(
			"ID" => $arIBlock["ID"],
			"NAME" => $arIBlock["NAME"],
			"NAME~" => htmlspecialcharsex($arIBlock["NAME"]),
			"ELEMENTS_NAME" => $arIBlock["ELEMENTS_NAME"],
			"URL_PART" => "type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
		);
		$bHasXRight = true;
	}

	$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("MIN_PERMISSION" => "U"));
	while($arIBlock = $rsIBlocks->Fetch())
	{
		if(!$arIBlock["ELEMENTS_NAME"])
			$arIBlock["ELEMENTS_NAME"] = $arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["ELEMENT_NAME"]?: GetMessage("IBLOCK_MENU_ELEMENTS");

		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["W"][] = array(
			"ID" => $arIBlock["ID"],
			"NAME" => $arIBlock["NAME"],
			"NAME~" => htmlspecialcharsex($arIBlock["NAME"]),
			"ELEMENTS_NAME" => $arIBlock["ELEMENTS_NAME"],
			"URL_PART" => "type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
		);
		$bHasWRight = true;
	}

	$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("MIN_PERMISSION" => "S"));
	while($arIBlock = $rsIBlocks->Fetch())
	{
		if(!$arIBlock["ELEMENTS_NAME"])
			$arIBlock["ELEMENTS_NAME"] = $arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["ELEMENT_NAME"]?: GetMessage("IBLOCK_MENU_ELEMENTS");

		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["S"][] = array(
			"ID" => $arIBlock["ID"],
			"NAME" => $arIBlock["NAME"],
			"NAME~" => htmlspecialcharsex($arIBlock["NAME"]),
			"ELEMENTS_NAME" => $arIBlock["ELEMENTS_NAME"],
			"URL_PART" => "type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
		);
		$bHasSRight = true;
	}

	$rsIBlocks = CIBlock::GetList(array("SORT"=>"asc", "NAME"=>"ASC"), array("OPERATION" => "iblock_export"));
	while($arIBlock = $rsIBlocks->Fetch())
	{
		if(!$arIBlock["ELEMENTS_NAME"])
			$arIBlock["ELEMENTS_NAME"] = $arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["ELEMENT_NAME"]?: GetMessage("IBLOCK_MENU_ELEMENTS");

		$arTypes[$arIBlock["IBLOCK_TYPE_ID"]]["IBLOCKS"]["E"][] = array(
			"ID" => $arIBlock["ID"],
			"NAME" => $arIBlock["NAME"],
			"NAME~" => htmlspecialcharsex($arIBlock["NAME"]),
			"ELEMENTS_NAME" => $arIBlock["ELEMENTS_NAME"],
			"URL_PART" => "type=".$arIBlock["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arIBlock["ID"],
		);
		$bHasERight = true;
	}
}

//Build menu items
$obt_index = 0;
foreach($arTypes as $type_id => $arType)
{
	if(!empty($arType["IBLOCKS"]["S"]))
	{
		$aMenu[] = array(
			"text" => $arType["NAME~"],
			"url" => "iblock_admin.php?type=".$type_id."&amp;lang=".LANGUAGE_ID."&amp;admin=N",
			"more_url" => array(
				"iblock_admin.php?type=".$type_id."&lang=".LANGUAGE_ID."&admin=N",
			),
			"title" => $arType["NAME~"],
			"parent_menu" => "global_menu_content",
			"sort" => 200 + ($obt_index++),
			"icon" => "iblock_menu_icon_types",
			"page_icon" => "iblock_page_icon_types",
			"module_id" => "iblock",
			"items_id" => "menu_iblock_/".$type_id,
			"dynamic" => true,
			"items" => _get_iblocks_menu($arType),
		);
	}
}

if($bUserIsAdmin || $bHasWRight || $bHasXRight || $bHasSRight || $bHasERight)
{
	$arItems = array();
	if($bHasXRight || $bHasERight)
	{
		$arItems[] = array(
			"text" => GetMessage("IBLOCK_MENU_EXPORT"),
			"title" => GetMessage("IBLOCK_MENU_EXPORT_ALT"),
			"url" => "iblock_data_export.php?lang=".LANGUAGE_ID,
			"items_id" => "iblock_export",
			"module_id" => "iblock",
			"items" => array(
				array(
					"text" => "CSV",
					"url" => "iblock_data_export.php?lang=".LANGUAGE_ID,
					"module_id" => "iblock",
					"more_url" => array("iblock_data_export.php"),
				),
				array(
					"text" => "XML",
					"url" => "iblock_xml_export.php?lang=".LANGUAGE_ID,
					"module_id" => "iblock",
					"more_url" => array("iblock_xml_export.php"),
				),
			),
		);
	}

	if($bUserIsAdmin)
	{
		$arItems[] = array(
			"text" => GetMessage("IBLOCK_MENU_IMPORT"),
			"title" => GetMessage("IBLOCK_MENU_IMPORT_ALT"),
			"url" => "iblock_data_import.php?lang=".LANGUAGE_ID,
			"items_id" => "iblock_import",
			"module_id" => "iblock",
			"items" => array(
				array(
					"text" => "CSV",
					"url" => "iblock_data_import.php?lang=".LANGUAGE_ID,
					"module_id" => "iblock",
					"more_url" => array("iblock_data_import.php"),
				),
				array(
					"text" => "XML",
					"url" => "iblock_xml_import.php?lang=".LANGUAGE_ID,
					"module_id" => "iblock",
					"more_url" => array("iblock_xml_import.php"),
				),
			),
		);
	}
	elseif($bHasWRight)
	{
		$arItems[] = array(
			"text" => GetMessage("IBLOCK_MENU_IMPORT"),
			"title" => GetMessage("IBLOCK_MENU_IMPORT_ALT"),
			"url" => "iblock_data_import.php?lang=".LANGUAGE_ID,
			"items_id" => "iblock_import",
			"module_id" => "iblock",
			"items" => array(
				array(
					"text" => "CSV",
					"url" => "iblock_data_import.php?lang=".LANGUAGE_ID,
					"module_id" => "iblock",
					"more_url" => array("iblock_data_import.php"),
				),
			),
		);
	}

	if($bUserIsAdmin || $bHasXRight)
	{
		$arItems[] = array(
			"text" => GetMessage("IBLOCK_MENU_ITYPE"),
			"url" => "iblock_type_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("iblock_type_edit.php"),
			"module_id" => "iblock",
			"title" => GetMessage("IBLOCK_MENU_ITYPE_TITLE"),
			"items_id" => "iblock_admin",
			"items" => _get_types_admin_menu($arTypes, $bUserIsAdmin),
		);
	}

	if($bUserIsAdmin)
	{
		$arItems[] = array(
			"text" => GetMessage("IBLOCK_MENU_REINDEX"),
			"url" => "iblock_reindex_admin.php?lang=".LANGUAGE_ID,
			"more_url" => array("iblock_reindex.php", "iblock_reindex_admin.php"),
			"module_id" => "iblock",
			"title" => GetMessage("IBLOCK_MENU_REINDEX_TITLE"),
		);
	}

	$adminToolsMenu = array();
	if ($bHasSRight)
	{
		$adminToolsMenu[] = array(
			"text" => GetMessage('IBLOCK_MENU_ADMIN_TOOLS_REDIRECT_IBLOCK'),
			"title" => GetMessage('IBLOCK_MENU_ADMIN_TOOLS_REDIRECT_IBLOCK_TITLE'),
			"url" => "iblock_redirect_entity.php?lang=".LANGUAGE_ID,
			"module_id" => "iblock"
		);
	}

	if (!empty($adminToolsMenu))
	{
		$arItems[] = array(
			"text" => GetMessage('IBLOCK_MENU_ADMIN_TOOLS'),
			"title" => GetMessage('IBLOCK_MENU_ADMIN_TOOLS_TITLE'),
			"module_id" => "iblock",
			"items_id" => "iblock_redirect",
			"items" => $adminToolsMenu
		);
	}
	unset($adminToolsMenu);

	$aMenu[] = array(
		"parent_menu" => "global_menu_content",
		"section" => "iblock",
		"sort" => 300,
		"text" => GetMessage("IBLOCK_MENU_SEPARATOR"),
		"title" => GetMessage("IBLOCK_MENU_SETTINGS_TITLE"),
		"icon" => "iblock_menu_icon_settings",
		"page_icon" => "iblock_page_icon_settings",
		"items_id" => "menu_iblock",
		"module_id" => "iblock",
		"items" => $arItems,
	);

}

return $aMenu;