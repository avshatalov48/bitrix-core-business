<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="rounded-block">
	<div class="corner left-top"></div><div class="corner right-top"></div>
	<div class="block-content">
		<h3>Popular Photos</h3>
<?$APPLICATION->IncludeComponent("bitrix:photogallery.detail.list", "ascetic", array(
	"IBLOCK_TYPE" => "photos",
	"IBLOCK_ID" => "#PHOTO_USER_IBLOCK_ID#",
	"BEHAVIOUR" => "USER",
	"USER_ALIAS" => "",
	"SECTION_ID" => "",
	"ELEMENT_LAST_TYPE" => "none",
	"ELEMENT_SORT_FIELD" => "date_create",
	"ELEMENT_SORT_ORDER" => "desc",
	"ELEMENT_SORT_FIELD1" => "PROPERTY_RATING",
	"ELEMENT_SORT_ORDER1" => "desc",
	"PROPERTY_CODE" => array(),
	"ELEMENT_FILTER" => array(
		"PROPERTY_PUBLIC_ELEMENT" => "Y", 
		">PROPERTY_RATING" => 0),
	"USE_DESC_PAGE" => "N",
	"PAGE_ELEMENTS" => "16",
	"PAGE_NAVIGATION_TEMPLATE" => "",
	"GALLERY_URL" => "#SITE_DIR#people/user/#USER_ID#/photo/",
	"DETAIL_URL" => "#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
	"DETAIL_SLIDE_SHOW_URL" => "#SITE_DIR#people/user/#USER_ID#/photo/photo/#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
	"SEARCH_URL" => "#SITE_DIR#search/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "36000000",
	"SET_TITLE" => "N",
	"USE_PERMISSIONS" => "Y",
	"GET_GALLERY_INFO" => "Y",
	"SHOW_PHOTO_USER" => "N",
	"GROUP_PERMISSIONS" => array(
		0 => "2",
	),
	"DATE_TIME_FORMAT" => "F j, Y h:i a",
	"SET_STATUS_404" => "N",
	"GALLERY_SIZE" => "",
	"ADDITIONAL_SIGHTS" => array(),
	"PICTURES_SIGHT" => "",
	"THUMBS_SIZE" => "65",
	"SHOW_PAGE_NAVIGATION" => "none",
	"SHOW_FORM" => "N",
	"SHOW_CONTROLS" => "N",
	"SHOW_RATING" => "N",
	"SHOW_SHOWS" => "N",
	"SHOW_COMMENTS" => "N",
	"MAX_VOTE" => "5",
	"VOTE_NAMES" => array(
		0 => "1",
		1 => "2",
		2 => "3",
		3 => "4",
		4 => "5",
		5 => "",
	),
	"DISPLAY_AS_RATING" => "vote_avg", 
	),
	false
);?>
	</div>
	<div class="corner left-bottom"></div><div class="corner right-bottom"></div>
</div>

<div class="rounded-block">
	<div class="corner left-top"></div><div class="corner right-top"></div>
	<div class="block-content">
		<div class="search-cloud">
			<noindex>
				<?$APPLICATION->IncludeComponent(
					"bitrix:search.tags.cloud",
					"",
					Array(
						"FONT_MAX" => "25", 
						"FONT_MIN" => "12", 
						"COLOR_NEW" => "8FA4BA", 
						"COLOR_OLD" => "2775C7", 
						"PERIOD_NEW_TAGS" => "", 
						"SHOW_CHAIN" => "N", 
						"COLOR_TYPE" => "Y", 
						"WIDTH" => "100%", 
						"SORT" => "NAME", 
						"PAGE_ELEMENTS" => "150", 
						"PERIOD" => "", 
						"URL_SEARCH" => "#SITE_DIR#search/index.php", 
						"TAGS_INHERIT" => "Y", 
						"CHECK_DATES" => "N", 
						"CACHE_TYPE" => "A", 
						"CACHE_TIME" => "36000000"
					)
				);?>
			</noindex>
		</div>							
	</div>
	<div class="corner left-bottom"></div><div class="corner right-bottom"></div>
</div>