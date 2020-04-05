<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поиск");
?>

<?$APPLICATION->IncludeComponent("bitrix:search.page", "clear", array(
	"RESTART" => "N",
	"CHECK_DATES" => "N",
	"USE_TITLE_RANK" => "N",
	"DEFAULT_SORT" => "rank",
	"FILTER_NAME" => "",
	"arrFILTER" => array(
		0 => "main",
		1 => "forum",
		2 => "iblock_photos",
		3 => "iblock_news",
		4 => "iblock_services",
		5 => "iblock_job",
		6 => "blog",
	),
	"arrFILTER_main" => array(
	),
	"arrFILTER_forum" => array(
		0 => "all",
	),
	"arrFILTER_iblock_photos" => array(
		0 => "all",
	),
	"arrFILTER_iblock_news" => array(
		0 => "all",
	),
	"arrFILTER_iblock_services" => array(
		0 => "all",
	),
	"arrFILTER_iblock_job" => array(
		0 => "all",
	),
	"arrFILTER_blog" => array(
		0 => "all",
	),
	"SHOW_WHERE" => "Y",
	"arrWHERE" => array(
		0 => "iblock_photos",
		1 => "iblock_news",
		2 => "iblock_job",
		3 => "blog",
	),
	"SHOW_WHEN" => "N",
	"PAGE_RESULT_COUNT" => "10",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "Результаты поиска",
	"PAGER_SHOW_ALWAYS" => "N",
	"PAGER_TEMPLATE" => "",
	"USE_SUGGEST" => "N",
	"SHOW_ITEM_TAGS" => "Y",
	"TAGS_INHERIT" => "Y",
	"SHOW_ITEM_DATE_CHANGE" => "Y",
	"SHOW_ORDER_BY" => "Y",
	"SHOW_TAGS_CLOUD" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SHOW_RATING" => "Y",
	"PATH_TO_USER_PROFILE" => "#SITE_DIR#forum/user/#USER_ID#/",
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>