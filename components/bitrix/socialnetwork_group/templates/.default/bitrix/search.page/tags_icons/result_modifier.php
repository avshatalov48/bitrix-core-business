<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//You may customize user card fields to display
$arResult['USER_PROPERTY'] = array(
	"UF_DEPARTMENT",
);

$parent = $this->__component->GetParent();
if (is_object($parent) && $parent->__name <> '')
	$arParams["~PATH_TO_GROUP_WIKI"] = $parent->arResult["PATH_TO_GROUP_WIKI_INDEX"];

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

$arIBlocks = array();

$image_path = $this->GetFolder()."/images/";
$abs_path = $_SERVER["DOCUMENT_ROOT"].$image_path;

global $MESS;
include($_SERVER["DOCUMENT_ROOT"].$this->GetFolder()."/lang/".LANGUAGE_ID."/result_modifier.php");

$arActiveFeatures = CSocNetFeatures::GetActiveFeaturesNames(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"]);

$arMenuTmp = array();
$events = GetModuleEvents("socialnetwork", "OnFillSocNetMenu");
while ($arEvent = $events->Fetch())
{
	ExecuteModuleEventEx($arEvent, array(&$arMenuTmp, array("ENTITY_TYPE" => SONET_ENTITY_GROUP, "ENTITY_ID" => $arParams["SOCNET_GROUP_ID"])));
}

$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

foreach($arSocNetFeaturesSettings as $feature_id => $arFeatureTmp)
{
	if (
		array_key_exists("allowed", $arFeatureTmp)
		&& is_array($arFeatureTmp["allowed"])
		&& count($arFeatureTmp["allowed"]) > 0
		&& !in_array(SONET_ENTITY_GROUP, $arFeatureTmp["allowed"])
	)
		continue;

	$arFeaturesTitles[$feature_id] = (
		array_key_exists($feature_id, $arActiveFeatures) && $arActiveFeatures[$feature_id] <> '' 
		? $arActiveFeatures[$feature_id] 
		: (
			isset($arMenuTmp["Title"])
			&& isset($arMenuTmp["Title"][$feature_id])
			&& $arMenuTmp["Title"][$feature_id] <> ''
				? $arMenuTmp["Title"][$feature_id]
				: GetMessage("SEARCH_CONTENT_TYPE_".mb_strtoupper($feature_id)."_".SONET_ENTITY_GROUP)
		)
	);
}

if (array_key_exists("PATH_TO_GROUP_TASKS_SECTION", $arParams))
{
	$arParams["PATH_TO_GROUP_TASKS_SECTION"] .= (mb_strpos($arParams["PATH_TO_GROUP_TASKS_SECTION"], "?") !== false ? "&" : "?")."flt_iblock_section=#section_id#";
	$arParams["~PATH_TO_GROUP_TASKS_SECTION"] = htmlspecialcharsback($arParams["PATH_TO_GROUP_TASKS_SECTION"]);
}

$strParams = "q=".urlencode($_REQUEST["q"]);

if($_REQUEST["where"] !== false && trim($_REQUEST["where"]) <> '')
	$strParams .= "&amp;where=".urlencode(trim($_REQUEST["where"]));
if($GLOBALS[$arParams["FILTER_NAME"]]["SONET_FEATURE"] !== false && trim($GLOBALS[$arParams["FILTER_NAME"]]["SONET_FEATURE"]) <> '')
	$strParams .= "&amp;".$arParams["FILTER_NAME"]."=".urlencode(trim($GLOBALS[$arParams["FILTER_NAME"]]["SONET_FEATURE"]));
if($_REQUEST[$arParams["FILTER_DATE_NAME"]."_from"] !== false && trim($_REQUEST[$arParams["FILTER_DATE_NAME"]."_from"]) <> '')
	$strParams .= "&amp;".$arParams["FILTER_DATE_NAME"]."_from"."=".urlencode(trim($_REQUEST[$arParams["FILTER_DATE_NAME"]."_from"]));
if($_REQUEST[$arParams["FILTER_DATE_NAME"]."_to"] !== false && trim($_REQUEST[$arParams["FILTER_DATE_NAME"]."_to"]) <> '')
	$strParams .= "&amp;".$arParams["FILTER_DATE_NAME"]."_to"."=".urlencode(trim($_REQUEST[$arParams["FILTER_DATE_NAME"]."_to"]));
if($_REQUEST["tags"] !== false && trim($_REQUEST["tags"]) <> '')
	$strParams .= "&amp;tags=".urlencode(trim($_REQUEST["tags"]));

$params_to_kill = array("q", "how", "where", "tags");
$params_to_kill[] = $arParams["FILTER_NAME"];
$params_to_kill[] = $arParams["FILTER_DATE_NAME"]."_from";
$params_to_kill[] = $arParams["FILTER_DATE_NAME"]."_to";

$arResult["URL"] = $APPLICATION->GetCurPageParam($strParams, $params_to_kill);

foreach($arResult["SEARCH"] as $i=>$arItem)
{

	$arChainBody	= array();
	$bWiki = false;
	$file = false;
	switch($arItem["MODULE_ID"])
	{
		case "socialnetwork":
		case "iblock":

			if ($arItem["MODULE_ID"] == "socialnetwork" && $arItem["PARAM1"] == "wiki")
			{
				$arChainBody[] = '<a href="'.CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_WIKI"], array("group_id" => $arParams["SOCNET_GROUP_ID"])).'">'.$arFeaturesTitles["wiki"].'</a>';
				$bWiki = true;
			}

			if(mb_substr($arItem["ITEM_ID"], 0, 1) === "G")
			{
				if(file_exists($abs_path."socialnetwork_group.gif"))
					$file = "socialnetwork_group.gif";
			}
			elseif(CModule::IncludeModule('iblock'))
			{
				if(!array_key_exists($arItem["PARAM2"], $arIBlocks))
					$arIBlocks[$arItem["PARAM2"]] = CIBlock::GetArrayByID($arItem["PARAM2"]);

				//section /element
				if(mb_substr($arItem["ITEM_ID"], 0, 1) !== "S")
				{
					//Try to find gif by element proprety value xml id
					$rsElement = CIBlockElement::GetList(array(), array(
							"=ID" => $arItem["ITEM_ID"],
							"IBLOCK_ID" => $arItem["PARAM2"],
						),
						false, false, array(
							"ID",
							"IBLOCK_ID",
							"IBLOCK_SECTION_ID",
							"CODE",
							"XML_ID",
							"PROPERTY_DOC_TYPE",
						)
					);
					$arElement = $rsElement->Fetch();
					if($arElement && $arElement["PROPERTY_DOC_TYPE_ENUM_ID"] <> '')
					{
						$arEnum = CIBlockPropertyEnum::GetByID($arElement["PROPERTY_DOC_TYPE_ENUM_ID"]);
						if($arEnum && $arEnum["XML_ID"])
						{
							if(file_exists($abs_path."iblock_doc_type_".mb_strtolower($arEnum["XML_ID"]).".gif"))
								$file = "iblock_doc_type_".mb_strtolower($arEnum["XML_ID"]).".gif";
						}
					}

					// add chain items if iblock (task, calendar event, library document)
					$element_type = false;
					if($arElement)
					{
						if ($arElement["IBLOCK_ID"] == $arParams["TASKS_GROUP_IBLOCK_ID"])
							$element_type = "tasks";
						elseif ($arElement["IBLOCK_ID"] == $arParams["CALENDAR_GROUP_IBLOCK_ID"])
							$element_type = "calendar";
						elseif ($arElement["IBLOCK_ID"] == $arParams["FILES_GROUP_IBLOCK_ID"])
							$element_type = "files";
						elseif ($arElement["IBLOCK_ID"] == $arParams["PHOTO_GROUP_IBLOCK_ID"])
							$element_type = "photo";

						$arElement["SECTION_PATH"] = array();
						if($arElement["IBLOCK_SECTION_ID"] > 0)
						{
							$rsPath = GetIBlockSectionPath($arElement["IBLOCK_ID"], $arElement["IBLOCK_SECTION_ID"]);
							while($arPath = $rsPath->GetNext())
								$arElement["SECTION_PATH"][] = $arPath;
						}

						$strUrl = "";
						
						if (!$bWiki)
							$arChainBody[] = '<a href="'.CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_".toUpper($element_type)], array("group_id" => $arParams["SOCNET_GROUP_ID"], "path" => "")).'">'.$arFeaturesTitles[$element_type].'</a>';

						$k = 0;
						foreach($arElement["SECTION_PATH"] as $arPath)
						{
							if ($k > 0)
							{
								$strUrl .= $arPath["NAME"]."/";
								$arPath["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_".toUpper($element_type)."_SECTION"], array(
											"group_id" => $arParams["SOCNET_GROUP_ID"], 
											"path" => rtrim($strUrl, "/"),
											"section_id" => $arPath["ID"],
											"user_alias" => "group_".$arParams["SOCNET_GROUP_ID"], 
										));
										
								if ($element_type != "calendar")
									$arChainBody[] = '<a href="'.$arPath["URL"].'">'.htmlspecialcharsex($arPath["NAME"]).'</a>';
								else
									$arChainBody[] = htmlspecialcharsex($arPath["NAME"]);										
							}
							$k++;
						}
						$arResult["SEARCH"][$i]["CHAIN_PATH"] = implode('&nbsp;/&nbsp;', $arChainBody);
					}
					
					if (!$file && $element_type && $element_type != "files")
						if(file_exists($abs_path.$element_type."_default.gif"))
							$file = $element_type."_default.gif";

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
						if(mb_strlen($arSection["CODE"]) && file_exists($abs_path."iblock_section_".mb_strtolower($arSection["CODE"]).".gif"))
							$file = "iblock_section_".mb_strtolower($arSection["CODE"]).".gif";
						elseif(file_exists($abs_path."iblock_section_".mb_strtolower($arSection["ID"]).".gif"))
							$file = "iblock_section_".mb_strtolower($arSection["ID"]).".gif";
						elseif(mb_strlen($arSection["XML_ID"]) && file_exists($abs_path."iblock_section_".mb_strtolower($arSection["XML_ID"]).".gif"))
							$file = "iblock_section_".mb_strtolower($arSection["XML_ID"]).".gif";
					}
				}
				//Try to detect by "extension"
				if(!$file && preg_match("/\\.([a-z]+?)$/i", $arItem["TITLE"], $match))
				{
					if(file_exists($abs_path."iblock_type_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"])."_".$match[1].".gif"))
						$file = "iblock_type_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"])."_".$match[1].".gif";
				}
				//We still failed to find icon? Try iblock itself
				if(!$file)
				{
					if(mb_strlen($arIBlocks[$arItem["PARAM2"]]["CODE"]) && file_exists($abs_path."iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["CODE"]).".gif"))
						$file = "iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["CODE"]).".gif";
					elseif(file_exists($abs_path."iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["ID"]).".gif"))
						$file = "iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["ID"]).".gif";
					elseif(mb_strlen($arIBlocks[$arItem["PARAM2"]]["XML_ID"]) && file_exists($abs_path."iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["XML_ID"]).".gif"))
						$file = "iblock_iblock_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["XML_ID"]).".gif";
					elseif(file_exists($abs_path."iblock_type_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"]).".gif"))
						$file = "iblock_type_".mb_strtolower($arIBlocks[$arItem["PARAM2"]]["IBLOCK_TYPE_ID"]).".gif";
				}

				if(!$file)
				{
					if(mb_substr($arItem["ITEM_ID"], 0, 1) !== "S")
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
			if(file_exists($abs_path."main_".mb_strtolower($ext).".gif"))
				$file = "main_".mb_strtolower($ext).".gif";
			break;
		case "blog":
			if(mb_substr($arItem["ITEM_ID"], 0, 1) === "P" && file_exists($abs_path."blog_post.gif"))
				$file = "blog_post.gif";
			elseif(mb_substr($arItem["ITEM_ID"], 0, 1) === "U" && file_exists($abs_path."blog_user.gif"))
				$file = "blog_user.gif";

			$arChainBody[] = '<a href="'.CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_BLOG"], array("group_id" => $arParams["SOCNET_GROUP_ID"])).'">'.$arFeaturesTitles["blog"].'</a>';
			$arResult["SEARCH"][$i]["CHAIN_PATH"] = implode('&nbsp;/&nbsp;', $arChainBody);

			break;
		case "forum":
			if(file_exists($abs_path."forum_message.gif"))
				$file = "forum_message.gif";

			$arChainBody[] = '<a href="'.CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP_FORUM"], array("group_id" => $arParams["SOCNET_GROUP_ID"])).'">'.$arFeaturesTitles["forum"].'</a>';
			$arResult["SEARCH"][$i]["CHAIN_PATH"] = implode('&nbsp;/&nbsp;', $arChainBody);

			break;
		case "intranet":
			if(mb_substr($arItem["ITEM_ID"], 0, 1) === "U" && file_exists($abs_path."intranet_user.gif"))
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

	$arResult["CHAIN_PATH"] = $GLOBALS["APPLICATION"]->GetNavChain($arResult["URL"], 0, $this->GetFolder()."/chain_template.php", true, false);
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
	if($arResult["STRUCTURE_FILTER"] == '')
		$arResult["STRUCTURE_FILTER"] = "structure";

	$bSoNet = CModule::IncludeModule('socialnetwork');
	$arDepCache = array();
	$arDepCacheValue = array();

	foreach($arResult["SEARCH"] as $i=>$arItem)
	{
		if($arItem["MODULE_ID"] ===  "intranet" && mb_substr($arItem["ITEM_ID"], 0, 1) === "U")
		{
			$rsUser = CUser::GetList('', '', array("ID_EQUAL_EXACT" => mb_substr($arItem["ITEM_ID"], 1), ), array('SELECT' => array('UF_*')));
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

				$arUser['IS_ONLINE'] = ($bSoNet && $arUser["IS_ONLINE"] == "Y");

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
			$arResult['USER_PROP'][$val["FIELD_NAME"]] = ($val["EDIT_FORM_LABEL"] <> '' ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
		}
	}
}

$arrDropdown = array();
$arResult["DROPDOWN_SONET"] = array();

$EntityType = (array_key_exists("arrFILTER", $arParams) && in_array("socialnetwork", $arParams["arrFILTER"]) ? SONET_ENTITY_GROUP : SONET_ENTITY_USER);
$EntityID = ($EntityType == SONET_ENTITY_GROUP ? $arParams["arrFILTER_socialnetwork"][0] : $arParams["arrFILTER_socialnetwork_user"]);

$arActiveFeaturesNames = CSocNetFeatures::GetActiveFeaturesNames($EntityType, $EntityID);
foreach($arParams["arrWHERE_SONET"] as $feature_id)
{
	if (
		$feature_id <> ''
		&& array_key_exists($feature_id, $arActiveFeaturesNames) 
		&& CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $EntityType, $EntityID, $feature_id, $arSocNetFeaturesSettings[$feature_id]["minoperation"][0], CSocNetUser::IsCurrentUserModuleAdmin())
		&& array_key_exists($feature_id, $arSocNetFeaturesSettings)
	)
	{
		$arrDropdown[$feature_id] = ($arActiveFeaturesNames[$feature_id] <> '' ? $arActiveFeaturesNames[$feature_id] : GetMessage("SEARCH_CONTENT_TYPE_".mb_strtoupper($feature_id)."_".$EntityType));
	}
}
if (count($arrDropdown) > 0)
{
	$arResult["DROPDOWN_SONET"] = htmlspecialcharsex($arrDropdown);
}
?>