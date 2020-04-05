<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Communities");
?>
<?
$APPLICATION->IncludeComponent("bitrix:socialnetwork.group_search", ".default", array(
	"ITEMS_COUNT" => "32",
	"DATE_TIME_FORMAT" => "F j, Y h:i a",
	"PATH_TO_GROUP" => SITE_DIR."groups/group/#group_id#/",
	"PATH_TO_GROUP_SEARCH" => SITE_DIR."groups/index.php",
	"PATH_TO_GROUP_CREATE" => SITE_DIR."people/user/#user_id#/groups/create/",
	"SET_NAV_CHAIN" => "Y",
	"SET_TITLE" => "Y",
	"PAGE_VAR" => "page",
	"USER_VAR" => "user_id",
	"GROUP_VAR" => "group_id"
	)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>