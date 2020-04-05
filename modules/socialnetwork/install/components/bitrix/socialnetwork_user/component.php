<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arDefaultUrlTemplates404 = array(
	"index" => "index.php",

	"user_reindex" => "user_reindex.php",
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

	"group_request_group_search" => "group/#user_id#/group_search/",
	"group_request_user" => "group/#group_id#/user/#user_id#/request/",

	"search" => "search.php",

	"message_form" => "messages/form/#user_id#/",
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
	"crm" => "crm/",
	"log_entry" => "log/#log_id#/",
	"activity" => "user/#user_id#/activity/",
	"subscribe" => "subscribe/",
	"bizproc" => "bizproc/",
	"bizproc_edit" => "bizproc/#task_id#/",
	"bizproc_task_list" => "user/#user_id#/bizproc/",
	"bizproc_task" => "user/#user_id#/bizproc/#task_id#/",
	"video_call" => "video/#user_id#/",

	"processes" => "processes/",

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
	"user_files_element_comment" => "user/#user_id#/files/element/comment/#topic_id#/#message_id#/",
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

	"user_blog" => "user/#user_id#/blog/",
	"user_blog_post_important" => "user/#user_id#/blog/important/",
	"user_blog_post_edit" => "user/#user_id#/blog/edit/#post_id#/",
	"user_blog_rss" => "user/#user_id#/blog/rss/#type#/",
	"user_blog_post_rss" => "user/#user_id#/blog/rss/#type#/#post_id#/",
	"user_blog_draft" => "user/#user_id#/blog/draft/",
	"user_blog_moderation" => "user/#user_id#/blog/moderation/",
	"user_blog_tags" => "user/#user_id#/blog/tags/",
	"user_blog_post" => "user/#user_id#/blog/#post_id#/",

	"user_tasks" => "user/#user_id#/tasks/",
	"user_tasks_board" => "user/#user_id#/tasks/board/",
	"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
	"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",
	"user_tasks_departments_overview" => "user/#user_id#/tasks/departments/",
	"user_tasks_employee_plan" => "user/#user_id#/tasks/employee/plan/",
	"user_tasks_projects_overview" => "user/#user_id#/tasks/projects/",
	"user_tasks_effective" => "user/#user_id#/tasks/effective/",
	"user_tasks_effective_detail" => "user/#user_id#/tasks/effective/show/",
	"user_tasks_effective_inprogress" => "user/#user_id#/tasks/effective/inprogress/",
	"user_tasks_report" => "user/#user_id#/tasks/report/",
	"user_tasks_report_construct" => "user/#user_id#/tasks/report/construct/#report_id#/#action#/",
	"user_tasks_report_view" => "user/#user_id#/tasks/report/view/#report_id#/",
	"user_tasks_templates" => "user/#user_id#/tasks/templates/",
	"user_tasks_report_board" => "user/#user_id#/tasks/reportboard/",
	"user_templates_template" => "user/#user_id#/tasks/templates/template/#action#/#template_id#/",

	"user_tasks_import" => "user/#user_id#/tasks/import/",

	"user_forum" => "user/#user_id#/forum/",
	"user_forum_topic" => "user/#user_id#/forum/#topic_id#/",
	"user_forum_topic_edit" => "user/#user_id#/forum/edit/#action#/#topic_id#/#message_id#/",
	"user_forum_message" => "user/#user_id#/forum/message/#topic_id#/#message_id#/",
	"user_forum_message_edit" => "user/#user_id#/forum/message/#action#/#topic_id#/#message_id#/",

	"user_security" => "user/#user_id#/security/",
	"user_codes" => "user/#user_id#/codes/",
	"user_passwords" => "user/#user_id#/passwords/",
);

$diskEnabled = (
	\Bitrix\Main\Config\Option::get('disk', 'successfully_converted', false)
	&& CModule::includeModule('disk')
);

$bExtranetEnabled = IsModuleInstalled('extranet');
if ($bExtranetEnabled)
{
	$extranetSiteId = COption::GetOptionString("extranet", "extranet_site");
	if (strlen($extranetSiteId) <= 0)
	{
		$bExtranetEnabled = false;
	}
	elseif ($arParams["SEF_MODE"] == "Y")
	{
		$arRedirectSite = CSocNetLogComponent::getExtranetRedirectSite($extranetSiteId);
	}
}

$davEnabled = \Bitrix\Main\Loader::includeModule('dav');

if($diskEnabled)
{
	$arDefaultUrlTemplates404["user_disk"] = "user/#user_id#/disk/path/#PATH#";
	$arDefaultUrlTemplates404["user_disk_file"] = "user/#user_id#/disk/file/#FILE_PATH#";
	$arDefaultUrlTemplates404["user_trashcan_list"] = "user/#user_id#/disk/trashcan/#TRASH_PATH#";
	$arDefaultUrlTemplates404["user_trashcan_file_view"] = "user/#user_id#/disk/trash/file/#TRASH_FILE_PATH#";
	$arDefaultUrlTemplates404["user_external_link_list"] = "user/#user_id#/disk/external";
	$arDefaultUrlTemplates404["user_disk_help"] = "user/#user_id#/disk/help";
	$arDefaultUrlTemplates404["user_disk_volume"] = "user/#user_id#/disk/volume/#ACTION#";
}

if ($bExtranetEnabled)
{
	unset($arDefaultUrlTemplates404["search"]);
}

if ($davEnabled)
{
	$arDefaultUrlTemplates404["user_synchronize"] = "user/#user_id#/synchronize/";
}

$arDefaultUrlTemplatesN404 = array(
	"index" => "",

	"user_reindex" => "page=user_reindex",
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
	"user_requests" => "page=user_requests&user_id=#user_id#",

	"group_request_group_search" => "page=group_request_group_search&user_id=#user_id#",
	"group_request_user" => "page=group_request_user&group_id=#group_id#&user_id=#user_id#",

	"search" => "page=search",

	"message_form" => "page=message_form&user_id=#user_id#",
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
	"crm" => "page=crm",
	"log_entry" => "page=log_entry&log_id=#log_id#",
	"activity" => "page=activity&user_id=#user_id#",
	"subscribe" => "page=subscribe",
	"bizproc" => "page=bizproc",
	"bizproc_edit" => "page=bizproc_edit&task_id=#task_id#",
	"bizproc_task_list" => "page=bizproc_task_list&user_id=#user_id#",
	"bizproc_task" => "page=bizproc_task&user_id=#user_id#&task_id=#task_id#",
	"video_call" => "page=video_call&user_id=#user_id#",

	"processes" => "processes/",

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
	"user_files_element_comment" => "page=user_files_element_comment&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#",
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

	"user_blog" => "page=user_blog&user_id=#user_id#",
	"user_blog_post_edit" => "page=user_blog_post_edit&user_id=#user_id#&post_id=#post_id#",
	"user_blog_rss" => "page=user_blog_rss&user_id=#user_id#&type=#type#",
	"user_blog_post_rss" => "page=user_blog_post_rss&user_id=#user_id#&type=#type#&post_id=#post_id#",
	"user_blog_draft" => "page=user_blog_draft&user_id=#user_id#",
	"user_blog_moderation" => "page=user_blog_moderation&user_id=#user_id#",
	"user_blog_tags" => "page=user_blog_tags&user_id=#user_id#",
	"user_blog_post" => "page=user_blog_post&user_id=#user_id#&post_id=#post_id#",

	"user_tasks" => "page=user_tasks&user_id=#user_id#",
	"user_tasks_board" => "page=user_tasks_board&user_id=#user_id#",
	"user_tasks_task" => "page=user_tasks_task&user_id=#user_id#&action=#action#&task_id=#task_id#",
	"user_tasks_view" => "page=user_tasks_view&user_id=#user_id#&action=#action#&view_id=#view_id#",
	"user_tasks_report" => "page=user_tasks_report&user_id=#user_id#",
	"user_tasks_report_construct" => "page=user_tasks_report_construct&user_id=#user_id#&action=#action#&report_id=#report_id#",
	"user_tasks_report_view" => "page=user_tasks_report_view&user_id=#user_id#&report_id=#report_id#",
	"user_tasks_effective" => "page=user_tasks_effective&user_id=#user_id#",
	"user_tasks_effective_detail" => "page=user_tasks_effective_detail&user_id=#user_id#",
	"user_tasks_effective_inprogress" => "page=user_tasks_effective_inprogress&user_id=#user_id#",
	"user_tasks_templates" => "page=user_tasks_templates&user_id=#user_id#",
	"user_tasks_employee_plan" => "page=user_tasks_employee_plan&user_id=#user_id#",
	"user_templates_template" => "page=user_templates_template&user_id=#user_id#&action=#action#&template_id=#template_id#",

	"user_forum" => "page=user_forum&user_id=#user_id#",
	"user_forum_topic" => "page=user_forum_topic&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_topic_edit" => "page=user_forum_topic_edit&user_id=#user_id#&topic_id=#topic_id#",
	"user_forum_message" => "page=user_forum_message&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#",
	"user_forum_message_edit" => "page=user_forum_message_edit&user_id=#user_id#&topic_id=#topic_id#&message_id=#message_id#&action=#action#",

	"user_security" => "page=user_security&user_id=#user_id#",
	"user_passwords" => "page=user_passwords&user_id=#user_id#",
);
$arDefaultVariableAliases404 = array();
$arDefaultVariableAliases = array();
$componentPage = "";
$arComponentVariables = array("user_id", "group_id", "page", "message_id", "path", "section_id", "element_id", "action", "post_id", "category", "topic_id", "task_id", "view_id", "type", "report_id", "log_id");

if ($davEnabled)
{
	$arDefaultUrlTemplatesN404["user_synchronize"] = "user/#user_id#/synchronize/";
}

if (
	$_REQUEST["auth"]=="Y" 
	&& $USER->IsAuthorized()
)
{
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));
}

if (!array_key_exists("SET_NAV_CHAIN", $arParams))
{
	$arParams["SET_NAV_CHAIN"] = $arParams["SET_NAVCHAIN"];
}
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
$arParams["LOG_AUTH"] = (StrToUpper($arParams["LOG_AUTH"]) == "Y" ? "Y" : "N");
$arParams["HIDE_OWNER_IN_TITLE"] = ($arParams["HIDE_OWNER_IN_TITLE"] == "Y" ? "Y" : "N");

if (!array_key_exists("ALLOW_GROUP_CREATE_REDIRECT_REQUEST", $arParams))
{
	$arParams["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] = "Y";
}

if (
	$arParams["ALLOW_GROUP_CREATE_REDIRECT_REQUEST"] != "N" 
	&& (
		!array_key_exists("GROUP_CREATE_REDIRECT_REQUEST", $arParams) 
		|| strlen(trim($arParams["GROUP_CREATE_REDIRECT_REQUEST"])) <= 0
	)
)
{
	$arParams["GROUP_CREATE_REDIRECT_REQUEST"] = SITE_DIR."workgroups/group/#group_id#/user_search/";
}

if (strlen(trim($arParams["NAME_TEMPLATE"])) <= 0)
{
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
}
$arParams["SHOW_LOGIN"] = $arParams['SHOW_LOGIN'] != "N" ? "Y" : "N";

if ($arParams["GROUP_USE_KEYWORDS"] != "N") $arParams["GROUP_USE_KEYWORDS"] = "Y";

if (!is_array($arParams["VARIABLE_ALIASES"]))
{
	$arParams["VARIABLE_ALIASES"] = array();
}

$arParams['CAN_OWNER_EDIT_DESKTOP'] = (
	IsModuleInstalled("intranet")
		? ($arParams['CAN_OWNER_EDIT_DESKTOP'] != "Y" ? "N" : "Y")
		: ($arParams['CAN_OWNER_EDIT_DESKTOP'] != "N" ? "Y" : "N")
);

// for bitrix:main.user.link
if (IsModuleInstalled('intranet'))
{
	$arTooltipFieldsDefault	= serialize(array(
		"EMAIL",
		"PERSONAL_MOBILE",
		"WORK_PHONE",
		"PERSONAL_ICQ",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION",
	));
	$arTooltipPropertiesDefault = serialize(array(
		"UF_DEPARTMENT",
		"UF_PHONE_INNER",
	));
}
else
{
	$arTooltipFieldsDefault = serialize(array(
		"PERSONAL_ICQ",
		"PERSONAL_BIRTHDAY",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION"
	));
	$arTooltipPropertiesDefault = serialize(array());
}

if (!array_key_exists("SHOW_FIELDS_TOOLTIP", $arParams))
{
	$arParams["SHOW_FIELDS_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", $arTooltipFieldsDefault));
}

if (!array_key_exists("USER_PROPERTY_TOOLTIP", $arParams))
{
	$arParams["USER_PROPERTY_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", $arTooltipPropertiesDefault));
}

if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
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

if (IsModuleInstalled("blog"))
{
	if (!array_key_exists("PATH_TO_GROUP_POST", $arParams))
	{
		$arParams["PATH_TO_GROUP_POST"] = SITE_DIR."workgroups/group/#group_id#/blog/#post_id#/";
	}
	if (!array_key_exists("PATH_TO_GROUP_BLOG", $arParams))
	{
		$arParams["PATH_TO_GROUP_BLOG"] = SITE_DIR."workgroups/group/#group_id#/blog/";
	}

	if (!array_key_exists("BLOG_ALLOW_POST_CODE", $arParams))
	{
		$arParams["BLOG_ALLOW_POST_CODE"] = "Y";
	}
}

$arParams["USE_MAIN_MENU"] = (
	isset($arParams["USE_MAIN_MENU"])
	&& $arParams["USE_MAIN_MENU"] == "Y"
		? $arParams["USE_MAIN_MENU"]
		: false
);

if (
	$arParams["USE_MAIN_MENU"] == "Y"
	&& !array_key_exists("MAIN_MENU_TYPE", $arParams)
)
{
	$arParams["MAIN_MENU_TYPE"] = "left";
}

$arParams["ALLOW_RATING_SORT"] = ($arParams["ALLOW_RATING_SORT"] != "Y" ? "N" : "Y");

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

$arCustomPagesPath = array();

if ($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
	{
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplates404, &$arCustomPagesPath, $arParams));
	}

	$engine = new CComponentEngine($this);
	if($diskEnabled)
	{
		$engine->addGreedyPart("#PATH#");
		$engine->addGreedyPart("#FILE_PATH#");
		$engine->addGreedyPart("#TRASH_PATH#");
		$engine->addGreedyPart("#TRASH_FILE_PATH#");
		$engine->addGreedyPart("#ACTION#");
		$engine->setResolveCallback(array(\Bitrix\Disk\Driver::getInstance()->getUrlManager(), "resolveSocNetPathComponentEngine"));
	}

	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);

	/* This code is needed to use short paths in WebDAV */
	$arUrlTemplates["user_files_short"] = str_replace("#path#", $arDefaultUrlTemplates404["user_files_short"], $arUrlTemplates["user_files"]);
	/* / This code is needed to use short paths in WebDAV */

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

//	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
		$componentPage = $arVariables["page"];

	if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
	{
		//if (strlen($componentPage) <= 0)
		$componentPage = "index";
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

	if (intval($arVariables["user_id"]) <= 0)
		$arVariables["user_id"] = $USER->GetID();

	foreach ($arUrlTemplates as $url => $value)
	{
		$arResult["PATH_TO_".strToUpper($url)] = (
			(substr($value, 0, 1) == "/")
				? $value
				: $arParams["SEF_FOLDER"].$value
		);
	}

	if ($_REQUEST["auth"] == "Y")
	{
		$componentPage = "auth";
	}

	\Bitrix\Socialnetwork\ComponentHelper::setComponentOption(
		array(
			array(
				'CHECK_SEF_FOLDER' => true,
				'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'user_page'),
				'VALUE' => $arParams["SEF_FOLDER"]
			)
		),
		array(
			'SEF_FOLDER' => $arParams["SEF_FOLDER"],
			'SITE_ID' => SITE_ID
		)
	);
}
else
{
	if (is_array($arParams["VARIABLE_ALIASES"]))
	{
		foreach ($arParams["VARIABLE_ALIASES"] as $key => $val)
		{
			$arParams["VARIABLE_ALIASES"][$key] = (!empty($val) ? $val : $key);
		}
	}

	$events = GetModuleEvents("socialnetwork", "OnParseSocNetComponentPath");
	while ($arEvent = $events->Fetch())
	{
		ExecuteModuleEventEx($arEvent, array(&$arDefaultUrlTemplatesN404, &$arCustomPagesPath, $arParams));
	}

	$arVariables = array();
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);

	$events = GetModuleEvents("socialnetwork", "OnInitSocNetComponentVariables");
	while ($arEvent = $events->Fetch())
	{
		ExecuteModuleEventEx($arEvent, array(&$arVariableAliases, &$arCustomPagesPath));
	}

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
				"edit_section", "sessid", "post_id", "category", "topic_id", "result", "MESSAGE_TYPE", "q", "how", "tags", "where",
				"log_id");
		$arParamsKill = array_merge($arParamsKill, $arParams["VARIABLE_ALIASES"], array_values($arVariableAliases));
		$arResult["PATH_TO_".strToUpper($url)] = $GLOBALS["APPLICATION"]->GetCurPageParam($value, $arParamsKill);
	}
	if (array_key_exists($arVariables["page"], $arDefaultUrlTemplatesN404))
	{
		$componentPage = $arVariables["page"];
	}

	if (
		empty($componentPage)
		|| (!array_key_exists($componentPage, $arDefaultUrlTemplatesN404))
	)
	{
		$componentPage = "index";
	}

	if ($_REQUEST["auth"] == "Y")
	{
		$componentPage = "auth";
	}
}

if (
	$arRedirectSite
	&& $arParams["SEF_MODE"] == "Y"
)
{
	if(is_array($arVariables))
	{
		foreach($arVariables as $i => $variable)
		{
			if(!is_string($variable))
			{
				unset($arVariables[$i]);
			}
		}
		unset($variable);
	}

	CSocNetLogComponent::redirectExtranetSite($arRedirectSite, $componentPage, $arVariables, $arDefaultUrlTemplates404, "user");
}

\Bitrix\Socialnetwork\ComponentHelper::setComponentOption(
	array(
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'userbloggroup_id'),
			'VALUE' => $arParams["BLOG_GROUP_ID"]
		),
		array(
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'smile_page'),
			'VALUE' => $arParams["PATH_TO_SMILE"]
		),
		array(
			'CHECK_SEF_FOLDER' => true,
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'friends_page'),
			'VALUE' => (COption::GetOptionString("socialnetwork", "allow_frields", "Y") == "Y" ? $arResult["PATH_TO_USER_FRIENDS"] : false)
		),
		array(
			'CHECK_SEF_FOLDER' => true,
			'OPTION' => array('MODULE_ID' => 'socialnetwork', 'NAME' => 'userblogpost_page'),
			'VALUE' => $arResult["PATH_TO_USER_BLOG_POST"]
		),
		array(
			'CHECK_SEF_FOLDER' => true,
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
		"ALIASES" => $arParams["SEF_MODE"] == "Y"? array(): $arVariableAliases,
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
if (substr($tooltipPathToUser, 0, strlen($arParams["SEF_FOLDER"])) !== $arParams["SEF_FOLDER"])
{
	COption::SetOptionString("main", "TOOLTIP_PATH_TO_USER", $arParams["SEF_FOLDER"]."user/#user_id#/", false, SITE_ID);
}

\Bitrix\Socialnetwork\ComponentHelper::setComponentOption(
	array(
		array(
			'CHECK_SEF_FOLDER' => true,
			'OPTION' => array('MODULE_ID' => 'main', 'NAME' => 'TOOLTIP_PATH_TO_MESSAGES_CHAT'),
			'VALUE' => $arResult["PATH_TO_MESSAGES_CHAT"]
		),
		array(
			'CHECK_SEF_FOLDER' => true,
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
	),
	array(
		'SEF_FOLDER' => $arParams["SEF_FOLDER"],
		'SITE_ID' => SITE_ID
	)
);

$arResult["PATH_TO_SEARCH_INNER"] = (IsModuleInstalled("intranet") ? SITE_DIR."company/structure.php" : $arResult["PATH_TO_SEARCH"]);
$arParams["PATH_TO_SEARCH_EXTERNAL"] = Trim($arParams["PATH_TO_SEARCH_EXTERNAL"]);
if (StrLen($arParams["PATH_TO_SEARCH_EXTERNAL"]) > 0)
{
	$arResult["PATH_TO_SEARCH"] = $arParams["PATH_TO_SEARCH_EXTERNAL"];
}

$arParams["ERROR_MESSAGE"] = "";
$arParams["NOTE_MESSAGE"] = "";
/********************************************************************
				Search Index
********************************************************************/
if(check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] == "PUT")
{
	global $bxSocNetSearch;
	if (
		!is_object($bxSocNetSearch)
		&& CModule::IncludeModule("socialnetwork")
	)
	{
		$bxSocNetSearch = new CSocNetSearch($arResult["VARIABLES"]["user_id"], $arResult["VARIABLES"]["group_id"],
			array(
				"BLOG_GROUP_ID" => $arParams["BLOG_GROUP_ID"],
				"PATH_TO_GROUP_BLOG" => "",
				"PATH_TO_GROUP_BLOG_POST" => "",
				"PATH_TO_GROUP_BLOG_COMMENT" => "",
				"PATH_TO_USER_BLOG" => $arResult["PATH_TO_USER_BLOG"],
				"PATH_TO_USER_BLOG_POST" => $arResult["PATH_TO_USER_BLOG_POST"],
				"PATH_TO_USER_BLOG_COMMENT" => $arResult["PATH_TO_USER_BLOG_POST"]."?commentId=#comment_id##com#comment_id#",

				"FORUM_ID" => $arParams["FORUM_ID"],
				"PATH_TO_GROUP_FORUM_MESSAGE" => "",
				"PATH_TO_USER_FORUM_MESSAGE" => $arResult["PATH_TO_USER_FORUM_MESSAGE"],

				"PHOTO_GROUP_IBLOCK_ID" => false,
				"PATH_TO_GROUP_PHOTO_ELEMENT" => "",
				"PHOTO_USER_IBLOCK_ID" => $arParams["PHOTO_USER_IBLOCK_ID"],
				"PATH_TO_USER_PHOTO_ELEMENT" => $arResult["PATH_TO_USER_PHOTO_ELEMENT"],
				"PHOTO_FORUM_ID" => $arParams["PHOTO_FORUM_ID"],

				"CALENDAR_GROUP_IBLOCK_ID" => false,
				"PATH_TO_GROUP_CALENDAR_ELEMENT" => "",

				"PATH_TO_GROUP_TASK_ELEMENT" => "",
				"PATH_TO_USER_TASK_ELEMENT" => $arResult["PATH_TO_USER_TASKS_TASK"],
				"TASK_FORUM_ID" => $arParams["TASK_FORUM_ID"],

				"FILES_PROPERTY_CODE" => $arParams["NAME_FILE_PROPERTY"],
				"FILES_FORUM_ID" => $arParams["FILES_FORUM_ID"],
				"FILES_GROUP_IBLOCK_ID" => false,
				"PATH_TO_GROUP_FILES_ELEMENT" => "",
				"PATH_TO_GROUP_FILES" => "",
				"FILES_USER_IBLOCK_ID" => $arParams["FILES_USER_IBLOCK_ID"],
				"PATH_TO_USER_FILES_ELEMENT" => $arResult["PATH_TO_USER_FILES_ELEMENT"],
				"PATH_TO_USER_FILES" => $arResult["PATH_TO_USER_FILES"],
				"PATH_TO_WORKFLOW" => SITE_DIR."services/processes/#list_id#/bp_log/#workflow_id#/"
		));
		AddEventHandler("search", "BeforeIndex", Array($bxSocNetSearch, "BeforeIndex"));
		AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array($bxSocNetSearch, "IBlockElementUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockElementDelete", Array($bxSocNetSearch, "IBlockElementDelete"));
		AddEventHandler("iblock", "OnAfterIBlockSectionUpdate", Array($bxSocNetSearch, "IBlockSectionUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockSectionAdd", Array($bxSocNetSearch, "IBlockSectionUpdate"));
		AddEventHandler("iblock", "OnAfterIBlockSectionDelete", Array($bxSocNetSearch, "IBlockSectionDelete"));
	}
}
/********************************************************************
				Disk
********************************************************************/
if(
	$componentPage === 'user_disk' ||
	$componentPage === 'user_disk_file' ||
	$componentPage === 'user_disk_volume' ||
	$componentPage === 'user_trashcan_list' ||
	$componentPage === 'user_trashcan_file_view' ||
	$componentPage === 'user_external_link_list'
)
{
	if(!CSocNetFeatures::isActiveFeature(SONET_ENTITY_USER, $arResult["VARIABLES"]["user_id"], "files"))
	{
		ShowError(GetMessage("SONET_FILES_IS_NOT_ACTIVE"));
		return 0;
	}
}
/********************************************************************
				Disk
********************************************************************/
/********************************************************************
				WebDav
********************************************************************/
if (strPos($componentPage, "user_files") === false && strPos($componentPage, "group_files") === false)
{
	$sCurrUrl = strToLower(str_replace("//", "/", "/".$APPLICATION->GetCurPage()."/"));
	$arBaseUrl = array(
		"user" => $arParams["FILES_USER_BASE_URL"],
		"group" => $arParams["FILES_GROUP_BASE_URL"]);

	if ($arParams["SEF_MODE"] == "Y" )
	{
		$arBaseUrl = array(
			"user" => $arResult["PATH_TO_USER_FILES"],
			"group" => $arResult["PATH_TO_GROUP_FILES"]);
	}
	foreach ($arBaseUrl as $key => $res)
	{
		if (strPos($res, "#path#") !== false)
			$res = subStr($res, 0, strPos($res, "#path#"));
		$res = strToLower(str_replace("//", "/", "/".$res."/"));
		$pos = strPos($res, "#".$key."_id#");
		if ($pos !== false && subStr($res, 0, $pos) == subStr($sCurrUrl, 0, $pos))
		{
			$v1 = subStr($res, $pos + strLen("#".$key."_id#"));
			$v2 = subStr($sCurrUrl, $pos);
			$v3 = subStr($v2, strPos($v2, subStr($v1, 0, 1)), strLen($v1));
			if ($v1 == $v3)
			{
				$componentPage = $key."_files";
				$arResult["VARIABLES"]["#".$key."_id#"] = intVal(subStr($v2, 0, strPos($v2, subStr($v1, 0, 1))));
				$arResult["VARIABLES"][$key."_id"] = intVal(subStr($v2, 0, strPos($v2, subStr($v1, 0, 1))));
			}
		}
	}
}

if (strPos($componentPage, "user_files")!== false || strPos($componentPage, "group_files")!== false)
{
	if (
		$bExtranetEnabled
		&& strPos($componentPage, "user_files") !== false
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
				$GLOBALS["CACHE_MANAGER"]->StartTagCache($path);
				$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_CARD_".intval($arResult["VARIABLES"]["user_id"] / TAGGED_user_card_size));
			}

			$rsIBlock = CIBlock::GetList(array(), array("ACTIVE" => "Y", "CHECK_PERMISSIONS"=>"N", "CODE"=>"user_files%"));

			while($arIBlock = $rsIBlock->Fetch())
			{
				$rsSite = CIBlock::GetSite($arIBlock["ID"]);
				while($arSite = $rsSite->Fetch())
				{
					if (
						$arSite["SITE_ID"] == $extranetSiteId
						&& intval($extranet_iblock_id) <= 0
					)
					{
						$extranet_iblock_id = $arIBlock["ID"];
					}
					elseif (
						$arSite["SITE_ID"] != $extranetSiteId
						&& intval($intranet_iblock_id) <= 0
					)
					{
						$intranet_iblock_id = $arIBlock["ID"];
					}
				}

				if (
					intval($intranet_iblock_id) > 0
					&& intval($extranet_iblock_id) > 0
				)
				{
					break;
				}
			}

			if (
				intval($intranet_iblock_id) > 0
				&& intval($extranet_iblock_id) > 0
			)
			{
				if (CSocNetUser::IsUserModuleAdmin($arResult["VARIABLES"]["user_id"]))
				{
					$bIsUserExtranet = false;
				}
				else
				{
					$rsUser = CUser::GetList(($by="id"), ($order="asc"), array("ID" => $arResult["VARIABLES"]["user_id"]), array("SELECT" => array("UF_DEPARTMENT"), "FIELDS" => array("ID")));
					if ($arUser = $rsUser->Fetch())
					{
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
			if (defined("BX_COMP_MANAGED_CACHE"))
			{
				$GLOBALS["CACHE_MANAGER"]->EndTagCache();
			}
		}
		else
		{
			$arCachedResult = $obCache->GetVars();
		}

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
				$GLOBALS["CACHE_MANAGER"]->StartTagCache($path);
				$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_CARD_".intval($arResult["VARIABLES"]["user_id"] / TAGGED_user_card_size));
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
					&& intval($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"] > 0)
				)
				{
					$arForumTopic = CForumTopic::GetByID($arIBlockElement["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
					$arCachedResult["FILES_FORUM_ID"] = $arForumTopic["FORUM_ID"];
				}
			}

			$obCache->EndDataCache($arCachedResult);
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$GLOBALS["CACHE_MANAGER"]->EndTagCache();
			}
		}
		else
		{
			$arCachedResult = $obCache->GetVars();
		}

		if (
			isset($arCachedResult["FILES_FORUM_ID"])
			&& intval($arCachedResult["FILES_FORUM_ID"]) > 0
		)
		{
			$arParams["FILES_FORUM_ID"]= $arCachedResult["FILES_FORUM_ID"];
		}
	}
}

$path2 = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/webdav_2.php");
if (file_exists($path2))
	include_once($path2);

if (strPos($componentPage, "user_files")!== false || strPos($componentPage, "group_files")!== false)
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/webdav.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "WebDAV file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
	if ($arParams["FATAL_ERROR"] === "Y")
	{
		if (strlen($arParams["NOTE_MESSAGE"]) > 0)
		{
			ShowNote($arParams["NOTE_MESSAGE"]);
		}

		if (strlen($arParams["ERROR_MESSAGE"]) > 0)
		{
			ShowError($arParams["ERROR_MESSAGE"]);
		}

		return 0;
	}
}

/********************************************************************
				/WebDav
********************************************************************/
/********************************************************************
				Photogalley
********************************************************************/
elseif (
	strPos($componentPage, "user_photo") !== false
	|| strPos($componentPage, "group_photo") !== false
)
{
	if (
		strPos($componentPage, "user_photofull") !== false
		|| strPos($componentPage, "group_photofull") !== false
	)
	{
		$componentPage = str_replace("_photofull", "_photo", $componentPage);
	}

	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/photogallery.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Photogallery file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Photogalley
********************************************************************/
/********************************************************************
				Forum
********************************************************************/
elseif (
	strPos($componentPage, "user_forum") !== false
	|| strPos($componentPage, "group_forum") !== false
	|| $componentPage == "user"
	|| $componentPage == "group"
	|| $componentPage == "index"
)
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/forum.php");
	if (!file_exists($path))
	{
		$arParams["ERROR_MESSAGE"] = "Forum file is not exist.";
		$res = 0;
	}
	else
	{
		$res = include_once($path);
	}

	$arParams["FATAL_ERROR"] = ($res <= 0 ? "Y" : "N");
}
/********************************************************************
				/Forum
********************************************************************/
/********************************************************************
				Content Search
********************************************************************/
elseif (
	strPos($componentPage, "user_content_search") !== false
	|| strPos($componentPage, "group_content_search") !== false
)
{
	$path = str_replace(array("\\", "//"), "/", dirname(__FILE__)."/include/search.php");
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
if ($componentPage == "bizproc_task")
{
	$componentPage = "bizproc_edit";
}
elseif ($componentPage == "bizproc_task_list")
{
	$componentPage = "bizproc";
}
/********************************************************************
				/Business-process
********************************************************************/
if (
	!in_array($componentPage, array("message_form_mess", "messages_chat", "messages_users_messages"))
	&& IntVal($arResult["VARIABLES"]["user_id"]) > 0 
	&& $arResult["VARIABLES"]["user_id"] != $USER->GetID()
)
{
	$arContext = array();
	if (
		isset($_REQUEST["entityType"])
		&& strlen($_REQUEST["entityType"]) > 0
	)
	{
		$arContext["ENTITY_TYPE"] = $_REQUEST["entityType"];
	}

	if (
		isset($_REQUEST["entityId"])
		&& intval($_REQUEST["entityId"]) > 0
	)
	{
		$arContext["ENTITY_ID"] = intval($_REQUEST["entityId"]);
	}

	if (
		$componentPage == "user"
		&& !CSocNetUser::IsCurrentUserModuleAdmin()
	)
	{
		\Bitrix\Socialnetwork\ComponentHelper::checkProfileRedirect(intval($arResult["VARIABLES"]["user_id"]));
	}

	if (!CSocNetUser::CanProfileView($USER->GetID(), intval($arResult["VARIABLES"]["user_id"]), SITE_ID, $arContext))
	{
		$bAccessFound = false;
		if ($componentPage == "user_blog_post")
		{
			if (
				isset($arResult["VARIABLES"]["post_id"])
				&& intval($arResult["VARIABLES"]["post_id"]) > 0
			)
			{
				$blogPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();
				$rsLog = CSocNetLog::GetList(
					array(),
					array(
						"EVENT_ID" => $blogPostLivefeedProvider->getEventId(),
						"SOURCE_ID" => intval($arResult["VARIABLES"]["post_id"])
					),
					false,
					false,
					array("ID"),
					array(
						"CHECK_RIGHTS" => "Y"
					)
				);
				if ($arLog = $rsLog->Fetch())
				{
					$bAccessFound = true;
				}
			}
		}
		elseif ($componentPage == "user_tasks_task")
		{
			if (
				isset($arResult["VARIABLES"]["task_id"])
				&& intval($arResult["VARIABLES"]["task_id"]) > 0
				&& \Bitrix\Main\Loader::includeModule('tasks')
			)
			{
				$task = \CTaskItem::getInstance($arResult["VARIABLES"]["task_id"], $USER->getId());
				if ($task->checkCanRead())
				{
					$bAccessFound = true;
				}
			}
		}

		if (!$bAccessFound)
		{
			ShowError(GetMessage("SONET_ACCESS_DENIED"));
			return;
		}
	}
}

//registering routes for building preview
Bitrix\Main\UrlPreview\Router::setRouteHandler(
		$arParams['SEF_FOLDER'].$arUrlTemplates['user_blog_post'],
		'socialnetwork',
		'\Bitrix\Socialnetwork\Ui\Preview\Post',
		array(
				'postId' => '$post_id',
				'userId' => '$user_id',
				'PATH_TO_USER_PROFILE' => $arParams['SEF_FOLDER'].$arUrlTemplates['user'],
		)
);
if(\Bitrix\Main\ModuleManager::isModuleInstalled('tasks'))
{
	Bitrix\Main\UrlPreview\Router::setRouteHandler(
			$arParams['SEF_FOLDER'].$arUrlTemplates['user_tasks_task'],
			'tasks',
			'\Bitrix\Tasks\Ui\Preview\Task',
			array(
					'taskId' => '$task_id',
					'userId' => '$user_id',
					'action' => '$action',
					'PATH_TO_USER_PROFILE' => $arParams['SEF_FOLDER'].$arUrlTemplates['user'],
			)
	);
}

CUtil::InitJSCore(array("window", "ajax"));
\Bitrix\Main\UI\Extension::load("socialnetwork.slider");

$this->IncludeComponentTemplate($componentPage, array_key_exists($componentPage, $arCustomPagesPath) ? $arCustomPagesPath[$componentPage] : "");

//top panel button to reindex
if($GLOBALS['USER']->IsAdmin())
{
	$GLOBALS['APPLICATION']->AddPanelButton(array(
		"HREF"=> $arResult["PATH_TO_USER_REINDEX"],
		"ICON"=>"bx-panel-reindex-icon",
		"ALT"=>GetMessage('SONET_PANEL_REINDEX_TITLE'),
		"TEXT"=>GetMessage('SONET_PANEL_REINDEX'),
		"MAIN_SORT"=>"1000",
		"SORT"=>100
	));
}
?>