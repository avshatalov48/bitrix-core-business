<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI;

$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/style.css');
$APPLICATION->AddHeadScript("/bitrix/components/bitrix/socialnetwork.log.ex/templates/.default/script.js");
UI\Extension::load([
	'socialnetwork.livefeed',
	'socialnetwork.commentaux'
]);


?><div id="blog-posts-content">
<?
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

if(
	is_array($arResult["POST"])
	&& !empty($arResult["POST"])
)
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
						"PATH_TO_BLOG_CATEGORY"	=> $arParams["PATH_TO_CATEGORY"] ?? null,
						"PATH_TO_POST_EDIT"		=> $arParams["PATH_TO_POST_EDIT"],
						"PATH_TO_USER"			=> $arParams["PATH_TO_USER"] ?? null,
						"PATH_TO_GROUP"			=> $arParams["PATH_TO_GROUP"] ?? null,
						"PATH_TO_SMILE" 		=> $arParams["PATH_TO_BLOG_SMILE"] ?? null,
						"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"] ?? null,
						"SET_NAV_CHAIN" 		=> "N", 
						"SET_TITLE"				=> "N",
						"POST_PROPERTY"			=> $arParams["POST_PROPERTY"] ?? null,
						"DATE_TIME_FORMAT"		=> $arParams["DATE_TIME_FORMAT"],
						"USER_ID" 				=> $arParams["USER_ID"],
						"GROUP_ID" 				=> $arParams["GROUP_ID"],
						"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"] ?? null,
						"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"] ?? null,
						"SHOW_YEAR" 			=> $arParams["SHOW_YEAR"] ?? null,
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"] ?? null,
						"PATH_TO_VIDEO_CALL" 	=> $arParams["PATH_TO_VIDEO_CALL"] ?? null,
						"USE_SHARE" 			=> $arParams["USE_SHARE"] ?? null,
						"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"] ?? null,
						"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"] ?? null,
						"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"] ?? null,
						"SHARE_SHORTEN_URL_LOGIN"	=> $arParams["SHARE_SHORTEN_URL_LOGIN"] ?? null,
						"SHARE_SHORTEN_URL_KEY" 	=> $arParams["SHARE_SHORTEN_URL_KEY"] ?? null,
						"SHOW_RATING" 			=> "N",
						"RATING_TYPE" 			=> $arParams["RATING_TYPE"] ?? null,
						"IMAGE_MAX_WIDTH" 		=> $arParams["IMAGE_MAX_WIDTH"] ?? null,
						"IMAGE_MAX_HEIGHT" 		=> $arParams["IMAGE_MAX_HEIGHT"] ?? null,
						"ALLOW_POST_CODE" 		=> $arParams["ALLOW_POST_CODE"],
						"ID"					=> $CurPost["ID"],
						"POST_DATA"				=> $CurPost,
						"TYPE"					=> "DRAFT",
						"ADIT_MENU"				=> $CurPost["ADIT_MENU"],
						"BLOG_NO_URL_IN_COMMENTS" => $arParams["BLOG_NO_URL_IN_COMMENTS"],
						"BLOG_NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["BLOG_NO_URL_IN_COMMENTS_AUTHORITY"],
						"SELECTOR_VERSION"		=> 2
					),
				$component 
			);
		?>
		<?
	}
	if($arResult["NAV_STRING"] <> '')
		echo $arResult["NAV_STRING"];
}
?>	
</div>