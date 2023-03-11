<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Catalog\v2\Integration\Seo\Entity\ExportedProductTable;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Iblock;

class Product extends Entity
{
	/** @var string Need to recalculate parent product available */
	protected const ACTION_CHANGE_PARENT_AVAILABLE = 'SKU_AVAILABLE';
	/** @var string Need to check parent product type */
	protected const ACTION_CHANGE_PARENT_TYPE = 'PARENT_TYPE';
	/** @var string Need to recalculate sets with current product */
	protected const ACTION_RECALCULATE_SETS = 'SETS';
	/** @var string Need to send notifications about available products */
	protected const ACTION_SEND_NOTIFICATIONS = 'SUBSCRIPTION';

	/** @var null|bool Enable offers automation */
	private static ?bool $separateSkuMode = null;
	/** @var null|bool Sale exists */
	private static ?bool $saleIncluded = null;
	/** @var null|string Query for update element timestamp */
	private static ?string $queryElementDate = null;

	/**
	 * Returns product tablet name.
	 *
	 * @return string
	 */
	public static function getTabletClassName(): string
	{
		return '\Bitrix\Catalog\ProductTable';
	}

	/**
	 * Returns product default fields list for caching.
	 *
	 * @return array
	 */
	protected static function getDefaultCachedFieldList(): array
	{
		return [
			'ID',
			'TYPE',
			'AVAILABLE',
			'QUANTITY',
			'QUANTITY_TRACE' => 'QUANTITY_TRACE_ORIG',
			'CAN_BUY_ZERO' => 'CAN_BUY_ZERO_ORIG',
			'SUBSCRIBE' => 'SUBSCRIBE_ORIG'
		];
	}

	/**
	 * Delete product item. Use instead of DataManager method.
	 *
	 * @param int $id
	 * @return ORM\Data\DeleteResult
	 */
	public static function delete($id): ORM\Data\DeleteResult
	{
		return parent::deleteNoDemands($id);
	}

	/**
	 * Check and modify fields before add product. Need for product automation.
	 *
	 * @param ORM\Data\AddResult $result
	 * @param int|null $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForAdd(ORM\Data\AddResult $result, $id, array &$data): void
	{
		if (isset($data['fields']['ID']))
			$id = $data['fields']['ID'];
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_WRONG_PRODUCT_ID')
			));
			return;
		}

		$iblockId = 0;
		if (isset($data['external_fields']['IBLOCK_ID']))
			$iblockId = (int)$data['external_fields']['IBLOCK_ID'];
		if ($iblockId <= 0)
			$iblockId = \CIBlockElement::GetIBlockByID($id);
		if (empty($iblockId))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_ELEMENT_NOT_EXISTS')
			));
			return;
		}
		$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($iblockData))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_SIMPLE_IBLOCK')
			));
			return;
		}
		$data['external_fields']['IBLOCK_ID'] = $iblockId;

		$allowedTypes = self::getProductTypes($iblockData['CATALOG_TYPE']);

		$fields = $data['fields'];
		parent::prepareForAdd($result, $id, $fields);
		if (!$result->isSuccess())
			return;

		if (self::$separateSkuMode === null)
		{
			self::$separateSkuMode = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
		}

		static $defaultValues = null,
			$blackList = null,
			$paymentPeriods = null,
			$tripleFields = null,
			$booleanFields = null,
			$nullFields = null,
			$sizeFields = null;
		if ($defaultValues === null)
		{
			$defaultValues = [
				'QUANTITY' => 0,
				'QUANTITY_RESERVED' => 0,
				'QUANTITY_TRACE' => Catalog\ProductTable::STATUS_DEFAULT,
				'CAN_BUY_ZERO' => Catalog\ProductTable::STATUS_DEFAULT,
				'WEIGHT' => 0,
				'PRICE_TYPE' => Catalog\ProductTable::PAYMENT_TYPE_SINGLE,
				'RECUR_SCHEME_LENGTH' => null,
				'RECUR_SCHEME_TYPE' => Catalog\ProductTable::PAYMENT_PERIOD_DAY,
				'TRIAL_PRICE_ID' => null,
				'WITHOUT_ORDER' => Catalog\ProductTable::STATUS_NO,
				'SELECT_BEST_PRICE' => Catalog\ProductTable::STATUS_NO,
				'VAT_ID' => null,
				'VAT_INCLUDED' => Catalog\ProductTable::STATUS_NO,
				'BARCODE_MULTI' => Catalog\ProductTable::STATUS_NO,
				'SUBSCRIBE' => Catalog\ProductTable::STATUS_DEFAULT,
				'BUNDLE' => Catalog\ProductTable::STATUS_NO,
				'PURCHASING_PRICE' => null,
				'PURCHASING_CURRENCY' => null,
				'TMP_ID' => null,
				'MEASURE' => null,
				'WIDTH' => null,
				'LENGTH' => null,
				'HEIGHT' => null,
			];

			$blackList = [
				'NEGATIVE_AMOUNT_TRACE' => true
			];

			$paymentPeriods = Catalog\ProductTable::getPaymentPeriods(false);
			$tripleFields = ['QUANTITY_TRACE', 'CAN_BUY_ZERO', 'SUBSCRIBE'];
			$booleanFields = ['WITHOUT_ORDER', 'SELECT_BEST_PRICE', 'VAT_INCLUDED', 'BARCODE_MULTI', 'BUNDLE'];
			$nullFields = ['MEASURE', 'TRIAL_PRICE_ID', 'VAT_ID', 'RECUR_SCHEME_LENGTH'];
			$sizeFields = ['WIDTH', 'LENGTH', 'HEIGHT'];
		}
		$defaultValues['TYPE'] = self::getDefaultProductType($iblockData['CATALOG_TYPE']);

		$fields = array_merge($defaultValues, array_diff_key($fields, $blackList));

		$fields['TYPE'] = (int)$fields['TYPE'];
		if (!isset($allowedTypes[$fields['TYPE']]))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_BAD_PRODUCT_TYPE')
			));
			return;
		}

		$isSpecialType = (
			$fields['TYPE'] === Catalog\ProductTable::TYPE_EMPTY_SKU
			|| $fields['TYPE'] === Catalog\ProductTable::TYPE_FREE_OFFER
		);
		if ($isSpecialType)
		{
			$fields['AVAILABLE'] = Catalog\ProductTable::STATUS_NO;
			$fields['QUANTITY'] = 0;
			$fields['QUANTITY_RESERVED'] = 0;
			$fields['QUANTITY_TRACE'] = Catalog\ProductTable::STATUS_YES;
			$fields['CAN_BUY_ZERO'] = Catalog\ProductTable::STATUS_NO;
		}
		$isService = $fields['TYPE'] === Catalog\ProductTable::TYPE_SERVICE;
		if ($isService)
		{
			$fields['QUANTITY_RESERVED'] = 0;
			$fields['QUANTITY_TRACE'] = Catalog\ProductTable::STATUS_NO;
			$fields['CAN_BUY_ZERO'] = Catalog\ProductTable::STATUS_YES;
		}

		if (is_string($fields['QUANTITY']) && !is_numeric($fields['QUANTITY']))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage(
					'BX_CATALOG_MODEL_PRODUCT_ERR_BAD_NUMERIC_FIELD',
					['#FIELD#' => 'QUANTITY']
				)
			));
		}

		if ($isService)
		{
			$fields['QUANTITY'] = (int)$fields['QUANTITY'];
			if ($fields['QUANTITY'] !== 1)
			{
				$fields['QUANTITY'] = 0;
			}
		}
		else
		{
			$fields['QUANTITY'] = (float)$fields['QUANTITY'];
		}

		if (is_string($fields['QUANTITY_RESERVED']) && !is_numeric($fields['QUANTITY_RESERVED']))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage(
					'BX_CATALOG_MODEL_PRODUCT_ERR_BAD_NUMERIC_FIELD',
					['#FIELD#' => 'QUANTITY_RESERVED']
				)
			));
		}
		$fields['QUANTITY_RESERVED'] = (float)$fields['QUANTITY_RESERVED'];

		foreach ($tripleFields as $fieldName)
		{
			if (
				$fields[$fieldName] != Catalog\ProductTable::STATUS_NO
				&& $fields[$fieldName] != Catalog\ProductTable::STATUS_YES
			)
				$fields[$fieldName] = $defaultValues[$fieldName];
		}
		foreach ($booleanFields as $fieldName)
		{
			if ($fields[$fieldName] != Catalog\ProductTable::STATUS_YES)
				$fields[$fieldName] = $defaultValues[$fieldName];
		}
		foreach ($nullFields as $fieldName)
		{
			if ($fields[$fieldName] !== null)
			{
				$fields[$fieldName] = (int)$fields[$fieldName];
				if ($fields[$fieldName] <= 0)
					$fields[$fieldName] = null;
			}
		}
		foreach ($sizeFields as $fieldName)
		{
			if ($fields[$fieldName] !== null)
			{
				$fields[$fieldName] = (float)$fields[$fieldName];
				if ($fields[$fieldName] <= 0)
					$fields[$fieldName] = null;
			}
		}
		unset($fieldName);

		if (
			$fields['PRICE_TYPE'] != Catalog\ProductTable::PAYMENT_TYPE_REGULAR
			&& $fields['PRICE_TYPE'] != Catalog\ProductTable::PAYMENT_TYPE_TRIAL
		)
			$fields['PRICE_TYPE'] = $defaultValues['PRICE_TYPE'];
		if (!in_array($fields['RECUR_SCHEME_TYPE'], $paymentPeriods, true))
			$fields['RECUR_SCHEME_TYPE'] = $defaultValues['RECUR_SCHEME_TYPE'];

		if (is_string($fields['WEIGHT']) && !is_numeric($fields['WEIGHT']))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage(
					'BX_CATALOG_MODEL_PRODUCT_ERR_BAD_NUMERIC_FIELD',
					['#FIELD#' => 'WEIGHT']
				)
			));
		}
		$fields['WEIGHT'] = (float)$fields['WEIGHT'];
		if ($fields['TMP_ID'] !== null)
			$fields['TMP_ID'] = mb_substr($fields['TMP_ID'], 0, 40);

		/* purchasing price */
		$purchasingCurrency = null;
		$purchasingPrice = static::checkPriceValue($fields['PURCHASING_PRICE']);
		if ($purchasingPrice !== null)
		{
			$purchasingCurrency = static::checkPriceCurrency($fields['PURCHASING_CURRENCY']);
			if ($purchasingCurrency === null)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_WRONG_PURCHASING_CURRENCY')
				));
				$purchasingPrice = null;
			}
		}
		$fields['PURCHASING_PRICE'] = $purchasingPrice;
		$fields['PURCHASING_CURRENCY'] = $purchasingCurrency;
		unset($purchasingCurrency, $purchasingPrice);
		/* purchasing price end */

		if (array_key_exists('AVAILABLE', $fields))
		{
			if (
				$fields['AVAILABLE'] != Catalog\ProductTable::STATUS_YES
				&& $fields['AVAILABLE'] != Catalog\ProductTable::STATUS_NO
			)
			{
				unset($fields['AVAILABLE']);
			}
			else
			{
				if (!$isSpecialType)
				{
					if ($isService)
					{
						//TODO: remove this hack after reservation resource
						$fields['QUANTITY'] = $fields['AVAILABLE'] !== Catalog\ProductTable::STATUS_YES ? 0 : 1;
					}
					else
					{
						$data['actions'][self::ACTION_CHANGE_PARENT_AVAILABLE] = true;
					}
				}
			}
		}

		if ($result->isSuccess())
		{
			$fields['ID'] = $id;
			$fields['NEGATIVE_AMOUNT_TRACE'] = $fields['CAN_BUY_ZERO'];
			$fields['TIMESTAMP_X'] = new Main\Type\DateTime();

			if (!isset($fields['AVAILABLE']))
			{
				self::calculateAvailable($fields, $data['actions']);
				if ($fields['AVAILABLE'] === null)
					$fields['AVAILABLE'] = Catalog\ProductTable::STATUS_NO;
			}
			if ($fields['TYPE'] === Catalog\ProductTable::TYPE_OFFER)
			{
				$data['actions'][self::ACTION_CHANGE_PARENT_TYPE] = true;
			}
		}

		if ($result->isSuccess())
			$data['fields'] = $fields;
		unset($fields);
	}

	/**
	 * Check and modify fields before update product. Need for product automation.
	 *
	 * @param ORM\Data\UpdateResult $result
	 * @param int $id
	 * @param array &$data
	 * @return void
	 */
	protected static function prepareForUpdate(ORM\Data\UpdateResult $result, $id, array &$data): void
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_WRONG_PRODUCT_ID')
			));
			return;
		}

		$iblockId = 0;
		if (isset($data['external_fields']['IBLOCK_ID']))
			$iblockId = (int)$data['external_fields']['IBLOCK_ID'];
		if ($iblockId <= 0)
			$iblockId = \CIBlockElement::GetIBlockByID($id);
		if (empty($iblockId))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_ELEMENT_NOT_EXISTS')
			));
			return;
		}
		$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($iblockData))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_SIMPLE_IBLOCK')
			));
			return;
		}
		$data['external_fields']['IBLOCK_ID'] = $iblockId;

		$fields = $data['fields'];
		parent::prepareForUpdate($result, $id, $fields);
		if (!$result->isSuccess())
			return;

		if (self::$separateSkuMode === null)
		{
			self::$separateSkuMode = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
		}

		static $quantityFields = null,
			$paymentPeriods = null,
			$tripleFields = null,
			$booleanFields = null,
			$nullFields = null,
			$sizeFields = null,
			$blackList = null;

		if ($quantityFields === null)
		{
			$quantityFields = ['QUANTITY', 'QUANTITY_RESERVED'];
			$paymentPeriods = Catalog\ProductTable::getPaymentPeriods(false);
			$tripleFields = ['QUANTITY_TRACE', 'CAN_BUY_ZERO', 'SUBSCRIBE'];
			$booleanFields = ['WITHOUT_ORDER', 'SELECT_BEST_PRICE', 'VAT_INCLUDED', 'BARCODE_MULTI', 'BUNDLE', 'AVAILABLE'];
			$nullFields = ['MEASURE', 'TRIAL_PRICE_ID', 'VAT_ID', 'RECUR_SCHEME_LENGTH'];
			$sizeFields = ['WIDTH', 'LENGTH', 'HEIGHT'];

			$blackList = [
				'ID' => true,
				'NEGATIVE_AMOUNT_TRACE' => true
			];
		}
		$fields = array_diff_key($fields, $blackList);

		$allowedTypes = self::getProductTypes($iblockData['CATALOG_TYPE']);

		if (array_key_exists('TYPE', $fields))
		{
			$fields['TYPE'] = (int)$fields['TYPE'];
			if (!isset($allowedTypes[$fields['TYPE']]))
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_BAD_PRODUCT_TYPE')
				));
				return;
			}
		}

		foreach ($quantityFields as $fieldName)
		{
			if (array_key_exists($fieldName, $fields))
			{
				if ($fields[$fieldName] === null)
					unset($fields[$fieldName]);
				else
					$fields[$fieldName] = (float)$fields[$fieldName];
			}
		}
		foreach ($tripleFields as $fieldName)
		{
			if (array_key_exists($fieldName, $fields))
			{
				if (
					$fields[$fieldName] != Catalog\ProductTable::STATUS_NO
					&& $fields[$fieldName] != Catalog\ProductTable::STATUS_YES
					&& $fields[$fieldName] != Catalog\ProductTable::STATUS_DEFAULT
				)
					unset($fields[$fieldName]);
			}
		}
		if (isset($fields['SUBSCRIBE']))
			$data['actions'][self::ACTION_SEND_NOTIFICATIONS] = true;
		foreach ($booleanFields as $fieldName)
		{
			if (array_key_exists($fieldName, $fields))
			{
				if (
					$fields[$fieldName] != Catalog\ProductTable::STATUS_NO
					&& $fields[$fieldName] != Catalog\ProductTable::STATUS_YES
				)
					unset($fields[$fieldName]);
			}
		}
		foreach ($nullFields as $fieldName)
		{
			if (isset($fields[$fieldName]))
			{
				$fields[$fieldName] = (int)$fields[$fieldName];
				if ($fields[$fieldName] <= 0)
				{
					$fields[$fieldName] = null;
				}
			}
		}
		foreach ($sizeFields as $fieldName)
		{
			if (isset($fields[$fieldName]))
			{
				$fields[$fieldName] = (float)$fields[$fieldName];
				if ($fields[$fieldName] <= 0)
				{
					$fields[$fieldName] = null;
				}
			}
		}
		unset($fieldName);

		if (array_key_exists('PRICE_TYPE', $fields))
		{
			if (
				$fields['PRICE_TYPE'] != Catalog\ProductTable::PAYMENT_TYPE_REGULAR
				&& $fields['PRICE_TYPE'] != Catalog\ProductTable::PAYMENT_TYPE_TRIAL
				&& $fields['PRICE_TYPE'] != Catalog\ProductTable::PAYMENT_TYPE_SINGLE
			)
				unset($fields['PRICE_TYPE']);
		}
		if (array_key_exists('RECUR_SCHEME_TYPE', $fields))
		{
			if (!in_array($fields['RECUR_SCHEME_TYPE'], $paymentPeriods, true))
				unset($fields['RECUR_SCHEME_TYPE']);
		}

		if (array_key_exists('WEIGHT', $fields))
		{
			if ($fields['WEIGHT'] === null)
			{
				unset($fields['WEIGHT']);
			}
			else
			{
				$fields['WEIGHT'] = (float)$fields['WEIGHT'];
				$data['actions'][self::ACTION_RECALCULATE_SETS] = true;
			}
		}
		if (isset($fields['TMP_ID']))
			$fields['TMP_ID'] = mb_substr($fields['TMP_ID'], 0, 40);

		/* purchasing price */
		$existPurchasingPrice = array_key_exists('PURCHASING_PRICE', $fields);
		$existPurchasingCurrency = array_key_exists('PURCHASING_CURRENCY', $fields);
		if ($existPurchasingPrice)
		{
			$fields['PURCHASING_PRICE'] = static::checkPriceValue($fields['PURCHASING_PRICE']);
			if ($fields['PURCHASING_PRICE'] === null)
			{
				$fields['PURCHASING_CURRENCY'] = null;
				$existPurchasingCurrency = false;
			}
		}
		if ($existPurchasingCurrency)
		{
			$fields['PURCHASING_CURRENCY'] = static::checkPriceCurrency($fields['PURCHASING_CURRENCY']);
			if ($fields['PURCHASING_CURRENCY'] === null)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRODUCT_ERR_WRONG_PURCHASING_CURRENCY')
				));
			}
		}
		/* purchasing price end */

		if ($result->isSuccess())
		{
			if (isset($fields['CAN_BUY_ZERO']))
				$fields['NEGATIVE_AMOUNT_TRACE'] = $fields['CAN_BUY_ZERO'];
			$fields['TIMESTAMP_X'] = new Main\Type\DateTime();

			if (isset($fields['AVAILABLE']))
			{
				$copyFields =
					isset($fields['TYPE'])
						? $fields
						: array_merge(static::getCacheItem($id, true), $fields)
				;
				$copyFields['TYPE'] = (int)$copyFields['TYPE'];
				$isService = $copyFields['TYPE'] === Catalog\ProductTable::TYPE_SERVICE;
				if ($isService)
				{
					//TODO: remove this hack after reservation resource
					$fields['QUANTITY'] = $fields['AVAILABLE'] !== Catalog\ProductTable::STATUS_YES ? 0 : 1;
				}
				if (!$isService)
				{
					$data['actions'][self::ACTION_CHANGE_PARENT_AVAILABLE] = true;
				}
				$data['actions'][self::ACTION_RECALCULATE_SETS] = true;
				$data['actions'][self::ACTION_SEND_NOTIFICATIONS] = true;
			}
			else
			{
				$needCalculateAvailable = (isset($fields['TYPE'])
					|| isset($fields['QUANTITY'])
					|| isset($fields['QUANTITY_TRACE'])
					|| isset($fields['CAN_BUY_ZERO'])
				);
				if ($needCalculateAvailable)
				{
					$needCache = (!isset($fields['TYPE'])
						|| !isset($fields['QUANTITY'])
						|| !isset($fields['QUANTITY_TRACE'])
						|| !isset($fields['CAN_BUY_ZERO'])
					);
					$copyFields = (
						$needCache
						? array_merge(static::getCacheItem($id, true), $fields)
						: $fields
					);
					$copyFields['TYPE'] = (int)$copyFields['TYPE'];
					self::calculateAvailable($copyFields, $data['actions']);
					if ($copyFields['AVAILABLE'] !== null)
						$fields['AVAILABLE'] = $copyFields['AVAILABLE'];
					unset($copyFields, $needCache);
				}
				unset($needCalculateAvailable);
			}
			$data['fields'] = $fields;
		}

		unset($fields);
	}

	/**
	 * Run core automation after add product.
	 *
	 * @param int $id
	 * @param array $data
	 * @return void
	 */
	protected static function runAddExternalActions($id, array $data): void
	{
		switch ($data['fields']['TYPE'])
		{
			case Catalog\ProductTable::TYPE_OFFER:
				if (
					isset($data['actions'][self::ACTION_CHANGE_PARENT_AVAILABLE])
					|| isset($data['actions'][self::ACTION_CHANGE_PARENT_TYPE])
				)
				{
					Catalog\Product\Sku::calculateComplete(
						$id,
						$data['external_fields']['IBLOCK_ID'],
						Catalog\ProductTable::TYPE_OFFER
					);
				}
				break;
			case Catalog\ProductTable::TYPE_SKU:
				if (isset($data['actions'][self::ACTION_CHANGE_PARENT_AVAILABLE]))
				{
					Catalog\Product\Sku::calculateComplete(
						$id,
						$data['external_fields']['IBLOCK_ID'],
						Catalog\ProductTable::TYPE_SKU
					);
				}
				break;
		}
	}

	/**
	 * Run core automation after update product.
	 *
	 * @param int $id
	 * @param array $data
	 * @return void
	 */
	protected static function runUpdateExternalActions($id, array $data): void
	{
		$product = self::getCacheItem($id);
		if (isset($data['actions'][self::ACTION_CHANGE_PARENT_AVAILABLE]))
		{
			switch ($product['TYPE'])
			{
				case Catalog\ProductTable::TYPE_OFFER:
					if (isset($data['actions']['SKU_AVAILABLE']))
					{
						Catalog\Product\Sku::calculateComplete(
							$id,
							$data['external_fields']['IBLOCK_ID'],
							Catalog\ProductTable::TYPE_OFFER
						);
					}
					self::updateElementModificationTime($id);
					break;
				case Catalog\ProductTable::TYPE_SKU:
					if (isset($data['actions']['SKU_AVAILABLE']))
					{
						Catalog\Product\Sku::calculateComplete(
							$id,
							$data['external_fields']['IBLOCK_ID'],
							Catalog\ProductTable::TYPE_SKU
						);
					}
					break;
				case Catalog\ProductTable::TYPE_PRODUCT:
				case Catalog\ProductTable::TYPE_SET:
					self::updateElementModificationTime($id);
					break;
			}
		}
		if (isset($data['actions'][self::ACTION_SEND_NOTIFICATIONS]))
		{
			self::checkSubscription($id, $product);
		}
		if (isset($data['actions'][self::ACTION_RECALCULATE_SETS]))
		{
			\CCatalogProductSet::recalculateSetsByProduct($id);
		}

		$changeAvailable = (
			isset($product['AVAILABLE'])
			&& isset($product[self::PREFIX_OLD.'AVAILABLE'])
			&& $product[self::PREFIX_OLD.'AVAILABLE'] != $product['AVAILABLE']
		);
		if ($changeAvailable)
		{
			// clear public components cache
			\CIBlock::clearIblockTagCache($data['external_fields']['IBLOCK_ID']);
			// send old event
			$eventId = 'OnProductQuantityTrace';
			if (
				Main\Config\Option::get('catalog', 'enable_processing_deprecated_events') === 'Y'
				&& Event::existEventHandlersById($eventId)
			)
			{
				$description = [
					'ID' => $product['ID'],
					'ELEMENT_IBLOCK_ID' => $data['external_fields']['IBLOCK_ID'],
					'IBLOCK_ID' => $data['external_fields']['IBLOCK_ID'],
					'TYPE' => $product['TYPE'],
					'AVAILABLE' => $product['AVAILABLE'],
					'CAN_BUY_ZERO' => $product['CAN_BUY_ZERO'],
					'NEGATIVE_AMOUNT_TRACE' => $product['CAN_BUY_ZERO'],
					'QUANTITY_TRACE' => $product['QUANTITY_TRACE'],
					'QUANTITY' => $product['QUANTITY'],
					'OLD_QUANTITY' => $product[self::PREFIX_OLD.'QUANTITY'] ?? $product['QUANTITY'],
				];
				$description['DELTA'] = $description['QUANTITY'] - $description['OLD_QUANTITY'];
				$handlerData = [
					$product['ID'],
					$description,
				];
				unset($description);

				$eventManager = Main\EventManager::getInstance();
				$handlerList = $eventManager->findEventHandlers('catalog', $eventId);
				foreach ($handlerList as $handler)
				{
					$handler['FROM_MODULE_ID'] = 'catalog';
					$handler['MESSAGE_ID'] = $eventId;
					ExecuteModuleEventEx($handler, $handlerData);
				}
				unset($handler, $handlerList);
				unset($handlerData);
			}
		}

		unset($product);
	}

	/**
	 * Run core automation after delete product.
	 *
	 * @param int $id
	 * @return void
	 */
	protected static function runDeleteExternalActions($id): void
	{
		Catalog\PriceTable::deleteByProduct($id);
		Catalog\MeasureRatioTable::deleteByProduct($id);
		Catalog\ProductGroupAccessTable::deleteByProduct($id);
		Catalog\StoreProductTable::deleteByProduct($id);
		Catalog\SubscribeTable::onIblockElementDelete($id);
		ExportedProductTable::deleteProduct($id);
		//TODO: replace this code
		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();
		$conn->queryExecute(
			'delete from '.$helper->quote('b_catalog_product_sets').
			' where '.$helper->quote('ITEM_ID').' = '.$id.' or '.$helper->quote('OWNER_ID').' = '.$id
		);
		unset($helper, $conn);
	}

	/**
	 * Sending messages that the product has become available.
	 * @internal
	 *
	 * @param $id
	 * @param array $product
	 * @return void
	 */
	private static function checkSubscription($id, array $product): void
	{
		if (
			isset($product[self::PREFIX_OLD.'AVAILABLE'])
			&& Catalog\SubscribeTable::checkPermissionSubscribe($product['SUBSCRIBE'])
		)
		{
			if (
				$product[self::PREFIX_OLD.'AVAILABLE'] == Catalog\ProductTable::STATUS_NO
				&& $product['AVAILABLE'] == Catalog\ProductTable::STATUS_YES
			)
			{
				Catalog\SubscribeTable::runAgentToSendNotice($id);
			}
			elseif (
				$product[self::PREFIX_OLD.'AVAILABLE'] == Catalog\ProductTable::STATUS_YES
				&& $product['AVAILABLE'] == Catalog\ProductTable::STATUS_NO
			)
			{
				Catalog\SubscribeTable::runAgentToSendRepeatedNotice($id);
			}
			if (
				$product[self::PREFIX_OLD.'QUANTITY'] <= 0
				&& $product['QUANTITY'] > 0
			)
			{
				if (self::$saleIncluded === null)
					self::$saleIncluded = Loader::includeModule('sale');
				if (self::$saleIncluded)
					\CSaleBasket::ProductSubscribe($id, 'catalog');
			}
		}
	}

	private static function checkPriceValue($price): ?float
	{
		$result = null;

		if ($price !== null)
		{
			if (is_string($price))
			{
				if ($price !== '' && is_numeric($price))
				{
					$price = (float)$price;
					if (is_finite($price))
						$result = $price;
				}
			}
			elseif (
				is_int($price)
				|| (is_float($price) && is_finite($price))
			)
			{
				$result = $price;
			}
		}

		return $result;
	}

	private static function checkPriceCurrency($currency): ?string
	{
		$result = null;
		if (is_string($currency) && $currency !== '')
		{
			$result = $currency;
		}

		return $result;
	}

	private static function calculateAvailable(array &$fields, array &$actions)
	{
		$result = null;

		switch ($fields['TYPE'])
		{
			case Catalog\ProductTable::TYPE_PRODUCT:
			case Catalog\ProductTable::TYPE_FREE_OFFER:
				$result = Catalog\ProductTable::calculateAvailable($fields);
				$actions[self::ACTION_RECALCULATE_SETS] = true;
				$actions[self::ACTION_SEND_NOTIFICATIONS] = true;
				break;
			case Catalog\ProductTable::TYPE_OFFER:
				$result = Catalog\ProductTable::calculateAvailable($fields);
				if (!self::$separateSkuMode)
					$actions[self::ACTION_CHANGE_PARENT_AVAILABLE] = true;
				$actions[self::ACTION_RECALCULATE_SETS] = true;
				$actions[self::ACTION_SEND_NOTIFICATIONS] = true;
				break;
			case Catalog\ProductTable::TYPE_SKU:
				if (self::$separateSkuMode)
					$result = Catalog\ProductTable::calculateAvailable($fields);
				else
					$actions[self::ACTION_CHANGE_PARENT_AVAILABLE] = true;
				break;
			case Catalog\ProductTable::TYPE_SET:
				$result = Catalog\ProductTable::calculateAvailable($fields);
				break;
			case Catalog\ProductTable::TYPE_EMPTY_SKU:
				$result = Catalog\ProductTable::STATUS_NO;
				break;
			case Catalog\ProductTable::TYPE_SERVICE:
				if (isset($fields['QUANTITY']))
				{
					$result = ($fields['QUANTITY'] > 0 ? Catalog\ProductTable::STATUS_YES : Catalog\ProductTable::STATUS_NO);
					$actions[self::ACTION_SEND_NOTIFICATIONS] = true;
				}
		}

		$fields['AVAILABLE'] = $result;
	}

	//TODO: remove after create \Bitrix\Catalog\Model\CatalogIblock
	private static function getProductTypes($catalogType): array
	{
		$result = [];

		switch ($catalogType)
		{
			case \CCatalogSku::TYPE_CATALOG:
				$result = [
					Catalog\ProductTable::TYPE_PRODUCT => true,
					Catalog\ProductTable::TYPE_SET => true,
					Catalog\ProductTable::TYPE_SERVICE => true,
				];
				break;
			case \CCatalogSku::TYPE_OFFERS:
				$result = [
					Catalog\ProductTable::TYPE_OFFER => true,
					Catalog\ProductTable::TYPE_FREE_OFFER => true
				];
				break;
			case \CCatalogSku::TYPE_FULL:
				$result = [
					Catalog\ProductTable::TYPE_PRODUCT => true,
					Catalog\ProductTable::TYPE_SET => true,
					Catalog\ProductTable::TYPE_SKU => true,
					Catalog\ProductTable::TYPE_EMPTY_SKU => true,
					Catalog\ProductTable::TYPE_SERVICE => true,
				];
				break;
			case \CCatalogSku::TYPE_PRODUCT:
				$result = [
					Catalog\ProductTable::TYPE_SKU => true,
					Catalog\ProductTable::TYPE_EMPTY_SKU => true
				];
				break;
		}

		return $result;
	}

	//TODO: remove after create \Bitrix\Catalog\Model\CatalogIblock
	private static function getDefaultProductType($catalogType): ?int
	{
		$result = null;

		switch ($catalogType)
		{
			case \CCatalogSku::TYPE_CATALOG:
			case \CCatalogSku::TYPE_FULL:
				$result = Catalog\ProductTable::TYPE_PRODUCT;
				break;
			case \CCatalogSku::TYPE_OFFERS:
				$result = Catalog\ProductTable::TYPE_OFFER;
				break;
			case \CCatalogSku::TYPE_PRODUCT:
				$result = Catalog\ProductTable::TYPE_SKU;
				break;
		}

		return $result;
	}

	private static function updateElementModificationTime(int $elementId): void
	{
		$conn = Main\Application::getConnection();
		if (self::$queryElementDate === null)
		{
			$helper = $conn->getSqlHelper();
			self::$queryElementDate = 'update ' . $helper->quote(Iblock\ElementTable::getTableName())
				. ' set ' . $helper->quote('TIMESTAMP_X') . ' = ' . $helper->getCurrentDateTimeFunction()
				. ' where ' . $helper->quote('ID') . '=';
		}
		$conn->queryExecute(self::$queryElementDate . $elementId);
	}
}
