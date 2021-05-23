<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-mainpage-blogs">
<?
foreach($arResult as $arBlog)
{
	if($arBlog["FIRST_BLOG"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	
	<div class="blog-mainpage-item">
	<?if(intval($arBlog["SOCNET_GROUP_ID"]) <= 0):?>
	<div class="blog-author">
		<?if($arParams["SEO_USER"] == "Y"):?>
			<noindex>
				<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>" rel="nofollow"></a>
			</noindex>
		<?else:?>
			<a class="blog-author-icon" href="<?=$arBlog["urlToAuthor"]?>"></a>
		<?endif;?>
		<?
		if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($arBlog["urlToBlog"] <> '' || $arBlog["urlToAuthor"] <> '') && array_key_exists("ALIAS", $arBlog["BlogUser"]) && $arBlog["BlogUser"]["ALIAS"] <> '')
			$arTmpUser = array(
				"NAME" => "",
				"LAST_NAME" => "",
				"SECOND_NAME" => "",
				"LOGIN" => "",
				"NAME_LIST_FORMATTED" => $arBlog["BlogUser"]["~ALIAS"],
				);
		elseif ($arBlog["urlToBlog"] <> '' || $arBlog["urlToAuthor"] <> '')
			$arTmpUser = array(
				"NAME" => $arBlog["arUser"]["~NAME"],
				"LAST_NAME" => $arBlog["arUser"]["~LAST_NAME"],
				"SECOND_NAME" => $arBlog["arUser"]["~SECOND_NAME"],
				"LOGIN" => $arBlog["arUser"]["~LOGIN"],
				"NAME_LIST_FORMATTED" => "",
			);
		?>
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
			'',
			array(
				"ID" => $arBlog["arUser"]["ID"],
				"HTML_ID" => "blog_popular_blogs_".$arBlog["arUser"]["ID"],
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
	<div class="blog-clear-float"></div>
	<?endif;?>
	<div class="blog-mainpage-title"><a href="<?=$arBlog["urlToBlog"]?>"><?echo $arBlog["NAME"]; ?></a></div>
	<?if($arParams["SHOW_DESCRIPTION"] == "Y" && $arBlog["DESCRIPTION"] <> '')
	{
		?>
		<div class="blog-mainpage-content">
			<?=$arBlog["DESCRIPTION"]?>
		</div>
		<?
	}
	?>
	<div class="blog-clear-float"></div>
	</div>
	<?
}
?>	
</div>