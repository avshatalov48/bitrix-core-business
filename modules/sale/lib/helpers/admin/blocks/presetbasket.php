<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Sale\Order;
use Bitrix\Sale\Provider;
use Bitrix\Main\Config\Option;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Helpers\Admin\OrderEdit;

Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

class PresetBasket extends OrderBasket
{

	/**
	 * PresetBasket constructor.
	 */
	public function __construct(Order $order, $jsObjName = "", $idPrefix = "", $createProductBasement = true, $mode = self::EDIT_MODE)
	{
		parent::__construct($order, $jsObjName, $idPrefix, $createProductBasement, $mode);
		$this->createProductBasement = false;
	}

	protected static function getDefaultVisibleColumns()
	{
		return array(
			"IMAGE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_IMAGE"),
			"NAME" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_NAME"),
			"PROPS" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_PROPS"),
			"PRICE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_PRICE"),
		);
	}

	public function getScripts($defTails = false)
	{
		if(!static::$jsInited)
		{
			\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_basket.js");
			\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/discount_preset_basket.js");
			static::$jsInited = true;
		}

		$langPhrases = array("SALE_ORDER_BASKET_TURN", "SALE_ORDER_BASKET_EXPAND", "SALE_ORDER_BASKET_UP_RATIO",
			"SALE_ORDER_BASKET_PROD_EDIT", "SALE_ORDER_BASKET_DOWN_RATIO", "SALE_ORDER_BASKET_PROD_COUNT",
			"SALE_ORDER_BASKET_NO_PICTURE", "SALE_ORDER_BASKET_PROD_CREATE", "SALE_ORDER_BASKET_ROW_SETTINGS",
			"SALE_ORDER_BASKET_PROD_MENU_EDIT", "SALE_ORDER_BASKET_PROD_MENU_DELETE", "SALE_ORDER_BASKET_BASE_CATALOG_PRICE",
			"SALE_ORDER_BASKET_PROD_EDIT_ITEM_SAVE", "SALE_ORDER_BASKET_KG", "SALE_ORDER_BASKET_COUPON",
			"SALE_ORDER_BASKET_COUPON_STATUS", "SALE_ORDER_BASKET_COUPON_APPLY", "SALE_ORDER_BASKET_COUPON_DELETE",
			"SALE_ORDER_BASKET_POSITION_EXISTS", "SALE_ORDER_BASKET_ADD_COUPON_ERROR"
		);
		$result = '<script type="text/javascript">';

		foreach($langPhrases as $phrase)
			$result .= ' BX.message({'.$phrase.': "'.\CUtil::jsEscape(Loc::getMessage($phrase)).'"});';

		if(!$defTails)
			$data = static::prepareData();

		$result .= '
			BX.ready(function(){
				var obParams = {
					tableId: "'.$this->idPrefix.'sale_order_edit_product_table",
					idPrefix: "'.$this->idPrefix.'",
					visibleColumns: '.\CUtil::phpToJSObject($this->visibleColumns).',
					objName: "'.$this->jsObjName.'",
					createProductBasement: '.($this->createProductBasement ? 'true' : 'false').',
					columnsCount: '.count($this->visibleColumns).',
					createBasketBottom: false,
					isShowXmlId: '.($this->isShowXmlId ? 'true' : 'false').',
					mode: "edit",
					unRemovableFields: [],
					formatQuantity: "'.Option::get('sale', 'format_quantity', 'AUTO').'",
					weightUnit: "'.$this->weightUnit.'"
				};';

		if(!$defTails)
		{
			$result .= '
				obParams.productsOrder = '.\CUtil::phpToJSObject($data["ITEMS_ORDER"]).';
				obParams.products = '.\CUtil::phpToJSObject($data["ITEMS"]).';
				obParams.iblocksSkuParams = '.\CUtil::phpToJSObject($data["IBLOCKS_SKU_PARAMS"]).';
				obParams.iblocksSkuParamsOrder = '.\CUtil::phpToJSObject($data["IBLOCKS_SKU_PARAMS_ORDER"]).';
				obParams.productsOffersSkuParams = '.\CUtil::phpToJSObject($data["PRODUCTS_OFFERS_SKU"]).';';
		}

		$result .=
				$this->jsObjName.'= new BX.Sale.Admin.PresetBasket(obParams);
				BX.Sale.Admin.OrderEditPage.registerFieldsUpdaters( '.$this->jsObjName.'.getFieldsUpdaters() );
			});';

		$result .= $this->settingsDialog->getScripts();

		$result .= '</script>';
		return $result;
	}

	public function getEdit($defTails = false)
	{
		$result = '
			<div class="adm-s-gray-title" style="padding-right: 2px;">
				'.Loc::getMessage("SALE_ORDER_BASKET_COMPOSITION").'
				<div class="adm-s-gray-title-btn-container">
					<span
						class="adm-btn adm-btn-green adm-btn-add"
						onClick="'.$this->jsObjName.'.addProductSearch({lang: \''.LANGUAGE_ID.'\', siteId: \''.$this->order->getSiteId().'\', orderId: '.intval($this->order->getId()).'});"
						>'.
							Loc::getMessage("SALE_ORDER_BASKET_PRODUCT_ADD").
					'</span>
				</div>
				<div class="clb"></div>
			</div>';

		$result .= '
			<div class="adm-s-order-table-ddi">
				<table class="adm-s-order-table-ddi-table" style="width: 100%;" id="'.$this->idPrefix.'sale_order_edit_product_table">
					<thead style="text-align: left;">
					<tr>
						<td>
						</td>	';

		foreach($this->visibleColumns as $name)
			$result .= "<td>".htmlspecialcharsbx($name)."</td>";

		$result .= '</tr>
					</thead>';

		if($defTails)
		{
			$result .='
					<tbody style="border: 1px solid rgb(221, 221, 221);" id="'.$this->idPrefix.'sale-adm-order-basket-loading-row">
						<tr>
							<td colspan="'.(count($this->visibleColumns)+1).'" style="padding: 20px;">
								<img src="/bitrix/images/sale/admin-loader.gif"/>
							</td>
						</tr>
					</tbody>';
		}

		$result .='
			<tbody style="border: 1px solid rgb(221, 221, 221);'.($defTails ? ' display:none;' : '').'" id="'.$this->idPrefix.'sale-adm-order-edit-basket-empty-row">
				<tr>
					<td colspan="'.(count($this->visibleColumns)+1).'" style="padding: 20px;">
						'.Loc::getMessage("SALE_ORDER_BASKET_EMPTY_ROW").'.
					</td>
				</tr>
			</tbody>';

		$result .= '
				</table>
			</div>
			<div class="adm-s-gray-title" style="padding-right: 2px;">
				<div class="adm-s-gray-title-btn-container">';

		$result .= '<span
						class="adm-btn adm-btn-green adm-btn-add"
						onClick="'.$this->jsObjName.'.addProductSearch({lang: \''.LANGUAGE_ID.'\', siteId: \''.$this->order->getSiteId().'\', index: 1, orderId: '.intval($this->order->getId()).'});"
						>'.
						Loc::getMessage("SALE_ORDER_BASKET_PRODUCT_ADD").
					'</span>
				</div>
				<div class="clb"></div>
			</div>
			<input type="hidden" name="BASKET[ID_PREFIX]" value="'.$this->idPrefix.'">
			<div class="adm-s-result-container">';

		$result .= '
			</div>
			<div class="clb"></div>';
		return $result;
	}

}