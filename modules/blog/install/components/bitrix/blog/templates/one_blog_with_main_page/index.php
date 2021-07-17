<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent(
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
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"SET_NAV_CHAIN"			=> $arResult["SET_NAV_CHAIN"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
		),
	$component
);?>

<script>
function BXBlogTabShow(id, type)
{
	if(type == 'post')
	{
		document.getElementById('new_posts').style.display = 'none';
		document.getElementById('popular_posts').style.display = 'none';
		document.getElementById('commented_posts').style.display = 'none';
		document.getElementById('new_posts_title').className = 'blog-tab';
		document.getElementById('popular_posts_title').className = 'blog-tab';
		document.getElementById('commented_posts_title').className = 'blog-tab';

		document.getElementById(id).style.display = 'block';
		document.getElementById(id+'_title').className = 'blog-tab-selected';
	}
	else if(type == 'blog')
	{
		document.getElementById('new_blogs').style.display = 'none';
		document.getElementById('popular_blogs').style.display = 'none';
		document.getElementById('new_blogs_title').className = 'blog-tab';
		document.getElementById('popular_blogs_title').className = 'blog-tab';

		document.getElementById(id).style.display = 'block';
		document.getElementById(id+'_title').className = 'blog-tab-selected';
	}
	
}
</script>
<table width="100%">
<tr>
	<td width="50%" valign="top">
	<h4><?=GetMessage("BC_MESSAGES")?></h4>
	<a id="new_posts_title" class="blog-tab-selected" href="#" onclick="BXBlogTabShow('new_posts', 'post'); return false;"><?=GetMessage("BC_NEW_POSTS")?></a><a id="commented_posts_title" class="blog-tab" href="#" onclick="BXBlogTabShow('commented_posts', 'post'); return false;"><?=GetMessage("BC_COMMENTED_POSTS")?></a><a id="popular_posts_title" class="blog-tab" href="#" onclick="BXBlogTabShow('popular_posts', 'post'); return false;"><?=GetMessage("BC_POPULAR_POSTS")?></a><br /><br />

	<div id="new_posts" style="display:block;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.new_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arResult["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arResult["MESSAGE_LENGTH"],
			"PATH_TO_BLOG"		=>	$arResult["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arResult["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arResult["PATH_TO_USER"],
			"PATH_TO_SMILE"		=>	$arResult["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arResult["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arResult["CACHE_TIME"],
			"BLOG_VAR"			=>	$arResult["ALIASES"]["blog"],
			"POST_VAR"			=>	$arResult["ALIASES"]["post_id"],
			"USER_VAR"			=>	$arResult["ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arResult["ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			),
			$component 
		);
		?>
	</div>
	<div id="commented_posts" style="display:none;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.commented_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arResult["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arResult["MESSAGE_LENGTH"],
			"PERIOD_DAYS"		=>	$arResult["PERIOD_DAYS"],
			"PATH_TO_BLOG"		=>	$arResult["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arResult["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arResult["PATH_TO_USER"],
			"PATH_TO_SMILE"		=>	$arResult["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arResult["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arResult["CACHE_TIME"],
			"BLOG_VAR"			=>	$arResult["ALIASES"]["blog"],
			"POST_VAR"			=>	$arResult["ALIASES"]["post_id"],
			"USER_VAR"			=>	$arResult["ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arResult["ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			),
			$component 
		);
		?>
	</div>
	<div id="popular_posts" style="display:none;">
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.popular_posts", ".default", Array(
			"MESSAGE_COUNT"		=> $arResult["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arResult["MESSAGE_LENGTH"],
			"PERIOD_DAYS"		=>	$arResult["PERIOD_DAYS"],
			"PATH_TO_BLOG"		=>	$arResult["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arResult["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arResult["PATH_TO_USER"],
			"PATH_TO_SMILE"		=>	$arResult["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arResult["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arResult["CACHE_TIME"],
			"BLOG_VAR"			=>	$arResult["ALIASES"]["blog"],
			"POST_VAR"			=>	$arResult["ALIASES"]["post_id"],
			"USER_VAR"			=>	$arResult["ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arResult["ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			),
			$component 
		);
		?>
	</div>
	</td>
	<td width="50%" valign="top" style="padding-left:10px;">

	<h4><?=GetMessage("BC_NEW_COMMENTS")?></h4>
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.new_comments", ".default", Array(
	"COMMENT_COUNT"		=> $arResult["MESSAGE_COUNT_MAIN"],
	"MESSAGE_LENGTH"	=>	$arResult["MESSAGE_LENGTH"],
	"PATH_TO_BLOG"		=>	$arResult["PATH_TO_BLOG"],
	"PATH_TO_POST"		=>	$arResult["PATH_TO_POST"],
	"PATH_TO_USER"		=>	$arResult["PATH_TO_USER"],
	"PATH_TO_SMILE"		=>	$arResult["PATH_TO_SMILE"],
	"CACHE_TYPE"		=>	$arResult["CACHE_TYPE"],
	"CACHE_TIME"		=>	$arResult["CACHE_TIME"],
	"BLOG_VAR"			=>	$arResult["ALIASES"]["blog"],
	"POST_VAR"			=>	$arResult["ALIASES"]["post_id"],
	"USER_VAR"			=>	$arResult["ALIASES"]["user_id"],
	"PAGE_VAR"			=>	$arResult["ALIASES"]["page"],
	"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
	"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
	"GROUP_ID" 			=> $arParams["GROUP_ID"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
	"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
	"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
	),
	$component 
);
		?>
	<br />
		<div align="left">
		<h4><?echo GetMessage("BLOG_BLOG_FAVORITE");?></h4>
		<?
		$APPLICATION->IncludeComponent("bitrix:blog.blog.favorite", ".default", Array(
			"MESSAGE_COUNT"		=> $arResult["MESSAGE_COUNT_MAIN"],
			"MESSAGE_LENGTH"	=>	$arResult["MESSAGE_LENGTH"],
			"PERIOD_DAYS"		=>	$arResult["PERIOD_DAYS"],
			"PATH_TO_BLOG"		=>	$arResult["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arResult["PATH_TO_POST"],
			"PATH_TO_USER"		=>	$arResult["PATH_TO_USER"],
			"PATH_TO_SMILE"		=>	$arResult["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arResult["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arResult["CACHE_TIME"],
			"BLOG_VAR"			=>	$arResult["ALIASES"]["blog"],
			"POST_VAR"			=>	$arResult["ALIASES"]["post_id"],
			"USER_VAR"			=>	$arResult["ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arResult["ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arResult["DATE_TIME_FORMAT"],
			"BLOG_URL"				=> $arResult["VARIABLES"]["blog"],
			),
			$component 
		);
		?>
		</div>
	</td>
</tr>
</table>
<?
if($arResult["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_TITLE"));
?>