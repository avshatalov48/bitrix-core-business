<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:blog",
	"",
	Array(
		"SEF_MODE" => "N", 
		"PATH_TO_SMILE" => "/bitrix/images/blog/smile/", 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "600", 
		"SET_TITLE" => "Y", 
		"CACHE_TIME_LONG" => "604800", 
		"SET_NAV_CHAIN" => "Y", 
		"MESSAGE_COUNT" => "20", 
		"BLOG_COUNT" => "20", 
		"NOT_USE_COMMENT_TITLE" => "Y",
		"VARIABLE_ALIASES" => Array(
			"blog" => "blog",
			"post_id" => "id",
			"user_id" => "id",
			"page" => "page",
			"group_id" => "id"
		)
	)
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>