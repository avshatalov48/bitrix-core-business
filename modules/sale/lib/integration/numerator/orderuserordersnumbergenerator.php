<?php
namespace Bitrix\Sale\Integration\Numerator;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Numerator\Generator\Contract\DynamicConfigurable;
use Bitrix\Main\Numerator\Generator\NumberGenerator;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Internals\OrderArchiveTable;
use Bitrix\Sale\Registry;

/**
 * Class OrderUserOrdersNumberGenerator
 * @package Bitrix\Sale\Integration\Numerator
 */
class OrderUserOrdersNumberGenerator extends NumberGenerator implements DynamicConfigurable
{
	protected $orderId;

	const TEMPLATE_WORD_USER_ID_ORDERS_COUNT = "USER_ID_ORDERS_COUNT";

	/** @inheritdoc */
	public static function getTemplateWordsForParse()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_USER_ID_ORDERS_COUNT),
		];
	}

	/** @inheritdoc */
	public static function getTemplateWordsSettings()
	{
		return [
			static::getPatternFor(static::TEMPLATE_WORD_USER_ID_ORDERS_COUNT)
			=> Loc::getMessage('BITRIX_SALE_INTEGRATION_NUMERATOR_ORDERUSERORDERSNUMBERGENERATOR_WORD_USER_ID_ORDERS_COUNT'),
		];
	}

	/**
	 * @return string
	 */
	public static function getAvailableForType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return string
	 */
	protected function getTableName()
	{
		return OrderTable::class;
	}

	/** @inheritdoc */
	public function parseTemplate($template)
	{
		$tableName = $this->getTableName();
		/** @var \Bitrix\Main\Entity\DataManager $tableName */
		$userIdOfOrder = $tableName::query()
			->addSelect('USER_ID')
			->where('ID', $this->orderId)
			->exec()
			->fetch();

		if ($userIdOfOrder)
		{
			$userIdOfOrder = intval($userIdOfOrder['USER_ID']);
			$countArchiveOrderOfUser = OrderArchiveTable::query()
				->addSelect('ORDERS_COUNT')
				->registerRuntimeField(
					new ExpressionField(
						'ORDERS_COUNT',
						'COUNT(ID)'
					)
				)
				->where('USER_ID', $userIdOfOrder)
				->addGroup('USER_ID')
				->exec()
				->fetch();
			$countArchiveOrderOfUser = (int)$countArchiveOrderOfUser['ORDERS_COUNT'];

			$countOrderOfUser = $tableName::query()
				->addSelect('ORDERS_COUNT')
				->registerRuntimeField(
					new ExpressionField(
						'ORDERS_COUNT',
						'COUNT(ID)'
					)
				)
				->where('USER_ID', $userIdOfOrder)
				->addGroup('USER_ID')
				->exec()
				->fetch();

			$countOrderOfUser = (int)$countOrderOfUser['ORDERS_COUNT'] + $countArchiveOrderOfUser;
			$numID = ($countOrderOfUser > 0) ? $countOrderOfUser : 1;
			$value = $userIdOfOrder . "_" . $numID;
		}
		else
		{
			$value = '';
		}

		return str_replace($this->getWordToReplace(), $value, $template);
	}

	/**
	 * @param array $config
	 */
	public function setDynamicConfig($config)
	{
		if (is_array($config) && array_key_exists('ORDER_ID', $config))
		{
			$this->orderId = $config['ORDER_ID'];
		}
	}

	/**
	 * @return string
	 */
	protected function getWordToReplace()
	{
		return self::getPatternFor(self::TEMPLATE_WORD_USER_ID_ORDERS_COUNT);
	}
}