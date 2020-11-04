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

<?$APPLICATION->IncludeComponent(
		"bitrix:blog.search", 
		"", 
		Array(
				"PAGE_RESULT_COUNT"	=> 0,
				"SEARCH_PAGE"		=> $arResult["PATH_TO_SEARCH"],
				"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
				"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
				"USER_VAR"			=> $arResult["ALIASES"]["user_id"],
				"PAGE_VAR"			=> $arResult["ALIASES"]["page"],
				"PATH_TO_BLOG"		=> $arResult["PATH_TO_BLOG"],
				"PATH_TO_POST"		=> $arResult["PATH_TO_POST"],
				"PATH_TO_USER"		=> $arResult["PATH_TO_USER"],
				"SET_TITLE"			=> "N",
			),
		$component 
	);?>

	<?if($USER->IsAuthorized() && CBlog::CanUserCreateBlog($USER->GetID()))
	{
		$bHaveBlog = false;
		if(intval($arParams["GROUP_ID"]) > 0)
		{
			$dbBl = CBlog::GetList(Array(), Array("GROUP_ID" => $arParams["GROUP_ID"], "OWNER_ID" => $USER->GetID(), "ACTIVE" => "Y", "GROUP_SITE_ID" => SITE_ID));
			if($dbBl->Fetch())
				$bHaveBlog = true;
			
		}
		elseif(CBlog::GetByOwnerID($USER->GetID()))
			$bHaveBlog = true;
			
		if(!$bHaveBlog)
		{
			?>
			<h4><a href="<?=$arResult["PATH_TO_NEW_BLOG"]?>"><img src="<?=$templateFolder?>/images/add_blog.gif" width="42" height="42" border="0" title="<?=GetMessage("BLOG_CREATE_BLOG")?>" align="absmiddle"></a>&nbsp;<a href="<?=$arResult["PATH_TO_NEW_BLOG"]?>"><?=GetMessage("BLOG_CREATE_BLOG")?></a></h4> 
			<br />
			<?
		}
	}

?>
<script>
<!--
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
//-->
</script>
<table width="100%" style="font-size:100%;">
<tr>
	<td width="50%" valign="top">
	<h4><?=GetMessage("BC_MESSAGES")?></h4>
	<a id="new_posts_title" class="blog-tab-selected" href="#" onclick="BXBlogTabShow('new_posts', 'post'); return false;"><?=GetMessage("BC_NEW_POSTS")?></a><a id="commented_posts_title" class="blog-tab" href="#" onclick="BXBlogTabShow('commented_posts', 'post'); return false;"><?=GetMessage("BC_COMMENTED_POSTS")?></a><a id="popular_posts_title" class="blog-tab" href="#" onclick="BXBlogTabShow('popular_posts', 'post'); return false;"><?=GetMessage("BC_POPULAR_POSTS")?></a>
<br /><br />
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			),
			$component 
		);
		?>
		<?
		if($arResult["PATH_TO_HISTORY"] == '')
			$arResult["PATH_TO_HISTORY"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=history");
		?>
		<div style="text-align:right;"><a href="<?=$arResult["PATH_TO_HISTORY"]?>"><?=GetMessage("BC_ALL_POSTS")?></a></div>
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
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
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			),
			$component 
		);
		?>
	</div>
	</td>
	<td width="50%" valign="top" style="padding-left:10px;">
<?
if(IsModuleInstalled("search")):
?>
<h4><?=GetMessage("BC_SEARCH_TAG")?></h4>
<?
$APPLICATION->IncludeComponent(
	"bitrix:search.tags.cloud",
	"",
	Array(
		"FONT_MAX" => (intval($arParams["FONT_MAX"]) >0 ? $arParams["FONT_MAX"] : 20),
		"FONT_MIN" => (intval($arParams["FONT_MIN"]) >0 ? $arParams["FONT_MIN"] : 10),
		"COLOR_NEW" => ($arParams["COLOR_NEW"] <> '' ? $arParams["COLOR_NEW"] : "3f75a2"),
		"COLOR_OLD" => ($arParams["COLOR_OLD"] <> '' ? $arParams["COLOR_OLD"] : "8D8D8D"),
		"ANGULARITY" => $arParams["ANGULARITY"], 
		"PERIOD_NEW_TAGS" => $arResult["PERIOD_NEW_TAGS"], 
		"SHOW_CHAIN" => "N", 
		"COLOR_TYPE" => $arParams["COLOR_TYPE"], 
		"WIDTH" => $arParams["WIDTH"], 
		"SEARCH" => "", 
		"TAGS" => "", 
		"SORT" => "NAME", 
		"PAGE_ELEMENTS" => "150", 
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
<?endif?>
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
	"GROUP_ID" 			=> $arParams["GROUP_ID"],
	),
	$component 
);
		?>
	<br />	

	<a id="new_blogs_title" class="blog-tab-selected" href="#1" onclick="BXBlogTabShow('new_blogs', 'blog'); return false;"><?=GetMessage("BC_NEW_BLOGS")?></a><a id="popular_blogs_title" id="popular_blogs_title" class="blog-tab" href="#1" onclick="BXBlogTabShow('popular_blogs', 'blog'); return false;"><?=GetMessage("BC_POPULAR_BLOGS")?></a>
	<br /><br />
	<div id="new_blogs" style="display:block;">
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
					),
				$component 
			);
		?>
		<?
		if(intval($arParams["GROUP_ID"]) > 0)
		{
			if($arResult["PATH_TO_GROUP"] == '')
				$arResult["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=group&".$arResult["ALIASES"]["group_id"]."=#group_id#");
			?>
			<br />
			<div style="text-align:right;"><a href="<?=CComponentEngine::MakePathFromTemplate($arResult["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]))?>"><?=GetMessage("BC_ALL_BLOGS")?></a></div>
			<?
		}
		?>
	</div>
	<div id="popular_blogs" style="display:none;">
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
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
					),
				$component 
			);
		?>
	</div>
	<br /><div align="left"><?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.rss.link",
				"",
				Array(
						"RSS1"				=> "Y",
						"RSS2"				=> "Y",
						"ATOM"				=> "Y",
						"BLOG_VAR"			=> $arResult["ALIASES"]["blog"],
						"POST_VAR"			=> $arResult["ALIASES"]["post_id"],
						"GROUP_VAR"			=> $arResult["ALIASES"]["group_id"],
						"PATH_TO_RSS"		=> $arResult["PATH_TO_RSS"],
						"PATH_TO_RSS_ALL"	=> $arResult["PATH_TO_RSS_ALL"],
						"BLOG_URL"			=> $arResult["VARIABLES"]["blog"],
						"GROUP_ID" 			=> $arParams["GROUP_ID"],
						"MODE"				=> "S",
					),
				$component 
			);
		?>
		</div>
	</td>
</tr>
<?if(empty($arParams["GROUP_ID"]))
{
?>
<tr>
	<td colspan="2">
	<br />
	<h4><?=GetMessage("BC_GROUPS")?></h4>
	<div style="height:1px; width:80%; overflow:hidden; background-color:#C7D2D5; margin-top:8px; margin-bottom:3px;"></div>
		<?
		$APPLICATION->IncludeComponent(
				"bitrix:blog.groups", 
				"", 
				Array(
						"GROUPS_COUNT"	=> 0,
						"COLS_COUNT"	=> 3,
						"GROUP_VAR"		=> $arResult["ALIASES"]["group_id"],
						"PAGE_VAR"		=> $arResult["ALIASES"]["page"],
						"PATH_TO_GROUP"	=> $arResult["PATH_TO_GROUP"],
						"CACHE_TYPE"	=> $arResult["CACHE_TYPE"],
						"CACHE_TIME"	=> $arResult["CACHE_TIME"],
					),
				$component 
			);
		?>
	</td>
</tr>
<?
}
?>
</table>
<?
if($arResult["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_TITLE"));
?>