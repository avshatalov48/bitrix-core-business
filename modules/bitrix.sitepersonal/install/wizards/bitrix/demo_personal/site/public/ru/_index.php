<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(" ");
?> 
<p><?$APPLICATION->IncludeComponent("bitrix:blog", "one_blog", array(
	"MESSAGE_COUNT" => "10",
	"PERIOD_DAYS" => "30",
	"MESSAGE_COUNT_MAIN" => "6",
	"BLOG_COUNT_MAIN" => "6",
	"COMMENTS_COUNT" => "25",
	"MESSAGE_LENGTH" => "100",
	"BLOG_COUNT" => "20",
	"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
	"NAV_TEMPLATE" => "arrows",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "#SITE_DIR#",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"CACHE_TIME_LONG" => "60480000",
	"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
	"SET_TITLE" => "Y",
	"SET_NAV_CHAIN" => "N",
	"COMMENT_ALLOW_IMAGE_UPLOAD" => "A",
	"COMMENT_ALLOW_VIDEO" => "Y",
	"USER_PROPERTY" => array(
	),
	"BLOG_PROPERTY" => array(
	),
	"BLOG_PROPERTY_LIST" => array(
	),
	"POST_PROPERTY" => array(
	),
	"POST_PROPERTY_LIST" => array(
	),
	"USE_ASC_PAGING" => "N",
	"NOT_USE_COMMENT_TITLE" => "Y",
	"AJAX_POST" => "Y",
	"THEME" => "blue",
	"USER_PROPERTY_NAME" => "",
	"BLOG_URL" => "#BLOG_URL#",
	"NAME_TEMPLATE" => "",
	"SHOW_LOGIN" => "Y",
	"DO_NOT_SHOW_SIDEBAR" => "Y",
	"DO_NOT_SHOW_MENU" => "Y",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SEF_URL_TEMPLATES" => array(
		"index" => "",
		"group" => "group/#group_id#.php",
		"blog" => "",
		"user" => "user/#user_id#.php",
		"user_friends" => "friends/#user_id#.php",
		"search" => "search.php",
		"user_settings" => "#blog#/user_settings.php",
		"user_settings_edit" => "#blog#/user_settings_edit.php?id=#user_id#",
		"group_edit" => "#blog#/group_edit.php",
		"blog_edit" => "blog_edit/",
		"category_edit" => "#blog#/category_edit.php",
		"post_edit" => "edit/#post_id#/",
		"draft" => "draft/",
		"moderation" => "moderation/",
		"trackback" => POST_FORM_ACTION_URI."&blog=#blog#&id=#post_id#&page=trackback",
		"post" => "#post_id#/",
		"post_rss" => "rss/#post_id#",
		"rss" => "rss/",
		"rss_all" => "rss2/",
	),
	"VARIABLE_ALIASES" => array(
		"user_settings_edit" => array(
			"user_id" => "id",
		),
		"trackback" => array(
			"blog" => "blog",
			"post_id" => "id",
		),
	)
	),
	false
);?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>