<?php
namespace Bitrix\Sale;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class OrderDiscountManager
 * @package Bitrix\Sale
 *
 * @deprecated
 * @see OrderDiscount
 */
class OrderDiscountManager extends OrderDiscount
{
	/** @deprecated */
	const DESCR_TYPE_SIMPLE = Discount\Formatter::TYPE_SIMPLE;
	/** @deprecated */
	const DESCR_TYPE_VALUE = Discount\Formatter::TYPE_VALUE;
	/** @deprecated */
	const DESCR_TYPE_LIMIT_VALUE = Discount\Formatter::TYPE_LIMIT_VALUE;
	/** @deprecated */
	const DESCR_TYPE_FIXED = Discount\Formatter::TYPE_FIXED;
	/** @deprecated */
	const DESCR_TYPE_MAX_BOUND = Discount\Formatter::TYPE_MAX_BOUND;

	/** @deprecated */
	const DESCR_VALUE_TYPE_PERCENT = Discount\Formatter::VALUE_TYPE_PERCENT;
	/** @deprecated */
	const DESCR_VALUE_TYPE_CURRENCY = Discount\Formatter::VALUE_TYPE_CURRENCY;
	/** @deprecated */
	const DESCR_VALUE_TYPE_SUMM = Discount\Formatter::VALUE_TYPE_SUMM;
	/** @deprecated */
	const DESCR_VALUE_TYPE_SUMM_BASKET = Discount\Formatter::VALUE_TYPE_SUMM_BASKET;

	/** @deprecated */
	const DESCR_VALUE_ACTION_DISCOUNT = Discount\Formatter::VALUE_ACTION_DISCOUNT;
	/** @deprecated */
	const DESCR_VALUE_ACTION_EXTRA = Discount\Formatter::VALUE_ACTION_EXTRA;
	/** @deprecated */
	const DESCR_VALUE_ACTION_ACCUMULATE = Discount\Formatter::VALUE_ACTION_CUMULATIVE;
	/** @deprecated */
	const DESCR_VALUE_ACTION_CUMULATIVE = Discount\Formatter::VALUE_ACTION_CUMULATIVE;

	/** @deprecated */
	const DESCR_LIMIT_MAX = Discount\Formatter::LIMIT_MAX;
	/** @deprecated */
	const DESCR_LIMIT_MIN = Discount\Formatter::LIMIT_MIN;

	/**
	 * Load applied discount list
	 * @deprecated
	 * @see OrderDiscount::loadResultFromDb
	 *
	 * @param int $order				Order id.
	 * @param bool $extendedMode		Get full information by discount - unused.
	 * @param array|bool $basketList	Correspondence between basket ids and basket codes.
	 * @param array $basketData			Basket data.
	 * @return Result
	 */
	public static function loadResultFromDatabase($order, $extendedMode = false, $basketList = false, $basketData = array())
	{
		if (!is_array($basketList))
			$basketList = [];
		if (!is_array($basketData))
			$basketData = [];
		$result = parent::loadResultFromDb($order, $basketList, $basketData);

		/* for compatibility only */
		$data = $result->getData();

		$data['BASKET'] = [];
		$data['ORDER'] = [];
		$data['DISCOUNT_MODULES'] = [];
		$data['DATA'] = [];
		if (isset($data['APPLY_BLOCKS'][0]))
		{
			$data['BASKET'] = $data['APPLY_BLOCKS'][0]['BASKET'];
			$data['ORDER'] = $data['APPLY_BLOCKS'][0]['ORDER'];
		}
		if (!empty($data['DISCOUNT_LIST']))
		{
			foreach (array_keys($data['DISCOUNT_LIST']) as $index)
			{
				if (empty($data['DISCOUNT_LIST'][$index]['MODULES']))
					continue;
				$data['DISCOUNT_MODULES'][$index] = $data['DISCOUNT_LIST'][$index]['MODULES'];
			}
			unset($index);
		}
		$data['DATA']['STORED_ACTION_DATA'] = $data['STORED_ACTION_DATA'];
		unset($data['STORED_ACTION_DATA']);

		$result->setData($data);

		return $result;
	}

	/**
	 * Prepare discount description.
	 *
	 * @deprecated
	 * @see Discount\Formatter::prepareRow
	 *
	 * @param int $type					Description type.
	 * @param array|string $data		Description data.
	 * @return Result
	 */
	public static function prepareDiscountDescription($type, $data)
	{
		$result = new Result();

		$config = static::getManagerConfig();

		$type = (int)$type;
		switch ($type)
		{
			case Discount\Formatter::TYPE_LIMIT_VALUE:
				if (!is_array($data))
					$data = array();
				if (!isset($data['LIMIT_UNIT']) && isset($config['CURRENCY']))
					$data['LIMIT_UNIT'] = $config['CURRENCY'];
				if (!isset($data['VALUE_UNIT']) && isset($config['CURRENCY']))
					$data['VALUE_UNIT'] = $config['CURRENCY'];
				break;
			case Discount\Formatter::TYPE_VALUE:
			case Discount\Formatter::TYPE_FIXED:
			case Discount\Formatter::TYPE_MAX_BOUND:
				if (!is_array($data))
					$data = array();
				if (!isset($data['VALUE_UNIT']) && isset($config['CURRENCY']))
					$data['VALUE_UNIT'] = $config['CURRENCY'];
				break;
		}

		$description = Discount\Formatter::prepareRow($type, $data);
		if ($description !== null)
		{
			$result->setData($description);
		}
		else
		{
			self::transferFormatterErrors($result);
		}

		return $result;
	}

	/**
	 * Format discount description.
	 *
	 * @deprecated
	 * @see Discount\Formatter::formatRow
	 *
	 * @param array $data		Discount description.
	 * @return Result
	 */
	public static function formatDiscountDescription($data)
	{
		$result = new Result();

		if (!is_array($data))
			$data = array();

		$description = Discount\Formatter::formatRow($data);
		if ($description !== null)
		{
			$result->setData(array('DESCRIPTION' => $description));
		}
		else
		{
			self::transferFormatterErrors($result);
		}

		return $result;
	}

	/**
	 * Return string discount description.
	 *
	 * @deprecated
	 * @see Discount\Formatter::formatRow
	 *
	 * @param array $data			Description.
	 * @return bool|string
	 */
	public static function formatDescription($data)
	{
		$result = false;
		if (!is_array($data))
			$data = array();
		$description = Discount\Formatter::formatRow($data);
		if ($description !== null)
			$result = $description;

		return $result;
	}

	/**
	 * Format discount result.
	 *
	 * @deprecated
	 * @see Discount\Formatter::formatList
	 *
	 * @param array $data			Description data.
	 * @return array|bool
	 */
	public static function formatArrayDescription($data)
	{
		$result = array();
		if (!empty($data) && is_array($data))
		{
			$description = Discount\Formatter::formatList($data);
			if ($description !== null)
				$result = $description;
		}

		return (empty($result) ? false: $result);
	}

	/**
	 * Create simple description for unknown discount.
	 *
	 * @deprecated
	 * @see Discount\Result\CompatibleFormat::createResultDescription
	 *
	 * @param float $newPrice			New price.
	 * @param float $oldPrice			Old price.
	 * @param string $currency			Currency.
	 * @return array
	 */
	public static function createSimpleDescription($newPrice, $oldPrice, $currency)
	{
		return Discount\Result\CompatibleFormat::createResultDescription($newPrice, $oldPrice, $currency);
	}

	/**
	 * Return basket code for discount rule.
	 * @deprecated
	 *
	 * @param array $rule			Discount rule.
	 * @param bool $translate		Use entity id or basket id.
	 * @param array|bool $basketList		Convert table basket id to basket code.
	 * @return string
	 */
	protected static function getBasketCodeByRule(array $rule, $translate, $basketList)
	{
		$translate = ($translate === true);
		$index = '';
		if ($translate)
		{
			if (is_array($basketList) && isset($basketList[$rule['ENTITY_ID']]))
				$index = $basketList[$rule['ENTITY_ID']];
		}
		else
		{
			$index = $rule['ENTITY_ID'];
		}
		return $index;
	}

	/**
	 * Returns formatter errors.
	 *
	 * @param Result $result        Result object.
	 * @return void
	 */
	private static function transferFormatterErrors(Result $result)
	{
		$errors = Discount\Formatter::getErrors();
		Discount\Formatter::clearErrors();
		$result->addWarning(new Main\Error(
			implode('. ', $errors),
			self::ERROR_ID
		));
	}
}