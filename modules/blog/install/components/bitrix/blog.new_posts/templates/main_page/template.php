<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
?>
<div class="bx-new-layout-include">
<?
if(empty($arResult))
	echo GetMessage("BLOG_BLOG_EMPTY");

foreach($arResult as $arPost)
{
	?>
	<div class="blg-mp-info">
		<div class="blg-mp-info-inner">
			<div class="blg-mp-date intranet-date"><?echo $arPost["DATE_PUBLISH_FORMATED"];?></div>
			<div class="blg-mp-name">
			<?
			if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($arPost["urlToBlog"] <> '' || $arPost["urlToAuthor"] <> '') && array_key_exists("BLOG_USER_ALIAS", $arPost) && $arPost["BLOG_USER_ALIAS"] <> '')
				$arTmpUser = array(
					"NAME" => "",
					"LAST_NAME" => "",
					"SECOND_NAME" => "",
					"LOGIN" => "",
					"NAME_LIST_FORMATTED" => $arPost["~BLOG_USER_ALIAS"],
				);
			elseif ($arPost["urlToBlog"] <> '' || $arPost["urlToAuthor"] <> '')
				$arTmpUser = array(
					"NAME" => $arPost["~AUTHOR_NAME"],
					"LAST_NAME" => $arPost["~AUTHOR_LAST_NAME"],
					"SECOND_NAME" => $arPost["~AUTHOR_SECOND_NAME"],
					"LOGIN" => $arPost["~AUTHOR_LOGIN"],
					"NAME_LIST_FORMATTED" => "",
				);	
			?>			
			<?
			$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
				'',
				array(
					"ID" => $arPost["AUTHOR_ID"],
					"HTML_ID" => "blog_new_posts_".$arPost["AUTHOR_ID"],
					"NAME" => $arTmpUser["NAME"],
					"LAST_NAME" => $arTmpUser["LAST_NAME"],
					"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
					"LOGIN" => $arTmpUser["LOGIN"],
					"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
					"USE_THUMBNAIL_LIST" => "N",
					"PROFILE_URL" => $arPost["urlToAuthor"],
					"PROFILE_URL_LIST" => $arPost["urlToBlog"],							
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
			<div class="blg-mp-post"><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]?></a></div>
			<?if(intval($arPost["VIEWS"]) > 0):?>
				<div class="blg-mp-post"><?=GetMessage("BLOG_BLOG_M_VIEWS")?> <?=$arPost["VIEWS"]?></div>
			<?endif;?>
			<?if(intval($arPost["NUM_COMMENTS"]) > 0):?>
				<div class="blg-mp-post"><?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?> <?=$arPost["NUM_COMMENTS"]?></div>
			<?endif;?>
			<div class="bx-users-delimiter"></div>
		</div>
	</div>
	<?
}
?>	
</div>