<?php

namespace Bitrix\Sale\Helpers\Admin\Blocks;

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;
use Bitrix\Sale\Shipment;
use Bitrix\Sale\UserMessageException;
use Bitrix\Main\Entity\EntityError;
use Bitrix\Sale\Configuration;

Loc::loadMessages(__FILE__);

require_once __DIR__.'/../../../../general/admin_tool.php';

class OrderBasketShipment extends OrderBasket
{
	protected $shipment = null;
	protected static $useStoreControl = null;
	protected $systemJsObjName = 'BX.Sale.Admin.SystemShipmentBasketObj';

	/**
	 * @param \Bitrix\Sale\Shipment $shipment
	 * @param string $jsObjName
	 * @param string $idPrefix
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function __construct(Shipment $shipment, $jsObjName = "", $idPrefix = "")
	{
		self::$useStoreControl = Configuration::useStoreControl();
		$order = $shipment->getCollection()->getOrder();
		$this->visibleColumns = $this->getVisibleColumns('sale_shipment_basket');
		parent::__construct($order, $jsObjName, $idPrefix);

		$this->shipment = $shipment;
		$this->data = array();
		$this->mode = self::VIEW_MODE;
	}

	public function getEdit($defTails = false)
	{
		$result = '
			<script>
				function searchProductByBarcode(_this)
				{
					event = window.event;
					if(event.keyCode == 13)
					{
						'.$this->jsObjName.'.checkProductByBarcode(_this.nextElementSibling);
						event.preventDefault();
					}
				}
			</script>
			<div class="adm-s-gray-title" style="padding-right: 2px;">
				'.Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_COMPOSITION").'
				<div class="adm-s-gray-title-btn-container">
					<span
						class="adm-btn adm-btn-green adm-btn-add"
						onClick="'.$this->systemJsObjName.'.addProductSearch();"
						>'.
							Loc::getMessage("SALE_ORDER_BASKET_PRODUCT_ADD").
					'</span>
				</div>
				<div class="adm-s-gray-title-btn-container" style="margin-right: 25px;">
					<span class="adm-bus-order-find-by-barcode" onclick="BX.Sale.Admin.GeneralShipment.findProductByBarcode(this);">'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_FIND_BY_BARCODE').'</span>
				</div>
				<div class="adm-s-gray-title-btn-container" style="margin-right: 25px; display: none;">
					<input type="text" style="width: 150px;" onkeypress="searchProductByBarcode(this);">
					<span class="adm-btn" onclick="'.$this->jsObjName.'.checkProductByBarcode(this);">'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_FIND').'</span>
				</div>
				<div class="clb"></div>
			</div>';

		$result .= '
			<div class="adm-s-order-table-ddi">
				<table class="adm-s-order-table-ddi-table" style="width: 100%;" id="'.$this->idPrefix.'sale_order_edit_product_table">
					<thead>
					<tr>
						<td class="adm-s-order-table-context-menu-column">
							<span class="adm-s-order-table-title-icon"
								title="'.Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_BUTTON_TITLE").'"
								onclick="'.$this->jsObjName.'.onHeadMenu(this);"
								>
							</span>
						</td>
						<td></td>';

		foreach($this->visibleColumns as $id => $name)
			if ($id == 'STORE')
				$result .= "<td style='width: 200px'>".$name."</td>";
			else
				$result .= "<td>".$name."</td>";

		$result .= '</tr>
					</thead>
				</table>
			</div>';

		$result .= '<div class="adm-list-table-footer" id="b_sale_order_shipment_footer" style="margin-top: -5px; padding-top: 10px;">
			<span class="adm-selectall-wrap" style="margin-top: 5px; font-weight: bold;">'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_GOODS_ALL').': </span><span class="adm-selectall-wrap" style="margin-top: 5px; font-weight: bold;" id="'.$this->idPrefix.'_count">0</span>
			<span class="adm-selectall-wrap" style="margin-top: 5px; font-weight: bold;">'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_GOODS_SELECTED').': </span><span class="adm-selectall-wrap" style="margin-top: 5px; font-weight: bold;" id="'.$this->idPrefix.'_selected_count">0</span>
			<span class="adm-selectall-wrap" style="margin-top: 5px; font-weight: bold;">'.str_replace(array('#CURRENT_PAGE#', '#COUNT_PAGE#'), array('1', '1'),Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_PAGE')).'</span>
		</div>';

		$result .= '<div class="adm-list-table-footer" id="b_sale_order_shipment_footer">
			<span class="adm-selectall-wrap">
				<input type="checkbox" class="adm-checkbox adm-designed-checkbox" name="action_target" value="selected" id="action_target" '.($this->shipment->isShipped() ? 'disabled' : '').'>
				<label for="action_target" class="adm-checkbox adm-designed-checkbox-label"></label>
				<label title="'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_APPLY').'" for="action_target" class="adm-checkbox-label">'.Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_APPLY_FOR_ALL').'</label>
			</span>

			<span class="adm-table-item-edit-wrap adm-table-item-edit-single">
				<a href="javascript:void(0);" class="adm-table-btn-delete adm-edit-disable" hidefocus="true" title="" id="action_delete_button" onclick="'.$this->jsObjName.'.groupMoveToSystemBasket(this);"></a>
			</span>
		</div>';

		return $result;
	}

	public function getView($index = 0)
	{
		$exceptionFields = array('QUANTITY', 'REMAINING_QUANTITY');
		foreach ($exceptionFields as $field)
		{
			if (array_key_exists($field, $this->visibleColumns))
				unset($this->visibleColumns[$field]);
		}

		$result = '
				<div class="adm-s-order-table-ddi">
					<table class="adm-s-order-table-ddi-table" style="width: 100%;" id="'.$this->idPrefix.'_'.$index.'">
						<thead>
						<tr>';

		foreach ($this->visibleColumns as $id => $name)
		{
			if ($id == 'STORE')
				$result .= "<td style='width: 200px'>".$name."</td>";
			else
				$result .= "<td>".$name."</td>";
		}
		$result .= '</tr>
					</thead>
				</table>
			</div>';

		$result .= $this->getViewScript($index, $this->visibleColumns);
		return $result;
	}

	protected static function getDefaultVisibleColumns()
	{
		$columnName = array(
			"NUMBER" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_NUMBER"),
			"IMAGE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_IMAGE"),
			"NAME" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_NAME"),
			"PROPS" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_PROPS"),
			"QUANTITY" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_QUANTITY"),
			"AMOUNT" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_AMOUNT")
		);

		if (self::$useStoreControl)
		{
			$columnName = array_merge(
				$columnName,
				array(
					"STORE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_STORE"),
					"CUR_AMOUNT" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_CUR_AMOUNT"),
					"REMAINING_QUANTITY" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_REMAINING_QUANTITY")
				)
			);
		}

		$columnName = array_merge(
			$columnName,
			array(
				"BARCODE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_BARCODE"),
			)
		);

		return $columnName;
	}

	/**
	 * @return array
	 */
	protected static function getDefaultUnShippedVisibleColumns()
	{
		return array(
			"NUMBER" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_NUMBER"),
			"IMAGE" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_IMAGE"),
			"NAME" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_NAME"),
			"PROPS" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_PROPS"),
			"AMOUNT" => Loc::getMessage("SALE_ORDER_BASKET_SETTINGS_COL_QUANTITY")
		);
	}

	/**
	 * @param array $inParams
	 * @return array|null
	 */
	public function prepareData(array $inParams = array())
	{
		$result = null;
		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $this->shipment->getCollection();
		$shipmentItemCollection = $this->shipment->getShipmentItemCollection();
		$result = $this->getProductsInfo($shipmentItemCollection);
		$result = $this->getOffersSkuParams($result);
		$result['UNSHIPPED_PRODUCTS'] = array();

		if ($this->shipment->getId() > 0)
		{
			/** @var \Bitrix\Sale\Shipment $systemShipment */
			$systemShipment = $shipmentCollection->getSystemShipment();
			$systemItemsCollection = $systemShipment->getShipmentItemCollection();

			$systemCollectionProduct = $this->getProductsInfo($systemItemsCollection);
			$systemCollectionProduct = $this->getOffersSkuParams($systemCollectionProduct);
			$result['UNSHIPPED_PRODUCTS'] = array_diff_key($systemCollectionProduct['ITEMS'], $result['ITEMS']);
		}

		return $result;
	}

	/**
	 * @param \Bitrix\Sale\ShipmentItemCollection $shipmentItemCollection
	 * @return array
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getProductsInfo($shipmentItemCollection)
	{
		/** @var \Bitrix\Sale\Shipment $shipment */
		$shipment = $shipmentItemCollection->getShipment();
		$systemShipmentItemCollection = null;
		$systemShipmentItem = null;

		$result = array(
			"ITEMS" => array()
		);

		if (!$shipment->isSystem())
		{
			/** @var \Bitrix\Sale\Shipment $systemShipment */
			$systemShipment = $shipment->getCollection()->getSystemShipment();
			$systemShipmentItemCollection = $systemShipment->getShipmentItemCollection();
		}

		$items = array();

		$catalogProductsIds = array();

		/** @var \Bitrix\Sale\ShipmentItem $item */
		foreach($shipmentItemCollection as $item)
		{
			$basketItem = $item->getBasketItem();

			if ($basketItem && $basketItem->getField("MODULE") == "catalog")
				$catalogProductsIds[] = $basketItem->getProductId();
		}

		if(!empty($catalogProductsIds))
			$catalogProductsFields = self::getProductsData($catalogProductsIds, $this->order->getSiteId(), $this->visibleColumns, $this->order->getUserId());

		/** @var \Bitrix\Sale\ShipmentItem $item */
		foreach($shipmentItemCollection as $item)
		{
			$params = array();

			$basketItem = $item->getBasketItem();
			if ($basketItem)
			{
				$systemShipmentItem = null;
				if ($systemShipmentItemCollection)
				{
					/** @var \Bitrix\Sale\ShipmentItemCollection $systemShipmentItemCollection */
					$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketCode($basketItem->getBasketCode());
				}

				$productId = $basketItem->getProductId();

				if ($basketItem->getField("MODULE") === "catalog" && !empty($catalogProductsFields[$productId]))
				{
					$params = $catalogProductsFields[$productId];
				}

				if (intval($basketItem->getField("MEASURE_CODE")) > 0)
				{
					$params["MEASURE_CODE"] = intval($basketItem->getField("MEASURE_CODE"));
				}
				elseif (!isset($params["MEASURE_CODE"]))
				{
					$params["MEASURE_CODE"] = 0;
				}

				if (!empty($basketItem->getField("MEASURE_NAME")))
				{
					$params["MEASURE_TEXT"] = $basketItem->getField("MEASURE_NAME");
				}
				elseif(!isset($params["MEASURE_TEXT"]))
				{
					$params["MEASURE_TEXT"] = "";
				}

				if ($basketItem->isBundleParent())
				{
					$params["BASE_ELEMENTS_QUANTITY"] = $basketItem->getBundleBaseQuantity();
					if (!isset($params['IS_SET_ITEM']))
						$params['IS_SET_ITEM'] = 'N';
					if (!isset($params['IS_SET_PARENT']))
						$params['IS_SET_PARENT'] = 'Y';
					if (!isset($params['OLD_PARENT_ID']))
						$params['OLD_PARENT_ID'] = '';
				}
				$params["BASKET_ID"] = $basketItem->getId();
				$params["PRODUCT_PROVIDER_CLASS"] = $basketItem->getProvider();
				$params["NAME"] = $basketItem->getField("NAME");
				$params["MODULE"] = $basketItem->getField("MODULE");
				$params['TYPE'] = (int)$basketItem->getField('TYPE');
				$params['BARCODE_INFO'] = [];

				$itemStoreCollection = $item->getShipmentItemStoreCollection();
				if ($itemStoreCollection)
				{
					/** @var \Bitrix\Sale\ShipmentItemStore $barcode */
					foreach ($itemStoreCollection as $barcode)
					{
						$storeId = $barcode->getStoreId();
						if (!isset($params['BARCODE_INFO'][$storeId]))
						{
							$params['BARCODE_INFO'][$storeId] = [];
						}

						$params['BARCODE_INFO'][$storeId][] = [
							'ID' => $barcode->getId(),
							'BARCODE' => $barcode->getBarcode(),
							'MARKING_CODE' => $barcode->getMarkingCode(),
							'QUANTITY' => $barcode->getQuantity(),
						];
					}
				}

				if (!$shipment->isSystem())
					$params['ORDER_DELIVERY_BASKET_ID'] = $item->getId();

				$systemItemQuantity = ($systemShipmentItem) ? $systemShipmentItem->getQuantity() : 0;
				$params["QUANTITY"] = floatval($item->getQuantity() + $systemItemQuantity);
				$params["AMOUNT"] = floatval($item->getQuantity());
				$params["PRICE"] = $basketItem->getPrice();
				$params["CURRENCY"] = $basketItem->getCurrency();
				$params["PROPS"] = array();

				/** @var \Bitrix\Sale\BasketPropertyItem $property */
				foreach($basketItem->getPropertyCollection() as $property)
				{
					$params["PROPS"][] = array(
						"VALUE" => $property->getField("VALUE"),
						"NAME" => $property->getField("NAME"),
						"CODE" => $property->getField("CODE"),
						"SORT" => $property->getField("SORT")
					);
				}

				if(\Bitrix\Main\Loader::includeModule("catalog"))
				{
					$productInfo = \CCatalogSku::GetProductInfo($productId);
					if ($productInfo)
					{
						$params["OFFERS_IBLOCK_ID"] = $productInfo["OFFER_IBLOCK_ID"];
						$params["IBLOCK_ID"] = $productInfo["IBLOCK_ID"];
						$params["PRODUCT_ID"] = $productInfo["ID"];
					}
				}

				$params["PARENT_BASKET_ID"] = $basketItem->getParentBasketItemId() ?? 0;

				//If product became bundle, but in saved order it is a simple product.
				if ($basketItem->getBasketCode() == intval($basketItem->getBasketCode()) && !$basketItem->isBundleParent() && !empty($params['SET_ITEMS']))
				{
					unset($params['SET_ITEMS'], $params['OLD_PARENT_ID']);
					$params['IS_SET_PARENT'] = 'N';
				}

				$params['IS_SUPPORTED_MARKING_CODE'] = $basketItem->isSupportedMarkingCode() ? 'Y' : 'N';
			}
			else
			{
				$systemShipmentItem = null;
				if ($systemShipmentItemCollection)
				{
					/** @var \Bitrix\Sale\ShipmentItemCollection $systemShipmentItemCollection */
					$systemShipmentItem = $systemShipmentItemCollection->getItemByBasketId($item->getBasketId());
				}

				$systemItemQuantity = ($systemShipmentItem) ? $systemShipmentItem->getQuantity() : 0;
				$params = array(
					'NAME' => Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_WRONG_BASKET_ITEM"),
					'QUANTITY' => floatval($item->getQuantity() + $systemItemQuantity),
					'AMOUNT' => floatval($item->getQuantity()),
					'BASKET_ID' => $item->getBasketId(),
				);
			}

			$items[$params['BASKET_ID']] = $params;
		}

		$bundleChild = array();
		foreach ($items as $basketId => $item)
		{
			$parentBasketId = $item['PARENT_BASKET_ID'];
			if ($parentBasketId > 0)
			{
				$item['IS_SET_ITEM'] = 'Y';
				$item['OLD_PARENT_ID'] = $items[$parentBasketId]['OLD_PARENT_ID'];
				$bundleChild[$parentBasketId][] = $item;
				unset($items[$basketId]);
			}
		}

		foreach ($items as $basketId => $item)
		{
			if (isset($bundleChild[$basketId]))
				$items[$basketId]['SET_ITEMS'] = $bundleChild[$basketId];
		}

		$result['ITEMS'] = $items;

		return $result;
	}

	/**
	 * @param $data
	 * @param $request
	 * @return mixed
	 */
	public function modifyFromRequest($data, $request)
	{
		// recovery on delete
		foreach ($data['ITEMS'] as $code => $item)
		{
			$basketCode = $item['BASKET_ID'];

			if (!isset($request[$basketCode]))
			{
				$data['UNSHIPPED_PRODUCTS'][$code] = $data['ITEMS'][$code];
				unset($data['ITEMS'][$code]);
			}
		}

		// recovery on add
		foreach ($data['UNSHIPPED_PRODUCTS'] as $code => $item)
		{
			$basketCode = $item['BASKET_ID'];

			if (isset($request[$basketCode]))
			{
				$data['ITEMS'][$code] = $data['UNSHIPPED_PRODUCTS'][$code];
				unset($data['UNSHIPPED_PRODUCTS'][$code]);
			}
		}

		// recovery barcode info
		if ($request)
		{
			foreach ($request as $basketCode => $product)
			{
				$basket = $this->order->getBasket();
				/** @var \Bitrix\Sale\BasketItem $basketItem */
				$basketItem = $basket->getItemById($product['BASKET_ID']);
				if ($basketItem && $basketItem->isBundleChild())
				{
					$parentBasketItem = $basketItem->getParentBasketItem();
					foreach ($data['ITEMS'][$parentBasketItem->getId()]['SET_ITEMS'] as $id => $setItem)
					{
						if ($setItem['PRODUCT_ID'] == $product['PRODUCT_ID'])
						{
							$item = &$data['ITEMS'][$parentBasketItem->getId()]['SET_ITEMS'][$id];
							break;
						}
					}
				}
				else
				{
					$item = &$data['ITEMS'][$product['BASKET_ID']];
				}
				$item['AMOUNT'] = $product['AMOUNT'];
				$item['QUANTITY'] = $product['QUANTITY'];
				if ($product['BARCODE_INFO'])
				{
					foreach ($product['BARCODE_INFO'] as $id => $info)
					{
						$storeId = $info['STORE_ID'];
						if (!isset($item['BARCODE_INFO'][$storeId]))
						{
							$item['BARCODE_INFO'][$storeId] = array();
							if ($basketItem->isBundleParent())
							{
								$item['BARCODE_INFO'][$storeId][0] = array();
								$item['BARCODE_INFO'][$storeId][0]['QUANTITY'] = $info['QUANTITY'];
							}
							if ($info['BARCODE'])
							{
								foreach ($info['BARCODE'] as $barcode)
								{
									$item['BARCODE_INFO'][$storeId][] = array(
										'ID' => (int)$barcode['ID'],
										'BARCODE' => (string)$barcode['VALUE'],
										'MARKING_CODE' => (string)$barcode['MARKING_CODE'],
										'QUANTITY' => ($basketItem->isbarcodeMulti()) ? 1 : $info['QUANTITY']
									);
								}
							}
						}
						else
						{
							foreach ($item['BARCODE_INFO'][$storeId] as &$barcodeInfo)
							{
								if ($info['BARCODE'])
								{
									$barcode = array_shift($info['BARCODE']);
									$barcodeInfo['ID'] = (int)$barcode['ID'];
									$barcodeInfo['BARCODE'] = (string)$barcode['VALUE'];
									$barcodeInfo['MARKING_CODE'] = (string)$barcode['MARKING_CODE'];
								}
								$barcodeInfo['QUANTITY'] = ($basketItem->isbarcodeMulti()) ? 1 : $info['QUANTITY'];
							}
							unset($barcodeInfo);
						}
					}
				}
			}
		}
		unset($item);

		return $data;
	}

	public function getScripts($recoveryData = array())
	{
		if(!static::$jsInited)
		{
			\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_basket.js");
			\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
			\Bitrix\Main\UI\Extension::load('sale.admin_order');
			static::$jsInited = true;
		}
		$data =	$this->prepareData();

		if (!empty($recoveryData))
		{
			$data = $this->modifyFromRequest($data, $recoveryData['1']['PRODUCT']);
		}

		$keys = array_merge(array_keys($data["ITEMS"]), array_keys($data["UNSHIPPED_PRODUCTS"]));

		$result = '
			<script>
				BX.message({
					SALE_ORDER_BASKET_ROW_SETTINGS: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_ROW_SETTINGS")).'",
					SALE_ORDER_BASKET_PROD_MENU_ADD: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_PROD_MENU_ADD")).'",
					SALE_ORDER_BASKET_PROD_MENU_DELETE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_PROD_MENU_DELETE")).'",
					SALE_ORDER_BASKET_TURN: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_TURN")).'",
					SALE_ORDER_BASKET_EXPAND: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_EXPAND")).'",
					SALE_ORDER_BASKET_NO_PICTURE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_BASKET_NO_PICTURE")).'",
					SALE_ORDER_SHIPMENT_BASKET_SELECTED_PRODUCTS_DEL: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_SELECTED_PRODUCTS_DEL")).'",
					SALE_ORDER_SHIPMENT_BASKET_ALL_PRODUCTS_DEL: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_ALL_PRODUCTS_DEL")).'",
					SALE_ORDER_SHIPMENT_BASKET_ADD_NEW_STORE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_ADD_NEW_STORE")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE")).'",
					SALE_ORDER_SHIPMENT_BASKET_CLOSE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_CLOSE")).'",
					SALE_ORDER_SHIPMENT_BASKET_ADD: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_ADD")).'",
					SALE_ORDER_SHIPMENT_BASKET_NO_PRODUCTS: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_NO_PRODUCTS")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE_ALREADY_USED: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_ALREADY_USED")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE_CLOSE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_CLOSE")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE_ENTER: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_ENTER")).'",
					SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED")).'",
					SALE_ORDER_SHIPMENT_BASKET_ERROR_NOT_FOUND: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_ERROR_NOT_FOUND")).'",
					SALE_ORDER_SHIPMENT_BASKET_BARCODE_EMPTY: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_EMPTY")).'",
				});

				BX.ready(function(){
					'.$this->jsObjName.' = new BX.Sale.Admin.ShipmentBasketEdit({
						tableId: "'.$this->idPrefix.'sale_order_edit_product_table",
						productsOrder: '.\CUtil::PhpToJSObject($keys).',
						idPrefix: "'.$this->idPrefix.'",
						products: '.\CUtil::PhpToJSObject($data["ITEMS"]).',
						orderId: '.((int)$this->order->getId()).',
						visibleColumns: '.\CUtil::PhpToJSObject($this->visibleColumns).',
						objName: "'.$this->jsObjName.'",
						isShipped: "'.$this->shipment->isShipped().'",
						totalBlockFields: {
							PRICE_DELIVERY_DISCOUNT: {
								id: "'.$this->idPrefix.'sale_order_edit_basket_price_delivery_discount",
								value: "'.roundEx(floatval(0), SALE_VALUE_PRECISION).'",
								type: "currency"
							}
						},
						dataForRecovery: '.\CUtil::PhpToJSObject($recoveryData).',
						useStoreControl: "'.self::$useStoreControl.'"
					});

					'.$this->systemJsObjName.' = new BX.Sale.Admin.SystemShipmentBasketEdit({
						tableId: "unshipped",
						productsOrder: '.\CUtil::PhpToJSObject($keys).',
						idPrefix: "del",
						products: '.\CUtil::PhpToJSObject($data["UNSHIPPED_PRODUCTS"]).',
						visibleColumns: '.\CUtil::PhpToJSObject(self::getDefaultUnShippedVisibleColumns()).',
						objName: "'.$this->systemJsObjName.'"
					});

					'.$this->jsObjName.'.link = '.$this->systemJsObjName.';
					'.$this->systemJsObjName.'.link = '.$this->jsObjName.';
				});';

		$result .= $this->settingsDialog->getScripts();
		$result .= '</script>';

		return $result;
	}

	public function getViewScript($index, $visibleColumns)
	{
		\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/admin/order_shipment_basket.js");
		\Bitrix\Main\UI\Extension::load('sale.admin_order');

		$data = $this->prepareData();

		return '<script>
			BX.message({
				SALE_ORDER_SHIPMENT_VIEW_BASKET_NO_PRODUCTS: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_VIEW_BASKET_NO_PRODUCTS")).'",
				SALE_ORDER_SHIPMENT_BASKET_BARCODE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE")).'",
				SALE_ORDER_SHIPMENT_BASKET_BARCODE_CLOSE: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_CLOSE")).'",
				SALE_ORDER_SHIPMENT_BASKET_BARCODE_EMPTY: "'.\CUtil::JSEscape(Loc::getMessage("SALE_ORDER_SHIPMENT_BASKET_BARCODE_EMPTY")).'",
			});

			BX.ready(function()
			{
				var viewBasket_'.$index.' = new BX.Sale.Admin.ShipmentBasket({
					idPrefix: "'.$this->idPrefix.'",
					productsOrder: '.\CUtil::PhpToJSObject(array_keys($data["ITEMS"])).',
					tableId: "'.$this->idPrefix.'_'.$index.'",
					products: '.\CUtil::PhpToJSObject($data["ITEMS"]).',
					visibleColumns: '.\CUtil::PhpToJSObject($visibleColumns).',
					useStoreControl: "'.self::$useStoreControl.'"
				});
			});
		</script>';
	}

	public static function updateData(Order &$order, &$shipment, $shipmentBasket)
	{
		/**@var \Bitrix\Sale\Shipment $shipment */
		$result = new Result();
		$shippingItems = array();
		$idsFromForm = array();
		$basket = $order->getBasket();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		if (is_null(self::$useStoreControl))
			self::$useStoreControl = Configuration::useStoreControl();

		if(is_array($shipmentBasket))
		{
			// PREPARE DATA FOR SET_FIELDS
			foreach ($shipmentBasket as $items)
			{
				$items['QUANTITY'] = floatval(str_replace(',', '.', $items['QUANTITY'] ?? 0));
				$items['AMOUNT'] = floatval(str_replace(',', '.', $items['AMOUNT']));
				if (isset($items['BASKET_ID']) && $items['BASKET_ID'] > 0)
				{
					if (!$basketItem = $basket->getItemById($items['BASKET_ID']))
					{
						$result->addError(new ResultError(
							Loc::getMessage(
								'SALE_ORDER_SHIPMENT_BASKET_BASKET_ITEM_NOT_FOUND',
								[
									'#BASKET_ITEM_ID#' => $items['BASKET_ID'],
								]
							),
							'PROVIDER_UNRESERVED_SHIPMENT_ITEM_WRONG_BASKET_ITEM')
						);
						return $result;
					}
					/** @var \Bitrix\Sale\BasketItem $basketItem */
					$basketCode = $basketItem->getBasketCode();
				}
				else
				{
					$basketCode = $items['BASKET_CODE'];
					if(!$basketItem = $basket->getItemByBasketCode($basketCode))
					{
						$result->addError(new ResultError(
							Loc::getMessage(
								'SALE_ORDER_SHIPMENT_BASKET_BASKET_ITEM_NOT_FOUND',
								[
									'#BASKET_ITEM_ID#' => $items['BASKET_ID'],
								]
							),
							'PROVIDER_UNRESERVED_SHIPMENT_ITEM_WRONG_BASKET_ITEM')
						);
						return $result;
					}
				}

				$tmp = array(
					'BASKET_CODE' => $basketCode,
					'AMOUNT' => $items['AMOUNT'],
					'ORDER_DELIVERY_BASKET_ID' => $items['ORDER_DELIVERY_BASKET_ID'] ?? null,
				);
				$idsFromForm[$basketCode] = array();

				if (!empty($items['BARCODE_INFO']) && (self::$useStoreControl || $basketItem->isSupportedMarkingCode()))
				{
					foreach ($items['BARCODE_INFO'] as $item)
					{
						if ($basketItem->isBundleParent())
						{
							$shippingItems[] = $tmp;
							continue;
						}

						$tmp['BARCODE'] = array(
							'ORDER_DELIVERY_BASKET_ID' => $items['ORDER_DELIVERY_BASKET_ID'],
							'STORE_ID' => (int)$item['STORE_ID'],
							'QUANTITY' => ($basketItem->isBarcodeMulti() || $basketItem->isSupportedMarkingCode()) ? 1 : $item['QUANTITY']
						);

						$barcodeCount = 0;
						if (!empty($item['BARCODE']) && is_array($item['BARCODE']))
						{
							foreach ($item['BARCODE'] as $barcode)
							{
								if(self::$useStoreControl && ($barcodeCount >= $items['QUANTITY']))
								{
									break;
								}

								$barcode['ID'] = (int)$barcode['ID'];
								$idsFromForm[$basketCode]['BARCODE_IDS'][$barcode['ID']] = true;

								if ($barcode['ID'] > 0)
									$tmp['BARCODE']['ID'] = $barcode['ID'];
								else
									unset($tmp['BARCODE']['ID']);

								$tmp['BARCODE']['BARCODE'] = $barcode['VALUE'];
								$tmp['BARCODE']['MARKING_CODE'] = $barcode['MARKING_CODE'];
								$shippingItems[] = $tmp;
								$barcodeCount++;
							}
						}
						elseif (!$basketItem->isBarcodeMulti() && !$basketItem->isSupportedMarkingCode())
						{
							$shippingItems[] = $tmp;
							continue;
						}

						if ($basketItem->isBarcodeMulti() || $basketItem->isSupportedMarkingCode())
						{
							while ($barcodeCount < $item['QUANTITY'])
							{
								unset($tmp['BARCODE']['ID']);
								$tmp['BARCODE']['BARCODE'] = '';
								$tmp['BARCODE']['MARKING_CODE'] = '';
								$shippingItems[] = $tmp;
								$barcodeCount++;
							}
						}
					}
				}
				else
				{
					$shippingItems[] = $tmp;
				}
			}
		}


		// DELETE FROM COLLECTION
		/** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
		foreach ($shipmentItemCollection as $shipmentItem)
		{
			if (!array_key_exists($shipmentItem->getBasketCode(), $idsFromForm))
			{
				/** @var Result $r */
				$r = $shipmentItem->delete();
				if (!$r->isSuccess())
					$result->addErrors($r->getErrors());
			}

			$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
			if ($shipmentItemStoreCollection)
			{
				/** @var \Bitrix\Sale\ShipmentItemStore $shipmentItemStore */
				foreach ($shipmentItemStoreCollection as $shipmentItemStore)
				{
					$shipmentItemId = $shipmentItemStore->getId();
					if (!isset($idsFromForm[$shipmentItem->getBasketCode()]['BARCODE_IDS'][$shipmentItemId]))
					{
						$delResult = $shipmentItemStore->delete();
						if (!$delResult->isSuccess())
							$result->addErrors($delResult->getErrors());
					}
				}
			}
		}

		$isStartField = $shipmentItemCollection->isStartField();

		// SET DATA
		foreach ($shippingItems as $shippingItem)
		{
			if ((int)$shippingItem['ORDER_DELIVERY_BASKET_ID'] <= 0)
			{
				$basketCode = $shippingItem['BASKET_CODE'];
				/** @var \Bitrix\Sale\Order $order */
				$basketItem = $order->getBasket()->getItemByBasketCode($basketCode);

				/** @var \Bitrix\Sale\BasketItem $basketItem */
				$shipmentItem = $shipmentItemCollection->createItem($basketItem);
				if ($shipmentItem === null)
				{
					$result->addError(
						new EntityError(
							Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_ERROR_ALREADY_SHIPPED')
						)
					);
					return $result;
				}
				unset($shippingItem['BARCODE']['ORDER_DELIVERY_BASKET_ID']);
			}
			else
			{
				$shipmentItem = $shipmentItemCollection->getItemById($shippingItem['ORDER_DELIVERY_BASKET_ID']);
				$basketItem = $shipmentItem->getBasketItem();
			}

			if ($shippingItem['AMOUNT'] <= 0)
			{
				$result->addError(
					new EntityError(
						Loc::getMessage('SALE_ORDER_SHIPMENT_BASKET_ERROR_QUANTITY', array('#BASKET_ITEM#' => $basketItem->getField('NAME'))),
						'BASKET_ITEM_'.$basketItem->getBasketCode()
					)
				);
				continue;
			}

			if ($shipmentItem->getQuantity() < $shippingItem['AMOUNT'])
			{
				$order->setMathActionOnly(true);
				$setFieldResult = $shipmentItem->setField('QUANTITY', $shippingItem['AMOUNT']);
				$order->setMathActionOnly(false);

				if (!$setFieldResult->isSuccess())
					$result->addErrors($setFieldResult->getErrors());
			}

			if (!empty($shippingItem['BARCODE']) && (self::$useStoreControl || $basketItem->isSupportedMarkingCode()))
			{
				$barcode = $shippingItem['BARCODE'];

				/** @var \Bitrix\Sale\ShipmentItemStoreCollection $shipmentItemStoreCollection */
				$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
				if ($shipmentItemStoreCollection)
				{
					if (!$basketItem->isBarcodeMulti() && !$basketItem->isSupportedMarkingCode())
					{
						/** @var Result $r */
						$r = $shipmentItemStoreCollection->setBarcodeQuantityFromArray($shipmentBasket[$basketItem->getId()]);
						if(!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}

					if (isset($barcode['ID']) && intval($barcode['ID']) > 0)
					{
						/** @var \Bitrix\Sale\ShipmentItemStore $shipmentItemStore */
						if ($shipmentItemStore = $shipmentItemStoreCollection->getItemById($barcode['ID']))
						{
							unset($barcode['ID']);
							$setFieldResult = $shipmentItemStore->setFields($barcode);

							if (!$setFieldResult->isSuccess())
								$result->addErrors($setFieldResult->getErrors());
						}
					}
					else
					{
						$shipmentItemStore = $shipmentItemStoreCollection->createItem($basketItem);
						$setFieldResult = $shipmentItemStore->setFields($barcode);
						if (!$setFieldResult->isSuccess())
							$result->addErrors($setFieldResult->getErrors());
					}
				}
			}

			$setFieldResult = $shipmentItem->setField('QUANTITY', $shippingItem['AMOUNT']);
			if (!$setFieldResult->isSuccess())
				$result->addErrors($setFieldResult->getErrors());
		}

		if ($isStartField)
		{
			$hasMeaningfulFields = $shipmentItemCollection->hasMeaningfulField();

			/** @var Result $r */
			$r = $shipmentItemCollection->doFinalAction($hasMeaningfulFields);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}
}