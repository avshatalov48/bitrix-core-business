<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if(!array_key_exists("PATH_TO_BLOG_CATEGORY", $arParams) || !is_string($arParams["PATH_TO_BLOG_CATEGORY"]))
	$arParams["PATH_TO_BLOG_CATEGORY"] = "";

//0 no limit
$arParams["TAGS_COUNT"] = intval($arParams["TAGS_COUNT"]);

$obCache = new CPHPCache;
$cache_id = md5(
	serialize(
		array(
			$arParams["BLOG_URL"],
			$arParams["TAGS_COUNT"],
			$arParams["PATH_TO_BLOG_CATEGORY"]
		)
	)
);
$cache_path = '/'.SITE_ID.'/idea/tags/';

if(!$obCache->StartDataCache(60*60*24, $cache_id, $cache_path))
{
	$arResult = $obCache->GetVars();
}
else if (($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"])) && ($arBlog["ACTIVE"] == "Y") &&
	($arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"])) && ($arGroup["SITE_ID"] == SITE_ID))
{
	$arResult = array("BLOG" => $arBlog, "CATEGORY" => Array());
	$toCnt = array();
	$res = CBlogCategory::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]));
	while ($arCategory=$res->GetNext())
	{
		$arSumCat["C".$arCategory["ID"]] = Array(
			"ID" => $arCategory["ID"],
			"NAME" => $arCategory["NAME"],
		);
		$toCnt[] = $arCategory['ID'];
	}

	$resCnt = CBlogPostCategory::GetList(
		Array(),
		Array("BLOG_ID" => $arBlog["ID"], "CATEGORY_ID"=> $toCnt),
		Array("CATEGORY_ID"),
		($arParams["TAGS_COUNT"] > 0 ? array("nTopCount" => $arParams["TAGS_COUNT"]) : false),
		array("ID", "BLOG_ID", "CATEGORY_ID", "NAME")
	);
	if (($arCategoryCount = $resCnt->Fetch()) && $arCategoryCount)
	{
		$cntMin = $cntMax = $arCategoryCount['CNT'];
		$arRes =  array();
		do
		{
			$arRes["C".$arCategoryCount["CATEGORY_ID"]] = array(
				"CNT" => $arCategoryCount['CNT'],
				"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("category_id" => $arCategoryCount["CATEGORY_ID"]))
			);
			$cntMin = min($cntMin, $arCategoryCount['CNT']);
			$cntMax = max($cntMax, $arCategoryCount['CNT']);
		} while($arCategoryCount = $resCnt->Fetch());
		$arSumCat = array_merge_recursive(array_intersect_key($arSumCat, $arRes), $arRes);
		foreach($arSumCat as $id => $arTag)
		{
			$cnt = $arTag["CNT"];
			$arSumCat[$id]["~SIZE"] = ( ( ( $cntMax - $cntMin) > 0 ) ? pow(($cnt - $cntMin)/($cntMax - $cntMin), 0.8) * 100 : 100);
			$arSumCat[$id]["SIZE"] = 50 + $arSumCat[$id]["~SIZE"];
		}
		$arResult["CATEGORY"] = $arSumCat;
	}
	$obCache->EndDataCache($arResult);
}
else
{
	$arResult = array("FATAL_ERROR_MESSAGE" => GetMessage("BLOG_ERR_NO_BLOG"));
	$this->AbortResultCache();
}
$this->IncludeComponentTemplate();
?>