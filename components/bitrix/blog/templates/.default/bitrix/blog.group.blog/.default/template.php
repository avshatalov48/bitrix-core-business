<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<?
if($arResult["FATAL_ERROR"] <> '')
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<?=$arResult["FATAL_ERROR"]?>
		</div>
	</div>
	<?
}
else
{
	if(is_array($arResult["BLOG"]) && count($arResult["BLOG"])>0)
	{
		foreach($arResult["BLOG"] as $arBlog)
		{
			if(intval($arBlog["LAST_POST_ID"])>0 || $arParams["SHOW_BLOG_WITHOUT_POSTS"] == "Y")
			{
				?>
			
			<div class="blog-mainpage-item">
			<?if(intval($arBlog["OWNER_ID"]) > 0)
			{
				?>
				<div class="blog-author">
				<?if($arParams["SEO_USER"] == "Y"):?>
					<noindex>
						<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>" rel="nofollow"></a>
					</noindex>
				<?else:?>
					<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>"></a>
				<?endif;?>
				<?
				if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($arBlog["urlToBlog"] <> '' || $arBlog["urlToAuthor"] <> '') && array_key_exists("BLOG_USER_ALIAS", $arBlog) && $arBlog["BLOG_USER_ALIAS"] <> '')
					$arTmpUser = array(
						"NAME" => "",
						"LAST_NAME" => "",
						"SECOND_NAME" => "",
						"LOGIN" => "",
						"NAME_LIST_FORMATTED" => $arBlog["~BLOG_USER_ALIAS"],
						);
				elseif ($arBlog["urlToBlog"] <> '' || $arBlog["urlToAuthor"] <> '')
					$arTmpUser = array(
						"NAME" => $arBlog["~OWNER_NAME"],
						"LAST_NAME" => $arBlog["~OWNER_LAST_NAME"],
						"SECOND_NAME" => $arBlog["~OWNER_SECOND_NAME"],
						"LOGIN" => $arBlog["~OWNER_LOGIN"],
						"NAME_LIST_FORMATTED" => "",
					);
				?>
				<?
				$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
					'',
					array(
						"ID" => $arBlog["OWNER_ID"],
						"HTML_ID" => "blog_group_blog_".$arBlog["OWNER_ID"],
						"NAME" => $arTmpUser["NAME"],
						"LAST_NAME" => $arTmpUser["LAST_NAME"],
						"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
						"LOGIN" => $arTmpUser["LOGIN"],
						"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
						"USE_THUMBNAIL_LIST" => "N",
						"PROFILE_URL" => $arBlog["urlToAuthor"],
						"PROFILE_URL_LIST" => $arBlog["urlToBlog"],							
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

			<div class="blog-mainpage-title"><a href="<?=$arBlog["urlToBlog"]?>"><?echo $arBlog["NAME"]; ?></a></div>
			<?if($arBlog["DESCRIPTION"] <> '')
			{
				?>
				<div class="blog-mainpage-content">
					<?=$arBlog["DESCRIPTION"]?>
				</div>
				<?
			}
			?>
			<?if(intval($arBlog["LAST_POST_ID"])>0):?>
				<div class="blog-mainpage-meta"><?=GetMessage("B_B_GR_LAST_M")?> <a href="<?=$arBlog["urlToPost"]?>"><?=$arBlog["LAST_POST_DATE_FORMATED"]?></a></div>
			<?endif;?>

			<div class="blog-clear-float"></div>
			</div>
			<div class="blog-line"></div>
					
				<?
			}
		}
		if($arResult["NAV_STRING"] <> '')
			echo $arResult["NAV_STRING"];
	}
	else
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
}
?>	