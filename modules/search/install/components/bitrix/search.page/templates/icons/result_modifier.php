<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//You may customize user card fields to display
$arResult['USER_PROPERTY'] = array(
	"UF_DEPARTMENT",
	"PERSONAL_PHOTO",
);

//Code below searches for appropriate icon for search index item.
//All filenames should be lowercase.

//1
//Check if index item is information block element with property DOC_TYPE set.
//This property should be type list and we'll take it's values XML_ID as parameter
//iblock_doc_type_<xml_id>.gif

//2
//When no such fle found we'll check for section attributes
//iblock_section_<code>.gif
//iblock_section_<id>.gif
//iblock_section_<xml_id>.gif

//3
//Next we'll try to detect icon by "extention".
//where extension is all a-z between dot and end of title
//iblock_type_<iblock type id>_<extension>.gif

//4
//If we still failed. Try to match information block attributes.
//iblock_iblock_<code>.gif
//iblock_iblock_<id>.gif
//iblock_iblock_<xml_id>.gif

//5
//If indexed item is section when checkj for
//iblock_section.gif
//If it is an element when chek for
//iblock_element.gif

//6
//If item belongs to main module (static file)
//when check is done by it's extention
//main_<extention>.gif

//7
//For blog module we'll check if icon for post or user exists
//blog_post.gif
//blog_user.gif

//8, 9 and 10
//forum_message.gif
//intranet_user.gif
//socialnetwork_group.gif

//11
//In case we still failed to find an icon
//<module_id>_default.gif

//12
//default.gif

if (IsModuleInstalled('socialnetwork'))
{
	if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
		$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
	$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

	$arParams["PATH_TO_SONET_MESSAGES_CHAT"] = trim($arParams["PATH_TO_SONET_MESSAGES_CHAT"]);
	if (strlen($arParams["PATH_TO_SONET_MESSAGES_CHAT"]) <= 0)
		$arParams["PATH_TO_SONET_MESSAGES_CHAT"] = "/company/personal/messages/chat/#USER_ID#/";

	if (IsModuleInstalled('intranet'))
	{
		$arParams["PATH_TO_CONPANY_DEPARTMENT"] = trim($arParams["PATH_TO_CONPANY_DEPARTMENT"]);
		if (strlen($arParams["PATH_TO_CONPANY_DEPARTMENT"]) <= 0)
			$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
	}

	if (IsModuleInstalled('video'))
	{
		$arParams["PATH_TO_VIDEO_CALL"] = trim($arParams["PATH_TO_VIDEO_CALL"]);
		if (strlen($arParams["PATH_TO_VIDEO_CALL"]) <= 0)
			$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#USER_ID#/";
	}
}

$arIBlocks = array();

$image_path = $this->GetFolder()."/images/";
$abs_path = $_SERVER["DOCUMENT_ROOT"].$image_path;

foreach($arResult["SEARCH"] as $i=>$arItem)
{
	$file = false;
	switch($arItem["MODULE_ID"])
	{
		case "socialnetwork":
		case "iblock":
			if(substr($arItem["ITEM_ID"], 0, 1) === "G")
			{
				if(file_exists($abs_path."socialnetwork_group.gif"))
					$file = "socialnetwork_group.gif";
			}
			elseif(CModule::IncludeModule('iblock'))
			{
				if(!array_key_exists($arItem["PARAM2"], $arIBlocks))
					$arIBlocks[$arItem["PARAM2"]] = CIBlock::GetArrayByID($arItem["PARAM2"]);

				//section /element
				if(substr($arItem["ITEM_ID"], 0, 1) !== "S")
				{
					//Try to find gif by element proprety value xml id
					$rsElement = CIBlockElement::GetList(array(), array(
							"=ID" => $arItem["ITEM_ID"],
							"IBLOCK_ID" => $arItem["PARAM2"],
						),
						false, false, array(
							"ID",
							"IBLOCK_ID",
							"CODE",
							"XML_ID",
							"PROPERTY_DOC_TYPE",
						)
					);
					$arElement = $rsElement->Fetch();
					if($arElement && strlen($arElement["PROPERTY_DOC_TYPE_ENUM_ID"]) > 0)
					{
						$arEnum = CIBlockPropertyEnum::GetByID($arElement["PROPERTY_DOC_TYPE_ENUM_ID"]);
						if($arEnum && $arEnum["XML_ID"])
						{
							if(file_exists($abs_path."iblock_doc_type_".strtolower($arEnum["XML_ID"]).".gif"))
								$file = "iblock_doc_type_".strtolower($arEnum["XML_ID"]).".gif";
						}
					}

					//We failed. next try should be element section
					if(!$file)
					{
						$rsSection = CIBlockElement::GetElementGroups($arItem["ITEM_ID"], true);
						$arSection = $rsSection->Fetch();
						if($arSection)
							$SECTION_ID = $arSection["ID"];
					}
					else
					{
						$SECTION_ID = false;
					}
				}
				else
				{
					$SECTION_ID = $arItem["ITEM_ID"];
				}

				//If no element icon was found. We'll take chances with section
				if(!$file && $SECTION_ID)
				{
					$rsSection = CIBlockSection::GetList(array(), array(
						"=ID" => $SECTION_ID,
						"IBLOCK_ID" => $arItem["PARAM2"],
						)
					);
					if($arSection = $rsSection->Fetch())
					{
						if(strlen($arSection["CODE"]) && file_exists($abs_path."iblock_section_".strtolower($arSection["CODE"]).".gif"))
							$file = "iblock_section_".strtolower($arSection["CODE"]).".gif";
						elseif(file_exists($abs_path."iblock_section_".strtolower($arSection["ID"]).".gif"))
							$file = "iblock_section_".strtolower($arSection["ID"]).".gif";
						elseif(strlen($arSection["XML_ID"]) && file_exists($abs_path."iblock_section_".strtolower($arSection["XML_ID"]).".gif"))
							$file = "iblock_section_".strtolower($arSection["XML_ID"]).".gif";
					}
				}
				//Try to detect by "extension"
				$match = array();
				if(!$file && preg_match("/\\.([a-z]+?)$/i", $arItem["TITLE"], $match))
				{
					if(file_exists($abs_path."iblock_type_".strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"])."_".$match[1].".gif"))
						$file = "iblock_type_".strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"])."_".$match[1].".gif";
				}
				//We still failed to find icon? Try iblock itself
				if(!$file)
				{
					if(strlen($arIBlocks[$arItem["PARAM2"]]["CODE"]) && file_exists($abs_path."iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["CODE"]).".gif"))
						$file = "iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["CODE"]).".gif";
					elseif(file_exists($abs_path."iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["ID"]).".gif"))
						$file = "iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["ID"]).".gif";
					elseif(strlen($arIBlocks[$arItem["PARAM2"]]["XML_ID"]) && file_exists($abs_path."iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["XML_ID"]).".gif"))
						$file = "iblock_iblock_".strtolower($arIBlocks[$arItem["PARAM2"]]["XML_ID"]).".gif";
					elseif(file_exists($abs_path."iblock_type_".strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"]).".gif"))
						$file = "iblock_type_".strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"]).".gif";
				}

				if(!$file)
				{
					if(substr($arItem["ITEM_ID"], 0, 1) !== "S")
					{
						if(file_exists($abs_path."iblock_element.gif"))
							$file = "iblock_element.gif";
					}
					else
					{
						if(file_exists($abs_path."iblock_section.gif"))
							$file = "iblock_section.gif";
					}
				}
			}
			break;
		case "main":
			$ext = end(explode('.', $arItem["ITEM_ID"]));
			if(file_exists($abs_path."main_".strtolower($ext).".gif"))
				$file = "main_".strtolower($ext).".gif";
			break;
		case "blog":
			if(substr($arItem["ITEM_ID"], 0, 1) === "P" && file_exists($abs_path."blog_post.gif"))
				$file = "blog_post.gif";
			elseif(substr($arItem["ITEM_ID"], 0, 1) === "U" && file_exists($abs_path."blog_user.gif"))
				$file = "blog_user.gif";
			break;
		case "forum":
			if(file_exists($abs_path."forum_message.gif"))
				$file = "forum_message.gif";
			break;
		case "intranet":
			if(substr($arItem["ITEM_ID"], 0, 1) === "U" && file_exists($abs_path."intranet_user.gif"))
				$file = "intranet_user.gif";
			break;
	}

	if(!$file)
	{
		if(file_exists($abs_path.$arItem["MODULE_ID"]."_default.gif"))
			$file = $arItem["MODULE_ID"]."_default.gif";
		else
			$file = "default.gif";
	}

	$arResult["SEARCH"][$i]["ICON"] = $image_path.$file;
}

if(CModule::IncludeModule('intranet'))
{
	$arResult["STRUCTURE_PAGE"] = "";
	$structure_iblock_id = COption::GetOptionInt("intranet", "iblock_structure", 0);
	if($structure_iblock_id > 0)
	{
		$arIBlock = CIBlock::GetArrayByID($structure_iblock_id);
		if($arIBlock)
			$arResult["STRUCTURE_PAGE"] = CIBlock::ReplaceDetailURL($arIBlock["LIST_PAGE_URL"], $arIBlock, true);
	}
	$arResult["STRUCTURE_FILTER"] = trim($arParams["STRUCTURE_FILTER"]);
	if(strlen($arResult["STRUCTURE_FILTER"]) <= 0)
		$arResult["STRUCTURE_FILTER"] = "structure";

	$bSoNet = CModule::IncludeModule('socialnetwork');
	$arDepCache = array();
	$arDepCacheValue = array();

	foreach($arResult["SEARCH"] as $i=>$arItem)
	{
		if($arItem["MODULE_ID"] ===  "intranet" && substr($arItem["ITEM_ID"], 0, 1) === "U")
		{
			$rsUser = CUser::GetList(($by = ''), ($ord = ''), array("ID_EQUAL_EXACT" => substr($arItem["ITEM_ID"], 1), ), array('SELECT' => array('UF_*')));
			$arUser = $rsUser->Fetch();
			if($arUser)
			{
				if ($arUser['PERSONAL_PHOTO'])
				{
					$arImage = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 100);
					$arUser['PERSONAL_PHOTO'] = $arImage['IMG'];
				}

				$arDep = array();
				if (is_array($arUser['UF_DEPARTMENT']) && count($arUser['UF_DEPARTMENT']) > 0)
				{
					$arNewDep = array_diff($arUser['UF_DEPARTMENT'], $arDepCache);

					if (count($arNewDep) > 0)
					{
						$dbRes = CIBlockSection::GetList(array('SORT' => 'ASC'), array('ID' => $arNewDep));
						while ($arSect = $dbRes->Fetch())
						{
							$arDepCache[] = $arSect['ID'];
							$arDepCacheValue[$arSect['ID']] = $arSect['NAME'];
						}
					}

					foreach ($arUser['UF_DEPARTMENT'] as $key => $sect)
					{
						$arDep[$sect] = $arDepCacheValue[$sect];
					}
				}

				$arUser['UF_DEPARTMENT'] = $arDep;

				$arUser["DETAIL_URL"] = $arItem["URL"];

				$arUser['IS_ONLINE'] = $bSoNet && CSocNetUser::IsOnLine($arUser['ID']);

				if ($arUser['PERSONAL_BIRTHDAY'])
				{
					$arBirthDate = ParseDateTime($arUser['PERSONAL_BIRTHDAY'], CSite::GetDateFormat('SHORT'));
					$arUser['IS_BIRTHDAY'] = (intval($arBirthDate['MM']) == date('n')) && (intval($arBirthDate['DD']) == date('j'));
				}

				$arUser['IS_FEATURED'] = CIntranetUtils::IsUserHonoured($arUser['ID']);
				$arUser['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arUser['ID']);

				$arResult["SEARCH"][$i]["USER"] = $arUser;
			}
		}
	}

	$arResult['USER_PROP'] = array();

	$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
	if (!empty($arRes))
	{
		foreach ($arRes as $key => $val)
		{
			$arResult['USER_PROP'][$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
		}
	}
}
?>