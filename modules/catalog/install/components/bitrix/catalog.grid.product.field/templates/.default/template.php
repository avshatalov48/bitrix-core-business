<?php
/**
 * @var $component \CatalogProductVariationGridComponent
 * @var $arResult array
 * @var $arParams array
 */

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

Extension::load([
	'catalog.product-selector',
]);

?>
<div class="catalog-product-field" id="<?=$arResult['GUID']?>"></div>
<script>
	BX(function() {
		new BX.Catalog.Grid.ProductField(
			'<?=CUtil::JSEscape($arResult['GUID'])?>',
			{
				componentName: '<?=$component->getName()?>',
				signedParameters: '<?=$component->getSignedParameters()?>',
				productId: '<?=CUtil::JSEscape($arResult['PRODUCT_FIELDS']['PRODUCT_ID'])?>',
				iblockId: '<?=CUtil::JSEscape($arResult['IBLOCK_ID'])?>',
				basePriceId: '<?=CUtil::JSEscape($arResult['BASE_PRICE_ID'])?>',
				skuId: '<?=CUtil::JSEscape($arResult['SKU_ID'])?>',
				fields: <?=CUtil::PhpToJSObject($arResult['PRODUCT_FIELDS'])?>,
				skuTree: <?=Json::encode($arResult['SKU_TREE'])?>,
				config: <?=CUtil::PhpToJSObject($arResult['PRODUCT_CONFIG'])?>,
				fileEmptyInput: <?=CUtil::PhpToJSObject($arResult['IMAGE_EMPTY_HTML'])?>,
				fileInput: <?=CUtil::PhpToJSObject($arResult['IMAGE_HTML'])?>,
				fileInputId: '<?=CUtil::JSEscape($arResult['IMAGE_INPUT_ID'])?>',
				morePhotoValues: <?=CUtil::PhpToJSObject($arResult['IMAGE_VALUES'])?>,
				fileView: <?=CUtil::PhpToJSObject($arResult['FILE_PREVIEW'])?>,
				rowIdMask: '<?=CUtil::JSEscape($arParams['ROW_ID_MASK'])?>',
				mode: '<?=CUtil::JSEscape($arResult['MODE'])?>',
				fileType: '<?=CUtil::JSEscape($arResult['FILE_TYPE'])?>',
				columnName: '<?=CUtil::JSEscape($arResult['COLUMN_NAME'])?>',
				immutableFields: ['NAME'],
				storeMap: [],
			}
		);
	});
</script>
