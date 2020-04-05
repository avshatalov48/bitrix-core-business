<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!(IsModuleInstalled("search") && $arParams["SHOW_TAGS"] == "Y"))
	return false;

CModule::IncludeModule("photogallery");
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arParams["MAX_LENGTH"] = 200;
$arParams["THUMBNAIL_SIZE"] = (intVal($arParams["THUMBNAIL_SIZE"]) > 0 ? intVal($arParams["THUMBNAIL_SIZE"]) : 100);
$arParams["~TAGS"] = $arResult["REQUEST"]["~TAGS"];
?>
<form action="" method="get">
<?if($arResult["REQUEST"]["HOW"]=="d"):?>
	<input type="hidden" name="how" value="d" />
<?endif;?>
	<input type="hidden" name="tags" value="<?echo $arResult["REQUEST"]["TAGS"]?>" />
	<input type="text" name="q" value="<?=$arResult["REQUEST"]["QUERY"]?>" size="40" />
	&nbsp;<input type="submit" value="<?=GetMessage("SEARCH_GO")?>" />
</form>
<?
	$arResult["TAGS_CHAIN"] = array();
	if ($arParams["~TAGS"])
	{
		?><div class="search-tags-chain" style="padding-bottom:0.2em;"><?=GetMessage("P_TAGS")?>: <?
		$res = array_unique(explode(",", $arParams["~TAGS"]));
		$url = array();
		foreach ($res as $key => $tags)
		{
			$tags = trim($tags);
			if (!empty($tags))
			{
				$url_without = $res;
				unset($url_without[$key]);
				$url[$tags] = $tags;
				$result = array(
					"TAG_NAME" => htmlspecialcharsex($tags),
					"TAG_PATH" => $APPLICATION->GetCurPageParam("tags=".urlencode(implode(",", $url)), array("tags")),
					"TAG_WITHOUT" => $APPLICATION->GetCurPageParam((count($url_without) > 0 ? "tags=".urlencode(implode(",", $url_without)) : ""), array("tags")),
				);
				$arResult["TAGS_CHAIN"][] = $result;
				?><a href="<?=$result["TAG_PATH"]?>" rel="nofollow"><?=$result["TAG_NAME"]?></a> <?
				?>[<a href="<?=$result["TAG_WITHOUT"]?>" class="search-tags-link" rel="nofollow">x</a>]  <?
			}
		}
	?></div><?
	}
?>
<br />
<?if(isset($arResult["REQUEST"]["ORIGINAL_QUERY"])):
	?>
	<div class="search-language-guess">
		<?echo GetMessage("CT_BSP_KEYBOARD_WARNING", array("#query#"=>'<a href="'.$arResult["ORIGINAL_QUERY_URL"].'">'.$arResult["REQUEST"]["ORIGINAL_QUERY"].'</a>'))?>
	</div><br /><?
endif;?>
<?
if ($arResult["REQUEST"]["QUERY"] === false && $arResult["REQUEST"]["TAGS"] === false):
	"";
elseif ($arResult["ERROR_CODE"]!=0):
	?><p><?=GetMessage("SEARCH_ERROR")?></p><?
	ShowError($arResult["ERROR_TEXT"]);
	?><p><?=GetMessage("SEARCH_CORRECT_AND_CONTINUE")?></p>
	<br /><br />
	<p><?=GetMessage("SEARCH_SINTAX")?><br /><b><?=GetMessage("SEARCH_LOGIC")?></b></p>
	<table border="0" cellpadding="5">
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_OPERATOR")?></td><td valign="top"><?=GetMessage("SEARCH_SYNONIM")?></td>
			<td><?=GetMessage("SEARCH_DESCRIPTION")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_AND")?></td><td valign="top">and, &amp;, +</td>
			<td><?=GetMessage("SEARCH_AND_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_OR")?></td><td valign="top">or, |</td>
			<td><?=GetMessage("SEARCH_OR_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top"><?=GetMessage("SEARCH_NOT")?></td><td valign="top">not, ~</td>
			<td><?=GetMessage("SEARCH_NOT_ALT")?></td>
		</tr>
		<tr>
			<td align="center" valign="top">( )</td>
			<td valign="top">&nbsp;</td>
			<td><?=GetMessage("SEARCH_BRACKETS_ALT")?></td>
		</tr>
	</table>
<?
	return true;
elseif (empty($arResult["ELEMENTS_LIST"])):
	ShowNote(GetMessage("SEARCH_NOTHING_TO_FOUND"));
	return true;
elseif (!empty($arResult["ELEMENTS_LIST"])):
	$arParams["THUMBNAIL_SIZE"] = $arParams["THUMBNAIL_SIZE"];
	$arParams["PERCENT"] = $arParams["PERCENT"];
	$arParams["TEMPLATE"] = "";
	$arParams["SHOW_ANCHOR"] = $arResult["USER_HAVE_ACCESS"];
	$arParams["SHOW_PAGE_NAVIGATION"] = "none";
	$arParams["SHOW_FORM"] = "N";
	$arParams["PICTURES"] = $arParams["PICTURES"];
	$arParams["PICTURES_SIGHT"] = $arParams["PICTURES_SIGHT"];
	$arParams["INCLUDE_SLIDER"] = "N";

	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery.detail.list/templates/.default/style.css');
	$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/components/bitrix/photogallery.detail.list/templates/.default/script.js');
	include_once(str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/bitrix/components/bitrix/photogallery.detail.list/templates/.default/template.php"));
?>
<div class="photo-navigation photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>

<div class="photo-controls photo-controls-search">
	<noindex>
	<ul class="photo-controls">
		<li class="photo-control photo-control-first photo-control-search-rank <?=($arResult["REQUEST"]["HOW"] == "d" ? "" : " photo-control-active")?>">
			<a href="<?=$arResult["SEARCH_URL"]?>"><?=GetMessage("SEARCH_SORT_BY_RANK")?></a>
		</li>
		<li class="photo-control photo-control-last photo-control-search-date <?=($arResult["REQUEST"]["HOW"] == "d" ? " photo-control-active" : "")?>">
			<a href="<?=$arResult["SEARCH_URL"]?>&amp;how=d"><?=GetMessage("SEARCH_SORT_BY_DATE")?></a>
		</li>
	</ul>
	<div class="empty-clear"></div>
	</noindex>
</div>
<?
endif;
?>