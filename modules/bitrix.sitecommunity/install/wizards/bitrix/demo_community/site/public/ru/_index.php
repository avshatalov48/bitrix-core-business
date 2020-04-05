<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Записи");
?> 
<p><?
$APPLICATION->IncludeComponent("bitrix:blog.new_posts.list", "general_page", array(
	"GROUP_ID" => "#BLOG_GROUP_ID#",
	"BLOG_URL" => "",
	"MESSAGE_PER_PAGE" => "10",
	"DATE_TIME_FORMAT" => "d.m.y G:i",
	"NAV_TEMPLATE" => "",
	"PATH_TO_BLOG" => "#SITE_DIR#people/user/#user_id#/blog/",
	"PATH_TO_POST" => "#SITE_DIR#people/user/#user_id#/blog/#post_id#/",
	"PATH_TO_USER" => "#SITE_DIR#people/user/#user_id#/",
	"PATH_TO_GROUP_BLOG_POST" => "#SITE_DIR#groups/group/#group_id#/blog/#post_id#/",
	"PATH_TO_BLOG_CATEGORY" => "#SITE_DIR#people/user/#user_id#/blog/?category=#category_id#",
	"PATH_TO_MESSAGES_CHAT" => "#SITE_DIR#people/messages/chat/#user_id#/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
	"SET_TITLE" => "N",
	"SHOW_RATING" => "Y",
	"POST_PROPERTY_LIST" => array(
	),
	"BLOG_VAR" => "blog",
	"POST_VAR" => "post_id",
	"USER_VAR" => "user_id",
	"PAGE_VAR" => "page",
	"SEO_USER" => "N",
	"USE_SOCNET" => "Y"
	),
	false
);
?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>