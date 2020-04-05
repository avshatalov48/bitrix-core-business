<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');

?><div id="blog-posts-content"><?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
			<?
			foreach($arResult["OK_MESSAGE"] as $v)
				echo $v."<br />";
			?>
		</span>
	</div><?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="feed-add-successfully">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
			<?
			foreach($arResult["MESSAGE"] as $v)
				echo $v."<br />";

			?>
		</span>
	</div><?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="feed-add-error">
		<span class="feed-add-info-text"><span class="feed-add-info-icon"></span>
			<?
			foreach($arResult["ERROR_MESSAGE"] as $v)
				echo $v."<br />";
			?>
		</span>
	</div><?
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
						"POST_VAR"				=> $arParams["POST_VAR"],
						"USER_VAR"				=> $arParams["USER_VAR"],
						"PAGE_VAR"				=> $arParams["PAGE_VAR"],
						"PATH_TO_BLOG"			=> $arParams["PATH_TO_BLOG"],
						"PATH_TO_POST" 			=> $arParams["PATH_TO_POST"],
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
						"GROUP_ID" 				=> $arParams["GROUP_ID"],
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
						"SHOW_RATING" 			=> "N",
						"RATING_TYPE" 			=> $arParams["RATING_TYPE"],
						"IMAGE_MAX_WIDTH" 		=> $arParams["IMAGE_MAX_WIDTH"],
						"IMAGE_MAX_HEIGHT" 		=> $arParams["IMAGE_MAX_HEIGHT"],
						"ALLOW_POST_CODE" 		=> $arParams["ALLOW_POST_CODE"],
						"ID"					=> $CurPost["ID"],
						"POST_DATA"				=> $CurPost,
						"TYPE"					=> "DRAFT",
						"ADIT_MENU"				=> $CurPost["ADIT_MENU"],
						"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
						"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"]
					),
					$this->getComponent()
			);
		?>
		<?
	}
	if(strlen($arResult["NAV_STRING"])>0)
	{
		echo $arResult["NAV_STRING"];
	}
}
?></div>