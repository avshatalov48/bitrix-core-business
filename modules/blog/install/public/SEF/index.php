<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:blog",
	"",
	Array(
		"SEF_MODE" => "Y", 
		"PATH_TO_SMILE" => "/bitrix/images/blog/smile/", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "600", 
		"SET_TITLE" => "Y", 
		"CACHE_TIME_LONG" => "604800", 
		"SET_NAV_CHAIN" => "Y", 
		"MESSAGE_COUNT" => "20", 
		"BLOG_COUNT" => "20", 
		"NOT_USE_COMMENT_TITLE" => "Y",
		"SEF_FOLDER" => "#SEF_FOLDER#", 
		"SEF_URL_TEMPLATES" => Array(
			"index" => "",
			"group" => "group/#group_id#.php",
			"blog" => "#blog#/",
			"user" => "user/#user_id#.php",
			"user_friends" => "friends/#user_id#.php",
			"search" => "search.php",
			"user_settings" => "#blog#/user_settings.php",
			"user_settings_edit" => "#blog#/user_settings_edit.php?id=#user_id#",
			"group_edit" => "#blog#/group_edit.php",
			"blog_edit" => "#blog#/blog_edit.php",
			"category_edit" => "#blog#/category_edit.php",
			"post_edit" => "#blog#/post_edit.php?id=#post_id#",
			"draft" => "#blog#/draft.php",
			"trackback" => '/blog/index.php?blog=#blog#&id=#post_id#&page=trackback',
			"post" => "#blog#/#post_id#.php",
			"rss" => "#blog#/rss/#type#"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"group" => Array(),
			"blog" => Array(),
			"user" => Array(),
			"user_friends" => Array(),
			"search" => Array(),
			"user_settings" => Array(),
			"user_settings_edit" => Array(
				"user_id" => "id"
			),
			"group_edit" => Array(),
			"blog_edit" => Array(),
			"category_edit" => Array(),
			"post_edit" => Array(
				"post_id" => "id"
			),
			"draft" => Array(),
			"trackback" => Array(
				"post_id" => "id"
			),
			"post" => Array(),
			"rss" => Array(),
		)
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>