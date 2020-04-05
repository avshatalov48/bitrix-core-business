<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-search-page">
<form method="get" action="<?=$arParams["SEARCH_PAGE"]?>">
<input type="hidden" name="<?=$arParams["PAGE_VAR"]?>" value="search">
<table cellspacing="2" cellpadding="0" border="0" class="blog-search">
	<tr>
	<td><?=GetMessage("BLOG_MAIN_SEARCH_SEARCH")?></td>
	<td><input type="text" name="q" size="20" value="<?=$arResult["q"]?>"></td>
	<td>
		<select name="where">
		<?foreach($arResult["WHERE"] as $k => $v)
		{
			?><option value="<?=$k?>"<?=$k==$arResult["where"]?" selected":""?>><?=$v?></option><?
		}
		?>
		</select>
	</td>
	<td><input type="submit" value="&nbsp;&nbsp;OK&nbsp;&nbsp;"></td>
	</tr>
</table>
<?if($arResult["how"]=="d"):?>
	<input type="hidden" name="how" value="d">
<?endif;?>
</form>

<?
if(strlen($arResult["ERROR_MESSAGE"])<=0)
{
	foreach($arResult["SEARCH_RESULT"] as $v)
	{
		?>
		
			<div class="blog-mainpage-item">


			<div class="blog-mainpage-title"><a href="<?echo $v["URL"]?>"><?echo $v["TITLE_FORMATED"]; ?></a></div>
			<?if(strlen($v["BODY_FORMATED"]) > 0)
			{
				?>
				<div class="blog-mainpage-content">
					<?=$v["BODY_FORMATED"]?>
				</div>
				<?
			}
			?>
			
			<?if(strlen($v["AuthorName"])>0 && strlen($v["BLOG_URL"])>0)
			{
				?>
				<div class="blog-author">
					<?if($arParams["SEO_USER"] == "Y"):?>
						<noindex>
						<a class="blog-author-icon" href="<?=$v["USER_URL"]?>" rel="nofollow"></a>
						</noindex>
					<?else:?>
						<a class="blog-author-icon" href="<?=$v["USER_URL"]?>"></a>
					<?endif;?>
					<?
					if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($v["BLOG_URL"]) > 0 || strlen($v["USER_URL"]) > 0) && array_key_exists("ALIAS", $v["BlogUser"]) && strlen($v["BlogUser"]["ALIAS"]) > 0)
						$arTmpUser = array(
							"NAME" => "",
							"LAST_NAME" => "",
							"SECOND_NAME" => "",
							"LOGIN" => "",
							"NAME_LIST_FORMATTED" => $v["BlogUser"]["~ALIAS"],
						);
					elseif (strlen($v["BLOG_URL"]) > 0 || strlen($v["USER_URL"]) > 0)
						$arTmpUser = array(
							"NAME" => $v["arUser"]["~NAME"],
							"LAST_NAME" => $v["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $v["arUser"]["~SECOND_NAME"],
							"LOGIN" => $v["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);	
					?>
					<?
					$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
						'',
						array(
							"ID" => $v["arUser"]["ID"],
							"HTML_ID" => "blog_search_".$v["arUser"]["ID"],
							"NAME" => $arTmpUser["NAME"],
							"LAST_NAME" => $arTmpUser["LAST_NAME"],
							"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
							"LOGIN" => $arTmpUser["LOGIN"],
							"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
							"USE_THUMBNAIL_LIST" => "N",
							"PROFILE_URL" => $v["USER_URL"],
							"PROFILE_URL_LIST" => $v["BLOG_URL"],							
							"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
							"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
							"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
							"SHOW_YEAR" => $arParams["SHOW_YEAR"],
							"CACHE_TYPE" => $arParams["CACHE_TYPE"],
							"CACHE_TIME" => $arParams["CACHE_TIME"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
							"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
							"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
							"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_SONET_USER_PROFILE"],
							"INLINE" => "Y",
							"SEO_USER" => $arParams["SEO_USER"],
						),
						false,
						array("HIDE_ICONS" => "Y")
					);
					?>					
				</div>
				<?
			}
			?>
			
				<div class="blog-mainpage-meta"><?=$v["FULL_DATE_CHANGE_FORMATED"]?></div>

			<div class="blog-clear-float"></div>
			</div>
			<div class="blog-line"></div>
		<?
	}
	if(strlen($arResult["NAV_STRING"]) > 0):
		?><p><?=$arResult["NAV_STRING"]?></p><?
	endif;
		
	if(strlen($arResult["ORDER_LINK"])>0)
	{
		if($arResult["how"]=="d"):
			?><p><a href="<?=$arResult["ORDER_LINK"]?>"><?=GetMessage("BLOG_MAIN_SEARCH_SORT_RELEVATION")?></a>&nbsp;|&nbsp;<b><?=GetMessage("BLOG_MAIN_SEARCH_SORTED_DATE")?></b></p><?
		else:
			?><p><b><?=GetMessage("BLOG_MAIN_SEARCH_SORTED_RELEVATION")?></b>&nbsp;|&nbsp;<a href="<?=$arResult["ORDER_LINK"]?>"><?=GetMessage("BLOG_MAIN_SEARCH_SORT_DATE")?></a></p><?
		endif;
	}
}
else
	echo ShowError($arResult["ERROR_MESSAGE"]);
?>
</div>