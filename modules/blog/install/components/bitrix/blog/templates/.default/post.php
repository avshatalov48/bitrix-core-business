<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">
<?
$APPLICATION->IncludeComponent(
	"bitrix:blog.menu",
	"",
	Array(
			"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
			"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
			"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
			"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
			"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
			"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
			"PATH_TO_BLOG_EDIT"		=> $arResult["PATH_TO_BLOG_EDIT"],
			"PATH_TO_BLOG_INDEX"	=> $arResult["PATH_TO_BLOG_INDEX"],
			"PATH_TO_DRAFT"			=> $arResult["PATH_TO_DRAFT"],
			"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
			"PATH_TO_USER_FRIENDS"	=> $arResult["PATH_TO_USER_FRIENDS"],
			"PATH_TO_USER_SETTINGS"	=> $arResult["PATH_TO_USER_SETTINGS"],
			"PATH_TO_GROUP_EDIT"	=> $arResult["PATH_TO_GROUP_EDIT"],
			"PATH_TO_CATEGORY_EDIT"	=> $arResult["PATH_TO_CATEGORY_EDIT"],
			"PATH_TO_RSS_ALL"		=> $arResult["PATH_TO_RSS_ALL"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
		),
	$component
);

$APPLICATION->IncludeComponent(
		"bitrix:blog.post", 
		"", 
		Array(
				"BLOG_VAR"				=> $arResult["ALIASES"]["blog"],
				"POST_VAR"				=> $arResult["ALIASES"]["post_id"],
				"USER_VAR"				=> $arResult["ALIASES"]["user_id"],
				"PAGE_VAR"				=> $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"			=> $arResult["PATH_TO_BLOG"],
				"PATH_TO_POST"			=> $arResult["PATH_TO_POST"],				
				"PATH_TO_BLOG_CATEGORY"	=> $arResult["PATH_TO_BLOG_CATEGORY"],
				"PATH_TO_POST_EDIT"		=> $arResult["PATH_TO_POST_EDIT"],
				"PATH_TO_USER"			=> $arResult["PATH_TO_USER"],
				"PATH_TO_SMILE"			=> $arResult["PATH_TO_SMILE"],
				"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
				"ID"					=> $arResult["VARIABLES"]["post_id"],
				"CACHE_TYPE"			=> $arResult["CACHE_TYPE"],
				"CACHE_TIME"			=> $arResult["CACHE_TIME"],
				"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
				"SET_TITLE"				=> $arResult["SET_TITLE"],
				"POST_PROPERTY"			=> $arParams["POST_PROPERTY"],
				"DATE_TIME_FORMAT"		=> $arResult["DATE_TIME_FORMAT"],
				"GROUP_ID" 				=> $arParams["GROUP_ID"],
				"SEO_USER"				=> $arParams["SEO_USER"],
				"NAME_TEMPLATE" 		=> $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" 			=> $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT"	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_SONET_USER_PROFILE" 	=> $arParams["PATH_TO_SONET_USER_PROFILE"],
				"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
				"PATH_TO_VIDEO_CALL" 	=> $arParams["PATH_TO_VIDEO_CALL"],
				"USE_SHARE" 			=> $arParams["USE_SHARE"],
				"SHARE_HIDE" 			=> $arParams["SHARE_HIDE"],
				"SHARE_TEMPLATE" 		=> $arParams["SHARE_TEMPLATE"],
				"SHARE_HANDLERS" 		=> $arParams["SHARE_HANDLERS"],
				"SHARE_SHORTEN_URL_LOGIN"	=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
				"SHARE_SHORTEN_URL_KEY" 	=> $arParams["SHARE_SHORTEN_URL_KEY"],
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"RATING_TYPE" => $arParams["RATING_TYPE"],
				"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
				"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
				"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
				"SEO_USE" => $arParams["SEO_USE"],
			),
		$component 
	);
	?>
	<div align="right">
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.rss.link",
				"group",
				Array(
						"RSS1"				=> "N",
						"RSS2"				=> "Y",
						"ATOM"				=> "N",
						"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
						"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
						"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
						"PATH_TO_POST_RSS"		=> $arResult["PATH_TO_POST_RSS"],
						"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
						"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
						"POST_ID"			=> $arResult["VARIABLES"]["post_id"],
						"MODE"				=> "C",
						"PARAM_GROUP_ID" 			=> $arParams["GROUP_ID"],
						"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
					),
				$component 
			);
		?>
	</div>
	<?

	$componentCommentName = "bitrix:blog.post.comment";
	if(isset($arParams["COMMENTS_LIST_VIEW"]) && $arParams["COMMENTS_LIST_VIEW"] == "Y")
		$componentCommentName = 'bitrix:blog.post.comment.list';
	
	$componentCommentParams = array(
		"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
		"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
		"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
		"POST_VAR"		=> $arResult["ALIASES"]["post_id"],
		"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
		"PATH_TO_POST"	=> $arResult["PATH_TO_POST"],
		"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
		"PATH_TO_SMILE"	=> $arResult["PATH_TO_SMILE"],
		"BLOG_URL"		=> $arResult["VARIABLES"]["blog"],
		"ID"			=> $arResult["VARIABLES"]["post_id"],
		"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
		"CACHE_TIME"	=> $arResult["CACHE_TIME"],
		"COMMENTS_COUNT" => $arResult["COMMENTS_COUNT"],
		"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
		"USE_ASC_PAGING"	=> $arParams["USE_ASC_PAGING"],
		"NOT_USE_COMMENT_TITLE"	=> $arParams["NOT_USE_COMMENT_TITLE"],
		"GROUP_ID" 			=> $arParams["GROUP_ID"],
		"SEO_USER"			=> $arParams["SEO_USER"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
		"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
		"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
		"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
		"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"RATING_TYPE" => $arParams["RATING_TYPE"],
		"SMILES_COUNT" => $arParams["SMILES_COUNT"],
		"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
		"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
		"EDITOR_RESIZABLE" => $arParams["COMMENT_EDITOR_RESIZABLE"],
		"EDITOR_DEFAULT_HEIGHT" => $arParams["COMMENT_EDITOR_DEFAULT_HEIGHT"],
		"EDITOR_CODE_DEFAULT" => $arParams["COMMENT_EDITOR_CODE_DEFAULT"],
		"ALLOW_VIDEO" => $arParams["COMMENT_ALLOW_VIDEO"],
		"ALLOW_IMAGE_UPLOAD" => $arParams["COMMENT_ALLOW_IMAGE_UPLOAD"],
		"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
		"SHOW_SPAM" => $arParams["SHOW_SPAM"],
		"NO_URL_IN_COMMENTS" => $arParams["NO_URL_IN_COMMENTS"],
		"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["NO_URL_IN_COMMENTS_AUTHORITY"],
		"AJAX_POST" => $arParams["AJAX_POST"],
		"COMMENT_PROPERTY" => $arParams["COMMENT_PROPERTY"],
		"AJAX_PAGINATION" => $arParams["AJAX_PAGINATION"],
	);
	if(isset($arParams["USER_CONSENT"]))
		$componentCommentParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
	if(isset($arParams["USER_CONSENT_ID"]))
		$componentCommentParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
	if(isset($arParams["USER_CONSENT_IS_CHECKED"]))
		$componentCommentParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
	if(isset($arParams["USER_CONSENT_IS_LOADED"]))
		$componentCommentParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
	$componentCommentParams["USER_CONSENT_FOR_REGISTERED"] = "Y";	// get consent for registered
	$APPLICATION->IncludeComponent(
		$componentCommentName,
		'',
		$componentCommentParams,
		$component 
	);
?>
</div>