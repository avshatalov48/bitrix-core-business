<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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

if(!CModule::IncludeModule("crm"))
	return;

global $USER;
$CCrmPerms = new CCrmPerms($USER->GetID());
$arSupportedTypes = array(); // all entity types are defined in settings
$arSettings = $arParams["arUserField"]["SETTINGS"];
if (isset($arSettings["LEAD"]) && $arSettings["LEAD"] === "Y")
{
	$arSupportedTypes[] = "LEAD";
}
if (isset($arSettings["CONTACT"]) && $arSettings["CONTACT"] === "Y")
{
	$arSupportedTypes[] = "CONTACT";
}
if (isset($arSettings["COMPANY"]) && $arSettings["COMPANY"] === "Y")
{
	$arSupportedTypes[] = "COMPANY";
}
if (isset($arSettings["DEAL"]) && $arSettings["DEAL"] === "Y")
{
	$arSupportedTypes[] = "DEAL";
}

$arParams["ENTITY_TYPE"] = array(); // only entity types are allowed for current user
foreach($arSupportedTypes as $supportedType)
{
	if(!$CCrmPerms->HavePerm($supportedType, BX_CRM_PERM_NONE, "READ"))
	{
		$arParams["ENTITY_TYPE"][] = $supportedType;
	}
}

$arResult["PREFIX"] = count($arSupportedTypes) > 1 ? "Y" : "N";
$arResult["MULTIPLE"] = $arParams["arUserField"]["MULTIPLE"];
if (!is_array($arResult["VALUE"]))
	$arResult["VALUE"] = explode(";", $arResult["VALUE"]);
else
{
	$ar = array();
	foreach ($arResult["VALUE"] as $value)
		foreach(explode(";", $value) as $val)
			if (!empty($val))
				$ar[$val] = $val;
	$arResult["VALUE"] = $ar;
}

$arValue = Array();
foreach ($arResult["VALUE"] as $value)
{
	if($arParams["PREFIX"])
	{
		$ar = explode("_", $value);
		$arValue[CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
	}
	else
	{
		if (is_numeric($value))
			$arValue[$arParams["ENTITY_TYPE"][0]][] = $value;
		else
		{
			$ar = explode("_", $value);
			$arValue[CUserTypeCrm::GetLongEntityType($ar[0])][] = intval($ar[1]);
		}
	}
}

$arResult["VALUE"] = array();
// last 50 entity
if (in_array("LEAD", $arParams["ENTITY_TYPE"], true))
{
	$arResult["VALUE"]["LEAD"] = array();
	if ($arValue["LEAD"])
	{
		$dbRes = CCrmLead::GetListEx(
			array("TITLE" => "ASC"),
			array("=ID" => $arValue["LEAD"]),
			false,
			array("nTopCount" => 50),
			array("ID", "TITLE", "FULL_NAME")
		);

		while ($res = $dbRes->Fetch())
		{
			$res["SID"] = $arResult["PREFIX"] == "Y"? "L_".$res["ID"]: $res["ID"];

			$arResult["VALUE"]["LEAD"][$res["SID"]] = Array(
				"id" => $res["ID"],
				"title" => $res["TITLE"]);
		}
	}
}
if (in_array("CONTACT", $arParams["ENTITY_TYPE"], true))
{
	$arResult["VALUE"]["CONTACT"] = array();
	if(!empty($arValue["CONTACT"]))
	{
		$hasNameFormatter = method_exists("CCrmContact", "PrepareFormattedName");
		$obRes = CCrmContact::GetListEx(
			array("LAST_NAME"=>"ASC", "NAME" => "ASC"),
			array("=ID" => $arValue["CONTACT"]),
			false,
			array("nTopCount" => 50),
			$hasNameFormatter
				? array("ID", "HONORIFIC", "NAME", "SECOND_NAME", "LAST_NAME", "COMPANY_TITLE", "PHOTO")
				: array("ID", "FULL_NAME", "COMPANY_TITLE", "PHOTO")
		);
		while ($res = $obRes->Fetch())
		{
			$arImg = array();
			if (!empty($res["PHOTO"]) && !isset($arFiles[$res["PHOTO"]]))
			{
				if(intval($res["PHOTO"]) > 0)
					$arImg = CFile::ResizeImageGet($res["PHOTO"], array("width" => 25, "height" => 25), BX_RESIZE_IMAGE_EXACT);
			}

			$res["SID"] = $arResult["PREFIX"] == "Y"? "C_".$res["ID"]: $res["ID"];

			if($hasNameFormatter)
			{
				$title = CCrmContact::PrepareFormattedName(
					array(
						"HONORIFIC" => isset($res["HONORIFIC"]) ? $res["HONORIFIC"] : '',
						"NAME" => isset($res["NAME"]) ? $res["NAME"] : '',
						"SECOND_NAME" => isset($res["SECOND_NAME"]) ? $res["SECOND_NAME"] : '',
						"LAST_NAME" => isset($res["LAST_NAME"]) ? $res["LAST_NAME"] : ''
					)
				);
			}
			else
			{
				$title = isset($res["FULL_NAME"]) ? $res["FULL_NAME"] : '';
			}

			$arResult["VALUE"]["CONTACT"][$res["SID"]] = array(
				"id" => $res["ID"],
				"title" => $title,
				"image" => $arImg["src"]
			);
		}
	}
}
if (in_array("COMPANY", $arParams["ENTITY_TYPE"], true))
{
	$arResult["VALUE"]["COMPANY"] = array();
	if (!empty($arValue["COMPANY"]))
	{
		$arCompanyTypeList = CCrmStatus::GetStatusListEx("COMPANY_TYPE");
		$arCompanyIndustryList = CCrmStatus::GetStatusListEx("INDUSTRY");
		$arSelect = array("ID", "TITLE", "COMPANY_TYPE", "INDUSTRY",  "LOGO");
		$obRes = CCrmCompany::GetList(array("TITLE"=>"ASC"), array("ID" => $arValue["COMPANY"]), $arSelect, 50);
		$arFiles = array();
		while ($res = $obRes->Fetch())
		{
			$arImg = array();
			if (!empty($res["LOGO"]) && !isset($arFiles[$res["LOGO"]]))
			{
				if(intval($res["LOGO"]) > 0)
					$arImg = CFile::ResizeImageGet($res["LOGO"], array("width" => 25, "height" => 25), BX_RESIZE_IMAGE_EXACT);

				$arFiles[$res["LOGO"]] = $arImg["src"];
			}

			$res["SID"] = $arResult["PREFIX"] == "Y"? "CO_".$res["ID"]: $res["ID"];

			$arResult["VALUE"]["COMPANY"][$res["SID"]] = array(
				"title" => (str_replace(array(";", ","), " ", $res["TITLE"])),
				"id" => $res["ID"],
				"image" => $arImg["src"]
			);
		}
	}
}
if (in_array("DEAL", $arParams["ENTITY_TYPE"], true))
{
	$arResult["VALUE"]["DEAL"] = array();
	if (!empty($arValue["DEAL"]))
	{
		$arSelect = array("ID", "TITLE", "STAGE_ID", "COMPANY_TITLE", "CONTACT_FULL_NAME");
		$obRes = CCrmDeal::GetList(array("TITLE"=>"ASC"), array("ID" => $arValue["DEAL"]), $arSelect, 50);
		while ($res = $obRes->Fetch())
		{
			$res["SID"] = $arResult["PREFIX"] == "Y"? "D_".$res["ID"]: $res["ID"];

			$arResult["VALUE"]["DEAL"][$res["SID"]] = array(
				"title" => (str_replace(array(";", ","), " ", $res["TITLE"])),
				"id" => $res["ID"]
			);
		}
	}
}
?>