<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("blog") >= "R")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "blog",
		"sort" => 550,
		"text" => GetMessage("BLG_AM_BLOGS"),
		"title"=> GetMessage("BLG_AM_BLOGS_ALT"),
		"icon" => "blog_menu_icon",
		"page_icon" => "blog_page_icon",
		"items_id" => "menu_blog",
		"items" => array(
			array(
				"text" => GetMessage("BLG_AM_BLOGS1"),
				"url" => "blog_blog.php?lang=".LANGUAGE_ID,
				"more_url" => array("blog_blog_edit.php"),
				"title" => GetMessage("BLG_AM_BLOGS1_ALT")
			),
			/*
			array(
				"text" => GetMessage("BLG_AM_POST"),
				"url" => "blog_post.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("BLG_AM_POST_ALT")
			),
			*/
			array(
				"text" => GetMessage("BLG_AM_COMMENT"),
				"url" => "blog_comment.php?lang=".LANGUAGE_ID,
				"more_url" => array(),
				"title" => GetMessage("BLG_AM_COMMENT_ALT")
			),
			array(
				"text" => GetMessage("BLG_AM_GROUPS"),
				"url" => "blog_group.php?lang=".LANGUAGE_ID,
				"more_url" => array("blog_group_edit.php"),
				"title" => GetMessage("BLG_AM_GROUPS_ALT")
			)
		)
	);

	return $aMenu;
}
return false;
?>
