<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>
<?if(!empty($arResult["TOP_CATEGORIES_LIST"]) && is_array($arResult["TOP_CATEGORIES_LIST"])):?>
	<div><b><?=Loc::getMessage("SALE_EBAY_SEC_CATEGORY")?></b></div>
	<div>
		<div style="padding-top: 10px;">
			<select id="sale_ebay_category_1" name="sale_ebay_category_1" onchange="BX.Sale.EbayCategories.onCategoryChange(this, 1);">
				<option value=""></option>
				<?foreach($arResult["TOP_CATEGORIES_LIST"] as $categoryId => $categoryName):?>
					<option value="<?=htmlspecialcharsbx($categoryId)?>"<?=$categoryId == $arResult["TOP_CATEGORY_ID"] ? " selected" : ""?>><?=htmlspecialcharsbx($categoryName)?></option>
				<?endforeach;?>
			</select>
		</div>

		<?if(intval($arResult["EBAY_CATEGORY_ID"]) > 0):?>
			<?foreach($arResult["CATEGORY_AND_PARENTS_INFO"] as $categoryLevel => $category):?>
				<?if(!empty($category["CHILDREN"])):?>
					<div style="padding-top: 10px;">
						<select id="sale_ebay_category_<?=$categoryLevel+1?>" name="sale_ebay_category_<?=$categoryLevel+1?>" onchange="BX.Sale.EbayCategories.onCategoryChange(this, <?=$categoryLevel+1?>);">
							<option value=""></option>
							<?foreach($category["CHILDREN"] as $childId => $child):	?>
								<option value="<?=htmlspecialcharsbx($childId)?>"<?=isset($arResult["CATEGORY_AND_PARENTS_INFO"][$categoryLevel+1]["CATEGORY_ID"]) && $arResult["CATEGORY_AND_PARENTS_INFO"][$categoryLevel+1]["CATEGORY_ID"] == $childId ? " selected" : ""?>><?=htmlspecialcharsbx($child["NAME"])?></option>
							<?endforeach;?>
						</select>
					</div>
				<?endif;?>
			<?endforeach;?>
		<?endif;?>
	</div>

	<input id="SALE_EBAY_CATEGORY_ID" name="<?=$arParams["CATEGORY_INPUT_NAME"]?>" type="hidden" value="<?=$arResult["EBAY_CATEGORY_ID"]?>">
	<div style="padding-top: 20px;"><b><?=Loc::getMessage("SALE_EBAY_SEC_PROPERTIES")?></b></div>
	<div id="<?=$arResult["VARIATIONS_BLOCK_ID"]?>" style="display:<?=(strlen($arResult["EBAY_CATEGORY_ID"]) <= 0 || empty($arResult["EBAY_CATEGORY_VARIATIONS"]) ? 'none' : 'block')?>;">
		<?foreach($arResult["VARIATIONS_VALUES"] as $ebayVariationId => $bitrixPropId):?>
			<div>
				<select name="<?=$arParams["EBAY_CATEGORY_VARIATIONS_SN"]?>[]">
					<option value=""></option>
					<?foreach ($arResult["EBAY_CATEGORY_VARIATIONS"] as $varId => $var):?>
						<option value="<?=htmlspecialcharsbx($varId)?>"<?=($ebayVariationId == $varId ? ' selected' : '')?>><?=htmlspecialcharsbx($var["NAME"])?></option>
					<?endforeach;?>
				</select>

				<select name="<?=$arParams["BITRIX_CATEGORY_PROPS_SN"]?>[]">
					<option value=""></option>
					<option value="">------------------</option>
					<option value=""><?=Loc::getMessage("SALE_EBAY_SEC_CATEGORY_PROPS")?></option>
					<option value="">------------------</option>
					<?foreach ($arResult["CATEGORY_PROPS"] as $propId => $prop):?>
						<option value="<?=htmlspecialcharsbx($propId)?>"<?=($bitrixPropId == $propId ? ' selected' : '')?>><?=htmlspecialcharsbx($prop["NAME"])?></option>
					<?endforeach;?>
					<?if(isset($arResult["OFFERS_IBLOCK_ID"])):?>
						<option value="">------------------</option>
						<option value=""><?=Loc::getMessage("SALE_EBAY_SEC_OFFERS_PROPS")?></option>
						<option value="">------------------</option>
						<?foreach ($arResult["CATEGORY_OFFERS_PROPS"] as $propId => $prop):?>
							<option value="<?=htmlspecialcharsbx($propId)?>"<?=($bitrixPropId == $propId ? ' selected' : '')?>><?=htmlspecialcharsbx($prop["NAME"])?></option>
						<?endforeach;?>
					<?endif;?>
				</select>&nbsp;&nbsp;<input type="button" value="<?=Loc::getMessage("SALE_EBAY_SEC_ADD_CATEGORY_PROP")?>" onclick="BX.Sale.EbayCategories.createCategoryProperty(<?=CUtil::PhpToJSObject($arResult["IBLOCK_IDS"])?>, this);" style="margin-top: 10px;">
				<?if(isset($arResult["EBAY_CATEGORY_VARIATIONS"][$ebayVariationId]["REQUIRED"]) && $arResult["EBAY_CATEGORY_VARIATIONS"][$ebayVariationId]["REQUIRED"] == "Y"):?>
					<span style="color: red;"><?=Loc::getMessage("SALE_EBAY_SEC_REQUIRED")?></span>
				<?endif;?>
			</div>
		<?endforeach;?>
		<input type="button" value="<?=Loc::getMessage("SALE_EBAY_SEC_ADD_CATEGORY_VARS")?>" onclick='BX.Sale.EbayCategories.addEmptyVariation();' style="margin-top: 10px;">
	</div>

	<script type="text/javascript">

		BX.message({
			"SALE_EBAY_SEC_REQUIRED": "<?=Loc::getMessage("SALE_EBAY_SEC_REQUIRED")?>",
			"SALE_EBAY_SEC_JS_CREATE_NEW_CATEGORY_PROP": "<?=Loc::getMessage("SALE_EBAY_SEC_JS_CREATE_NEW_CATEGORY_PROP")?>",
			"SALE_EBAY_SEC_JS_CONTINUE": "<?=Loc::getMessage("SALE_EBAY_SEC_JS_CONTINUE")?>",
			"SALE_EBAY_SEC_JS_CANCEL": "<?=Loc::getMessage("SALE_EBAY_SEC_JS_CANCEL")?>",
			"SALE_EBAY_SEC_JS_PROP_KIND": "<?=Loc::getMessage("SALE_EBAY_SEC_JS_PROP_KIND")?>"
		});

		BX.ready(function() {
			BX.Sale.EbayCategories.init({
				ajaxUrl: "<?=$componentPath.'/ajax.php'?>",
				categoriesSelectId: "category_select",
				variationsBlockId: "<?=$arResult["VARIATIONS_BLOCK_ID"]?>",
				ebayVarSelectName: "<?=$arParams["EBAY_CATEGORY_VARIATIONS_SN"]?>",
				bitrixPropsSelectName: "<?=$arParams["BITRIX_CATEGORY_PROPS_SN"]?>",
				bitrixCategoryId: "<?=$arResult["BITRIX_CATEGORY_ID"]?>",
				iBlockId: "<?=$arResult["IBLOCK_ID"]?>",
				siteId: "<?=$arResult["SITE_ID"]?>"
			});

			BX.addCustomEvent('onIblockPropertyAdded', function(params) {
				BX.Sale.EbayCategories.linkPropertyToCategory(<?=$arResult["BITRIX_CATEGORY_ID"]?>, params.propertyId);
			});
		});
	</script>
<?else:?>
	<?=Loc::getMessage(
		'SALE_EBAY_SEC_NO_CATEGORIES',
		array(
			'#A1#' => '<a href="/bitrix/admin/sale_ebay_exchange.php?lang='.LANGUAGE_ID.'&tabControl_active_tab=ebay_meta">',
			'#A2#' => '</a>'
		))?>
<?endif;?>