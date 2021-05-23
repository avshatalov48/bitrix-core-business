<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if(count($arResult) <= 0)
	echo GetMessage("SONET_BLOG_LM_EMPTY");
	
foreach($arResult as $arPost)
{
	if($arPost["FIRST"]!="Y")
	{
		?><div class="blog-profile-line"></div><?
	}
	?>
	<span class="blog-profile-post-date"><?=$arPost["DATE_PUBLISH_FORMATED"]?></span><br />
	<?
	if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && (strlen($arPost["urlToBlog"]) > 0 || strlen($arPost["urlToAuthor"]) > 0) && array_key_exists("BLOG_USER_ALIAS", $arPost) && strlen($arPost["BLOG_USER_ALIAS"]) > 0)
		$arTmpUser = array(
			"NAME" => "",
			"LAST_NAME" => "",
			"SECOND_NAME" => "",
			"LOGIN" => "",
			"NAME_LIST_FORMATTED" => $arPost["~BLOG_USER_ALIAS"],
		);
	elseif (strlen($arPost["urlToBlog"]) > 0 || strlen($arPost["urlToAuthor"]) > 0)
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
	<br />
	<b><a href="<?=$arPost["urlToPost"]?>"><?echo $arPost["TITLE"]; ?></a></b><br /><br />
	<?
	if(strlen($arPost["IMG"]) > 0)
		echo $arPost["IMG"];
	?>
	<?=$arPost["TEXT_FORMATED"]?><br clear="left"/><br />

	<span class="blog-profile-post-info">
		<?if(IntVal($arPost["VIEWS"]) > 0):?>
			<span class="blog-eye"><?=GetMessage("SONET_BLOG_LM_VIEWS")?></span>:&nbsp;<?=$arPost["VIEWS"]?>&nbsp;
		<?endif;?>
		<?if(IntVal($arPost["NUM_COMMENTS"]) > 0):?>
			<span class="blog-comment-num"><?=GetMessage("SONET_BLOG_LM_NUM_COMMENTS")?></span>:&nbsp;<?=$arPost["NUM_COMMENTS"]?>
		<?endif;?>
	</span>
	<?
}