<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("IBLOCK_INSTALL_PUBLIC_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:news", ".default", Array(
	"SEF_MODE"	=>	"N",
	"IBLOCK_TYPE"	=>	"news",
	"IBLOCK_ID"	=>	GetMessage("IBLOCK_INSTALL_PUBLIC_IBLOCK_ID"),
	"NEWS_COUNT"	=>	"5",
	"USE_SEARCH"	=>	"N",
	"USE_RSS"	=>	"Y",
	"USE_RATING"	=>	"N",
	"USE_CATEGORIES"	=>	"Y",
	"USE_REVIEW"	=>	"N",
	"USE_FILTER"	=>	"N",
	"SORT_BY1"	=>	"ACTIVE_FROM",
	"SORT_ORDER1"	=>	"DESC",
	"SORT_BY2"	=>	"SORT",
	"SORT_ORDER2"	=>	"ASC",
	"PREVIEW_TRUNCATE_LEN"	=>	"0",
	"LIST_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"LIST_FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"LIST_PROPERTY_CODE"	=>	array(
		0	=>	"",
	),
	"META_KEYWORDS"	=>	"KEYWORDS",
	"META_DESCRIPTION"	=>	"DESCRIPTION",
	"DETAIL_ACTIVE_DATE_FORMAT"	=>	"d.m.Y",
	"DETAIL_FIELD_CODE"	=>	array(
		0	=>	"",
	),
	"DETAIL_PROPERTY_CODE"	=>	array(
		0	=>	"SOURCE",
	),
	"DETAIL_DISPLAY_TOP_PAGER"	=>	"N",
	"DETAIL_DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"DETAIL_PAGER_TITLE"	=>	GetMessage("IBLOCK_INSTALL_PUBLIC_DETAIL_PAGER"),
	"DISPLAY_PANEL"	=>	"Y",
	"SET_TITLE"	=>	"Y",
	"INCLUDE_IBLOCK_INTO_CHAIN"	=>	"N",
	"USE_PERMISSIONS"	=>	"N",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"3600",
	"CACHE_FILTER"	=>	"N",
	"DISPLAY_TOP_PAGER"	=>	"N",
	"DISPLAY_BOTTOM_PAGER"	=>	"Y",
	"PAGER_TITLE"	=>	GetMessage("IBLOCK_INSTALL_PUBLIC_PAGER"),
	"PAGER_SHOW_ALWAYS"	=>	"N",
	"PAGER_DESC_NUMBERING"	=>	"N",
	"PAGER_DESC_NUMBERING_CACHE_TIME"	=>	"36000",
	"NUM_NEWS"	=>	"20",
	"NUM_DAYS"	=>	"360",
	"YANDEX"	=>	"N",
	"VARIABLE_ALIASES"	=>	array(
		"SECTION_ID"	=>	"SECTION_ID",
		"ELEMENT_ID"	=>	"news",
	)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>