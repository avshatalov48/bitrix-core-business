<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult as $arPost)
{
	if($arPost["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	<span class="blog-author">
		<a href="<?=$arPost["urlToAuthor"]?>" title="<?=GetMessage("BLOG_BLOG_M_TITLE_BLOG")?>" class="blog-user-grey"></a>&nbsp;
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
	</span><br clear="left" />
	<span class="blog-post-date"><b><a href="<?=$arPost["urlToPost"]?>"><?
	if($arPost["TITLE"] <> '')
		echo $arPost["TITLE"]; 
	else 
		echo GetMessage("BLOG_MAIN_MES_NO_SUBJECT"); 
	?></a></b></span><br />
	<?
	if($arPost["IMG"] <> '')
		echo $arPost["IMG"];
	?>
	<small><?=$arPost["TEXT_FORMATED"]?></small><br clear="left"/>
	<span class="blog-post-info">
		<br />
		<a href="<?=$arPost["urlToPost"]?>" class="blog-clock" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arPost["DATE_PUBLISH_FORMATED"]?></a>
		<?if(intval($arPost["VIEWS"]) > 0):?>
			&nbsp;&nbsp;<a href="<?=$arPost["urlToPost"]?>" class="blog-eye" title="<?=GetMessage("BLOG_BLOG_M_VIEWS")?>"><?=$arPost["VIEWS"]?></a>
		<?endif;?>
		<?if(intval($arPost["NUM_COMMENTS"]) > 0):?>
			&nbsp;&nbsp;<a href="<?=$arPost["urlToPost"]?>" class="blog-comment-num" title="<?=GetMessage("BLOG_BLOG_M_NUM_COMMENTS")?>"><?=$arPost["NUM_COMMENTS"]?></a>
		<?endif;?>
	</span>
	<?
}
?>	
