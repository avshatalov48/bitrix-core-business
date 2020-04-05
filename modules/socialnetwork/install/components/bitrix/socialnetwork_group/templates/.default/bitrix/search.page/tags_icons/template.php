<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arCloudParams = Array(
	"SEARCH" => $arResult["REQUEST"]["~QUERY"],
	"TAGS" => $arResult["REQUEST"]["~TAGS"],
	"CHECK_DATES" => $arParams["CHECK_DATES"],
	"arrFILTER" => $arParams["arrFILTER"],
	"SORT" => $arParams["TAGS_SORT"],
	"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
	"PERIOD" => $arParams["TAGS_PERIOD"],
	"URL_SEARCH" => $arParams["TAGS_URL_SEARCH"],
	"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],
	"FONT_MAX" => $arParams["FONT_MAX"],
	"FONT_MIN" => $arParams["FONT_MIN"],
	"COLOR_NEW" => $arParams["COLOR_NEW"],
	"COLOR_OLD" => $arParams["COLOR_OLD"],
	"PERIOD_NEW_TAGS" => $arParams["PERIOD_NEW_TAGS"],
	"SHOW_CHAIN" => $arParams["SHOW_CHAIN"],
	"COLOR_TYPE" => $arParams["COLOR_TYPE"],
	"WIDTH" => $arParams["WIDTH"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"RESTART" => $arParams["RESTART"],
);

if(is_array($arCloudParams["arrFILTER"]))
{
	foreach($arCloudParams["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			$arCloudParams["arrFILTER_main"] = $arParams["arrFILTER_main"];
		}
		elseif($strFILTER=="forum" && IsModuleInstalled("forum"))
		{
			$arCloudParams["arrFILTER_forum"] = $arParams["arrFILTER_forum"];
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			foreach($arParams["arrFILTER_".$strFILTER] as $strIBlock)
				$arCloudParams["arrFILTER_".$strFILTER] = $arParams["arrFILTER_".$strFILTER];
		}
		elseif($strFILTER=="blog")
		{
			$arCloudParams["arrFILTER_blog"] = $arParams["arrFILTER_blog"];
		}
		elseif($strFILTER=="socialnetwork")
		{
			$arCloudParams["arrFILTER_socialnetwork"] = $arParams["arrFILTER_socialnetwork"];
		}
		elseif($strFILTER=="socialnetwork_user")
		{
			$arCloudParams["arrFILTER_socialnetwork_user"] = $arParams["arrFILTER_socialnetwork_user"];
		}
	}
}

$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default", $arCloudParams, $component);

?><br /><div class="search-page">
<form action="" method="get" name="sonet_content_search_form" id="sonet_content_search_form">
	<?

	$params_to_kill = array("q", "how", "where", "tags", "clear_cache", "bitrix_include_areas", "show_include_exec_time", "show_page_exec_time", "bitrix_show_mode");
	if (array_key_exists("FILTER_NAME", $arParams) && strlen($arParams["FILTER_NAME"]) > 0)
		$params_to_kill[] = $arParams["FILTER_NAME"];
	if (array_key_exists("FILTER_DATE_NAME", $arParams) && strlen($arParams["FILTER_DATE_NAME"]) > 0)
	{
		$params_to_kill[] = $arParams["FILTER_DATE_NAME"]."_from";
		$params_to_kill[] = $arParams["FILTER_DATE_NAME"]."_to";
	}

	foreach($_GET as $key=>$val)
	{
		if (!in_array($key, $params_to_kill))
		{
			if(!is_array($val))
			{
				?>
				<input type="hidden" name="<?=htmlspecialcharsex($key)?>" value="<?=htmlspecialcharsex($val)?>">
				<?
			}
			else
			{
				foreach($val as $value)
				{
					?>
					<input type="hidden" name="<?=htmlspecialcharsex($key)?>[]" value="<?=htmlspecialcharsex($value)?>">
					<?
				}
			}
		}
	}
	?>
	<input type="hidden" name="tags" value="<?echo $arResult["REQUEST"]["TAGS"]?>" />
	<table class="sonet-query" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td style="width: 100%;"><input type="text" class="search-query" name="q" value="<?=$arResult["REQUEST"]["QUERY"]?>" /></td>
		<td>&nbsp;</td>
		<td><input type="submit" clas="sonet-search-filter-button" value="<?=GetMessage("SEARCH_GO")?>" /></td>
	</tr>
	</table>
	<?if(
		$arParams["SHOW_WHERE"] ||
		array_key_exists("DROPDOWN_SONET", $arResult) && is_array($arResult["DROPDOWN_SONET"]) && count($arResult["DROPDOWN_SONET"]) > 0 ||
		array_key_exists("FILTER_DATE_NAME", $arParams) && strlen($arParams["FILTER_DATE_NAME"]) > 0
	):
		?><div class="sonet-search-advanced"><div class="sonet-search-advanced-filter"><a href="#" onclick="document.getElementById('sonet_content_search_filter').style.display = document.getElementById('sonet_content_search_filter').style.display == 'none' ? 'block' : 'none'; return false;"><?=GetMessage("SEARCH_ADDITIONAL_FILTER")?></a></div></div><?

		$default_style = (
					strlen($GLOBALS[$arParams["FILTER_NAME"]]["SONET_FEATURE"]) > 0 ||
					strlen($_REQUEST[$arParams["FILTER_DATE_NAME"]."_from"]) > 0 ||
					strlen($_REQUEST[$arParams["FILTER_DATE_NAME"]."_to"]) > 0 ?
					"block" : "none"
				);
		?>
		<div id="sonet_content_search_filter" style="display: <?=$default_style;?>; padding-top: 10px;">
		<table class="sonet-search-filter" cellpadding="0" cellspacing="0">
		<?
		if($arParams["SHOW_WHERE"]):?>
			<tr>
				<td class="sonet-search-filter-name">&nbsp;</td>
				<td class="sonet-search-filter-field"><select name="where" class="select-field">
					<option value=""><?=GetMessage("SEARCH_ALL")?></option>
					<?foreach($arResult["DROPDOWN"] as $key=>$value):?>
						<option value="<?=$key?>"<?if($arResult["REQUEST"]["WHERE"]==$key) echo " selected"?>><?=$value?></option>
					<?endforeach?>
				</select></td>
			</tr>
		<?endif;

		if(array_key_exists("DROPDOWN_SONET", $arResult) && is_array($arResult["DROPDOWN_SONET"]) && count($arResult["DROPDOWN_SONET"]) > 0):?>
			<tr>
				<td class="sonet-search-filter-name"><?=GetMessage("SEARCH_ADDITIONAL_FILTER_FEATURE")?>:</td>
				<td class="sonet-search-filter-field"><select name="<?=$arParams["FILTER_NAME"]?>" class="select-field">
					<option value=""><?=GetMessage("SEARCH_ALL")?></option>
					<?foreach($arResult["DROPDOWN_SONET"] as $key=>$value):?>
						<option value="<?=$key?>"<?if($GLOBALS[$arParams["FILTER_NAME"]]["SONET_FEATURE"]==$key) echo " selected"?>><?=$value?></option>
					<?endforeach?>
				</select></td>
			</tr>
		<?endif;

		if(array_key_exists("FILTER_DATE_NAME", $arParams) && strlen($arParams["FILTER_DATE_NAME"]) > 0):
			?>
			<tr>
				<td class="sonet-search-filter-name"><?=GetMessage("SEARCH_ADDITIONAL_FILTER_DATE")?>:</td>
				<td class="sonet-search-filter-field"><?
				$GLOBALS["APPLICATION"]->IncludeComponent(
					'bitrix:main.calendar',
					'',
					array(
						'FORM_NAME' => "sonet_content_search_form",
						'SHOW_INPUT' => 'Y',
						'INPUT_NAME' => $arParams["FILTER_DATE_NAME"]."_from",
						'INPUT_VALUE' => $_REQUEST[$arParams["FILTER_DATE_NAME"]."_from"],
						'INPUT_NAME_FINISH' => $arParams["FILTER_DATE_NAME"]."_to",
						'INPUT_VALUE_FINISH' => $_REQUEST[$arParams["FILTER_DATE_NAME"]."_to"],
						'INPUT_ADDITIONAL_ATTR' => 'class="input-field" size="10"',
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);
				?>
				</td>
			</tr>
		<?endif;
		?>
		<tr>
			<td colspan="2" class="sonet-search-filter-field"><input type="submit" clas="sonet-search-filter-button" value="<?=GetMessage("SEARCH_GO")?>" /></td>
		</tr>
		</table></div><?
	endif;
	?>
	<input type="hidden" name="how" value="<?echo $arResult["REQUEST"]["HOW"]=="d"? "d": "r"?>" />
</form>
<?if(isset($arResult["REQUEST"]["ORIGINAL_QUERY"])):
	?>
	<div class="search-language-guess">
		<?echo GetMessage("CT_BSP_KEYBOARD_WARNING", array("#query#"=>'<a href="'.$arResult["ORIGINAL_QUERY_URL"].'">'.$arResult["REQUEST"]["ORIGINAL_QUERY"].'</a>'))?>
	</div><br /><?
endif;?>
<?if($arResult["REQUEST"]["QUERY"] === false && $arResult["REQUEST"]["TAGS"] === false):?>
<?elseif($arResult["ERROR_CODE"]!=0):?>
	<p><?=GetMessage("SEARCH_ERROR")?></p>
	<?ShowError($arResult["ERROR_TEXT"]);?>
	<p><?=GetMessage("SEARCH_CORRECT_AND_CONTINUE")?></p>
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
<?elseif(count($arResult["SEARCH"])>0):?>
	<?=$arResult["NAV_STRING"]?>
	<br /><hr />
	<table cellpadding="5" cellspacing="0" border="0">
	<?foreach($arResult["SEARCH"] as $arItem):?>
		<tr valign="top">
		<td><img src="<?echo $arItem["ICON"]?>"></td>
		<td>
		<a href="<?echo $arItem["URL"]?>"><?echo $arItem["TITLE_FORMATED"]?></a>
		<p><?echo $arItem["BODY_FORMATED"]?></p>
		<?if (
			$arParams["SHOW_RATING"] == "Y"
			&& strlen($arItem["RATING_TYPE_ID"]) > 0
			&& $arItem["RATING_ENTITY_ID"] > 0
		):?>
			<div class="search-item-rate"><?
				$APPLICATION->IncludeComponent(
					"bitrix:rating.vote", $arParams["RATING_TYPE"],
					Array(
						"ENTITY_TYPE_ID" => $arItem["RATING_TYPE_ID"],
						"ENTITY_ID" => $arItem["RATING_ENTITY_ID"],
						"OWNER_ID" => $arItem["USER_ID"],
						"USER_VOTE" => $arItem["RATING_USER_VOTE_VALUE"],
						"USER_HAS_VOTED" => $arItem["RATING_USER_VOTE_VALUE"] == 0? 'N': 'Y',
						"TOTAL_VOTES" => $arItem["RATING_TOTAL_VOTES"],
						"TOTAL_POSITIVE_VOTES" => $arItem["RATING_TOTAL_POSITIVE_VOTES"],
						"TOTAL_NEGATIVE_VOTES" => $arItem["RATING_TOTAL_NEGATIVE_VOTES"],
						"TOTAL_VALUE" => $arItem["RATING_TOTAL_VALUE"],
						"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
					),
					$component,
					array("HIDE_ICONS" => "Y")
				);?>
			</div>
		<?endif;?>
		<small><?=GetMessage("SEARCH_MODIFIED")?> <?=$arItem["DATE_CHANGE"]?><br /></small><?
		if($arItem["CHAIN_PATH"]):?>
			<small><?=GetMessage("SEARCH_PATH")?>&nbsp;<?=$arItem["CHAIN_PATH"]?></small><?
		endif;
		?><hr />
		</td>
		</tr>
	<?endforeach;?>
	</table>
	<?=$arResult["NAV_STRING"]?>
	<br />
	<p>
	<?if($arResult["REQUEST"]["HOW"]=="d"):?>
		<a href="<?=$arResult["URL"]?>&amp;how=r"><?=GetMessage("SEARCH_SORT_BY_RANK")?></a>&nbsp;|&nbsp;<b><?=GetMessage("SEARCH_SORTED_BY_DATE")?></b>
	<?else:?>
		<b><?=GetMessage("SEARCH_SORTED_BY_RANK")?></b>&nbsp;|&nbsp;<a href="<?=$arResult["URL"]?>&amp;how=d"><?=GetMessage("SEARCH_SORT_BY_DATE")?></a>
	<?endif;?>
	</p>
<?else:?>
	<?ShowNote(GetMessage("SEARCH_NOTHING_TO_FOUND"));?>
<?endif;?>
</div>