<?php

namespace Bitrix\Catalog\Product;

class QuantityControl
{
	private static $values = [];

	public const QUANTITY = 'quantity';
	public const AVAILABLE_QUANTITY = 'available_quantity';
	public const RESERVED_QUANTITY = 'reserved_quantity';
	public const RESERVER_STORE_QUANTITY = 'reserved_store_quantity';

	/** @deprecated */
	public const QUANTITY_CONTROL_QUANTITY = self::QUANTITY;
	/** @deprecated */
	public const QUANTITY_CONTROL_AVAILABLE_QUANTITY = self::AVAILABLE_QUANTITY;
	/** @deprecated */
	public const QUANTITY_CONTROL_RESERVED_QUANTITY = self::RESERVED_QUANTITY;

	/**
	 * @param string|int|null $productId
	 *
	 * @return void
	 */
	public static function resetAllQuantity($productId = null): void
	{
		static::resetValue(static::QUANTITY, $productId);
		static::resetValue(static::AVAILABLE_QUANTITY, $productId);
		static::resetValue(static::RESERVED_QUANTITY, $productId);
		static::resetValue(static::RESERVER_STORE_QUANTITY, $productId);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function setQuantity($productId, $value): void
	{
		self::setValue(
			static::QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			$value
		);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function addQuantity($productId, $value): void
	{
		static::setValue(
			static::QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			(float)$value + (float)static::getQuantity($productId)
		);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return null|float
	 */
	public static function getQuantity($productId): ?float
	{
		return self::getValue(
			static::QUANTITY,
			CatalogProvider::getDefaultStoreId(),
			$productId
		);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return void
	 */
	public static function resetQuantity($productId): void
	{
		self::resetValue(static::QUANTITY, $productId);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function setReservedQuantity($productId, $value): void
	{
		self::setValue(
			static::RESERVED_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			$value
		);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function addReservedQuantity($productId, $value): void
	{
		self::setValue(
			static::RESERVED_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			(float)$value + (float)static::getReservedQuantity($productId)
		);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return null|float
	 */
	public static function getReservedQuantity($productId): ?float
	{
		return self::getValue(
			static::RESERVED_QUANTITY,
			CatalogProvider::getDefaultStoreId(),
			$productId
		);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return void
	 */
	public static function resetReservedQuantity($productId): void
	{
		self::resetValue(static::RESERVED_QUANTITY, $productId);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return void
	 */
	public static function setReservedQuantityFromStores($productId): void
	{
		self::setValue(
			static::RESERVED_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			self::getSummaryValue(
				static::RESERVER_STORE_QUANTITY,
				$productId
			)
		);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function setAvailableQuantity($productId, $value): void
	{
		self::setValue(
			static::AVAILABLE_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			$value
		);
	}

	/**
	 * @param string|int $productId
	 * @param int|float|string|null $value
	 *
	 * @return void
	 */
	public static function addAvailableQuantity($productId, $value): void
	{
		self::setValue(
			static::AVAILABLE_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId(),
			(float)$value + (float)static::getAvailableQuantity($productId)
		);
	}

	/**
	 * @param string|int $productId
	 *
	 * @return null|float
	 */
	public static function getAvailableQuantity($productId): ?float
	{
		return self::getValue(
			static::AVAILABLE_QUANTITY,
			$productId,
			CatalogProvider::getDefaultStoreId()
		);
	}

	/**
	 * @param string|int|null $productId
	 */
	public static function resetAvailableQuantity($productId)
	{
		self::resetValue(static::AVAILABLE_QUANTITY, $productId);
	}

	/**
	 * @param string|int $productId
	 * @param int $storeId
	 * @param int|float|string|null $value
	 * @param bool $summary
	 *
	 * @return void
	 */
	public static function setReservedStoreQuantity($productId, int $storeId, $value, bool $summary = false): void
	{
		self::setValue(
			static::RESERVER_STORE_QUANTITY,
			$productId,
			$storeId,
			$value
		);

		if ($summary)
		{
			static::setReservedQuantity(
				$productId,
				self::getSummaryValue(
					static::RESERVER_STORE_QUANTITY,
					$productId
				)
			);
		}
	}

	/**
	 * @param string|int $productId
	 * @param int $storeId
	 * @param int|float|string|null $value
	 * @param bool $summary
	 *
	 * @return void
	 */
	public static function addReservedStoreQuantity($productId, int $storeId, $value, bool $summary = false): void
	{
		self::setValue(
			static::RESERVER_STORE_QUANTITY,
			$productId,
			$storeId,
			(float)$value
				+ (float)static::getReservedStoreQuantity(
					$productId,
					$storeId,
				)
		);

		if ($summary)
		{
			static::addReservedQuantity($productId, $value);
		}
	}

	/**
	 * @param string|int $productId
	 * @param int $storeId
	 *
	 * @return null|float
	 */
	public static function getReservedStoreQuantity($productId, int $storeId): ?float
	{
		return self::getValue(
			static::RESERVER_STORE_QUANTITY,
			$productId,
			$storeId
		);
	}

	/**
	 * @param string|int|null $productId
	 */
	public static function resetReservedStoreQuantity($productId)
	{
		self::resetValue(static::RESERVER_STORE_QUANTITY, $productId);
	}

	/**
	 * @param string $type
	 * @param string|int $productId
	 * @param int $storeId
	 * @param int|float|string|null $value
	 */
	private static function setValue(string $type, $productId, int $storeId, $value): void
	{
		if (!isset(self::$values[$type]))
		{
			self::$values[$type] = [];
		}
		if (isset(self::$values[$type][$productId]))
		{
			self::$values[$type][$productId] = [];
		}
		self::$values[$type][$productId][$storeId] = (float)$value;
	}

	/**
	 * @param string $type
	 * @param string|int $productId
	 * @param int $storeId
	 *
	 * @return null|float
	 */
	private static function getValue(string $type, $productId, int $storeId): ?float
	{
		$value = null;
		if (isset(self::$values[$type][$productId][$storeId]))
		{
			$value = self::$values[$type][$productId][$storeId];
		}

		return $value;
	}

	/**
	 * @param string $type
	 * @param string|int|null $productId
	 *
	 * @return void
	 */
	private static function resetValue(string $type, $productId = null): void
	{
		if ($productId == null)
		{
			unset(self::$values[$type]);
		}
		else
		{
			unset(self::$values[$type][$productId]);
		}
	}

	private static function getSummaryValue(string $type, $productId): ?float
	{
		$result = null;
		if (
			isset(self::$values[$type][$productId])
			&& is_array(self::$values[$type][$productId])
		)
		{
			$result = (float)array_sum(self::$values[$type][$productId]);
		}

		return $result;
	}

	/**
	 * @param string|int $productId
	 * @param array $values
	 *
	 * @return void
	 */
	public static function setValues($productId, array $values): void
	{
		static::resetAllQuantity($productId);

		if (isset($values[static::QUANTITY]))
		{
			self::setValue(
				static::QUANTITY,
				$productId,
				CatalogProvider::getDefaultStoreId(),
				$values[static::QUANTITY]
			);
		}

		if (isset($values[static::AVAILABLE_QUANTITY]))
		{
			self::setValue(
				static::AVAILABLE_QUANTITY,
				$productId,
				CatalogProvider::getDefaultStoreId(),
				$values[static::AVAILABLE_QUANTITY]
			);
		}

		if (isset($values[static::RESERVED_QUANTITY]))
		{
			self::setValue(
				static::RESERVED_QUANTITY,
				$productId,
				CatalogProvider::getDefaultStoreId(),
				$values[static::RESERVED_QUANTITY]
			);
		}

		if (
			isset($values[static::RESERVER_STORE_QUANTITY])
			&& is_array($values[static::RESERVER_STORE_QUANTITY])
		)
		{
			foreach ($values[static::RESERVER_STORE_QUANTITY] as $storeId => $storeValue)
			{
				self::setValue(
					static::RESERVER_STORE_QUANTITY,
					$productId,
					$storeId,
					$storeValue
				);
			}
		}
	}
}
