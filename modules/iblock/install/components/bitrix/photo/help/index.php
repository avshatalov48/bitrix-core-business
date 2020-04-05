<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
// Put this file into some empty folder
// and run "URLs processing" -> "Re-creation" ( /bitrix/admin/urlrewrite_reindex.php )
// Open http://<your site>/max/images/ in browser
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photo",
	"",
	Array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/max/images/",
		"SEF_URL_TEMPLATES" => Array(
			"sections_top" => "index.php",
			"section" => "#SECTION_ID#/",
			"detail" => "#SECTION_ID#/#ELEMENT_ID#.php",
		),
		"VARIABLE_ALIASES" => Array(
			"sections_top"=>array(),
			"section"=>array(),
			"detail"=>array("ELEMENT_CODE"=>"IMG"),
		),
//Common parameters
		"IBLOCK_TYPE" => "photo",
		"IBLOCK_ID" => "8",
		"DISPLAY_PANEL" => "N",
		"SET_TITLE" => "Y",
		"CACHE_TIME" => "0",
		"CACHE_FILTER" => "N",
		"FILTER_NAME" => "arrFilter",
		"ELEMENT_SORT_FIELD" => "RATING",
		"ELEMENT_SORT_ORDER" => "desc",
//Sections top specific
		"SECTION_SORT_FIELD" => "sort",
		"SECTION_SORT_ORDER" => "asc",
		"SECTION_COUNT" => "20",
		"TOP_ELEMENT_COUNT" => "9",
		"TOP_LINE_ELEMENT_COUNT" => "3",
//Section specific
		"SECTION_PAGE_ELEMENT_COUNT" => "4",
		"SECTION_LINE_ELEMENT_COUNT" => "2",
//Detail specific
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
