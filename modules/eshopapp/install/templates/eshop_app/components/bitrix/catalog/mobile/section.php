<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->SetPageProperty("BodyClass", "itemlist search");?>
<div class="item_list_top_container">
	<a href="javascript:void(0)" class="item_list_filter_button" onclick="showFilter()"></a>
	<a href="javascript:void(0)" class="item_list_barcode" onclick="makeBarCode();"></a>
	<div class="item_list_search_component">
		<?$APPLICATION->IncludeComponent("bitrix:search.title", "mobile", array(
			"NUM_CATEGORIES" => "1",
			"TOP_COUNT" => "5",
			"CHECK_DATES" => "N",
			"SHOW_OTHERS" => "N",
			"PAGE" => SITE_DIR."eshop_app/catalog/",
			"CATEGORY_0_TITLE" => GetMessage("SEARCH_GOODS") ,
			"CATEGORY_0" => array(
				0 => "iblock_catalog",
			),
			"CATEGORY_0_iblock_catalog" => array(
				0 => "all",
			),
			"CATEGORY_OTHERS_TITLE" => GetMessage("SEARCH_OTHER"),
			"SHOW_INPUT" => "Y",
			"INPUT_ID" => "title-search-input",
			"CONTAINER_ID" => "search"
		),
		false
	);?>
	</div>
	<div class="clb"></div>
</div>
<?
if (CModule::IncludeModule("iblock"))
{
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	);
	if(strlen($arResult["VARIABLES"]["SECTION_CODE"])>0)
	{
		$arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];
	}
	elseif($arResult["VARIABLES"]["SECTION_ID"]>0)
	{
		$arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
	}

	$obCache = new CPHPCache;
	if($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog"))
	{
		$arCurSection = $obCache->GetVars();
	}
	else
	{
		$arCurSection = array();
		$dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));
		$dbRes = new CIBlockResult($dbRes);

		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache("/iblock/catalog");

			if ($arCurSection = $dbRes->GetNext())
			{
				$CACHE_MANAGER->RegisterTag("iblock_id_".$arParams["IBLOCK_ID"]);
			}
			$CACHE_MANAGER->EndTagCache();
		}
		else
		{
			if(!$arCurSection = $dbRes->GetNext())
				$arCurSection = array();
		}

		$obCache->EndDataCache($arCurSection);
	}
}

?>
<div id="smart_filter" style="visibility:hidden">
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.smart.filter",
	"mobile",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arCurSection["ID"],
		"FILTER_NAME" => "arrFilter",
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_NOTES" => "",
		"CACHE_GROUPS" => "Y",
		"SAVE_IN_SESSION" => "N"
	),
	false
);?>
</div>
<div id="section_info">
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
		"TOP_DEPTH" => 1,//$arParams["SECTION_TOP_DEPTH"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
	),
	$component
);?>
<?/*
<?if($arParams["USE_COMPARE"]=="Y"):?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.compare.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"NAME" => $arParams["COMPARE_NAME"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		"COMPARE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["compare"],
	),
	$component
);?>
<br />
<?endif*/?>
<?
$detail_url = $arResult["URL_TEMPLATES"]["element"];
$detail_url = str_replace("#".$arResult["ALIASES"]["SECTION_ID"]."#", $arCurSection["ID"], $detail_url);
$element_alias = "#".$arResult["ALIASES"]["ELEMENT_ID"]."#";

if ($_REQUEST["ajax_get_page"] == "Y")
{
	$APPLICATION->RestartBuffer();
}
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
		"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
		"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
		"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
		"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
		"FILTER_NAME" => $arParams["FILTER_NAME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_FILTER" => $arParams["CACHE_FILTER"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
		"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
		"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],

		"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
		"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
		"PAGER_TITLE" => $arParams["PAGER_TITLE"],
		"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
		"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
		"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
		"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
		"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],

		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		"OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
		"OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
		"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
		"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
		"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
		'CURRENCY_ID' => $arParams['CURRENCY_ID'],
	),
	$component
);
?>
</div>

<script>
	function showFilter()
	{
		if (BX('smart_filter').style.display=='none')
		{
			BX('smart_filter').style.display='block';
			BX('section_info').style.display='none';
		}
		else
		{
			BX('smart_filter').style.display='none';
			BX('section_info').style.display='block';
		}
	}
	BX('smart_filter').style.display = "none";
	BX('smart_filter').style.visibility = "visible";

	function makeBarCode()
	{
		var detail_url = "<?=$detail_url?>";
		app.openBarCodeScanner({
			callback:function(data)
			{
				if (data.text)
				{
					app.showPopupLoader({test:''});
					BX.ajax({
						timeout:   30,
						method:   'POST',
						url:'<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
						data:
						{
							eshopapp_action : "barcode",
							barcode : data.text
						},
						processData: false,
						onsuccess: function(reply){
							json = JSON.parse(reply);
							if (json.product_id)
							{
								product_url = detail_url.replace("<?=$element_alias?>", json.product_id);
								app.openNewPage(product_url);
							}
							else if (json.error)
							{
								if (json.error == "empty")
									app.alert(
										{
											text : "<?=GetMessage("MD_BARCODE_EMPTY")?>",
											title : "<?=GetMessage("MB_ALERT_TITLE")?>",
											button:"OK"

										}
									);
							}
						}
					});
				}
			//handle data (example of the data  - {type:"SSD", canceled:0, text:"8293473200"})
			}
		});
	}

	app.pullDown({
		enable:true,
		callback:function(){document.location.reload();},
		downtext:"<?=GetMessage("MB_PULLDOWN_DOWN")?>",
		pulltext:"<?=GetMessage("MB_PULLDOWN_PULL")?>",
		loadtext:"<?=GetMessage("MB_PULLDOWN_LOADING")?>"
	});
</script>