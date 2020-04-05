<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div id="blog-posts-content">
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="blog-notes blog-note-box">
		<div class="blog-note-text">
			<ul>
				<?
				foreach($arResult["OK_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="blog-textinfo blog-note-box">
		<div class="blog-textinfo-text">
			<ul>
				<?
				foreach($arResult["MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="blog-errors blog-note-box blog-note-error">
		<div class="blog-error-text">
			<ul>
				<?
				foreach($arResult["ERROR_MESSAGE"] as $v)
				{
					?>
					<li><?=$v?></li>
					<?
				}
				?>
			</ul>
		</div>
	</div>
	<?
}

if(count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $ind => $CurPost)
	{
		?>
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.blog.post", 
				"", 
				Array(
						"FROM_LOG"				=> "Y",
						"POST_VAR"				=> $arParams["POST_VAR"],
						"USER_VAR"				=> $arParams["USER_VAR"],
						"PAGE_VAR"				=> $arParams["PAGE_VAR"],
						"PATH_TO_BLOG"			=> $arParams["PATH_TO_BLOG"],
						"PATH_TO_POST" 			=> $arParams["PATH_TO_POST"],
						"PATH_TO_POST_IMPORTANT" => $arParams["PATH_TO_POST_IMPORTANT"],
						"PATH_TO_BLOG_CATEGORY"	=> $arParams["PATH_TO_CATEGORY"],
						"PATH_TO_POST_EDIT"		=> $arParams["PATH_TO_POST_EDIT"],
						"PATH_TO_USER"			=> $arParams["PATH_TO_USER"],
						"PATH_TO_GROUP"			=> $arParams["PATH_TO_GROUP"],
						"PATH_TO_SMILE" 		=> $arParams["PATH_TO_BLOG_SMILE"], 
						"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
						"SET_NAV_CHAIN" 		=> "N", 
						"SET_TITLE"				=> "N",
						"POST_PROPERTY"			=> $arParams["POST_PROPERTY"],
						"DATE_TIME_FORMAT"		=> $arParams["DATE_TIME_FORMAT"],
						"USER_ID" 				=> $arParams["USER_ID"],
						"GROUP_ID" 				=> $this->__component->arParams["BLOG_GROUP_ID"],
						"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
						"SHOW_YEAR" 			=> $arParams["SHOW_YEAR"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"PATH_TO_VIDEO_CALL" 	=> $arParams["PATH_TO_VIDEO_CALL"],
						"USE_SHARE" 			=> $arParams["USE_SHARE"],
						"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"],
						"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"],
						"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"],
						"SHARE_SHORTEN_URL_LOGIN"	=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
						"SHARE_SHORTEN_URL_KEY" 	=> $arParams["SHARE_SHORTEN_URL_KEY"],
						"SHOW_RATING" 			=> $arParams["SHOW_RATING"],
						"RATING_TYPE" 			=> $arParams["RATING_TYPE"],
						"IMAGE_MAX_WIDTH" 		=> $arParams["IMAGE_MAX_WIDTH"],
						"IMAGE_MAX_HEIGHT" 		=> $arParams["IMAGE_MAX_HEIGHT"],
						"ALLOW_POST_CODE" 		=> $arParams["ALLOW_POST_CODE"],
						"ID"					=> $CurPost["ID"],
						"POST_DATA"				=> $CurPost,
						"RATING_DATA"			=> $arResult["RATING"],
						"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
						"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
					),
				$component 
			);
		?>
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
		echo $arResult["NAV_STRING"];
}
else
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>	
</div>