<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$arParams["THEME"] = trim($arParams["THEME"]);
$arParams["THEME"] = (in_array($arParams["THEME"], $arThemes) ? $arParams["THEME"] : (in_array("blue", $arThemes) ? "blue" : $arThemes[0]));

$arParams["NAV_TEMPLATE"] = trim($arParams["NAV_TEMPLATE"]);
$arParams["NAV_TEMPLATE"] = (empty($arParams["NAV_TEMPLATE"]) ? "blog" : $arParams["NAV_TEMPLATE"]);

$sTemplateDir = $this->__component->__template->__folder;
$sTemplateDir = preg_replace("'[\\\\/]+'", "/", $sTemplateDir."/");
$date = @filemtime($sTemplateDirFull."styles/additional.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'styles/additional.css?'.$date);

if($arParams["SHOW_NAVIGATION"] != "N" && (intval($arResult["VARIABLES"]["group_id"]) > 0 || $arResult["VARIABLES"]["blog"] <> '' || intval($arResult["VARIABLES"]["user_id"]) > 0))
{
	if($arParams["BLOG_VAR"] == '')
		$arParams["BLOG_VAR"] = "blog";
	if($arParams["PAGE_VAR"] == '')
		$arParams["PAGE_VAR"] = "page";
	if($arParams["USER_VAR"] == '')
		$arParams["USER_VAR"] = "page";
	if($arParams["GROUP_VAR"] == '')
		$arParams["GROUP_VAR"] = "group_id";
		
	$arResultTmp["PATH_TO_INDEX"] = trim($arResult["PATH_TO_INDEX"]);
	if($arResult["PATH_TO_INDEX"] == '')
		$arResultTmp["PATH_TO_INDEX"] = htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage());	
	$arResultTmp["PATH_TO_BLOG"] = trim($arResult["PATH_TO_BLOG"]);
	if($arResultTmp["PATH_TO_BLOG"] == '')
		$arResultTmp["PATH_TO_BLOG"] = htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");	
	$arResultTmp["PATH_TO_GROUP"] = trim($arResult["PATH_TO_GROUP"]);
	if($arResultTmp["PATH_TO_GROUP"] == '')
		$arResultTmp["PATH_TO_GROUP"] = htmlspecialcharsbx($GLOBALS['APPLICATION']->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");
		
	$arBlog = Array();
	?>
	<div class="blog-navigation-box">
	<ul class="blog-navigation">
	<li><a href="<?=$arResultTmp["PATH_TO_INDEX"]?>"><?=GetMessage("RESULT_BLOG")?></a></li>
	<?
	if(is_array($arParams["GROUP_ID"]))
	{
		$tmp = Array();
		foreach($arParams["GROUP_ID"] as $v)
			if(intval($v) > 0)
				$tmp[] = intval($v);
		$arParams["GROUP_ID"] = $tmp;
	}

	if(intval($arParams["GROUP_ID"]) <= 0 || (is_array($arParams["GROUP_ID"]) && count($arParams["GROUP_ID"]) > 1))
	{
		$groupID = 0;
		if($arResult["VARIABLES"]["blog"] <> '')
		{
			if($arBlog = CBlog::GetByUrl($arResult["VARIABLES"]["blog"], $arParams["GROUP_ID"]))
			{
				if($arBlog["ACTIVE"] == "Y")
				{
					$groupID = $arBlog["GROUP_ID"];
				}
			}
		}
		elseif(intval($arResult["VARIABLES"]["user_id"]) > 0)
		{
			if($arBlog = CBlog::GetByOwnerID($arResult["VARIABLES"]["user_id"], $arParams["GROUP_ID"]))
			{
				if($arBlog["ACTIVE"] == "Y")
				{
					$groupID = $arBlog["GROUP_ID"];
				}
			}
		}
		elseif(intval($arResult["VARIABLES"]["group_id"]) > 0)
		{
			$groupID = intval($arResult["VARIABLES"]["group_id"]);
		}
		if(intval($groupID) > 0)
		{		
			$arGroup = CBlogGroup::GetByID($groupID);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
			
				$pathToGroup = CComponentEngine::MakePathFromTemplate($arResultTmp["PATH_TO_GROUP"], array("group_id" => $groupID));
				?>
				<li><span class="blog-navigation-sep">&nbsp;&raquo;&nbsp;</span></li>
				<li><a href="<?=$pathToGroup?>"><?=htmlspecialcharsEx($arGroup["NAME"])?></a></li>
				<?
			}
		}
	}
	if($arResult["VARIABLES"]["blog"] <> '' || intval($arResult["VARIABLES"]["user_id"]) > 0)
	{
		if(empty($arBlog))
		{
			$arBlog = CBlog::GetByUrl($arResult["VARIABLES"]["blog"], $arParams["GROUP_ID"]);
		}
		if(empty($arBlog))
		{
			$arBlog = CBlog::GetByOwnerID($arResult["VARIABLES"]["user_id"], $arParams["GROUP_ID"]);
		}
		if(!empty($arBlog))
		{
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$pathToBlog = CComponentEngine::MakePathFromTemplate($arResultTmp["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
					?>
					<li><span class="blog-navigation-sep">&nbsp;&raquo;&nbsp;</span></li>
					<li><a href="<?=$pathToBlog?>"><?=htmlspecialcharsEx($arBlog["NAME"])?></a></li>
					<?
				}
			}
		}
	}
	?></ul></div><?
}

if (!empty($arParams["THEME"])):
{
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/'.$arParams["THEME"].'/style.css');
	if($arParams["THEME"] == "blue")
	{
		$arParams["COLOR_OLD"] = "7fa5ca";
		$arParams["COLOR_NEW"] = "0e5196";
	}
	elseif($arParams["THEME"] == "green")
	{
		$arParams["COLOR_OLD"] = "8dac8a";
		$arParams["COLOR_NEW"] = "33882a";
	}
	elseif($arParams["THEME"] == "orange")
	{
		$arParams["COLOR_OLD"] = "7fa5ca";
		$arParams["COLOR_NEW"] = "006bcf";
	}
	elseif($arParams["THEME"] == "red")
	{
		$arParams["COLOR_OLD"] = "e59494";
		$arParams["COLOR_NEW"] = "d52020";
	}
	elseif($arParams["THEME"] == "red2")
	{
		$arParams["COLOR_OLD"] = "92a6bb";
		$arParams["COLOR_NEW"] = "346ba4";
	}
}
endif;
