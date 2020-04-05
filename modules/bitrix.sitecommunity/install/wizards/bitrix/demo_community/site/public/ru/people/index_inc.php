<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="rounded-block">
	<div class="corner left-top"></div><div class="corner right-top"></div>
	<div class="block-content">
		<h3>Быстрый поиск</h3>
<?
$APPLICATION->IncludeComponent("bitrix:socialnetwork.user_search", "sidebar", array(
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
	),
	"USER_PROPERTIES_LIST" => array(
	),
	"USER_FIELDS_SEARCHABLE" => array(
	),
	"USER_PROPERTY_SEARCHABLE" => array(
	),
	"SHOW_YEAR" => "Y",
	"PAGE_VAR" => "page",
	"USER_VAR" => "user_id"
	)
);
?>
	</div>
	<div class="corner left-bottom"></div><div class="corner right-bottom"></div>
</div>