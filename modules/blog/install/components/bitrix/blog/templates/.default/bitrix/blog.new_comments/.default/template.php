<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-mainpage-comment">
<?
foreach($arResult as $arComment)
{
	if($arComment["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	
	<div class="blog-mainpage-item">
	<div class="blog-author">
	<?
	if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($arComment["urlToBlog"]) > 0 || strlen($arComment["urlToAuthor"]) > 0) && array_key_exists("ALIAS", $arComment["BlogUser"]) && strlen($arComment["BlogUser"]["ALIAS"]) > 0)
		$arTmpUser = array(
			"NAME" => "",
			"LAST_NAME" => "",
			"SECOND_NAME" => "",
			"LOGIN" => "",
			"NAME_LIST_FORMATTED" => $arComment["BlogUser"]["~ALIAS"],
			);
	elseif (strlen($arComment["urlToBlog"]) > 0 || strlen($arComment["urlToAuthor"]) > 0)
		$arTmpUser = array(
			"NAME" => $arComment["arUser"]["~NAME"],
			"LAST_NAME" => $arComment["arUser"]["~LAST_NAME"],
			"SECOND_NAME" => $arComment["arUser"]["~SECOND_NAME"],
			"LOGIN" => $arComment["arUser"]["~LOGIN"],
			"NAME_LIST_FORMATTED" => "",
		);
	?>
	<?if(strlen($arComment["urlToBlog"])>0)
	{
		if($arParams["SEO_USER"] == "Y"):?>
			<noindex>
				<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>" rel="nofollow"></a>
			</noindex>
		<?else:?>
			<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>"></a>
		<?endif;?>
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
			'',
			array(
				"ID" => $arComment["arUser"]["ID"],
				"HTML_ID" => "blog_new_comments_".$arComment["arUser"]["ID"],
				"NAME" => $arTmpUser["NAME"],
				"LAST_NAME" => $arTmpUser["LAST_NAME"],
				"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
				"LOGIN" => $arTmpUser["LOGIN"],
				"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
				"USE_THUMBNAIL_LIST" => "N",
				"PROFILE_URL" => $arComment["urlToAuthor"],
				"PROFILE_URL_LIST" => $arComment["urlToBlog"],							
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
		<?
	}
	elseif(strlen($arComment["urlToAuthor"])>0)
	{
		if($arParams["SEO_USER"] == "Y"):?>
			<noindex>
				<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>" rel="nofollow"></a>
			</noindex>
		<?else:?>
			<a class="blog-author-icon" href="<?=$arComment["urlToAuthor"]?>"></a>
		<?endif;?>
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
			'',
			array(
				"ID" => $arComment["arUser"]["ID"],
				"HTML_ID" => "blog_new_comments_".$arComment["arUser"]["ID"],
				"NAME" => $arTmpUser["NAME"],
				"LAST_NAME" => $arTmpUser["LAST_NAME"],
				"SECOND_NAME" => $arTmpUser["SECOND_NAME"],
				"LOGIN" => $arTmpUser["LOGIN"],
				"NAME_LIST_FORMATTED" => $arTmpUser["NAME_LIST_FORMATTED"],
				"USE_THUMBNAIL_LIST" => "N",
				"PROFILE_URL" => $arComment["urlToAuthor"],
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
		<?
	}
	else
	{
		?>
		<span class="blog-author-icon"></span><?=$arComment["AuthorName"]?>
		<?
	}?>
	</div>
	<div class="blog-mainpage-meta">
		<a href="<?=$arComment["urlToComment"]?>" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arComment["DATE_CREATE_FORMATED"]?></a>
	</div>
	<div class="blog-clear-float"></div>
	<!--<div class="blog-mainpage-title"><a href="<?=$arComment["urlToComment"]?>"><?echo $arComment["POST_TITLE_FORMATED"]; ?></a></div>//-->
	<div class="blog-mainpage-content">
		<a href="<?=$arComment["urlToComment"]?>"><?=$arComment["TEXT_FORMATED"]?></a>
	</div>

	<div class="blog-clear-float"></div>
	</div>
	<?
}
?>	
</div>