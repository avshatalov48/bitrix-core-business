<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
if (!CModule::IncludeModule("search"))
{
	//ShowError(GetMessage("SEARCH_MODULE_NOT_INSTALL"));
	return;
}

$arParams["PAGE_RESULT_COUNT"] = intval($arParams["PAGE_RESULT_COUNT"])>0 ? intval($arParams["PAGE_RESULT_COUNT"]): 20;
if($arParams["SEARCH_PAGE"] == '')
	$arParams["SEARCH_PAGE"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search";
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
$arParams["NAV_TEMPLATE"] = ($arParams["NAV_TEMPLATE"] <> '' ? $arParams["NAV_TEMPLATE"] : "");

$arResult["~q"] = trim($_REQUEST["q"]);
$arResult["~tags"] = trim($_REQUEST["tags"]);
$arResult["~where"] = trim($_REQUEST["where"]);
$arResult["~how"] = trim($_REQUEST["how"]);
$arResult["q"] = htmlspecialcharsbx($arResult["~q"]);
$arResult["tags"] = htmlspecialcharsbx($arResult["~tags"]);
$arResult["where"] = htmlspecialcharsbx($arResult["~where"]);
$arResult["how"] = htmlspecialcharsbx($arResult["~how"]);

if($arParams["BLOG_VAR"] == '')
	$arParams["BLOG_VAR"] = "blog";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "id";
if($arParams["POST_VAR"] == '')
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if($arParams["PATH_TO_BLOG"] == '')
	$arParams["PATH_TO_BLOG"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if($arParams["PATH_TO_POST"] == '')
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_MAIN_SEARCH_TITLE"));
	
$arResult["WHERE"] = Array(
			"POST" => GetMessage("BLOG_MAIN_SEARCH_SEARCH_POST"),
			"BLOG" => GetMessage("BLOG_MAIN_SEARCH_SEARCH_BLOG"),
			"USER" => GetMessage("BLOG_MAIN_SEARCH_SEARCH_USER"),
			"COMMENT" => GetMessage("BLOG_MAIN_SEARCH_SEARCH_COMMENT"),
		);

$arFilter = array(
	"SITE_ID"	=> SITE_ID,
	"QUERY"		=> $arResult["~q"],
	"MODULE_ID"	=> "blog",
	"CHECK_DATES"	=> "Y",
	"TAGS" => $arResult["~tags"],
);
if($arResult["~where"] <> '')
	$arFilter["PARAM1"]	= $arResult["~where"];
if($arResult["~how"]=="d")
	$aSort=array("DATE_CHANGE"=>"DESC", "CUSTOM_RANK"=>"DESC", "RANK"=>"DESC");
else
	$aSort=array("CUSTOM_RANK"=>"DESC", "RANK"=>"DESC", "DATE_CHANGE"=>"DESC");

$arResult["SEARCH_RESULT"] = Array();
if($arResult["~q"] <> '' || $arResult["~tags"] <> '')
{
$obSearch = new CSearch();
$obSearch->Search($arFilter, $aSort);
$arResult["SEARCH_RESULT"] = Array();
if($obSearch->errorno==0)
{
	$obSearch->NavStart($arParams["PAGE_RESULT_COUNT"]);
	$arResult["NAV_STRING"] = $obSearch->GetPageNavString(GetMessage("BMS_PAGES"), $arParams["NAV_TEMPLATE"], false, $component);

	while($arSearch = $obSearch->GetNext())
	{
		if($arSearch["PARAM1"]=="POST")
		{
			$Blog = CBlog::GetByID($arSearch["PARAM2"]);
			$Blog = CBlogTools::htmlspecialcharsExArray($Blog);
			$arSearch["PARAM2"] = $Blog["OWNER_ID"];
			$arSearch["BLOG_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $Blog["URL"]));
			$arSearch["USER_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $Blog["OWNER_ID"]));
			$arSearch["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $Blog["URL"], "post_id"=> mb_substr($arSearch["ITEM_ID"], 1)));
		}
		elseif($arSearch["PARAM1"]=="USER")
		{
			$arSearch["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arSearch["PARAM2"]));
		}
		elseif($arSearch["PARAM1"]=="BLOG")
		{
			$Blog = CBlog::GetByID(mb_substr($arSearch["ITEM_ID"], 1));
			$Blog = CBlogTools::htmlspecialcharsExArray($Blog);

			$arSearch["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $Blog["URL"]));
		}
		elseif($arSearch["PARAM1"]=="COMMENT")
		{
			$pos = mb_strpos($arSearch["PARAM2"], "|");
			if(intval($pos) > 0)
			{
				$blogID = mb_substr($arSearch["PARAM2"], 0, $pos);
				$postID = mb_substr($arSearch["PARAM2"], $pos + 1);
		
				$Blog = CBlog::GetByID($blogID);
				$Blog = CBlogTools::htmlspecialcharsExArray($Blog);

				$arSearch["PARAM2"] = $Blog["OWNER_ID"];
				$arSearch["BLOG_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $Blog["URL"]));
				$arSearch["USER_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $Blog["OWNER_ID"]));
				$arSearch["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $Blog["URL"], "post_id"=>$postID));
				if(mb_strpos($arSearch["URL"], "?") !== false)
					$arSearch["URL"] .= "&";
				else
					$arSearch["URL"] .= "?";
				$arSearch["URL"] .= "commentId=".mb_substr($arSearch["ITEM_ID"], 1)."#".mb_substr($arSearch["ITEM_ID"], 1);
			}
		}
		else
		{
			if(!empty($Blog))
			{
				$arSearch["USER_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $Blog["OWNER_ID"]));
			}
		}
		
		if($where!="USER")
		{
			$arSearch["BlogUser"]=CBlogUser::GetByID($arSearch["PARAM2"], BLOG_BY_USER_ID);
			$arSearch["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arSearch["BlogUser"]);
			$dbUser = CUser::GetByID($arSearch["PARAM2"]);
			$arSearch["arUser"] = $dbUser->GetNext();
			$arSearch["AuthorName"] = CBlogUser::GetUserName($arSearch["BlogUser"]["ALIAS"], $arSearch["arUser"]["NAME"], $arSearch["arUser"]["LAST_NAME"], $arSearch["arUser"]["LOGIN"]);
		}
		$arSearch["FULL_DATE_CHANGE_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arSearch["FULL_DATE_CHANGE"], CSite::GetDateFormat("FULL")));
		
		$arResult["SEARCH_RESULT"][] = $arSearch;
	}
	
	if(count($arResult["SEARCH_RESULT"])>0)
	{
		if($arResult["~tags"] <> '')
			$arResult["ORDER_LINK"] = $APPLICATION->GetCurPageParam("tags=".urlencode($arResult["tags"])."&where=".urlencode($arResult["where"]), Array("tags", "where", "how"));
		else
			$arResult["ORDER_LINK"] = $APPLICATION->GetCurPageParam("q=".urlencode($arResult["q"])."&where=".urlencode($arResult["where"]), Array("q", "where", "how"));
		if($arResult["~how"]!="d")
			$arResult["ORDER_LINK"] .= "&how=d";
		$arResult["ORDER_LINK"] = htmlspecialcharsbx($arResult["ORDER_LINK"]);
	}
	else
	{
		$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_MAIN_SEARCH_NOTHING_FOUND");
	}
}
else
	$arResult["ERROR_MESSAGE"] = GetMessage("BLOG_MAIN_SEARCH_ERROR").$obSearch->error;
}
$this->IncludeComponentTemplate();
?>
