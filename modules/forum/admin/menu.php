<?
IncludeModuleLangFile(__FILE__);
$Dict = array("W" => array(), "T" => array());
function __forum_chapter_menu_gen()
{
	$Dict = array("W" => array(), "T" => array());
	if (CModule::IncludeModule("forum"))
	{
		$db_res = CFilterDictionary::GetList();
		while ($res = $db_res->Fetch())
		{
			$Dict[$res["TYPE"]][] = array(
				"text" => htmlspecialcharsbx($res["TITLE"]),
				"url" => "/bitrix/admin/forum_".($res["TYPE"]=="T"?"letter":"words").".php?DICTIONARY_ID=".$res["ID"]."&amp;lang=".LANG,
				"more_url" => array(
					"/bitrix/admin/forum_".($res["TYPE"]=="T"?"letter":"words").".php?DICTIONARY_ID=".$res["ID"]."&lang=".LANG,
					"/bitrix/admin/forum_dictionary_edit.php?DICTIONARY_ID=".$res["ID"]."&lang=".LANG,
					"/bitrix/admin/forum_".($res["TYPE"]=="T"?"letter":"words")."_edit.php?DICTIONARY_ID=".$res["ID"]."&lang=".LANG),
				"title" => htmlspecialcharsbx($res["TITLE"]));
		}
	}
	return $Dict;
}
if($APPLICATION->GetGroupRight("forum") != "D")
{
	if ((
			method_exists($this, "IsSectionActive") &&
			($this->IsSectionActive("menu_forum_filter_DW") || $this->IsSectionActive("menu_forum_filter_DT"))
		) ||
		array_key_exists("TYPE", $_REQUEST) && ($_REQUEST["TYPE"] === "W" || $_REQUEST["TYPE"] === "T") ||
		array_key_exists("DICTIONARY_ID", $_REQUEST) && intval($_REQUEST["DICTIONARY_ID"]) > 0
	)
	{
		$Dict = __forum_chapter_menu_gen();
	}

	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"section" => "forum",
		"sort" => 550,
		"text" => GetMessage("FORUM_CONTROL"),
		"url" => "/bitrix/admin/forum_index.php?lang=".LANG,
		"more_url" => [
			"/bitrix/admin/forum_index.php?lang=".LANG,
			"/bitrix/admin/forum_index.php"
		],
		"title"=> GetMessage("FORUM_CONTROL"),
		"icon" => "forum_menu_icon",
		"page_icon" => "forum_page_icon",
		"items_id" => "menu_forum",
		"items" => array(
			array(
				"text" => GetMessage("FORUM_LIST"),
				"url" => "/bitrix/admin/forum_admin.php?lang=".LANG,
				"more_url" => array("/bitrix/admin/forum_edit.php"),
				"title" => GetMessage("FORUM_CONTROL_ALT")
			),
			array(
				"text" => GetMessage("FORUM_MENU_GROUP"),
				"url" => "/bitrix/admin/forum_group.php?lang=".LANG,
				"more_url" => array("/bitrix/admin/forum_group_edit.php"),
				"title" => GetMessage("FORUM_MENU_GROUP_ALT")
			),
			array(
				"text" => GetMessage("FORUM_MENU_RANK"),
				"url" => "/bitrix/admin/forum_points.php?lang=".LANG,
				"more_url" => array("/bitrix/admin/forum_points_edit.php"),
				"title" => GetMessage("FORUM_MENU_RANK_ALT")
			),
			array(
				"text" => GetMessage("POINTS_PER_MESSAGE"),
				"url" => "/bitrix/admin/forum_points2post.php?lang=".LANG,
				"more_url" => array("/bitrix/admin/forum_points2post_edit.php"),
				"title" => GetMessage("POINTS_PER_MESSAGE_ALT")
			),
			array(
				"text" => GetMessage("FORUM_MENU_TOPICS"),
				"url" => "/bitrix/admin/forum_topics.php?lang=".LANG,
				"more_url" => array("/bitrix/admin/forum_topics.php"),
				"title" => GetMessage("FORUM_MENU_TOPICS_ALT")
			),
			array(
				"text" => GetMessage("FORUM_MENU_SUBSCRIBE"),
				"url" => "/bitrix/admin/forum_subscribe.php?lang=".LANG,
				"more_url" => array(
									"/bitrix/admin/forum_subscribe.php", 
									"/bitrix/admin/forum_subscribe_edit.php"),
				"title" => GetMessage("FORUM_MENU_SUBSCRIBE_ALT")
			),
			array(
				"text" => GetMessage("FORUM_MENU_FILTER"),
				"items_id" => "menu_forum_filter",
				"url" => "/bitrix/admin/forum_dictionary.php?TYPE=W&amp;lang=".LANG,
				"more_url" =>Array(
					"/bitrix/admin/forum_dictionary.php"),
				"title" => GetMessage("FORUM_MENU_FILTER_ALT"),
				"items" => array(
					array(
						"text" => GetMessage("FORUM_MENU_FILTER_DW"),
						"title" => GetMessage("FORUM_MENU_FILTER_DW_ALT"),
						"items_id" => "menu_forum_filter_DW",
						"url" => "/bitrix/admin/forum_dictionary.php?TYPE=W&amp;lang=".LANG,
						"more_url" => array(
							"/bitrix/admin/forum_dictionary.php?TYPE=W&lang=".LANG,
							"/bitrix/admin/forum_dictionary_edit.php?TYPE=W&lang=".LANG,
							"/bitrix/admin/forum_words.php"),
						"module_id" => "forum",
						"dynamic" => true,
						"items" => $Dict["W"]
					),
					array(
						"text" => GetMessage("FORUM_MENU_FILTER_DT"),
						"title" => GetMessage("FORUM_MENU_FILTER_DT_ALT"),
						"items_id" => "menu_forum_filter_DT",
						"url" => "/bitrix/admin/forum_dictionary.php?TYPE=T&amp;lang=".LANG,
						"more_url" => array(
							"/bitrix/admin/forum_dictionary.php?TYPE=T&lang=".LANG,
							"/bitrix/admin/forum_dictionary_edit.php?TYPE=T&lang=".LANG,
							"/bitrix/admin/forum_letter.php"),
						"module_id" => "forum",
						"dynamic" => true,
						"items" => $Dict["T"])
				)
			)
		)
	);
	return $aMenu;
}
return false;
?>