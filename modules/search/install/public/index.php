<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("SEARCH_INSTALL_PUBLIC_TITLE"));?>

<?$APPLICATION->IncludeComponent("bitrix:search.page", "tags", Array(
	"RESTART"	=>	"N",
	"CHECK_DATES"	=>	"N",
	"arrWHERE"	=>	array(
		0	=>	"forum",
		1	=>	"iblock_news",
		2	=>	"iblock_articles",
		3	=>	"iblock_books",
		4	=>	"blog",
	),
	"arrFILTER"	=>	array(
		0	=>	"no",
	),
	"SHOW_WHERE"	=>	"Y",
	"PAGE_RESULT_COUNT"	=>	"10",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"TAGS_SORT"	=>	"NAME",
	"TAGS_PAGE_ELEMENTS"	=>	"20",
	"TAGS_PERIOD"	=>	"",
	"TAGS_URL_SEARCH"	=>	"",
	"TAGS_INHERIT"	=>	"Y",
	"FONT_MAX"	=>	"50",
	"FONT_MIN"	=>	"10",
	"COLOR_NEW"	=>	"000000",
	"COLOR_OLD"	=>	"C8C8C8",
	"PERIOD_NEW_TAGS"	=>	"",
	"SHOW_CHAIN"	=>	"Y",
	"COLOR_TYPE"	=>	"Y",
	"WIDTH"	=>	"100%"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>