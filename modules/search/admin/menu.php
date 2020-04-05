<?
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;

if($APPLICATION->GetGroupRight("search")!="D")
{
	$aMenu = array(
		"parent_menu" => "global_menu_settings",
		"section" => "search",
		"sort" => 200,
		"text" => GetMessage("mnu_search"),
		"title" => GetMessage("mnu_search_title"),
		"icon" => "search_menu_icon",
		"page_icon" => "search_page_icon",
		"items_id" => "menu_search",
		"items" => array(
			array(
				"text" => GetMessage("mnu_reindex"),
				"url" => "search_reindex.php?lang=".LANGUAGE_ID,
				"more_url" => Array("search_reindex.php"),
				"title" => GetMessage("mnu_reindex_alt"),
			),
			array(
				"text" => GetMessage("mnu_sitemap"),
				"url" => "search_sitemap.php?lang=".LANGUAGE_ID,
				"more_url" => Array("search_sitemap.php"),
				"title" => GetMessage("mnu_sitemap_alt"),
			),
			array(
				"text" => GetMessage("mnu_customrank"),
				"url" => "search_customrank_admin.php?lang=".LANGUAGE_ID,
				"more_url" => array("search_customrank_admin.php", "search_customrank_edit.php"),
				"title" => GetMessage("mnu_customrank_alt"),
			),
			array(
				"text" => GetMessage("mnu_statistic"),
				"title" => GetMessage("mnu_statistic_alt"),
				"items_id" => "menu_search_stat",
				"items" => array(
					array(
						"text" => GetMessage("mnu_stat_phrase_list"),
						"url" => "search_phrase_list.php?lang=".LANGUAGE_ID,
						"more_url" => Array("search_phrase_list.php"),
					),
					array(
						"text" => GetMessage("mnu_stat_phrase_stat"),
						"url" => "search_phrase_stat.php?lang=".LANGUAGE_ID,
						"more_url" => Array("search_phrase_stat.php"),
					),
					array(
						"text" => GetMessage("mnu_stat_tags_stat"),
						"url" => "search_tags_stat.php?lang=".LANGUAGE_ID,
						"more_url" => Array("search_tags_stat.php"),
					),
				),
			),
		)
	);
	return $aMenu;
}
return false;
?>
