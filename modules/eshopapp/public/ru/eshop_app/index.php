<?
require($_SERVER["DOCUMENT_ROOT"]."#SITE_DIR#eshop_app/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetPageProperty("BodyClass", "main");
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:eshopapp.top",
	"slider",
	Array(
		"IBLOCK_TYPE_ID" => "#CATALOG_IBLOCK_TYPE#",
		"IBLOCK_ID" => "#CATALOG_IBLOCK_ID#",
		"ELEMENT_SORT_FIELD" => "RAND",
		"ELEMENT_SORT_ORDER" => "asc",
		"ELEMENT_COUNT" => "2",
		"FLAG_PROPERTY_CODE" => "SPECIALOFFER",
		"OFFERS_LIMIT" => "5",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id_top",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"CATALOG_FOLDER" => SITE_DIR."eshop_app/catalog/",
		//"SECTION_ID_VARIABLE" => "SECTION_ID",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "180",
		"CACHE_GROUPS" => "Y",
		"DISPLAY_COMPARE" => "N",
		"PRICE_CODE" => array(0=>"BASE",),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"DISPLAY_IMG_WIDTH" => "180",
		"DISPLAY_IMG_HEIGHT" => "180",
		"PRODUCT_PROPERTIES" => array(),
		"BASKET_URL" => SITE_DIR."eshop_app/personal/cart/",
		"CONVERT_CURRENCY" => "N",
		"VARIABLE_ALIASES" => array(
			"SECTION_ID" => "SECTION_ID",
			"ELEMENT_ID" => "ELEMENT_ID",
		)
	)
);?>

<div class="maincontent_component">
	<div class="main_button_component">
		<a href="javascript:void(0)" id="saleleader_title" class = "current" onclick="BX('saleleaders_block').style.display='block'; BX('newproduct_block').style.display='none';BX.removeClass(BX('newproduct_title'), 'current'); BX.addClass(BX(this), 'current');">Хиты продаж<span></span></a>
		<a href="javascript:void(0)" id="newproduct_title" onclick="BX('saleleaders_block').style.display='none'; BX('newproduct_block').style.display='block';  BX.removeClass(BX('saleleader_title'), 'current'); BX.addClass(BX(this), 'current');">Новинки<span></span></a>
		<a href="javascript:void(0)" class="main_button_catalog" onclick="openSectionList();">Каталог<span></span></a>
		<div class="clb"></div>
	</div>
	<div id="saleleaders_block">
	<?$APPLICATION->IncludeComponent("bitrix:eshopapp.top", ".default", array(
	"IBLOCK_TYPE_ID" => "#CATALOG_IBLOCK_TYPE#",
	"IBLOCK_ID" => "#CATALOG_IBLOCK_ID#",
	"ELEMENT_SORT_FIELD" => "sort",
	"ELEMENT_SORT_ORDER" => "asc",
	"ELEMENT_COUNT" => "4",
	"FLAG_PROPERTY_CODE" => "SALELEADER",
	"OFFERS_LIMIT" => "5",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id_top1",
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",
	"PRODUCT_PROPS_VARIABLE" => "prop1",
	"CATALOG_FOLDER" => SITE_DIR."eshop_app/catalog/",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "180",
	"CACHE_GROUPS" => "Y",
	"DISPLAY_COMPARE" => "N",
	"PRICE_CODE" => array(
		0 => "BASE",
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRODUCT_PROPERTIES" => array(
	),
	"CONVERT_CURRENCY" => "N",
	"DISPLAY_IMG_WIDTH" => "130",
	"DISPLAY_IMG_HEIGHT" => "130",
	"BASKET_URL" => SITE_DIR."eshop_app/personal/cart/",
	"SHARPEN" => "30",
	"VARIABLE_ALIASES" => array(
		"SECTION_ID" => "SECTION_ID",
		"ELEMENT_ID" => "ELEMENT_ID",
	)
	),
	false
);?>
	</div>

	<div id="newproduct_block" style="display: none;">
	<?$APPLICATION->IncludeComponent(
		"bitrix:eshopapp.top",
		".default",
		Array(
			"IBLOCK_TYPE_ID" => "#CATALOG_IBLOCK_TYPE#",
			"IBLOCK_ID" => "#CATALOG_IBLOCK_ID#",
			"ELEMENT_SORT_FIELD" => "RAND",
			"ELEMENT_SORT_ORDER" => "asc",
			"ELEMENT_COUNT" => "4",
			"FLAG_PROPERTY_CODE" => "NEWPRODUCT",
			"OFFERS_LIMIT" => "5",
			"ACTION_VARIABLE" => "action",
			"PRODUCT_ID_VARIABLE" => "id_top2",
			"PRODUCT_QUANTITY_VARIABLE" => "quantity",
			"PRODUCT_PROPS_VARIABLE" => "prop",
			"CATALOG_FOLDER" => SITE_DIR."eshop_app/catalog/",
			//"SECTION_ID_VARIABLE" => "SECTION_ID",
			"CACHE_TYPE" => "A",
			"CACHE_TIME" => "180",
			"CACHE_GROUPS" => "Y",
			"DISPLAY_COMPARE" => "N",
			"PRICE_CODE" => array(0=>"BASE",),
			"USE_PRICE_COUNT" => "N",
			"SHOW_PRICE_COUNT" => "1",
			"PRICE_VAT_INCLUDE" => "Y",
			"PRODUCT_PROPERTIES" => array(),
			"CONVERT_CURRENCY" => "N",
			"DISPLAY_IMG_WIDTH" => "130",
			"DISPLAY_IMG_HEIGHT" => "130",
			"BASKET_URL" => SITE_DIR."eshop_app/personal/cart/",
			"SHARPEN" => "30",
			"VARIABLE_ALIASES" => array(
				"SECTION_ID" => "SECTION_ID",
				"ELEMENT_ID" => "ELEMENT_ID",
			)
		)
	);?>
	</div>
</div>

<script type="text/javascript">
	app.setPageTitle({"title" : "<?=htmlspecialcharsbx(COption::GetOptionString("main", "site_name", ""))?>"});
	function openSectionList()
	{
		app.openBXTable({
			url: '<?=SITE_DIR?>eshop_app/catalog/sections.php',
			TABLE_SETTINGS : {
				cache : true,
				use_sections : true,
				searchField : false,
				showtitle : true,
				name : "Каталог",
				button:
				{
					type:    'basket',
					style:   'custom',
					callback: function()
					{
						app.openNewPage("<?=SITE_DIR?>eshop_app/personal/cart/");
					}
				}
			}
		});
	}
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>