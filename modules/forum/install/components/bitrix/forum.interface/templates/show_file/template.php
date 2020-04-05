<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

//************************* Input params***************************************************************
//************************* BASE **********************************************************************
$arParams["FILE"] = (is_array($arParams["FILE"]) ? $arParams["FILE"] : intVal($arParams["FILE"]));
//************************* ADDITIONAL ****************************************************************
$arParams["SHOW_MODE"] = (in_array($arParams["SHOW_MODE"], array("LINK", "THUMB", "FULL", "RSS")) ? $arParams["SHOW_MODE"] : "FULL");
$arParams["MAX_FILE_SIZE"] = intVal($arParams["MAX_FILE_SIZE"] > 0 ? $arParams["MAX_FILE_SIZE"] : 100)*1024*1024;
//$arParams["SIZE"] user data in img tag <img width=... height=...>
if (!is_array($arParams["MAX_SIZE"]))
	$arParams["MAX_SIZE"] = ($arParams["WIDTH"] > 0 ? array("width" => $arParams["WIDTH"]) : array()) +
		($arParams["HEIGHT"] > 0 ? array("height" => $arParams["HEIGHT"]) : array());
//$arParams["HTML_SIZE"] html resize for image. This is helpful for disk space economy.
$arParams["CONVERT"] = ($arParams["CONVERT"] == "N" ? "N" : "Y");
$arParams["FAMILY"] = trim($arParams["FAMILY"]);
$arParams["FAMILY"] = CUtil::addslashes(empty($arParams["FAMILY"]) ? "FORUM" : $arParams["FAMILY"]);
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" || $arParams["RETURN"] == "ARRAY" ? $arParams["RETURN"] : "N");
//$arParams["SHOW_LINK"] = ($arParams["SHOW_LINK"] == "Y" ? "Y" : "N");
$arParams["ADDITIONAL_URL"] = htmlspecialcharsbx(trim($arParams["ADDITIONAL_URL"]));
$arParams["SERVER_NAME"] = (defined("SITE_SERVER_NAME") && strLen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : COption::GetOptionString("main", "server_name");
$arParams["NAME_TEMPLATE"] = str_replace(array("#NOBR#","#/NOBR#"), "", (!!$arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetDefaultNameFormat()));
// *************************/Input params***************************************************************

// ************************* Default params*************************************************************
$arResult["FILE"] = $arParams["FILE"];
if (!is_array($arParams["FILE"]) && intVal($arParams["FILE"]) > 0)
	$arResult["FILE"] = CFile::GetFileArray($arParams["FILE"]);
$arResult["FILE"]["~SRC"] = $arResult["FILE"]["SRC"];
if (intVal($arResult["FILE"]["ID"]) > 0)
	$arResult["FILE"]["SRC"] = "/bitrix/components/bitrix/forum.interface/show_file.php?fid=".
		htmlspecialcharsbx($arResult["FILE"]["ID"]).
		(!empty($arParams["ADDITIONAL_URL"]) ? "&".$arParams["ADDITIONAL_URL"] : "");
if ($arResult["FILE"]["USER_ID"] > 0)
{
	$forumUser = CForumUser::GetByUSER_ID($arResult["FILE"]["USER_ID"]);
	$db_res = CUser::GetById($arResult["FILE"]["USER_ID"]);
	if ($db_res && ($arUser = $db_res->Fetch()))
		$arResult["FILE"]["OWNER"] = ($forumUser && $forumUser["SHOW_NAME"] != "Y") ?  $arUser["LOGIN"] : CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, true, false);
}
$arResult["RETURN_DATA"] = "";
$arResult["RETURN_DATA_ARRAY"] = array();
// *************************/Default params*************************************************************
if (is_array($arResult["FILE"]) && !empty($arResult["FILE"]["SRC"]))
{
	$arResult["FILE"]["FULL_SRC"] = CHTTP::URN2URI($arResult["FILE"]["SRC"], $arParams["SERVER_NAME"]);

	$ct = strToLower($arResult["FILE"]["CONTENT_TYPE"]);
	if ($arParams["SHOW_MODE"] == "LINK")
	{
		// do nothing
	}
	elseif ($arParams["MAX_FILE_SIZE"] >= $arResult["FILE"]["FILE_SIZE"] && substr($ct, 0, 6) == "image/")
	{
		$arResult["RETURN_DATA"] = $GLOBALS["APPLICATION"]->IncludeComponent(
			"bitrix:forum.interface",
			"popup_image",
			Array(
				"URL" => ($arParams["SHOW_MODE"] == "RSS" ? $arResult["FILE"]["FULL_SRC"] : $arResult["FILE"]["SRC"]),
				"SIZE" => $arParams["SIZE"],
				"MAX_SIZE" => $arParams["MAX_SIZE"],
				"HTML_SIZE" => $arParams["HTML_SIZE"],
				"MODE" => ($arParams["SHOW_MODE"] == "RSS" ? "RSS" : "SHOW2IMAGES"),
				"IMG_WIDTH" => $arResult["FILE"]["WIDTH"],
				"IMG_HEIGHT" => $arResult["FILE"]["HEIGHT"],
				"IMG_NAME" => $arResult["FILE"]["ORIGINAL_NAME"],
				"IMG_SIZE" => $arResult["FILE"]["FILE_SIZE"],
				"CONVERT" => $arParams["CONVERT"],
				"FAMILY" => $arParams["FAMILY"],
				"RETURN" => "Y"
			),
			($this->__component->__parent !== null ? $this->__component->__parent : $this->__component),
			array("HIDE_ICONS" => "Y")
		);
	}
	$arResult["RETURN_DATA_ARRAY"]["DATA"] = $arResult["RETURN_DATA"];
	$arData = array();

	$size = (intVal($arResult["FILE"]["FILE_SIZE"]) > 0 ? CFile::FormatSize(intval($arResult['FILE']['FILE_SIZE'])) : '');
	$sTitle = (!empty($arResult["FILE"]["ORIGINAL_NAME"]) ? $arResult["FILE"]["ORIGINAL_NAME"] : GetMessage("FRM_DOWNLOAD"));
	$file_ext = GetFileExtension($arResult["FILE"]["ORIGINAL_NAME"]);
	$arData["TITLE"] = "<a href=\"".$arResult["FILE"]["SRC"]."&action=download"."\" class=\"forum-file forum-file-".$file_ext."\" ".
		(!empty($arResult["RETURN_DATA"]) ?: ( substr($ct, 0, 6) == "image/" ?
			" data-bx-viewer=\"image\" data-bx-src=\"".$arResult["FILE"]["SRC"]."\" ".
			" data-bx-title=\"".htmlspecialcharsbx($arResult["FILE"]["ORIGINAL_NAME"])."\" ".
			" data-bx-owner=\"".htmlspecialcharsbx($arResult["FILE"]["OWNER"])."\" ".
			" data-bx-dateModify=\"".htmlspecialcharsbx($arResult["FILE"]["TIMESTAMP_X"])."\" ".
			" data-bx-size=\"".htmlspecialcharsbx($arResult["FILE"]["FILE_SIZE"])."\" ".
			" data-bx-download=\"".$arResult["FILE"]["SRC"]."&action=download\" ".
			" data-bx-width=\"".htmlspecialcharsbx($arResult["FILE"]["WIDTH"])."\" ".
			" data-bx-height=\"".htmlspecialcharsbx($arResult["FILE"]["HEIGHT"])."\" " :
			" data-bx-viewer=\"unknown\" data-bx-src=\"".$arResult["FILE"]["SRC"]."\" ".
			" data-bx-title=\"".htmlspecialcharsbx($arResult["FILE"]["ORIGINAL_NAME"])."\" ".
			" data-bx-owner=\"".htmlspecialcharsbx($arResult["FILE"]["OWNER"])."\" ".
			" data-bx-dateModify=\"".htmlspecialcharsbx($arResult["FILE"]["TIMESTAMP_X"])."\" data-bx-tooBigSizeMsg=\"\" ".
			" data-bx-size=\"".$size."\" ".
			" data-bx-download=\"".$arResult["FILE"]["SRC"]."&action=download\" " )).
		"title=\"".str_replace("#FILE_NAME#", $arResult["FILE"]["ORIGINAL_NAME"], GetMessage("FRM_DOWNLOAD_TITLE")).'" target="_blank">'.
		'<span>'.$arResult["FILE"]["ORIGINAL_NAME"].'</span></a>';

	if ($size != '')
		$arData["SIZE"] = "<span class=\"forum-file-size\">(".$size.")</span>";

	$arResult["RETURN_DATA_ARRAY"] += $arData;
	if ($arParams["SHOW_MODE"] == "RSS")
		$arResult["RETURN_DATA"] = (!empty($arResult["RETURN_DATA"]) ?
			$arResult["RETURN_DATA"] : '<a href="'.$arResult["FILE"]["FULL_SRC"].'">'.$arResult["FILE"]["ORIGINAL_NAME"].'</a>');
	elseif ($arParams["SHOW_MODE"] == "THUMB" && !empty($arResult["RETURN_DATA"]))
		$arResult["RETURN_DATA"] = "<span class=\"forum-attach\" title=\"".htmlspecialcharsbx($arResult["FILE"]["ORIGINAL_NAME"])." (".$size.")\">".$arResult["RETURN_DATA"]."</span>";
	elseif ($arParams["SHOW_MODE"] !=   "FULL" || empty($arResult["RETURN_DATA"]))
		$arResult["RETURN_DATA"] = "<span class=\"forum-attach\">".implode(" ", $arData)."</span>";
	else
		$arResult["RETURN_DATA"] = "<div class=\"forum-attach\">".$arResult["RETURN_DATA"]."<div>".implode(" ", $arData)."</div></div>";
}

if ($arParams["RETURN"] == "Y")
	$this->__component->arParams["RETURN_DATA"] = $arResult["RETURN_DATA"];
elseif ($arParams["RETURN"] == "ARRAY")
	$this->__component->arParams["RETURN_DATA"] = $arResult["RETURN_DATA_ARRAY"] + array("RETURN_DATA" => $arResult["RETURN_DATA"]);
else
	echo $arResult["RETURN_DATA"];
return 0;
?>