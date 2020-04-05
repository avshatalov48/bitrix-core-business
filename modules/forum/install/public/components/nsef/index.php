<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:forum",
	"",
	Array(
		"SEF_MODE" => "N", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "7200", 
		"DATE_FORMAT" => "d.m.Y", 
		"DATE_TIME_FORMAT" => "d.m.Y H:i:s", 
		"SET_TITLE" => "Y", 
		"SET_NAVIGATION" => "Y", 
		"SHOW_FORUM_ANOTHER_SITE" => "N", 
		"FORUMS_PER_PAGE" => "20", 
		"TOPICS_PER_PAGE" => "20", 
		"MESSAGES_PER_PAGE" => "25", 
		"PATH_TO_AUTH_FORM" => "" 
	)
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>