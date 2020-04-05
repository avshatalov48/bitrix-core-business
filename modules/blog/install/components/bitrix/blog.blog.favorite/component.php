<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
}
else
{
	$arParams["CACHE_TIME"] = 0;
}
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$SORT = Array("FAVORITE_SORT" => "ASC", "DATE_PUBLISH" => "DESC");

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 6;
$arParams["PREVIEW_WIDTH"] = IntVal($arParams["PREVIEW_WIDTH"])>0 ? IntVal($arParams["PREVIEW_WIDTH"]): 100;
$arParams["PREVIEW_HEIGHT"] = IntVal($arParams["PREVIEW_HEIGHT"])>0 ? IntVal($arParams["PREVIEW_HEIGHT"]): 100;
$arParams["MESSAGE_LENGTH"] = (IntVal($arParams["MESSAGE_LENGTH"])>0)?$arParams["MESSAGE_LENGTH"]:100;
if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(IntVal($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
$arParams["ALLOW_POST_CODE"] = $arParams["ALLOW_POST_CODE"] !== "N";

$arResult["ERROR_MESSAGE"] = Array();
$arResultNFCache["ERROR_MESSAGE"] = Array();
$arResult["POST"] = Array();

if(strlen($arParams["BLOG_URL"]) > 0)
{
	$user_id = IntVal($USER->GetID());

	if($GLOBALS["USER"]->IsAuthorized())
		$arUserGroups = CBlogUser::GetUserGroups($user_id, $arParams["BLOG_URL"], "Y", BLOG_BY_USER_ID, "URL");
	else
		$arUserGroups = Array(1);

	$numUserGroups = count($arUserGroups);
	for ($i = 0; $i < $numUserGroups - 1; $i++)
	{
		for ($j = $i + 1; $j < $numUserGroups; $j++)
		{
			if ($arUserGroups[$i] > $arUserGroups[$j])
			{
				$tmpGroup = $arUserGroups[$i];
				$arUserGroups[$i] = $arUserGroups[$j];
				$arUserGroups[$j] = $tmpGroup;
			}
		}
	}

	$strUserGroups = "";
	for ($i = 0; $i < $numUserGroups; $i++)
		$strUserGroups .= "_".$arUserGroups[$i];

	$CACHE_TIME = $arParams["CACHE_TIME"];
	$cache_path = "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/favorite/";

	$cache = new CPHPCache;
	$cache_id = "blog_blog_message_".serialize($arParams)."_".$strUserGroups;
	if(($tzOffset = CTimeZone::GetOffset()) <> 0)
		$cache_id .= "_".$tzOffset;

	if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, $cache_path))
	{
		$Vars = $cache->GetVars();
		foreach($Vars["arResult"] as $k=>$v)
			$arResult[$k] = $v;
		CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);
		$cache->Output();
	}
	else
	{
		if ($CACHE_TIME > 0)
			$cache->StartDataCache($CACHE_TIME, $cache_id, $cache_path);

		if($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"], $arParams["GROUP_ID"]))
		{
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$arBlog["Group"] = $arGroup;
					$arResult["BLOG"] = $arBlog;
					$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $user_id);
					if($arResult["PostPerm"] >= BLOG_PERMS_READ)
					{
						$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;
						$arFilter[">PERMS"] = "D";
						$arFilter["BLOG_ID"] = $arBlog["ID"];
						$arFilter[">FAVORITE_SORT"] = 0;

						$arResult["filter"] = $arFilter;

						$dbPost = CBlogPost::GetList(
							$SORT,
							$arFilter,
							array(
								"FAVORITE_SORT", "DATE_PUBLISH", "ID", "MAX" => "PERMS"
							),
							array("nTopCount" => $arParams["MESSAGE_COUNT"])
						);

						$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);

						$bFirst = true;
						while($arPost = $dbPost->GetNext())
						{
							$CurPost = CBlogPost::GetByID($arPost["ID"]);
							$CurPost = CBlogTools::htmlspecialcharsExArray($CurPost);

							if($bFirst)
								$CurPost["FIRST"] = "Y";
							$bFirst = false;

							$CurPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>CBlogPost::GetPostID($CurPost["ID"], $CurPost["CODE"], $arParams["ALLOW_POST_CODE"])));

							if(IntVal($CurPost["ATTACH_IMG"]) <= 0)
							{
								$dbImage = CBlogImage::GetList(Array("ID" => "ASC"), Array("BLOG_ID" => $CurPost["BLOG_ID"], "POST_ID" => $CurPost["ID"], "IS_COMMENT" => "N"));
								if($arImage = $dbImage -> Fetch())
								{
									if($file = CFile::ResizeImageGet($arImage["FILE_ID"], array("width"=>$arParams["PREVIEW_WIDTH"], "height"=>$arParams["PREVIEW_HEIGHT"])))
										$CurPost["IMG"] = CFile::ShowImage($file["src"], false, false, 'align="left" hspace="2" vspace="2"');
								}
							}
							else
							{
								$CurPost["IMG"] = CFile::ShowImage($CurPost["ATTACH_IMG"], false, false, 'align="left" hspace="2" vspace="2"');
							}

							$text = preg_replace("#\[img\](.+?)\[/img\]#is", "", $CurPost["~DETAIL_TEXT"]);
							$text = preg_replace("#\[url(.+?)\](.+?)\[/url\]#is", "", $text);
							$text = preg_replace("#\[video(.+?)\](.+?)\[/video\]#is", "", $text);
							$text = preg_replace("#(\[|<)(/?)(b|u|i|list|code|quote|url|img|color|font|/*)(.*?)(\]|>)#is", "", $text);
							$text = TruncateText($text, $arParams["MESSAGE_LENGTH"]);
							$text = $p->convert($text, true, false, array("HTML" => "N", "ANCHOR" => "N", "BIU" => "N", "IMG" => "N", "QUOTE" => "N", "CODE" => "N", "FONT" => "N", "LIST" => "N", "SMILES" => "Y", "NL2BR" => "N"));

							$CurPost["TEXT_FORMATED"] = $text;
							$CurPost["DATE_PUBLISH_FORMATED"] = FormatDate($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($CurPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));

							$arResult["POST"][] = $CurPost;
						}
					}
				}
				else
					$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			}
			else
				$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		}
		else
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");

		if ($CACHE_TIME > 0)
			$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
	}
}
else
	$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");

$this->IncludeComponentTemplate();
?>