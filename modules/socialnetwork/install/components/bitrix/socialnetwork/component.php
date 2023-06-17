<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\ComponentHelper;


/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

global $CACHE_MANAGER;

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arDefaultUrlTemplates404 = array(
	"index" => "index.php",

	"reindex" => "reindex.php",
	"group_content_search" => "group/#group_id#/search/",
	"user_content_search" => "user/#user_id#/search/",

	"user" => "user/#user_id#/",
	"user_current" => "user/index.php",
	"user_friends" => "user/#user_id#/friends/",
	"user_friends_add" => "user/#user_id#/friends/add/",
	"user_friends_delete" => "user/#user_id#/friends/delete/",
	"user_groups" => "user/#user_id#/groups/",
	"user_groups_add" => "user/#user_id#/groups/add/",
	"group_create" => "user/#user_id#/groups/create/",
	"user_profile_edit" => "user/#user_id#/edit/",
	"user_settings_edit" => "user/#user_id#/settings/",
	"user_features" => "user/#user_id#/features/",
	"user_subscribe" => "user/#user_id#/subscribe/",
	"user_requests" => "user/#user_id#/requests/",
	"user_security" => "user/#user_id#/security/",
	"user_codes" => "user/#user_id#/codes/",
	"user_passwords" => "user/#user_id#/passwords/",

	"group_request_group_search" => "group/#user_id#/group_search/",
	"group_request_user" => "group/#group_id#/user/#user_id#/request/",

	"search" => "search.php",

	"message_form" => "messages/form/#user_id#/",
	"group" => "group/#group_id#/",
	"group_card" => "group/#group_id#/card/",
	"group_edit" => "group/#group_id#/edit/",
	"group_invite" => "group/#group_id#/invite/",
	"group_requests" => "group/#group_id#/requests/",
	"group_requests_out" => "group/#group_id#/requests_out/",
	"group_mods" => "group/#group_id#/moderators/",
	"group_users" => "group/#group_id#/users/",
	"group_ban" => "group/#group_id#/ban/",
	"group_delete" => "group/#group_id#/delete/",
	"group_features" => "group/#group_id#/features/",
	"group_subscribe" => "group/#group_id#/subscribe/",
	"group_list" => "group/",
	"group_search" => "group/search/",
	"group_search_subject" => "group/search/#subject_id#/",
	"user_leave_group" => "group/#group_id#/user_leave/",
	"user_request_group" => "group/#group_id#/user_request/",
	"group_request_search" => "group/#group_id#/user_search/",
	"message_to_group" => "group/#group_id#/chat/",
	"message_form_mess" => "messages/chat/#user_id#/#message_id#/",
	"messages_chat" => "messages/chat/#user_id#/",
	"messages_users" => "messages/",
	"messages_users_messages" => "messages/#user_id#/",
	"messages_input" => "messages/input/",
	"messages_output" => "messages/output/",
	"messages_input_user" => "messages/input/#user_id#/",
	"messages_output_user" => "messages/output/#user_id#/",
	"user_ban" => "messages/ban/",
	"log" => "log/",
	"log_entry" => "log/#log_id#/",
	"activity" => "user/#user_id#/activity/",
	"subscribe" => "subscribe/",
	"bizproc" => "bizproc/",
	"bizproc_edit" => "bizproc/#task_id#/",
	"bizproc_task_list" => "user/#user_id#/bizproc/",
	"bizproc_task" => "user/#user_id#/bizproc/#task_id#/",
	"video_call" => "video/#user_id#/",

	"user_photo" => "user/#user_id#/photo/",
	"user_photo_gallery" => "user/#user_id#/photo/gallery/",
	"user_photo_gallery_edit" => "user/#user_id#/photo/gallery/action/#action#/",
	"user_photo_galleries" => "user/#user_id#/photo/galleries/",
	"user_photo_section" => "user/#user_id#/photo/album/#section_id#/",
	"user_photo_section_edit" => "user/#user_id#/photo/album/#section_id#/action/#action#/",
	"user_photo_section_edit_icon" => "user/#user_id#/photo/album/#section_id#/icon/action/#action#/",
	"user_photo_element_upload" => "user/#user_id#/photo/photo/#section_id#/action/upload/",
	"user_photo_element" => "user/#user_id#/photo/photo/#section_id#/#element_id#/",
	"user_photo_element_edit" => "user/#user_id#/photo/photo/#section_id#/#element_id#/action/#action#/",
	"user_photo_element_slide_show" => "user/#user_id#/photo/photo/#section_id#/#element_id#/slide_show/",
	"user_photofull_gallery" => "user/#user_id#/photo/gallery/#user_alias#/",
	"user_photofull_gallery_edit" => "user/#user_id#/photo/gallery/#user_alias#/action/#action#/",
	"user_photofull_section" => "user/#user_id#/photo/album/#user_alias#/#section_id#/",
	"user_photofull_section_edit" => "user/#user_id#/photo/album/#user_alias#/#section_id#/action/#action#/",
	"user_photofull_section_edit_icon" => "user/#user_id#/photo/album/#user_alias#/#section_id#/icon/action/#action#/",
	"user_photofull_element_upload" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/action/upload/",
	"user_photofull_element" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/",
	"user_photofull_element_edit" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/action/#action#/",
	"user_photofull_element_slide_show" => "user/#user_id#/photo/photo/#user_alias#/#section_id#/#element_id#/slide_show/",

	"user_calendar" => "user/#user_id#/calendar/",

	"user_files" => "user/#user_id#/files/lib/#path#",
	"user_files_short" => "folder/view/#section_id#/#element_id#/#element_name#",
	"user_files_section_edit" => "user/#user_id#/files/folder/edit/#section_id#/#action#/",
	"user_files_element" => "user/#user_id#/files/element/view/#element_id#/",
	"user_files_element_edit" => "user/#user_id#/files/element/edit/#element_id#/#action#/",
	"user_files_element_file" => "",
	"user_files_element_history" => "user/#user_id#/files/element/history/#element_id#/",
	"user_files_element_history_get" => "user/#user_id#/files/element/historyget/#element_id#/#element_name#",
	"user_files_element_version" => "user/#user_id#/files/element/version/#action#/#element_id#/",
	"user_files_element_versions" => "user/#user_id#/files/element/versions/#element_id#/",
	"user_files_element_upload" => "user/#user_id#/files/element/upload/#section_id#/",
	"user_files_help" => "user/#user_id#/files/help/",
	"user_files_connector" => "user/#user_id#/files/connector/",
	"user_files_webdav_bizproc_history" => "user/#user_id#/files/bizproc/history/#element_id#/",
	"user_files_webdav_bizproc_history_get" => "user/#user_id#/files/bizproc/historyget/#element_id#/#id#/#element_name#",
	"user_files_webdav_bizproc_log" => "user/#user_id#/files/bizproc/log/#element_id#/#id#/",
	"user_files_webdav_bizproc_view" => "user/#user_id#/files/bizproc/bizproc/#element_id#/",
	"user_files_webdav_bizproc_workflow_admin" => "user/#user_id#/files/bizproc/admin/",
	"user_files_webdav_bizproc_workflow_edit" => "user/#user_id#/files/bizproc/edit/#id#/",
	"user_files_webdav_start_bizproc" => "user/#user_id#/files/bizproc/start/#element_id#/",

	"group_photo" => "group/#group_id#/photo/",
	"group_photo_gallery" => "group/#group_id#/photo/gallery/",
	"group_photo_gallery_edit" => "group/#group_id#/photo/gallery/action/#action#/",
	"group_photo_galleries" => "group/#group_id#/photo/galleries/",
	"group_photo_section" => "group/#group_id#/photo/album/#section_id#/",
	"group_photo_section_edit" => "group/#group_id#/photo/album/#section_id#/action/#action#/",
	"group_photo_section_edit_icon" => "group/#group_id#/photo/album/#section_id#/icon/action/#action#/",
	"group_photo_element_upload" => "group/#group_id#/photo/photo/#section_id#/action/upload/",
	"group_photo_element" => "group/#group_id#/photo/photo/#section_id#/#element_id#/",
	"group_photo_element_edit" => "group/#group_id#/photo/photo/#section_id#/#element_id#/action/#action#/",
	"group_photo_element_slide_show" => "group/#group_id#/photo/photo/#section_id#/#element_id#/slide_show/",
	"group_photofull_gallery" => "group/#group_id#/photo/gallery/#user_alias#/",
	"group_photofull_gallery_edit" => "group/#group_id#/photo/gallery/#user_alias#/action/#action#/",
	"group_photofull_section" => "group/#group_id#/photo/album/#user_alias#/#section_id#/",
	"group_photofull_section_edit" => "group/#group_id#/photo/album/#user_alias#/#section_id#/action/#action#/",
	"group_photofull_section_edit_icon" => "group/#group_id#/photo/album/#user_alias#/#section_id#/icon/action/#action#/",
	"group_photofull_element_upload" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/action/upload/",
	"group_photofull_element" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/",
	"group_photofull_element_edit" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/action/#action#/",
	"group_photofull_element_slide_show" => "group/#group_id#/photo/photo/#user_alias#/#section_id#/#element_id#/slide_show/",

	"group_calendar" => "group/#group_id#/calendar/",

	"group_files" => "group/#group_id#/files/lib/#path#/",
	"group_files_short" => "folder/view/#section_id#/#element_id#/#element_name#",
	"group_files_section_edit" => "group/#group_id#/files/folder/edit/#section_id#/#action#/",
	"group_files_element" => "group/#group_id#/files/element/view/#element_id#/",
	"group_files_element_edit" => "group/#group_id#/files/element/edit/#element_id#/#action#/",
	"group_files_element_file" => "",
	"group_files_element_history" => "group/#group_id#/files/element/history/#element_id#/",
	"group_files_element_history_get" => "group/#group_id#/files/element/historyget/#element_id#/#element_name#",
	"group_files_element_version" => "group/#group_id#/files/element/version/#action#/#element_id#/",
	"group_files_element_versions" => "group/#group_id#/files/element/versions/#element_id#/",
	"group_files_element_upload" => "group/#group_id#/files/element/upload/#section_id#/",
	"group_files_help" => "group/#group_id#/files/help/",
	"group_files_connector" => "group/#group_id#/files/connector/",
	"group_files_webdav_bizproc_history" => "group/#group_id#/files/bizproc/history/#element_id#/",
	"group_files_webdav_bizproc_history_get" => "group/#group_id#/files/bizproc/historyget/#element_id#/#id#/#element_name#",
	"group_files_webdav_bizproc_log" => "group/#group_id#/files/bizproc/log/#element_id#/#id#/",
	"group_files_webdav_bizproc_view" => "group/#group_id#/files/bizproc/bizproc/#element_id#/",
	"group_files_webdav_bizproc_workflow_admin" => "group/#group_id#/files/bizproc/admin/",
	"group_files_webdav_bizproc_workflow_edit" => "group/#group_id#/files/bizproc/edit/#id#/",
	"group_files_webdav_start_bizproc" => "group/#group_id#/files/bizproc/start/#element_id#/",
	"group_files_webdav_task_list" => "group/#group_id#/files/bizproc/task/list/",
	"group_files_webdav_task" => "group/#group_id#/files/bizproc/task/read/#id#/",

	"user_blog" => "user/#user_id#/blog/",
	"user_blog_post_important" => "user/#user_id#/blog/important/",
	"user_blog_post_edit" => "user/#user_id#/blog/edit/#post_id#/",
	"user_blog_rss" => "user/#user_id#/blog/rss/#type#/",
	"user_blog_post_rss" => "user/#user_id#/blog/rss/#type#/#post_id#/",
	"user_blog_draft" => "user/#user_id#/blog/draft/",
	"user_blog_moderation" => "user/#user_id#/blog/moderation/",
	"user_blog_tags" => "user/#user_id#/blog/tags/",
	"user_blog_post" => "user/#user_id#/blog/#post_id#/",

	"user_microblog" => "user/#user_id#/microblog/",
	"user_microblog_post" => "user/#user_id#/microblog/#post_id#/",

	"user_tasks" => "user/#user_id#/tasks/",
	"user_tasks_board" => "user/#user_id#/tasks/board/",
	"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
	"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",
	"user_tasks_departments_overview" => "user/#user_id#/tasks/departments/",
	"user_tasks_projects_overview" => "user/#user_id#/tasks/projects/",
	"user_tasks_scrum_overview" => "user/#user_id#/tasks/projects/",
	"user_tasks_report" => "user/#user_id#/tasks/report/",
	"user_tasks_report_construct" => "user/#user_id#/tasks/report/construct/#report_id#/#action#/",
	"user_tasks_report_view" => "user/#user_id#/tasks/report/view/#report_id#/",
	"user_tasks_templates" => "user/#user_id#/tasks/templates/",
	"user_templates_template" => "user/#user_id#/tasks/templates/template/#action#/#template_id#/",

	//"user_tasks_import" => "user/#user_id#/tasks/import/",

	"user_forum" => "user/#user_id#/forum/",
	"user_forum_topic" => "user/#user_id#/forum/#topic_id#/",
	"user_forum_topic_edit" => "user/#user_id#/forum/edit/#action#/#topic_id#/#message_id#/",
	"user_forum_message" => "user/#user_id#/forum/message/#topic_id#/#message_id#/",
	"user_forum_message_edit" => "user/#user_id#/forum/message/#action#/#topic_id#/#message_id#/",

	"group_blog" => "group/#group_id#/blog/",
	"group_blog_post_edit" => "group/#group_id#/blog/edit/#post_id#/",
	"group_blog_rss" => "group/#group_id#/blog/rss/#type#/",
	"group_blog_post_rss" => "group/#group_id#/blog/rss/#type#/#post_id#/",
	"group_blog_draft" => "group/#group_id#/blog/draft/",
	"group_blog_moderation" => "group/#group_id#/blog/moderation/",
	"group_blog_post" => "group/#group_id#/blog/#post_id#/",
	"group_microblog" => "group/#group_id#/microblog/",
	"group_microblog_post" => "group/#group_id#/microblog/#post_id#/",

	"group_forum" => "group/#group_id#/forum/",
	"group_forum_topic" => "group/#group_id#/forum/#topic_id#/",
	"group_forum_topic_edit" => "group/#group_id#/forum/edit/#topic_id#/",
	"group_forum_message" => "group/#group_id#/forum/message/#topic_id#/#message_id#/",
	"group_forum_message_edit" => "group/#group_id#/forum/message/#action#/#topic_id#/#message_id#/",

	"group_tasks" => "group/#group_id#/tasks/",
	"group_tasks_task" => "group/#group_id#/tasks/task/#action#/#task_id#/",
	"group_tasks_view" => "group/#group_id#/tasks/view/#action#/#view_id#/",
	"group_tasks_report" => "group/#group_id#/tasks/report/",
	"group_tasks_report_construct" => "group/#group_id#/tasks/report/construct/#report_id#/#action#/",
	"group_tasks_report_view" => "group/#group_id#/tasks/report/view/#report_id#/",

	"group_log" => "group/#group_id#/log/",
	"group_log_rss" => "group/#group_id#/log/rss/?bx_hit_hash=#sign#&events=#events#",
	"group_log_rss_mask" => "group/#group_id#/log/rss/",

	"group_app" => "group/#group_id#/app/#placement_id#/",
	"group_marketplace" => "group/#group_id#/marketplace/",
);

if (IsModuleInstalled('extranet'))
{
	unset($arDefaultUrlTemplates404["search"]);
}

$diskEnabled = (
	Option::get('disk', 'successfully_converted', false)
	&& CModule::includeModule('disk')
);

$arDefaultUrlTemplatesN404 = array(
	"index" => "",

	"reindex" => "page=reindex",
	"user_content_search" => "page=user_content_search&user_id=#user_id#",

	"user" => "page=user&user_id=#user_id#",
	"user_friends" => "page=user_friends&user_id=#user_id#",
	"user_friends_add" => "page=user_friends_add&user_id=#user_id#",
	"user_friends_delete" => "page=user_friends_delete&user_id=#user_id#",
	"user_groups" => "page=user_groups&user_id=#user_id#",
	"user_groups_add" => "page=user_groups_add&user_id=#user_id#",
	"group_create" => "page=group_create&user_id=#user_id#",
	"user_profile_edit" => "page=user_profile_edit&user_id=#user_id#",
	"user_settings_edit" => "page=user_settings_edit&user_id=#user_id#",
	"user_features" => "page=user_features&user_id=#user_id#",
	"user_subscribe" => "page=user_subscribe&user_id=#user_id#",

	"group_content_search" => "page=group_content_search&group_id=#group_id#",

	"group" => "page=group&group_id=#group_id#",
	"group_card" => "page=group_card&group_id=#group_id#",
	"group_edit" => "page=group_edit&group_id=#group_id#",
	"group_invite" => "page=group_invite&group_id=#group_id#",
	"group_requests" => "page=group_requests&group_id=#group_id#",
	"group_requests_out" => "page=group_requests_out&group_id=#group_id#",
	"group_mods" => "page=group_mods&group_id=#group_id#",
	"group_users" => "page=group_users&group_id=#group_id#",
	"group_ban" => "page=group_ban&group_id=#group_id#",
	"group_delete" => "page=group_delete&group_id=#group_id#",
	"group_features" => "page=group_features&group_id=#group_id#",
	"group_subscribe" => "page=group_subscribe&group_id=#group_id#",
	"group_list" => "page=group_list",
	"group_search" => "page=group_search",
	"group_search_subject" => "page=group_search_subject&subject_id=#subject_id#",
	"user_leave_group" => "page=user_leave_group&group_id=#group_id#",
	"group_request_user" => "page=group_request_user&group_id=#group_id#&user_id=#user_id#",
	"user_request_group" => "page=user_request_group&group_id=#group_id#",
	"group_request_search" => "page=group_request_search&group_id=#group_id#",
	"user_requests" => "page=user_requests&user_id=#user_id#",
	"user_security" => "page=user_security&user_id=#user_id#",
	"user_codes" => "page=user_codes&user_id=#user_id#",
	"user_passwords" => "page=user_passwords&user_id=#user_id#",

	"search" => "page=search",

	"message_form" => "page=message_form&user_id=#user_id#",
	"group_request_group_search" => "page=group_request_group_search&user_id=#user_id#",

	"message_form_mess" => "page=message_form_mess&user_id=#user_id#&message_id=#message_id#",
	"messages_chat" => "page=messages_chat&user_id=#user_id#",
	"messages_users" => "page=messages_users",
	"messages_users_messages" => "page=messages_users_messages&user_id=#user_id#",
	"messages_input" => "page=messages_input",
	"messages_output" => "page=messages_output",
	"messages_input_user" => "page=messages_input_user&user_id=#user_id#",
	"messages_output_user" => "page=messages_output_user&user_id=#user_id#",
	"user_ban" => "page=user_ban",
	"log" => "page=log",
	"log_entry" => "page=log_entry&log_id=#log_id#",
	"activity" => "page=activity&user_id=#user_id#",
	"subscribe" => "page=subscribe",
	"bizproc" => "page=bizproc",
	"bizproc_edit" => "page=bizproc_edit&task_id=#task_id#",
	"bizproc_task_list" => "page=bizproc_task_list&user_id=#user_id#",
	"bizproc_task" => "page=bizproc_task&user_id=#user_id#&task_id=#task_id#",
	"video_call" => "page=video_call&user_id=#user_id#",

	"user_photo" => "page=user_photo&user_id=#user_id#",
	"user_photo_gallery" => "page=user_photo_gallery&user_id=#user_id#",
	"user_photo_gallery_edit" => "page=user_photo_gallery&user_id=#user_id#&action=#action#",
	"user_photo_galleries" => "page=user_photo_galleries&user_id=#user_id#",
	"user_photo_section" => "page=user_photo_section&user_id=#user_id#&section_id=#section_id#",
	"user_photo_section_edit" => "page=user_photo_section_edit&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_photo_section_edit_icon" => "page=user_photo_section_edit_icon&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_photo_element_upload" => "page=user_photo_element_upload&user_id=#user_id#&section_id=#section_id#",
	"user_photo_element" => "page=user_photo_element&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#",
	"user_photo_element_edit" => "page=user_photo_element_edit&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"user_photo_element_slide_show" => "page=user_photo_element_slide_show&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#",
	"user_photofull_gallery" => "page=user_photofull_gallery&user_id=#user_id#&user_alias=#user_alias#",
	"user_photofull_gallery_edit" => "page=user_photofull_gallery_edit&user_id=#user_id#&user_alias=#user_alias#&action=#action#",
	"user_photofull_section" => "page=user_photofull_section&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#",
	"user_photofull_section_edit" => "page=user_photofull_section_edit&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"user_photofull_section_edit_icon" => "page=user_photofull_section_edit_icon&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"user_photofull_element_upload" => "page=user_photofull_element_upload&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#",
	"user_photofull_element" => "page=user_photofull_element&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",
	"user_photofull_element_edit" => "page=user_photofull_element_edit&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"user_photofull_element_slide_show" => "page=user_photofull_element_slide_show&user_id=#user_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",

	"user_calendar" => "page=user_calendar&user_id=#user_id#",

	"user_files" => "page=user_files&user_id=#user_id#&path=#path#",
	"user_files_short" => "page=user_files_short&user_id=#user_id#&section_id=#section_id#&element_id=#element_id#&element_name=#element_name#",
	"user_files_section_edit" => "page=user_files_section_edit&user_id=#user_id#&section_id=#section_id#&action=#action#",
	"user_files_element" => "page=user_files_element&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_edit" => "page=user_files_element_edit&user_id=#user_id#&element_id=#element_id#&action=#action#",
	"user_files_element_file" => "",
	"user_files_element_history" => "page=user_files_element_history&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_history_get" => "page=user_files_element_history_get&user_id=#user_id#&element_id=#element_id#&element_name=#element_name#",
	"user_files_element_version" => "page=user_files_element_version&user_id=#user_id#&element_id=#element_id#&action=#action#",
	"user_files_element_versions" => "page=user_files_element_versions&user_id=#user_id#&element_id=#element_id#",
	"user_files_element_upload" => "page=user_files_element_upload&user_id=#user_id#&section_id=#section_id#",
	"user_files_help" => "page=user_files_help&user_id=#user_id#",
	"user_files_connector" => "page=user_files_connector&user_id=#user_id#",
	"user_files_webdav_bizproc_history" => "page=user_files_webdav_bizproc_history&user_id=#user_id#&element_id=#element_id#",
	"user_files_webdav_bizproc_history_get" => "page=user_files_webdav_bizproc_history_get&user_id=#user_id#&element_id=#element_id#&element_name=#element_name#",
	"user_files_webdav_bizproc_log" => "page=user_files_webdav_bizproc_log&user_id=#user_id#&element_id=#element_id#&id=#id#",
	"user_files_webdav_bizproc_view" => "page=user_files_webdav_bizproc_view&user_id=#user_id#&element_id=#element_id#",
	"user_files_webdav_bizproc_workflow_admin" => "page=user_files_webdav_bizproc_workflow_admin&user_id=#user_id#",
	"user_files_webdav_bizproc_workflow_edit" => "page=user_files_webdav_bizproc_workflow_edit&user_id=#user_id#&id=#id#",
	"user_files_webdav_start_bizproc" => "page=user_files_webdav_start_bizproc&user_id=#user_id#&element_id=#element_id#",

	"group_photo" => "page=group_photo&group_id=#group_id#",
	"group_photo_gallery" => "page=group_photo_gallery&group_id=#group_id#",
	"group_photo_gallery_edit" => "page=group_photo_gallery&group_id=#group_id#&action=#action#",
	"group_photo_galleries" => "page=group_photo_galleries&group_id=#group_id#",
	"group_photo_section" => "page=group_photo_section&group_id=#group_id#&section_id=#section_id#",
	"group_photo_section_edit" => "page=group_photo_section_edit&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_photo_section_edit_icon" => "page=group_photo_section_edit_icon&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_photo_element_upload" => "page=group_photo_element_upload&group_id=#group_id#&section_id=#section_id#",
	"group_photo_element" => "page=group_photo_element&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#",
	"group_photo_element_edit" => "page=group_photo_element_edit&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"group_photo_element_slide_show" => "page=group_photo_element_slide_show&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#",
	"group_photofull_gallery" => "page=group_photofull_gallery&group_id=#group_id#&user_alias=#user_alias#",
	"group_photofull_gallery_edit" => "page=group_photofull_gallery_edit&group_id=#group_id#&user_alias=#user_alias#&action=#action#",
	"group_photofull_section" => "page=group_photofull_section&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#",
	"group_photofull_section_edit" => "page=group_photofull_section_edit&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"group_photofull_section_edit_icon" => "page=group_photofull_section_edit_icon&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&action=#action#",
	"group_photofull_element_upload" => "page=group_photofull_element_upload&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#",
	"group_photofull_element" => "page=group_photofull_element&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",
	"group_photofull_element_edit" => "page=group_photofull_element_edit&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#&action=#action#",
	"group_photofull_element_slide_show" => "page=group_photofull_element_slide_show&group_id=#group_id#&user_alias=#user_alias#&section_id=#section_id#&element_id=#element_id#",

	"user_blog" => "page=user_blog&user_id=#user_id#",
	"user_blog_post_edit" => "page=user_blog_post_edit&user_id=#user_id#&post_id=#post_id#",
	"user_blog_rss" => "page=user_blog_rss&user_id=#user_id#&type=#type#",
	"user_blog_post_rss" => "page=user_blog_post_rss&user_id=#user_id#&type=#type#&post_id=#post_id#",
	"user_blog_draft" => "page=user_blog_draft&user_id=#user_id#",
	"user_blog_moderation" => "page=user_blog_moderation&user_id=#user_id#",
	"user_blog_tags" => "page=user_blog_tags&user_id=#user_id#",
	"user_blog_post" => "page=user_blog_post&user_id=#user_id#&post_id=#post_id#",
	"user_microblog" => "page=user_microblog&user_id=#user_id#",
	"user_microblog_post" => "page=user_microblog_post&user_id=#user_id#&post_id=#post_id#",

	"user_tasks" => "page=user_tasks&user_id=#user_id#",
	"user_tasks_board" => "page=user_tasks_board&user_id=#user_id#",
	"user_tasks_task" => "page=user_tasks_task&user_id=#user_id#&action=#action#&task_id=#task_id#",
	"user_tasks_view" => "page=user_tasks_view&user_id=#user_id#&action=#action#&view_id=#view_id#",
	"user_tasks_report" => "page=user_tasks_report&user_id=#user_id#",
	"user_tasks_report_construct" => "page=user_tasks_report_construct&user_id=#user_id#&action=#action#&report_id=#report_id#",
	"user_tasks_report_view" => "page=user_tasks_report_view&user_id=#user_id#&report_id=#report_id#",
	"user_tasks_templates" => "page=user_tasks_templates&user_id=#user_id#",
	"user_templates_template" => "page=user_templates_template&user_id=#user_id#&action=#action#&template_id=#template_id#",

	"user_forum" => "page=user_forum&user_id=#user_id#",
	"user_forum_topic" => "page=user_forum_topic&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_topic_edit" => "page=user_forum_topic_edit&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_message" => "page=user_forum_message&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#",
	"user_forum_message_edit" => "page=user_forum_message_edit&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#&action=#action#",

	"group_calendar" => "page=group_calendar&group_id=#group_id#",

	"message_to_group" => "page=message_to_group&group_id=#group_id#",

	"group_files" => "page=group_files&group_id=#group_id#&path=#path#",
	"group_files_short" => "page=group_files_short&group_id=#group_id#&section_id=#section_id#&element_id=#element_id#&element_name=#element_name#",
	"group_files_section_edit" => "page=group_files_section_edit&group_id=#group_id#&section_id=#section_id#&action=#action#",
	"group_files_element" => "page=group_files_element&group_id=#group_id#&element_id=#element_id#",
	"group_files_element_edit" => "page=group_files_element_edit&group_id=#group_id#&element_id=#element_id#&action=#action#",
	"group_files_element_file" => "",
	"group_files_element_history" => "page=group_files_element_history&element_id=#element_id#",
	"group_files_element_history_get" => "page=group_files_element_history_get&element_id=#element_id#&element_name=#element_name#",
	"group_files_element_version" => "page=group_files_element_version&group_id=#group_id#&element_id=#element_id#&action=#action#",
	"group_files_element_versions" => "page=group_files_element_versions&group_id=#group_id#&element_id=#element_id#",
	"group_files_element_upload" => "page=group_files_element_upload&group_id=#group_id#&section_id=#section_id#",
	"group_files_help" => "page=group_files_help&group_id=#group_id#",
	"group_files_connector" => "page=group_files_connector&group_id=#group_id#",
	"group_files_webdav_bizproc_history" => "page=group_files_webdav_bizproc_history&group_id=#group_id#&element_id=#element_id#",
	"group_files_webdav_bizproc_history_get" => "page=group_files_webdav_bizproc_history_get&group_id=#group_id#&element_id=#element_id#&element_name=#element_name#",
	"group_files_webdav_bizproc_log" => "page=group_files_webdav_bizproc_log&group_id=#group_id#&element_id=#element_id#&id=#id#",
	"group_files_webdav_bizproc_view" => "page=group_files_webdav_bizproc_view&group_id=#group_id#&element_id=#element_id#",
	"group_files_webdav_bizproc_workflow_admin" => "page=group_files_webdav_bizproc_workflow_admin&group_id=#group_id#",
	"group_files_webdav_bizproc_workflow_edit" => "page=group_files_webdav_bizproc_workflow_edit&group_id=#group_id#&id=#id#",
	"group_files_webdav_start_bizproc" => "page=group_files_webdav_start_bizproc&group_id=#group_id#&element_id=#element_id#",
	"group_files_webdav_task_list" => "page=group_files_webdav_task_list&group_id=#group_id#",
	"group_files_webdav_task" => "page=group_files_webdav_task&group_id=#group_id#&id=#id#",

	"group_blog" => "page=group_blog&group_id=#group_id#",
	"group_blog_post_edit" => "page=group_blog_post_edit&group_id=#group_id#&post_id=#post_id#",
	"group_blog_rss" => "page=group_blog_rss&group_id=#group_id#&type=#type#",
	"group_blog_post_rss" => "page=group_blog_post_rss&group_id=#group_id#&type=#type#&post_id=#post_id#",
	"group_blog_draft" => "page=group_blog_draft&group_id=#group_id#",
	"group_blog_moderation" => "page=group_blog_moderation&group_id=#group_id#",
	"group_blog_post" => "page=group_blog_post&group_id=#group_id#&post_id=#post_id#",
	"group_microblog" => "page=group_microblog&group_id=#group_id#",
	"group_microblog_post" => "page=group_microblog_post&group_id=#group_id#&post_id=#post_id#",

	"group_forum" => "page=group_forum&group_id=#group_id#",
	"group_forum_topic" => "page=group_forum_topic&group_id=#group_id#&topic_id=#topic_id#",
	"group_forum_topic_edit" => "page=group_forum_topic_edit&group_id=#group_id#&topic_id=#topic_id#",
	"group_forum_message" => "page=group_forum_message&group_id=#group_id#&topic_id=#topic_id#&message_id=#message_id#",
	"group_forum_message_edit" => "page=group_forum_message_edit&group_id=#group_id#&topic_id=#topic_id#&message_id=#message_id#&action=#action#",

	"group_tasks" => "page=group_tasks&group_id=#group_id#",
	"group_tasks_task" => "page=group_tasks_task&group_id=#group_id#&action=#action#&task_id=#task_id#",
	"group_tasks_view" => "page=group_tasks_view&group_id=#group_id#&action=#action#&view_id=#view_id#",
	"group_tasks_report" => "page=group_tasks_report&group_id=#group_id#",
	"group_tasks_report_construct" => "page=group_tasks_report_construct&group_id=#group_id#&action=#action#&report_id=#report_id#",
	"group_tasks_report_view" => "page=group_tasks_report_view&group_id=#group_id#&report_id=#report_id#",

	"group_log" => "page=group_log&group_id=#group_id#",
	"group_log_rss" => "page=group_log_rss&group_id=#group_id#&sign=#sign#&events=#events#",
//	"group_log_rss_mask" => "page=group_log_rss&group_id=#group_id#",

	"group_app" => "page=group_app&group_id=#group_id#&placement_id=#placement_id#",
	"group_marketplace" => "page=group_marketplace&group_id=#group_id#"
	);
$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$componentPage = "";
$arComponentVariables = array("user_id", "group_id", "page", "message_id", "subject_id", "path", "section_id", "element_id", "action", "post_id", "category", "topic_id", "task_id", "view_id", "type", "report_id", "log_id");

if (
	$_REQUEST["auth"] === "Y"
	&& $USER->IsAuthorized()
)
{
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));
}

if (!array_key_exists("SET_NAV_CHAIN", $arParams))
{
	$arParams["SET_NAV_CHAIN"] = $arParams["SET_NAVCHAIN"];
}
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] === "N" ? "N" : "Y");
$arParams["LOG_AUTH"] = ((mb_strtoupper($arParams["LOG_AUTH"]) === "Y") ? "Y" : "N");
$arParams["HIDE_OWNER_IN_TITLE"] = ($arParams["HIDE_OWNER_IN_TITLE"] === "Y" ? "Y" : "N");

if (!array_key_exists("ALLOW_GROUP_CREATE_REDIRECT_REQUEST", $arParams))
{
	$arParams["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] = "Y";
}

if (!is_array($arParams["VARIABLE_ALIASES"]))
{
	$arParams["VARIABLE_ALIASES"] = array();
}

if ($arParams["GROUP_USE_KEYWORDS"] !== "N") $arParams["GROUP_USE_KEYWORDS"] = "Y";

$tooltipParams = ComponentHelper::checkTooltipComponentParams($arParams);
$arParams['SHOW_FIELDS_TOOLTIP'] = $tooltipParams['SHOW_FIELDS_TOOLTIP'];
$arParams['USER_PROPERTY_TOOLTIP'] = $tooltipParams['USER_PROPERTY_TOOLTIP'];

if (IsModuleInstalled('intranet') && !array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
{
	$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
}

if (
	IsModuleInstalled("search")
	&& !array_key_exists("PATH_TO_SEARCH_TAG", $arParams)
)
{
	$arParams["PATH_TO_SEARCH_TAG"] = SITE_DIR."search/?tags=#tag#";
}

if (trim($arParams["NAME_TEMPLATE"]) == '')
{
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
}

$arParams['SHOW_LOGIN'] = $arParams['SHOW_LOGIN'] !== "N" ? "Y" : "N";

if (IsModuleInstalled("intranet"))
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = $arParams['CAN_OWNER_EDIT_DESKTOP'] !== "Y" ? "N" : "Y";
else
	$arParams['CAN_OWNER_EDIT_DESKTOP'] = $arParams['CAN_OWNER_EDIT_DESKTOP'] !== "N" ? "Y" : "N";
if (IsModuleInstalled("blog"))
{
	if (!array_key_exists("BLOG_ALLOW_POST_CODE", $arParams))
		$arParams["BLOG_ALLOW_POST_CODE"] = "Y";
}

$arParams["USE_MAIN_MENU"] = (isset($arParams["USE_MAIN_MENU"]) && $arParams["USE_MAIN_MENU"] === "Y" ? $arParams["USE_MAIN_MENU"] : false);

if ($arParams["USE_MAIN_MENU"] === "Y" && !array_key_exists("MAIN_MENU_TYPE", $arParams))
	$arParams["MAIN_MENU_TYPE"] = "left";

$arParams["LOG_SUBSCRIBE_ONLY"] = (isset($arParams["LOG_SUBSCRIBE_ONLY"]) && $arParams["LOG_SUBSCRIBE_ONLY"] === "Y" ? "Y" : "N");

$arParams["LOG_RSS_TTL"] = (isset($arParams["LOG_RSS_TTL"]) && intval($arParams["LOG_RSS_TTL"]) > 0 ? $arParams["LOG_RSS_TTL"] : "60");

$arParams["GROUP_USE_BAN"] = $arParams["GROUP_USE_BAN"] !== "N" ? "Y" : "N";

$arParams["ALLOW_RATING_SORT"] = $arParams["ALLOW_RATING_SORT"] !== "Y" ? "N" : "Y";

// activation rating
CRatingsComponentsMain::GetShowRating($arParams);

if (
	!array_key_exists("RATING_ID", $arParams)
	|| (
		!is_array($arParams["RATING_ID"])
		&& intval($arParams["RATING_ID"]) <= 0
	)
)
{
	$arParams["RATING_ID"] = 0;
}

if (IsModuleInstalled("search"))
{
	if (!array_key_exists("SEARCH_FILTER_NAME", $arParams))
	{
		$arParams["SEARCH_FILTER_NAME"] = "sonet_search_filter";
	}

	if (!array_key_exists("SEARCH_FILTER_DATE_NAME", $arParams))
	{
		$arParams["SEARCH_FILTER_DATE_NAME"] = "sonet_search_filter_date";
	}
}

ComponentHelper::setModuleUsed();

$arCustomPagesPath = array();

if ($arParams["SEF_MODE"] === "Y")
{
	$arVariables = array();

	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplates404, &$arCustomPagesPath, $arParams));

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);

	/* This code is needed to use short paths in WebDAV */
	$arUrlTemplates["user_files_short"] = str_replace("#path#", $arDefaultUrlTemplates404["user_files_short"], $arUrlTemplates["user_files"]);
	$arUrlTemplates["group_files_short"] = str_replace("#path#", $arDefaultUrlTemplates404["group_files_short"], $arUrlTemplates["group_files"]);
	/* / This code is needed to use short paths in WebDAV */

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		$componentPage = "index";
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	foreach ($arUrlTemplates as $url => $value)
	{
		$arResult["PATH_TO_".mb_strtoupper($url)] = $arParams["SEF_FOLDER"].$value;
	}

	if (isset($_REQUEST["auth"]) && $_REQUEST["auth"] === "Y")
	{
		$componentPage = "auth";
	}

	ComponentHelper::setComponentOption(
		array(
			array(
				'CHECK_SEF_FOLDER' => true,
				'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'workgroups_page'),
				'VALUE' => $arParams["SEF_FOLDER"]
			),
			array(
				'CHECK_SEF_FOLDER' => true,
				'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'user_page'),
				'VALUE' => $arParams["SEF_FOLDER"]
			),
			array(
				'CHECK_SEF_FOLDER' => true,
				'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'workgroups_list_page'),
				'VALUE' => $arResult["PATH_TO_GROUP_SEARCH"]
			),
		),
		array(
			'SEF_FOLDER' => $arParams["SEF_FOLDER"],
			'SITE_ID' => SITE_ID
		)
	);
}
else
{
	if(is_array($arParams["VARIABLE_ALIASES"]))
		foreach ($arParams["VARIABLE_ALIASES"] as $key => $val)
			$arParams["VARIABLE_ALIASES"][$key] = (!empty($val) ? $val : $key);

	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplatesN404, &$arCustomPagesPath, $arParams));

	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);

	$events = GetModuleEvents("socialnetwork", "OnInitSocNetComponentVariables");
	while ($arEvent = $events->Fetch())
		ExecuteModuleEventEx($arEvent, array(&$arVariableAliases, &$arCustomPagesPath));

	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	if (!empty($arDefaultUrlTemplatesN404) && !empty($arParams["VARIABLE_ALIASES"]))
	{
		foreach ($arDefaultUrlTemplatesN404 as $url => $value)
		{
			$pattern = array();
			$replace = array();
			foreach ($arParams["VARIABLE_ALIASES"] as $key => $res)
			{
				if ($key != $res && !empty($res))
				{
					$pattern[] = preg_quote("/(^|([&?]+))".$key."\=/is");
					$replace[] = "$1".$res."=";
				}
			}
			if (!empty($pattern))
			{
				$value = preg_replace($pattern, $replace, $value);
				$arDefaultUrlTemplatesN404[$url] = $value;
			}
		}
	}
	foreach ($arDefaultUrlTemplatesN404 as $url => $value)
	{
		$arParamsKill = array("page", "path",
				"section_id", "element_id", "action", "user_id", "group_id", "action", "use_light_view", "AJAX_CALL", "MUL_MODE",
				"edit_section", "sessid", "post_id", "category", "topic_id", "result", "MESSAGE_TYPE", "user_settings_id", "q", "how", "tags", "where",
				"log_id");
		$arParamsKill = array_merge($arParamsKill, $arParams["VARIABLE_ALIASES"], array_values($arVariableAliases));
		$arResult["PATH_TO_".mb_strtoupper($url)] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arParamsKill);
	}

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplatesN404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplatesN404)))
	{
		$componentPage = "index";
	}
	if (isset($_REQUEST["auth"]) && $_REQUEST["auth"] === "Y")
		$componentPage = "auth";

	$arResult["PATH_TO_GROUP_LOG_RSS_MASK"] = $arResult["~PATH_TO_GROUP_LOG_RSS_MASK"] = $APPLICATION->GetCurPage(true)."?page=group_log_rss&group_id=".$arVariables["group_id"];
}

ComponentHelper::setComponentOption(
	array(
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'friends_page'),
			'VALUE' => (COption::GetOptionString("socialnetwork", "allow_frields", "Y") === "Y" ? $arResult["PATH_TO_USER_FRIENDS"] : false)
		),
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'userblogpost_page'),
			'VALUE' => $arResult["PATH_TO_USER_BLOG_POST"]
		),
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'userbloggroup_id'),
			'VALUE' => $arParams["BLOG_GROUP_ID"]
		),
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'smile_page'),
			'VALUE' => $arParams["PATH_TO_SMILE"]
		),
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'user_request_page'),
			'VALUE' => $arResult["PATH_TO_USER_REQUESTS"]
		),
		array(
			'CHECK_SEF_FOLDER' => true,
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'log_entry_page'),
			'VALUE' => $arResult["PATH_TO_LOG_ENTRY"]
		)
	),
	array(
		'SEF_FOLDER' => $arParams["SEF_FOLDER"],
		'SITE_ID' => SITE_ID
	)
);


$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arParams["SEF_MODE"] === "Y"? array(): $arVariableAliases,
		"SET_TITLE" => $arParams["SET_TITLE"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TIME_LONG" => $arParams["CACHE_TIME_LONG"],
		"SET_NAV_CHAIN" => $arParams["SET_NAV_CHAIN"],
		"SET_NAVCHAIN" => $arParams["SET_NAV_CHAIN"],
		"ITEM_DETAIL_COUNT" => $arParams["ITEM_DETAIL_COUNT"],
		"ITEM_MAIN_COUNT" => $arParams["ITEM_MAIN_COUNT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"USER_PROPERTY_MAIN" => $arParams["USER_PROPERTY_MAIN"],
		"USER_PROPERTY_CONTACT" => $arParams["USER_PROPERTY_CONTACT"],
		"USER_PROPERTY_PERSONAL" => $arParams["USER_PROPERTY_PERSONAL"],
		"USER_FIELDS_MAIN" => $arParams["USER_FIELDS_MAIN"],
		"USER_FIELDS_CONTACT" => $arParams["USER_FIELDS_CONTACT"],
		"USER_FIELDS_PERSONAL" => $arParams["USER_FIELDS_PERSONAL"],
		"USER_FIELDS_SEARCH_SIMPLE" => $arParams["USER_FIELDS_SEARCH_SIMPLE"],
		"USER_FIELDS_SEARCH_ADV" => $arParams["USER_FIELDS_SEARCH_ADV"],
		"USER_PROPERTIES_SEARCH_SIMPLE" => $arParams["USER_PROPERTIES_SEARCH_SIMPLE"],
		"USER_PROPERTIES_SEARCH_ADV" => $arParams["USER_PROPERTIES_SEARCH_ADV"],
		"USER_FIELDS_LIST" => $arParams["SONET_USER_FIELDS_LIST"],
		"USER_PROPERTY_LIST" => $arParams["SONET_USER_PROPERTY_LIST"],
		"USER_FIELDS_SEARCHABLE" => $arParams["SONET_USER_FIELDS_SEARCHABLE"],
		"USER_PROPERTY_SEARCHABLE" => $arParams["SONET_USER_PROPERTY_SEARCHABLE"],
		"GROUP_PROPERTY" => $arParams["GROUP_PROPERTY"],
		"GROUP" => array()
	),
	$arResult
);

// set options for tooltip
$tooltipPathToUser = COption::GetOptionString("main", "TOOLTIP_PATH_TO_USER", false, SITE_ID);
if (!$tooltipPathToUser)
{
	COption::SetOptionString("main", "TOOLTIP_PATH_TO_USER", $arResult["PATH_TO_USER"], false, SITE_ID);
	$tooltipPathToUser = $arResult["PATH_TO_USER"];
}
if (mb_substr($tooltipPathToUser, 0, mb_strlen($arParams["SEF_FOLDER"])) !== $arParams["SEF_FOLDER"])
{
	COption::SetOptionString("main", "TOOLTIP_PATH_TO_USER", $arParams["SEF_FOLDER"]."user/#user_id#/", false, SITE_ID);
}

ComponentHelper::setComponentOption(array(
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_PATH_TO_MESSAGES_CHAT'),
		'VALUE' => $arResult["PATH_TO_MESSAGES_CHAT"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_PATH_TO_VIDEO_CALL'),
		'VALUE' => $arResult["PATH_TO_VIDEO_CALL"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_DATE_TIME_FORMAT'),
		'VALUE' => $arResult["DATE_TIME_FORMAT"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_SHOW_YEAR'),
		'VALUE' => $arParams["SHOW_YEAR"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_NAME_TEMPLATE'),
		'VALUE' => $arParams["NAME_TEMPLATE"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_SHOW_LOGIN'),
		'VALUE' => $arParams["SHOW_LOGIN"]
	),
	array(
		'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_PATH_TO_CONPANY_DEPARTMENT'),
		'VALUE' => $arParams["PATH_TO_CONPANY_DEPARTMENT"]
	)
));

$arResult["PATH_TO_SEARCH_INNER"] = $arResult["PATH_TO_SEARCH"];
$arParams["PATH_TO_SEARCH_EXTERNAL"] = Trim($arParams["PATH_TO_SEARCH_EXTERNAL"]);
if ($arParams["PATH_TO_SEARCH_EXTERNAL"] <> '')
	$arResult["PATH_TO_SEARCH"] = $arParams["PATH_TO_SEARCH_EXTERNAL"];

$arParams["ERROR_MESSAGE"] = "";
$arParams["NOTE_MESSAGE"] = "";
/********************************************************************
				Search Index
********************************************************************/
if(check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] === "PUT")
{
	global $bxSocNetSearch;
	if(
		!is_object($bxSocNetSearch)
		&& CModule::IncludeModule("socialnetwork")
	)
	{
		$arSocNetSearchParams = array(
			"PATH_TO_GROUP" => $arResult["PATH_TO_GROUP"],

			"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
			"PATH_TO_GROUP_BLOG" => $arResult["PATH_TO_GROUP_BLOG"],
			"PATH_TO_GROUP_BLOG_POST" => $arResult["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_GROUP_BLOG_COMMENT" => $arResult["PATH_TO_GROUP_BLOG_POST"]."?commentId=#comment_id##com#comment_id#",
			"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
			"PATH_TO_USER_BLOG_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
			"PATH_TO_USER_BLOG_COMMENT" => $arResult["PATH_TO_USER_BLOG_POST"]."?commentId=#comment_id##com#comment_id#",
			"PATH_TO_USER_MICROBLOG" => $arResult["PATH_TO_USER_MICROBLOG"],
			"PATH_TO_USER_MICROBLOG_POST" => $arResult["PATH_TO_USER_MICROBLOG_POST"],
			"PATH_TO_GROUP_MICROBLOG" => $arResult["PATH_TO_GROUP_MICROBLOG"],
			"PATH_TO_GROUP_MICROBLOG_POST" => $arResult["PATH_TO_GROUP_MICROBLOG_POST"],

			"FORUM_ID" => $arParams["FORUM_ID"],
			"PATH_TO_GROUP_FORUM_MESSAGE" => $arResult["PATH_TO_GROUP_FORUM_MESSAGE"],
			"PATH_TO_USER_FORUM_MESSAGE" => $arResult["PATH_TO_USER_FORUM_MESSAGE"],

			"PHOTO_GROUP_IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
			"PATH_TO_GROUP_PHOTO_ELEMENT" => $arResult["PATH_TO_GROUP_PHOTO_ELEMENT"],
			"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
			"PATH_TO_USER_PHOTO_ELEMENT" => $arResult["PATH_TO_USER_PHOTO_ELEMENT"],
			"PHOTO_FORUM_ID" => $arParams["PHOTO_FORUM_ID"],

			"CALENDAR_GROUP_IBLOCK_ID" => $arParams["CALENDAR_GROUP_IBLOCK_ID"],
			"PATH_TO_GROUP_CALENDAR_ELEMENT" => $arResult["PATH_TO_GROUP_CALENDAR"]."?EVENT_ID=#element_id#",

			"PATH_TO_GROUP_TASK_ELEMENT" => $arResult["PATH_TO_GROUP_TASKS_TASK"],
			"PATH_TO_USER_TASK_ELEMENT" => $arResult["PATH_TO_USER_TASKS_TASK"],
			"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],
		);

		if (!$diskEnabled)
		{
			$arSocNetSearchParams["FILES_PROPERTY_CODE"] = $arParams["NAME_FILE_PROPERTY"];
			$arSocNetSearchParams["FILES_FORUM_ID"] = $arParams["FILES_FORUM_ID"];
			$arSocNetSearchParams["FILES_GROUP_IBLOCK_ID"] = $arParams["FILES_GROUP_IBLOCK_ID"];
			$arSocNetSearchParams["PATH_TO_GROUP_FILES_ELEMENT"] = $arResult["PATH_TO_GROUP_FILES_ELEMENT"];
			$arSocNetSearchParams["FILES_USER_IBLOCK_ID"] = $arParams["FILES_USER_IBLOCK_ID"];
			$arSocNetSearchParams["PATH_TO_USER_FILES_ELEMENT"] = $arResult["PATH_TO_USER_FILES_ELEMENT"];
		}

		$bxSocNetSearch = new CSocNetSearch($arResult["VARIABLES"]["user_id"], $arResult["VARIABLES"]["group_id"], $arSocNetSearchParams);

		AddEventHandler("search", "BeforeIndex", Array($bxSocNetSearch, "BeforeIndex"));
		AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementDelete", Array($bxSocNetSearch, "IBlockElementDelete"));
	}
}
/********************************************************************
				WebDav
********************************************************************/
if (
	!$diskEnabled
	&& mb_strpos($componentPage, "user_files") === false
	&& mb_strpos($componentPage, "group_files") === false
)
{
	$sCurrUrl = mb_strtolower(str_replace("//", "/", "/".$APPLICATION->GetCurPage()."/"));
	$arBaseUrl = array(
		"user" => $arParams["FILES_USER_BASE_URL"],
		"group" => $arParams["FILES_GROUP_BASE_URL"]);

	if ($arParams["SEF_MODE"] === "Y" )
	{
		$arBaseUrl = array(
			"user" => $arResult["PATH_TO_USER_FILES"],
			"group" => $arResult["PATH_TO_GROUP_FILES"]);
	}
	foreach ($arBaseUrl as $key => $res)
	{
		if (mb_strpos($res, "#path#") !== false)
			$res = mb_substr($res, 0, mb_strpos($res, "#path#"));
		$res = mb_strtolower(str_replace("//", "/", "/".$res."/"));
		$pos = mb_strpos($res, "#".$key."_id#");
		if ($pos !== false && mb_substr($res, 0, $pos) == mb_substr($sCurrUrl, 0, $pos))
		{
			$v1 = mb_substr($res, $pos + mb_strlen("#".$key."_id#"));
			$v2 = mb_substr($sCurrUrl, $pos);
			$v3 = mb_substr($v2, mb_strpos($v2, mb_substr($v1, 0, 1)), mb_strlen($v1));
			if ($v1 == $v3)
			{
				$componentPage = $key."_files";
				$arResult["VARIABLES"]["#".$key."_id#"] = intval(mb_substr($v2, 0, mb_strpos($v2, mb_substr($v1, 0, 1))));
				$arResult["VARIABLES"][$key."_id"] = intval(mb_substr($v2, 0, mb_strpos($v2, mb_substr($v1, 0, 1))));
			}
		}
	}
}

if (
	!$diskEnabled
	&& (
		mb_strpos($componentPage, "group_files") !== false
		|| mb_strpos($componentPage, "group_blog") !== false
		|| mb_strpos($componentPage, "group_log") !== false
		|| $componentPage === "group"
	)
)
{
	if (intval($arResult["VARIABLES"]["group_id"]) > 0)
	{
		$cache_time = 31536000;
		$arEvent = array();

		$cache = new CPHPCache;
		$cache_id = "files_iblock_id";
		$cache_path = "/sonet/group_comp/".$arResult["VARIABLES"]["group_id"]."/";

		if ($cache->InitCache($cache_time, $cache_id, $cache_path))
		{
			$arCacheVars = $cache->GetVars();
			$arParams["FILES_GROUP_IBLOCK_ID"] = $arCacheVars["FILES_GROUP_IBLOCK_ID"];
		}
		elseif (CModule::IncludeModule("iblock"))
		{
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);

			$arFilesIBlockID = array();
			$rsIBlock = CIBlock::GetList(array(), array("ACTIVE" => "Y", "CHECK_PERMISSIONS"=>"N", "CODE"=>"group_files%"));
			while($arIBlock = $rsIBlock->Fetch())
				$arFilesIBlockID[] = $arIBlock["ID"];

			if (count($arFilesIBlockID) > 0)
			{
				$rsFilesSection = CIBlockSection::GetList(
					array("timestamp_x"=>"desc"),
					array(
						"IBLOCK_ID" => $arFilesIBlockID,
						"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"]
					)
				);
				if ($arFilesSection = $rsFilesSection->Fetch())
					$arParams["FILES_GROUP_IBLOCK_ID"] = $arFilesSection["IBLOCK_ID"];
			}

			$arCacheData = Array(
				"FILES_GROUP_IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"]
			);
			$cache->EndDataCache($arCacheData);
		}
	}

	if (
		intval($arResult["VARIABLES"]["group_id"]) > 0
		&& intval($arResult["VARIABLES"]["element_id"]) > 0
		&& intval($arParams["FILES_FORUM_ID"]) > 0
		&& intval($arParams["FILES_GROUP_IBLOCK_ID"]) > 0
	)
	{
		$cache_time = 31536000;
		$arEvent = array();

		$cache = new CPHPCache;

		$arCacheID = array();
		$arKeys = array(
			"FILES_GROUP_IBLOCK_ID",
		);
		foreach($arKeys as $param_key)
		{
			if (array_key_exists($param_key, $arParams))
				$arCacheID[$param_key] = $arParams[$param_key];
			else
				$arCacheID[$param_key] = false;
		}
		$cache_id = "files_forum_id_".md5(serialize($arCacheID));
		$cache_path = "/sonet/group_comp/".$arResult["VARIABLES"]["group_id"]."/".$arResult["VARIABLES"]["element_id"];

		if ($cache->InitCache($cache_time, $cache_id, $cache_path))
		{
			$arCacheVars = $cache->GetVars();
			$arParams["FILES_FORUM_ID"] = $arCacheVars["FILES_FORUM_ID"];
		}
		elseif (
			CModule::IncludeModule("forum")
			&& CModule::IncludeModule("iblock")
		)
		{
			$cache->StartDataCache($cache_time, $cache_id, $cache_path);

			$rsIBlockElement = CIBlockElement::GetList(
				array(),
				array(
					"IBLOCK_ID" => $arParams["FILES_GROUP_IBLOCK_ID"],
					"ID" => $arResult["VARIABLES"]["element_id"]
				),
				false,
				false,
				array("IBLOCK_ID", "PROPERTY_FORUM_TOPIC_ID")
			);

			if (
				($arIBlockElement = $rsIBlockElement->Fetch())
				&& array_key_exists("PROPERTY_FORUM_TOPIC_ID_VALUE", $arIBlockElement)
				&& intval($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]) > 0
			)
			{
				$arForumTopic = CForumTopic::GetByID($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
				$arParams["FILES_FORUM_ID"] = $arForumTopic["FORUM_ID"];
			}

			$arCacheData = Array(
				"FILES_FORUM_ID" => $arParams["FILES_FORUM_ID"]
			);
			$cache->EndDataCache($arCacheData);
		}
	}
}

$path2 = str_replace(array("\\", "//"), "/", __DIR__."/include/webdav_2.php");
if (file_exists($path2))
	include_once($path2);

if (
	mb_strpos($componentPage, "user_files") !== false
	|| mb_strpos($componentPage, "group_files") !== false
)
{
	if (
		IsModuleInstalled("extranet")
		&& mb_strpos($componentPage, "user_files") !== false
		&& CModule::IncludeModule("iblock")
	)
	{
		$bIsUserExtranet = false;

		$obCache = new CPHPCache;
		$strCacheID = $arResult["VARIABLES"]["user_id"];
		$path = "/sonet_user_files_iblock_".intval($arResult["VARIABLES"]["user_id"] / 100)."_".SITE_ID;

		if($obCache->StartDataCache(60*60*24*365, $strCacheID, $path))
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($path);
				$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($arResult["VARIABLES"]["user_id"] / TAGGED_user_card_size));
			}

			$extranet_site_id = COption::GetOptionString("extranet", "extranet_site");

			$rsIBlock = CIBlock::GetList(array(), array("ACTIVE" => "Y", "CHECK_PERMISSIONS"=>"N", "CODE"=>"user_files%"));

			while($arIBlock = $rsIBlock->Fetch())
			{
				$rsSite = CIBlock::GetSite($arIBlock["ID"]);
				while($arSite = $rsSite->Fetch())
				{
					if (
						$arSite["SITE_ID"] == $extranet_site_id
						&& intval($extranet_iblock_id) <= 0
					)
						$extranet_iblock_id = $arIBlock["ID"];
					elseif (
						$arSite["SITE_ID"] != $extranet_site_id
						&& intval($intranet_iblock_id) <= 0
					)
						$intranet_iblock_id = $arIBlock["ID"];
				}

				if (
					intval($intranet_iblock_id) > 0
					&& intval($extranet_iblock_id) > 0
				)
					break;
			}

			if (
				intval($intranet_iblock_id) > 0
				&& intval($extranet_iblock_id) > 0
			)
			{
				if (CSocNetUser::IsUserModuleAdmin($arResult["VARIABLES"]["user_id"]))
					$bIsUserExtranet = false;
				else
				{
					$rsUser = CUser::GetList("id", "asc", array("ID" => $arResult["VARIABLES"]["user_id"]), array("SELECT" => array("UF_DEPARTMENT"), "FIELDS" => array("ID")));
					if ($arUser = $rsUser->Fetch())
						$bIsUserExtranet = (
							(
								is_array($arUser["UF_DEPARTMENT"])
								&& count($arUser["UF_DEPARTMENT"]) <= 0
							)
							|| (
								!is_array($arUser["UF_DEPARTMENT"])
								&& intval($arUser["UF_DEPARTMENT"]) <= 0
							)
						);
				}

				$arCachedResult["FILES_USER_IBLOCK_ID"] = ($bIsUserExtranet ? $extranet_iblock_id : $intranet_iblock_id);

			}
			else
			{
				$arCachedResult["FILES_USER_IBLOCK_ID"] = (
					(intval($extranet_iblock_id) > 0)
						? intval($extranet_iblock_id)
						: ((intval($intranet_iblock_id) > 0)
							? intval($intranet_iblock_id)
							: 0)
				);
			}

			$obCache->EndDataCache($arCachedResult);
			if(defined("BX_COMP_MANAGED_CACHE"))
				$CACHE_MANAGER->EndTagCache();
		}
		else
			$arCachedResult = $obCache->GetVars();

		if (
			isset($arCachedResult["FILES_USER_IBLOCK_ID"])
			&& intval($arCachedResult["FILES_USER_IBLOCK_ID"]) > 0
		)
		{
			$arParams["FILES_USER_IBLOCK_ID"]= $arCachedResult["FILES_USER_IBLOCK_ID"];
		}

		$arCachedResult = false;
		$obCache = new CPHPCache;
		$strCacheID = $arResult["VARIABLES"]["user_id"];
		$path = "/sonet_user_files_forum_".intval($arResult["VARIABLES"]["user_id"] / 100)."_".SITE_ID;

		if($obCache->StartDataCache(60*60*24*365, $strCacheID, $path))
		{
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$CACHE_MANAGER->StartTagCache($path);
				$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($arResult["VARIABLES"]["user_id"] / TAGGED_user_card_size));
			}

			if (
				intval($arResult["VARIABLES"]["element_id"]) > 0
				&& intval($arParams["FILES_FORUM_ID"]) > 0
				&& intval($arParams["FILES_USER_IBLOCK_ID"]) > 0
				&& CModule::IncludeModule("forum")
			)
			{
				$rsIBlockElement = CIBlockElement::GetList(
					array(),
					array(
						"IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
						"ID" => $arResult["VARIABLES"]["element_id"]
					),
					false,
					false,
					array("IBLOCK_ID", "PROPERTY_FORUM_TOPIC_ID")
				);

				if (
					($arIBlockElement = $rsIBlockElement->Fetch())
					&& array_key_exists("PROPERTY_FORUM_TOPIC_ID_VALUE", $arIBlockElement)
					&& intval($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]) > 0
				)
				{
					$arForumTopic = CForumTopic::GetByID($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
					$arCachedResult["FILES_FORUM_ID"] = $arForumTopic["FORUM_ID"];
				}
			}

			$obCache->EndDataCache($arCachedResult);
			if(defined("BX_COMP_MANAGED_CACHE"))
				$CACHE_MANAGER->EndTagCache();
		}
		else
			$arCachedResult = $obCache->GetVars();

		if (
			isset($arCachedResult["FILES_FORUM_ID"])
			&& intval($arCachedResult["FILES_FORUM_ID"]) > 0
		)
		{
			$arParams["FILES_FORUM_ID"]= $arCachedResult["FILES_FORUM_ID"];
		}
	}

	$path = str_replace(array("\\", "//"), "/", __DIR__."/include/webdav.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "WebDAV file is not exist.";
		$res = 0;
	}
	else
		$res = include_once($path);

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}

/********************************************************************
				/WebDav
********************************************************************/
/********************************************************************
				Photogalley
********************************************************************/
elseif (mb_strpos($componentPage, "user_photo") !== false || mb_strpos($componentPage, "group_photo") !== false)
{
	if (mb_strpos($componentPage, "user_photofull") !== false || mb_strpos($componentPage, "group_photofull") !== false)
		$componentPage = str_replace("_photofull", "_photo", $componentPage);

	$path = str_replace(array("\\", "//"), "/", __DIR__."/include/photogallery.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Photogallery file is not exist.";
		$res = 0;
	}
	else
		$res = include_once($path);

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");

	if (mb_strpos($componentPage, "group_photo") !== false && CModule::IncludeModule('iblock'))
	{
		$arPhotoIBlockID = array();
		$rsIBlock = CIBlock::GetList(array(), array("ACTIVE" => "Y", "CODE"=>"group_photogallery%"));
		while($arIBlock = $rsIBlock->Fetch())
			$arPhotoIBlockID[] = $arIBlock["ID"];

		if (count($arPhotoIBlockID) > 0)
		{
			$rsPhotoSection = CIBlockSection::GetList(
				array("timestamp_x"=>"desc"),
				array(
					"IBLOCK_ID" => $arPhotoIBlockID,
					"SOCNET_GROUP_ID" => $arResult["VARIABLES"]["group_id"]
				)
			);
			if ($arPhotoSection = $rsPhotoSection->Fetch())
			{
				$arParams["PHOTO_GROUP_IBLOCK_ID"] = $arPhotoSection["IBLOCK_ID"];

				if (
					intval($_GET["ELEMENT_ID"]) > 0
					&& intval($arParams["PHOTO"]["ALL"]["FORUM_ID"]) > 0
					&& $arParams["PHOTO"]["ALL"]["COMMENTS_TYPE"] === "FORUM"
					&& CModule::IncludeModule("forum")
				)
				{
					$rsIBlockElement = CIBlockElement::GetList(
						array(),
						array(
							"IBLOCK_ID" => $arParams["PHOTO_GROUP_IBLOCK_ID"],
							"ID" => $_GET["ELEMENT_ID"]
						),
						false,
						false,
						array("IBLOCK_ID", "PROPERTY_FORUM_TOPIC_ID")
					);

					if (
						($arIBlockElement = $rsIBlockElement->Fetch())
						&& array_key_exists("PROPERTY_FORUM_TOPIC_ID_VALUE", $arIBlockElement)
					)
					{
						$arForumTopic = CForumTopic::GetByID($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
						$arParams["PHOTO"]["ALL"]["FORUM_ID"] = $arForumTopic["FORUM_ID"];
					}
				}
			}
		}
	}
}
/********************************************************************
				/Photogalley
********************************************************************/
/********************************************************************
				Forum
********************************************************************/
elseif (mb_strpos($componentPage, "user_forum") !== false || mb_strpos($componentPage, "group_forum") !== false ||
	$componentPage === "user" || $componentPage === "group" || $componentPage === "index")
{
	$path = str_replace(array("\\", "//"), "/", __DIR__."/include/forum.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Forum file is not exist.";
		$res = 0;
	}
	else
		$res = include_once($path);

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Forum
********************************************************************/
/********************************************************************
				Content Search
********************************************************************/
elseif (mb_strpos($componentPage, "user_content_search") !== false || mb_strpos($componentPage, "group_content_search") !== false)
{
	$path = str_replace(array("\\", "//"), "/", __DIR__."/include/search.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Content search file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}
	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Content search
********************************************************************/

/********************************************************************
				Buziness-process
********************************************************************/
elseif ($componentPage === "bizproc_task")
	$componentPage = "bizproc_edit";
elseif ($componentPage === "bizproc_task_list")
	$componentPage = "bizproc";

/********************************************************************
				/Buziness-process
********************************************************************/

CUtil::InitJSCore(array("window", "ajax"));
\Bitrix\Main\UI\Extension::load("socialnetwork.slider");

$this->IncludeComponentTemplate($componentPage, array_key_exists($componentPage, $arCustomPagesPath) ? $arCustomPagesPath[$componentPage] : "");
/********************************************************************
				Activity after
********************************************************************/

//top panel button to reindex
if($USER->IsAdmin())
{
	$APPLICATION->AddPanelButton(array(
		"HREF"=> $arResult["PATH_TO_REINDEX"],
		"ICON"=>"bx-panel-reindex-icon",
		"ALT"=>GetMessage('SONET_PANEL_REINDEX_TITLE'),
		"TEXT"=>GetMessage('SONET_PANEL_REINDEX'),
		"MAIN_SORT"=>"1000",
		"SORT"=>100,
		"MODE"=>array("configure"),
	));
}

