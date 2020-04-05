<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main,
	Bitrix\Catalog;
/**
 * Class Price
 * Provides various useful methods for prices.
 *
 * @package Bitrix\Catalog\Product
 */
class Price
{
	/* cache id template */
	const CACHE_ID = 'catalog_price_round_';
	/* cache time */
	const CACHE_TIME = 360000;
	/* maximal precision */
	const VALUE_EPS = 1E-5;

	/* static cache */
	protected static $roundRules = array();

	/**
	 * Handler onAfterUpdateCurrencyBaseRate for update field PRICE_SCALE after change currency base rate.
	 *
	 * @param Main\Event $event			Event data (old and new currency rate).
	 * @return void
	 */
	public static function handlerAfterUpdateCurrencyBaseRate(Main\Event $event)
	{
		$params = $event->getParameters();
		if (empty($params))
			return;

		$oldBaseRate = (float)$params['OLD_BASE_RATE'];
		if ($oldBaseRate < 1E-4)
			return;
		$currentBaseRate = (float)$params['CURRENT_BASE_RATE'];
		if (abs($currentBaseRate - $oldBaseRate)/$oldBaseRate < 1E-4)
			return;
		$currency = $params['CURRENCY'];

		$conn = Main\Application::getConnection();
		$helper = $conn->getSqlHelper();

		$query = 'update '.$helper->quote(Catalog\PriceTable::getTableName()).
			' set '.$helper->quote('PRICE_SCALE').' = '.$helper->quote('PRICE').' * '.$currentBaseRate.
			' where '.$helper->quote('CURRENCY').' = \''.$helper->forSql($currency).'\'';
		$conn->queryExecute($query);

		if (defined('BX_COMP_MANAGED_CACHE'))
		{
			$taggedCache = Main\Application::getInstance()->getTaggedCache();
			$taggedCache->clearByTag('currency_id_'.$currency);
		}
	}

	/**
	 * Load round rules list for price types.
	 *
	 * @param array $priceTypes		Price type ids.
	 * @return void
	 */
	public static function loadRoundRules(array $priceTypes)
	{
		if (empty($priceTypes))
			return;
		Main\Type\Collection::normalizeArrayValuesByInt($priceTypes);
		if (empty($priceTypes))
			return;

		$skipCache = (defined('CATALOG_SKIP_CACHE') && CATALOG_SKIP_CACHE);
		$cacheTime = (int)self::CACHE_TIME;
		/** @var \Bitrix\Main\Data\ManagedCache $managedCache */
		$managedCache = Main\Application::getInstance()->getManagedCache();

		$needLoad = array();
		foreach ($priceTypes as $priceTypeId)
		{
			if (!isset(self::$roundRules[$priceTypeId]))
			{
				$rulesFound = false;
				$cacheId = static::getRulesCacheId($priceTypeId);
				if (!$skipCache)
				{
					$cacheExist = $managedCache->read($cacheTime, $cacheId, Catalog\RoundingTable::getTableName());
					if ($cacheExist)
					{
						$rulesFound = true;
						self::$roundRules[$priceTypeId] = $managedCache->get($cacheId);
					}
				}
				if ($skipCache || !$rulesFound)
					$needLoad[] = $priceTypeId;
				unset($cacheId, $rulesFound);
			}
		}
		unset($priceTypeId);

		if (!empty($needLoad))
		{
			foreach ($needLoad as $priceTypeId)
				self::$roundRules[$priceTypeId] = array();
			unset($priceTypeId);
			$iterator = Catalog\RoundingTable::getList(array(
				'select' => array('PRICE', 'ROUND_TYPE', 'ROUND_PRECISION', 'CATALOG_GROUP_ID'),
				'filter' => array('@CATALOG_GROUP_ID' => $needLoad),
				'order' => array('CATALOG_GROUP_ID' => 'ASC', 'PRICE' => 'DESC')
			));
			while ($row = $iterator->fetch())
			{
				$priceTypeId = (int)$row['CATALOG_GROUP_ID'];
				self::$roundRules[$priceTypeId][] = $row;
				unset($priceTypeId);
			}
			unset($row, $iterator);

			if (!$skipCache)
			{
				foreach ($needLoad as $priceTypeId)
					$managedCache->set(static::getRulesCacheId($priceTypeId), self::$roundRules[$priceTypeId]);
				unset($priceType);
			}
		}
		unset($needLoad);
		unset($managedCache, $cacheTime, $skipCache);
	}

	/**
	 * Return round rules list for price type.
	 *
	 * @param int $priceType		Price type id.
	 * @return array
	 */
	public static function getRoundRules($priceType)
	{
		$priceType = (int)$priceType;
		if ($priceType <= 0)
			return array();
		if (!isset(self::$roundRules[$priceType]))
			static::loadRoundRules(array($priceType));

		return self::$roundRules[$priceType];
	}

	/**
	 * Clear cache rules for price type.
	 *
	 * @param int $priceType		Price type id.
	 * @return void
	 */
	public static function clearRoundRulesCache($priceType)
	{
		$priceType = (int)$priceType;
		if ($priceType <= 0)
			return;
		if (isset(self::$roundRules[$priceType]))
			unset(self::$roundRules[$priceType]);
		Main\Application::getInstance()->getManagedCache()->clean(
			static::getRulesCacheId($priceType),
			Catalog\RoundingTable::getTableName()
		);
	}

	/**
	 * Get rule for price value.
	 *
	 * @param int $priceType		Price type id.
	 * @param float|int $price		Price value.
	 * @param string $currency		Price currency.
	 * @return array
	 */
	public static function searchRoundRule(
		$priceType,
		$price,
		/** @noinspection PhpUnusedParameterInspection */ $currency = ''
	)
	{
		$rules = static::getRoundRules($priceType);
		if (empty($rules))
			return array();
		foreach ($rules as $row)
		{
			if ($row['PRICE'] < $price)
				return $row;
		}
		return array();
	}

	/**
	 * Round price.
	 *
	 * @param int $priceType		Price type id.
	 * @param float|int $price		Price value.
	 * @param string $currency		Currency.
	 * @return float|int
	 */
	public static function roundPrice($priceType, $price, $currency)
	{
		$price = (float)$price;
		if ($price <= 0)
			return $price;
		$priceType = (int)$priceType;
		if ($priceType <= 0)
			return $price;
		$rule = static::searchRoundRule($priceType, $price, $currency);
		if (empty($rule))
			return $price;
		return static::roundValue($price, $rule['ROUND_PRECISION'], $rule['ROUND_TYPE']);
	}

	/**
	 * Round value with arbitrary precision.
	 *
	 * @param float|int $value			Round value.
	 * @param float|int $precision		Precision.
	 * @param int $type					Round type.
	 * @return float|int
	 */
	public static function roundValue($value, $precision, $type)
	{
		$type = (int)$type;
		if (!in_array($type, Catalog\RoundingTable::getRoundTypes(false)))
			return $value;

		$precision = (float)$precision;
		if ($precision <= 0)
			return 0;
		if ($precision >= 1)
			$precision = (int)$precision;

		$value = (float)$value;
		if (abs($value) <= self::VALUE_EPS)
			return 0;

		return ($precision < 1
			? static::roundFraction($value, $precision, $type)
			: static::roundWhole($value, $precision, $type)
		);
	}

	/**
	 * Return cache id for price type.
	 *
	 * @param string|int $priceType		Price type id.
	 * @return string
	 */
	protected static function getRulesCacheId($priceType)
	{
		return self::CACHE_ID.$priceType;
	}

	/**
	 * Round whole part.
	 *
	 * @param float|int $value			Round value.
	 * @param float|int $precision		Precision.
	 * @param int $type					Round type.
	 * @return float|int
	 */
	protected static function roundWhole($value, $precision, $type)
	{
		$quotient = $value/$precision;
		$quotientFloor = floor($quotient);
		switch ($type)
		{
			case Catalog\RoundingTable::ROUND_UP:
				if (($quotient - $quotientFloor) > self::VALUE_EPS)
					$quotientFloor += 1;
				break;
			case Catalog\RoundingTable::ROUND_DOWN:
				break;
			case Catalog\RoundingTable::ROUND_MATH:
			default:
				if (($quotient - $quotientFloor + self::VALUE_EPS) >= .5)
					$quotientFloor += 1;
				break;
		}

		return $quotientFloor*$precision;
	}

	/**
	 * Round fraction part.
	 *
	 * @param float|int $value			Round value.
	 * @param float|int $precision		Precision.
	 * @param int $type					Round type.
	 * @return float
	 */
	protected static function roundFraction($value, $precision, $type)
	{
		$valueFloor = floor($value);
		$fraction = $value - $valueFloor;
		if ($fraction <= self::VALUE_EPS)
			return $value;

		return $valueFloor + static::roundWhole($fraction, $precision, $type);
	}
}