<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["GROUPS_COUNT"] = intval($arParams["GROUPS_COUNT"]);
$arParams["COLS_COUNT"] = (intval($arParams["COLS_COUNT"])>0 ? intval($arParams["COLS_COUNT"]) : 2);

$arParams["SORT_BY1"] = ($arParams["SORT_BY1"] <> '' ? $arParams["SORT_BY1"] : "DATE_CREATE");
$arParams["SORT_ORDER1"] = ($arParams["SORT_ORDER1"] <> '' ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = ($arParams["SORT_BY2"] <> '' ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = ($arParams["SORT_ORDER2"] <> '' ? $arParams["SORT_ORDER2"] : "DESC");

if(!is_array($arParams["GROUP_ID"]))
	$arParams["GROUP_ID"] = array($arParams["GROUP_ID"]);
foreach($arParams["GROUP_ID"] as $k=>$v)
	if(intval($v) <= 0)
		unset($arParams["GROUP_ID"][$k]);
	
if($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "id";
if($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
	
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#", Array($arParams["PAGE_VAR"], $arParams["GROUP_VAR"])));

$cache = new CPHPCache;
$cache_id = "blog_blog_groups_".serialize($arParams);
$cache_path = "/".SITE_ID."/blog/blog_groups/";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;

	$template = new CBitrixComponentTemplate();
	$template->ApplyCachedData($Vars["templateCachedData"]);

	$cache->Output();
}
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
	$arFilter = Array("SITE_ID"=>SITE_ID);
	if(!empty($arParams["GROUP_ID"]))
		$arFilter["ID"] = $arParams["GROUP_ID"];
	$arSelectFields = false;
	
//	if($arParams["GROUPS_COUNT"]>0)
//		$COUNT = Array("nTopCount" => $arParams["GROUPS_COUNT"]*2);
//	else
		$COUNT = false;

	$arResult["GROUPS"] = Array();
	$arResult["GROUPS_TABLE"] = Array();
	$dbGroups = CBlogGroup::GetList(
				$SORT, 
				$arFilter, 
				false, 
				$COUNT, 
				$arSelectFields);
	$itemCnt = 0;
	while($arGroups = $dbGroups->Fetch())
	{
		$dbBlog = CBlog::GetList(Array(), Array("GROUP_ID"=>$arGroups["ID"]), false, false, Array("ID", "GROUP_ID"));
		if($arBlog = $dbBlog->Fetch())
		{
			$url = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroups["ID"]));
			$arResult["GROUPS"][] = array("ID"=>$arGroups["ID"], "NAME"=>htmlspecialcharsex($arGroups["NAME"]), "URL" => $url);
			$itemCnt++;
			if ($itemCnt >= $arParams["GROUPS_COUNT"] && $arParams["GROUPS_COUNT"] > 0)
				break;
		}
	}

	$cnt = count($arResult["GROUPS"]);
	$row1 = ceil($cnt/$arParams["COLS_COUNT"]);

	$all = $cnt-$row1;
	for($i=1; $i<$arParams["COLS_COUNT"]; $i++)
	{
		if(($arParams["COLS_COUNT"]-$i)>1)
			${"row".($i+1)} = ceil($all/($arParams["COLS_COUNT"]-$i));
		else
			${"row".($i+1)} = $all;
		$all = $all - ${"row".($i+1)};
	}
	$showed = 0;
	for($j=0; $j<$row1; $j++)
	{
		$index_old = 0;
		for($k=0; $k<$arParams["COLS_COUNT"]; $k++)
		{
			if($k==0)
				$index = $j;
			else
				$index = $index_old+${'row'.$k};

			if(!empty($arResult["GROUPS"][$index]))
				$arResult["GROUPS_TABLE"][$j][$k] = $arResult["GROUPS"][$index];

			$index_old = $index;
			$showed++;
			if($showed==$cnt)
				$k = $arParams["COLS_COUNT"];
		}
	}
	
	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>
