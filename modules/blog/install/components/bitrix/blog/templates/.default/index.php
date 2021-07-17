<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="body-blog">
<div class="blog-mainpage">
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
			"GROUP_ID" 				=> $arParams["GROUP_ID"],
		),
	$component
);?>
	<?if($USER->IsAuthorized() && CBlog::CanUserCreateBlog($USER->GetID()))
	{
		if(!CBlog::GetByOwnerID($USER->GetID(), $arParams["GROUP_ID"]))
		{
			?>
		<div class="blog-mainpage-create-blog">
		<a href="<?=$arResult["PATH_TO_NEW_BLOG"]?>" class="blog-author-icon"></a>&nbsp;<a href="<?=$arResult["PATH_TO_NEW_BLOG"]?>"><?=GetMessage("BLOG_CREATE_BLOG")?></a>
		</div>
			<?
		}
	}

?>
<script>
function BXBlogTabShow(id, type)
{
	if(type == 'post')
	{
		
		document.getElementById('new-posts').style.display = 'inline';
		document.getElementById('popular-posts').style.display = 'inline';
		document.getElementById('commented-posts').style.display = 'inline';
		
		document.getElementById('new-posts-title').style.display = 'none';
		document.getElementById('popular-posts-title').style.display = 'none';
		document.getElementById('commented-posts-title').style.display = 'none';
		
		document.getElementById('new-posts-content').style.display = 'none';
		document.getElementById('popular-posts-content').style.display = 'none';
		document.getElementById('commented-posts-content').style.display = 'none';

		document.getElementById(id).style.display = 'none';
		document.getElementById(id+'-title').style.display = 'inline';
		document.getElementById(id+'-content').style.display = 'block';
	}
	else if(type == 'blog')
	{
		document.getElementById('new-blogs').style.display = 'inline-block';
		document.getElementById('popular-blogs').style.display = 'inline-block';
		
		document.getElementById('new-blogs-title').style.display = 'none';
		document.getElementById('popular-blogs-title').style.display = 'none';
		
		document.getElementById('new-blogs-content').style.display = 'none';
		document.getElementById('popular-blogs-content').style.display = 'none';

		document.getElementById(id).style.display = 'none';
		document.getElementById(id+'-title').style.display = 'inline-block';
		document.getElementById(id+'-content').style.display = 'block';
	}
	
}
</script>
<div class="blog-mainpage-side-left">
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<div class="blog-tab-title">
			<span id="new-posts-title"><?=GetMessage("BC_NEW_POSTS_MES")?></span>
			<span id="commented-posts-title" style="display:none;"><?=GetMessage("BC_COMMENTED_POSTS_MES")?></span>
			<span id="popular-posts-title" style="display:none;"><?=GetMessage("BC_POPULAR_POSTS_MES")?></span>
		</div>		
		<div class="blog-tab-items">
			<span id="new-posts" style="display:none;"><a href="javascript:BXBlogTabShow('new-posts', 'post');"><?=GetMessage("BC_NEW_POSTS")?></a></span>
			<span id="commented-posts"><a href="javascript:BXBlogTabShow('commented-posts', 'post');"><?=GetMessage("BC_COMMENTED_POSTS")?></a></span>
			<span id="popular-posts"><a href="javascript:BXBlogTabShow('popular-posts', 'post');"><?=GetMessage("BC_POPULAR_POSTS")?></a></span>
		</div>
	</div>	
</div>
	<div class="blog-clear-float"></div>
	<div class="blog-tab-content">
	<div id="new-posts-content" style="display:block;">
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"SEO_USER"			=> $arParams["SEO_USER"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			),
			$component 
		);
		?>
	</div>
	<div id="commented-posts-content" style="display:none;">
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"SEO_USER"			=> $arParams["SEO_USER"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			),
			$component 
		);
		?>
	</div>
	<div id="popular-posts-content" style="display:none;">
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"SEO_USER"			=> $arParams["SEO_USER"],
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
			),
			$component 
		);
		?>
	</div>
	<?
	if($arResult["PATH_TO_HISTORY"] == '')
		$arResult["PATH_TO_HISTORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=history");
	?>
	<noindex>
	<div style="text-align:right;"><a href="<?=$arResult["PATH_TO_HISTORY"]?>" rel="nofollow"><?=GetMessage("BC_ALL_POSTS")?></a></div>
	</noindex>
	</div>
	
<?if(empty($arParams["GROUP_ID"]) || (is_array($arParams["GROUP_ID"]) && count($arParams["GROUP_ID"]) > 1))
{
	?>
	<div class="blog-tab-container">
		<div class="blog-tab-left"></div>
		<div class="blog-tab-right"></div>
		<div class="blog-tab">
			<span class="blog-tab-title"><?=GetMessage("BC_GROUPS")?></span>
		</div>	
	</div>
		<div class="blog-tab-content">
			<?
			$APPLICATION->IncludeComponent(
					"bitrix:blog.groups", 
					"", 
					Array(
							"GROUPS_COUNT"	=> 0,
							"COLS_COUNT"	=> 2,
							"GROUP_VAR"		=> $arResult["ALIASES"]["group_id"],
							"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
							"PATH_TO_GROUP"	=> $arResult["PATH_TO_GROUP"],
							"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
							"CACHE_TIME"	=> $arResult["CACHE_TIME"],
							"GROUP_ID" 			=> $arParams["GROUP_ID"],
						),
					$component 
				);
			?>
		</div>
	<?
}
?>
</div>
<div class="blog-mainpage-side-right">
<?
if(IsModuleInstalled("search")):
?>
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<span class="blog-tab-title"><?=GetMessage("BC_SEARCH_TAG")?></span>
	</div>	
</div>
	<div class="blog-tab-content">
		<div class="blog-mainpage-search-cloud">
		<?
		$APPLICATION->IncludeComponent(
			"bitrix:search.tags.cloud",
			"",
			Array(
				"FONT_MAX" => 18, 
				"FONT_MIN" => 10,
				"COLOR_NEW" => $arParams["COLOR_NEW"],
				"COLOR_OLD" => $arParams["COLOR_OLD"],
				"ANGULARITY" => $arParams["ANGULARITY"], 
				"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"], 
				"SHOW_CHAIN" => "N", 
				"COLOR_TYPE" => $arParams["COLOR_TYPE"], 
				"WIDTH" => $arParams["WIDTH"], 
				"SEARCH" => "", 
				"TAGS" => "", 
				"SORT" => "NAME", 
				"PAGE_ELEMENTS" => "70", 
				"PERIOD" => $arParams["PERIOD"], 
				"URL_SEARCH" => $arResult["PATH_TO_SEARCH"], 
				"TAGS_INHERIT" => "N", 
				"CHECK_DATES" => "Y", 
				"arrFILTER" => Array("blog"), 
				"CACHE_TYPE" => "A", 
				"CACHE_TIME" => "3600" 
			),
			$component
		);
		?>
		</div>
	</div>
<?endif?>
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<span class="blog-tab-title"><?=GetMessage("BC_NEW_COMMENTS")?></span>
	</div>	
</div>
	<div class="blog-tab-content">
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
	"GROUP_ID" 			=> $arParams["GROUP_ID"],
	"SEO_USER"			=> $arParams["SEO_USER"],
	"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
	"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
	"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
	"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
	"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
	"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
	"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
	"NO_URL_IN_COMMENTS" => $arParams["NO_URL_IN_COMMENTS"],
	"NO_URL_IN_COMMENTS_AUTHORITY" => $arParams["NO_URL_IN_COMMENTS_AUTHORITY"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],
	),
	$component 
);
		?>
	</div>
<div class="blog-tab-container">
	<div class="blog-tab-left"></div>
	<div class="blog-tab-right"></div>
	<div class="blog-tab">
		<span class="blog-tab-items">
			<span id="new-blogs"><a href="javascript:BXBlogTabShow('new-blogs', 'blog');"><?=GetMessage("BC_NEW_BLOGS")?></a></span>
			<span id="popular-blogs" style="display:none;"><a href="javascript:BXBlogTabShow('popular-blogs', 'blog');"><?=GetMessage("BC_POPULAR_BLOGS")?></a></span>
		</span>
		<span class="blog-tab-title">
			<span id="new-blogs-title" style="display:none;"><?=GetMessage("BC_NEW_BLOGS_MES")?></span>
			<span id="popular-blogs-title"><?=GetMessage("BC_POPULAR_BLOGS_MES")?></span>
		</span>
	</div>	
</div>
	<div class="blog-tab-content">
	<div id="new-blogs-content" style="display:none;">
	<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.new_blogs", 
				"", 
				Array(
						"BLOG_COUNT"	=> $arResult["BLOG_COUNT_MAIN"],
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
						"SEO_USER"			=> $arParams["SEO_USER"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
						"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					),
				$component 
			);
		?>
	</div>
	<div id="popular-blogs-content" style="display:block;">
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.popular_blogs", 
				"", 
				Array(
						"BLOG_COUNT"	=> $arResult["BLOG_COUNT_MAIN"],
						"PERIOD_DAYS"	=>	$arResult["PERIOD_DAYS"],
						"BLOG_VAR"		=> $arResult["ALIASES"]["blog"],
						"USER_VAR"		=> $arResult["ALIASES"]["user_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_BLOG"	=> $arResult["PATH_TO_BLOG"],
						"PATH_TO_USER"	=> $arResult["PATH_TO_USER"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
						"GROUP_ID"		=> $arParams["GROUP_ID"],
						"SEO_USER"		=> $arParams["SEO_USER"],
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
						"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
						"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
						"PATH_TO_SONET_USER_PROFILE" => $arParams["PATH_TO_SONET_USER_PROFILE"],
						"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
						"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					),
				$component 
			);
		?>
	</div>
	<?
	//if((!is_array($arParams["GROUP_ID"]) && IntVal($arParams["GROUP_ID"]) > 0) || (is_array($arParams["GROUP_ID"]) && count($arParams["GROUP_ID"]) == 1))
	//{
		if($arResult["PATH_TO_GROUP"] == '')
			$arResult["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=group&".$arResult["ALIASES"]["group_id"]."=#group_id#");
		?>
		
		<div style="text-align:right;"><a href="<?=CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP"], array("group_id" => "all"))?>"><?=GetMessage("BC_ALL_BLOGS")?></a></div>
		<?
	//}
	?>

	</div>
	<div class="blog-rss-subscribe">
	<?
	$APPLICATION->IncludeComponent(
			"bitrix:blog.rss.link",
			"mainpage",
			Array(
					"RSS1"				=> "N",
					"RSS2"				=> "Y",
					"ATOM"				=> "N",
					"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
					"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
					"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
					"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
					"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
					"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
					//"GROUP_ID" 			=> $arParams["GROUP_ID"],
					"MODE"				=> "S",
				),
			$component 
		);
	?>
	</div>
</div>
<div class="blog-clear-float"></div>

<?
if($arResult["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_TITLE"));
?>
</div>
</div>