<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
foreach($arResult as $arComment)
{
	if($arComment["FIRST"]!="Y")
	{
		?><div class="blog-line"></div><?
	}
	?>
	<span class="blog-author">
	<?
	if($arComment["urlToAuthor"] <> '')
	{
		?>
		<a href="<?=$arComment["urlToAuthor"]?>" class="blog-user-grey"></a>&nbsp;
		<?
		if (COption::GetOptionString("blog", "allow_alias", "Y") == "Y" && ($arComment["urlToBlog"] <> '' || $arComment["urlToAuthor"] <> '') && array_key_exists("ALIAS", $arComment["BlogUser"]) && $arComment["BlogUser"]["ALIAS"] <> '')
			$arTmpUser = array(
				"NAME" => "",
				"LAST_NAME" => "",
				"SECOND_NAME" => "",
				"LOGIN" => "",
				"NAME_LIST_FORMATTED" => $arComment["BlogUser"]["~ALIAS"],
				);
		elseif ($arComment["urlToBlog"] <> '' || $arComment["urlToAuthor"] <> '')
			$arTmpUser = array(
				"NAME" => $arComment["arUser"]["~NAME"],
				"LAST_NAME" => $arComment["arUser"]["~LAST_NAME"],
				"SECOND_NAME" => $arComment["arUser"]["~SECOND_NAME"],
				"LOGIN" => $arComment["arUser"]["~LOGIN"],
				"NAME_LIST_FORMATTED" => "",
			);
		?>		
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
		<div class="blog-user-grey"></div>&nbsp;<?=$arComment["AuthorName"]?>
		<?
	}
	?>
	</span>
	<span class="blog-post-info">
		&nbsp;&nbsp;<a href="<?=$arComment["urlToComment"]?>" class="blog-clock" title="<?=GetMessage("BLOG_BLOG_M_DATE")?>"><?=$arComment["DATE_CREATE_FORMATED"]?></a>
	</span>
	
	<br clear="all"/>	
	<?
	if($arComment["TitleFormated"] <> '') 
	{
		?>
		<span class="blog-post-date"><b><a href="<?=$arComment["urlToComment"]?>"><?
			echo $arComment["TitleFormated"];
		?></a></b></span><br /><?
	}
	else
	{
		?><a href="<?=$arComment["urlToComment"]?>"><?
	}
	?>
	<small><?=$arComment["TEXT_FORMATED"]?></small>
	<?
	if($arComment["TitleFormated"] <> '') 
	{
		?></a><?
	}
	?>
	<br />

	<?
}
?>	
