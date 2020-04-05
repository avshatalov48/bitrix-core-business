<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_REQUEST["from_detail_list"]))
{
	if ($arParams["PERMISSION"] < "U"):
	elseif (!check_bitrix_sessid()):
	elseif (!is_array($_REQUEST["items"]) || empty($_REQUEST["items"])):
	else:
		CModule::IncludeModule("photogallery");
		CModule::IncludeModule("iblock");
		$arSections = array();
		@set_time_limit(0);
		foreach ($_REQUEST["items"] as $item):
			$db_res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $item), false, false,
				array("ID", "ACTIVE", "IBLOCK_SECTION_ID", "PROPERTY_REAL_PICTURE"));
			if (!($db_res && $res = $db_res->Fetch()))
				continue;

			if ($_REQUEST["ACTION"] == "approve" || $_REQUEST["ACTION"] == "not_approve")
			{
				if ($_REQUEST["ACTION"] == "approve")
				{
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "Y", "APPROVE_ELEMENT");
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "Y", "PUBLIC_ELEMENT");
				}
				else
				{
					CIBlockElement::SetPropertyValues($item, $arParams["IBLOCK_ID"], "N", "APPROVE_ELEMENT");
				}
				if ($res["ACTIVE"] != "Y")
					$_REQUEST["ACTION"] = "active";

				PClearComponentCacheEx($arParams["IBLOCK_ID"], array($res["IBLOCK_SECTION_ID"]));
			}

			if ($_REQUEST["ACTION"] == "active" || $_REQUEST["ACTION"] == "drop")
			{
				$iFileSize = 0;
				if ($_REQUEST["ACTION"] == "active")
				{
					$arFields = array("ACTIVE" => "Y");
					$be = new CIBlockElement;
					$be->Update($item, $arFields);
				}
				else
				{
					$res["REAL_PICTURE"] = CFile::GetFileArray($res["PROPERTY_REAL_PICTURE_VALUE"]);
					$iFileSize = intVal($res["REAL_PICTURE"]["FILE_SIZE"]);
					CIBlockElement::Delete($item);
				}
				// section
				if (is_set($arSections, $res["IBLOCK_SECTION_ID"])):
					$res = $arSections[$res["IBLOCK_SECTION_ID"]];
				else:
					$db_res = CIBlockSection::GetList(array(), array("ID" => $res["IBLOCK_SECTION_ID"]), false,
						array("ID", "IBLOCK_SECTION_ID", "LEFT_MARGIN", "RIGHT_MARGIN"));
					if ($db_res && $res = $db_res->Fetch()):
						$arSections[$res["ID"]] = $res;
						// gallery
						$db_res = CIBlockSection::GetList(array(), array(
							"IBLOCK_ID" => $arParams["IBLOCK_ID"],
							"SECTION_ID" => 0,
							"!LEFT_MARGIN" => $res["LEFT_MARGIN"],
							"!RIGHT_MARGIN" => $res["RIGHT_MARGIN"],
							"!ID" => $res["ID"]), false, array("ID", "UF_GALLERY_SIZE"));
						if ($db_res && $res_g = $db_res->Fetch()):
							$res["GALLERY"] = $res_g;
							$arSections[$res["ID"]] = $res;
						endif;
					endif;
				endif;
				if (!empty($res["GALLERY"]) && $iFileSize > 0):
					$gallery = $res["GALLERY"];
					$gallery["UF_GALLERY_SIZE"] = doubleval($gallery["UF_GALLERY_SIZE"]) - $iFileSize;
					$gallery["UF_GALLERY_SIZE"] = ($gallery["UF_GALLERY_SIZE"] <= 0 ? 0 : $gallery["UF_GALLERY_SIZE"]);
					$arFields = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "UF_GALLERY_SIZE" => $gallery["UF_GALLERY_SIZE"]);
					$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
					$bs = new CIBlockSection;
					$bs->Update($gallery["ID"], $arFields, false, false);
				endif;
				PClearComponentCacheEx($arParams["IBLOCK_ID"], array($res["ID"], $res["IBLOCK_SECTION_ID"]));
			}
		endforeach;
		LocalRedirect($_REQUEST["from_detail_list"]);
	endif;
}

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$arParams["SHOW_BEST_ELEMENT"] = ($arParams["SHOW_BEST_ELEMENT"] == "N" ? "N" : "Y");
$arResult["MENU_VARIABLES"] = array();
if ($this->__page !== "menu"):
	$sTempatePage = $this->__page;
	$sTempateFile = $this->__file;
	$this->__component->IncludeComponentTemplate("menu");
	$arResult["MENU_VARIABLES"] = $this->__component->__photogallery_values;
	$this->__page = $sTempatePage;
	$this->__file = $sTempateFile;
	if (!is_array($arResult["MENU_VARIABLES"])):
		return false;
	elseif (empty($arResult["MENU_VARIABLES"]["USER_ALIAS"])):
		//CHTTP::SetStatus("404 Not Found");
		return false;
	elseif ($arResult["VARIABLES"]["USER_ALIAS"] != $arResult["MENU_VARIABLES"]["USER_ALIAS"] ||
			$arResult["VARIABLES"]["SECTION_ID"] != $arResult["MENU_VARIABLES"]["SECTION_ID"]):
		if ($arParams["SEF_MODE"] != "Y"):
			$url = $GLOBALS["APPLICATION"]->GetCurPageParam(
				$arResult["ALIASES"]["USER_ALIAS"]."=".$arResult["MENU_VARIABLES"]["USER_ALIAS"]."&".
					$arResult["ALIASES"]["SECTION_ID"]."=".$arResult["MENU_VARIABLES"]["SECTION_ID"],
				array($arResult["ALIASES"]["USER_ALIAS"], $arResult["ALIASES"]["SECTION_ID"]));
		else:
			$res = $arResult["VARIABLES"];
			$res["USER_ALIAS"] = $arResult["MENU_VARIABLES"]["USER_ALIAS"];
			$res["SECTION_ID"] = $arResult["MENU_VARIABLES"]["SECTION_ID"];
			$url = CComponentEngine::MakePathFromTemplate($arResult["URL_TEMPLATES"][$sTempatePage], $res);
		endif;
		LocalRedirect($url, false, "301 Moved Permanently");
		return false;
	endif;
else:
	return true;
endif;

$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
?>
<script type="text/javascript">
if (typeof(phpVars) != "object")
	var phpVars = {};
if (!phpVars.cookiePrefix)
	phpVars.cookiePrefix = '<?=CUtil::JSEscape(COption::GetOptionString("main", "cookie_name", "BITRIX_SM"))?>';
if (!phpVars.titlePrefix)
	phpVars.titlePrefix = '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - ';
if (!phpVars.messLoading)
	phpVars.messLoading = '<?=CUtil::JSEscape(GetMessage("P_LOADING"))?>';

var photoVars = {'templatePath' : '/bitrix/components/bitrix/photogallery/templates/old/'};

</script>