<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Люди");
?>
<?
$APPLICATION->IncludeComponent("bitrix:socialnetwork.user_search", ".default", array(
	"ITEMS_COUNT" => "32",
	"DATE_TIME_FORMAT" => "d.m.y G:i",
	"PATH_TO_USER" => SITE_DIR."people/user/#user_id#/",
	"PATH_TO_SEARCH" => SITE_DIR."people/",
	"PATH_TO_SEARCH_INNER" => SITE_DIR."people/",
	"PATH_TO_USER_FRIENDS_ADD" => SITE_DIR."people/user/#user_id#/friends/add/",
	"PATH_TO_MESSAGE_FORM" => SITE_DIR."people/messages/form/#user_id#/",
	"PATH_TO_MESSAGES_CHAT" => SITE_DIR."people/messages/chat/#user_id#/",
	"SET_NAV_CHAIN" => "N",
	"SET_TITLE" => "N",
	"SHOW_USERS_WITHOUT_FILTER_SET" => "Y",
	"USER_FIELDS_SEARCH_SIMPLE" => array(
		0 => "PERSONAL_CITY",
	),
	"USER_PROPERTIES_SEARCH_SIMPLE" => array(
	),
	"USER_FIELDS_SEARCH_ADV" => array(
		0 => "PERSONAL_GENDER",
		1 => "PERSONAL_COUNTRY",
		2 => "PERSONAL_CITY",
	),
	"USER_PROPERTIES_SEARCH_ADV" => array(
		0 => "UF_SKYPE",
	),
	"USER_FIELDS_LIST" => array(
		0 => "LAST_LOGIN",
		1 => "DATE_REGISTER",
		2 => "PERSONAL_CITY",
	),
	"USER_PROPERTIES_LIST" => array(
	),
	"USER_FIELDS_SEARCHABLE" => array(
		0 => "LOGIN",
		1 => "NAME",
		2 => "SECOND_NAME",
		3 => "LAST_NAME",
		4 => "PERSONAL_BIRTHDAY",
		5 => "PERSONAL_BIRTHDAY_YEAR",
		6 => "PERSONAL_BIRTHDAY_DAY",
		7 => "PERSONAL_PROFESSION",
		8 => "PERSONAL_GENDER",
		9 => "PERSONAL_COUNTRY",
		10 => "PERSONAL_STATE",
		11 => "PERSONAL_CITY",
		12 => "PERSONAL_ZIP",
		13 => "PERSONAL_STREET",
		14 => "PERSONAL_MAILBOX",
		15 => "WORK_COMPANY",
		16 => "WORK_DEPARTMENT",
		17 => "WORK_POSITION",
		18 => "WORK_COUNTRY",
		19 => "WORK_STATE",
		20 => "WORK_CITY",
		21 => "WORK_ZIP",
		22 => "WORK_STREET",
		23 => "WORK_MAILBOX",
	),
	"USER_PROPERTY_SEARCHABLE" => array(
		0 => "UF_SKYPE",
	),
	"SHOW_YEAR" => "Y",
	"ALLOW_RATING_SORT" => "Y",
	"SHOW_RATING" => "Y",
	"RATING_ID" => "#RATING_ID#",
	"PAGE_VAR" => "",
	"USER_VAR" => ""
	)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
