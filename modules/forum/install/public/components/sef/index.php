<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:forum",
	"",
	Array(
		"SEF_MODE" => "Y", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600", 
		"DATE_FORMAT" => "d.m.Y", 
		"DATE_TIME_FORMAT" => "d.m.Y H:i:s", 
		"FID" => Array(), 
		"SET_TITLE" => "Y", 
		"SET_NAVIGATION" => "Y", 
		"FORUMS_PER_PAGE" => "15", 
		"TOPICS_PER_PAGE" => "10", 
		"MESSAGES_PER_PAGE" => "3", 
		"USE_DESC_PAGE_TOPIC" => "Y",
		"USE_DESC_PAGE_MESSAGE" => "N", 
		"SHOW_FORUMS_LIST" => "N", 
		"SHOW_FORUM_ANOTHER_SITE" => "Y", 
		"SEF_FOLDER" => "#SEF_FOLDER#", 
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "forum#FID#/",
			"read" => "forum#FID#/topic#TID#/message#MID#/",
			"help" => "help/",
			"message_appr" => "message/approve/forum#FID#/topic#TID#/",
			"message_move" => "message/move/forum#FID#/topic#TID#/message#MID#/",
			"pm_list" => "pm/folder#FID#/",
			"pm_edit" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
			"pm_read" => "pm/folder#FID#/message#MID#/",
			"pm_search" => "pm/search/",
			"pm_folder" => "pm/folders/",
			"search" => "search/",
			"subscr_list" => "subscribe/",
			"active" => "topic/new/",
			"topic_move" => "topic/move/forum#FID#/topic#TID#/",
			"topic_new" => "topic/add/forum#FID#/",
			"topic_search" => "topic/search/",
			"user_list" => "users/",
			"profile" => "user/#UID#/edit/",
			"profile_view" => "user/#UID#/",
			"user_post" => "user/#UID#/post/#mode#/",
			"message_send" => "user/#UID#/send/#TYPE#/"
		)
	)
);
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>