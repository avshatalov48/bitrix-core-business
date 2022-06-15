<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Sale\TradingPlatform;

/**
 * Class TradeBindingEntity
 * @package Bitrix\Sale
 */
class TradeBindingEntity extends Internals\CollectableEntity
{
	private $tradePlatform = null;

	/**
	 * @return string
	 */
	public static function getRegistryEntity()
	{
		return Registry::ENTITY_TRADE_BINDING_ENTITY;
	}

	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return [
			'ORDER_ID', 'EXTERNAL_ORDER_ID',
			'TRADING_PLATFORM_ID', 'PARAMS', 'XML_ID'
		];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return array();
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return TradingPlatform\OrderTable::getMap();
	}

	/**
	 * @param TradeBindingCollection $collection
	 * @param TradingPlatform\Platform|null $platform
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function create(TradeBindingCollection $collection, TradingPlatform\Platform $platform = null)
	{
		/** @var TradeBindingEntity $entity */
		$entity = static::createEntityObject();

		$entity->setCollection($collection);

		if ($platform !== null)
		{
			$entity->setFieldNoDemand('TRADING_PLATFORM_ID', $platform->getId());
			$entity->tradePlatform = $platform;
		}

		$entity->setFieldNoDemand('XML_ID', static::generateXmlId());

		return $entity;
	}

	/**
	 * @return string
	 */
	protected static function generateXmlId()
	{
		return uniqid('bx_');
	}

	/**
	 * @param array $fields
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	private static function createEntityObject(array $fields = array())
	{
		$registry = Registry::getInstance(static::getRegistryType());
		$entityClassName = $registry->get(Registry::ENTITY_TRADE_BINDING_ENTITY);

		return new $entityClassName($fields);
	}

	/**
	 * @param $id
	 * @return array|false
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function loadForOrder($id)
	{
		if (intval($id) <= 0)
		{
			throw new Main\ArgumentNullException("id");
		}

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var TradeBindingCollection $tradeBindingCollection */
		$tradeBindingCollection = $registry->get(Registry::ENTITY_TRADE_BINDING_COLLECTION);
		$dbRes = $tradeBindingCollection::getList([
			'filter' => ['ORDER_ID' => $id]
		]);

		$entityList = [];
		while ($data = $dbRes->fetch())
		{
			$entityList[] = static::createEntityObject($data);
		}

		return $entityList;
	}

	/**
	 * @return Result
	 * @throws \Exception
	 */
	public function save()
	{
		$result = new Result();

		if (!$this->isChanged())
		{
			return $result;
		}

		$id = $this->getId();

		if ($id > 0)
		{
			$fields = $this->getFields()->getChangedValues();
			$r = $this->updateInternal($id, $fields);
			$result->setId($r->getId());
		}
		else
		{
			/** @var TradeBindingCollection $collection */
			$collection = $this->getCollection();

			/** @var Order $order */
			$order = $collection->getOrder();

			$this->setFieldNoDemand('ORDER_ID', $order->getId());

			if ((int)$this->getField('EXTERNAL_ORDER_ID') <= 0)
			{
				$this->setFieldNoDemand('EXTERNAL_ORDER_ID', $order->getId());
			}

			$fields = $this->getFields()->getValues();
			$r = $this->addInternal($fields);
			if ($r->isSuccess())
			{
				$id = $r->getId();
				$this->setFieldNoDemand('ID', $id);
			}
		}

		if (!$r->isSuccess())
		{
			$result->addErrors($r->getErrors());
			return $result;
		}

		$result->setId($id);

		return $result;
	}

	/**
	 * @return TradingPlatform\Platform|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getTradePlatform()
	{
		if ($this->tradePlatform === null)
		{
			if ($this->getField('TRADING_PLATFORM_ID') > 0)
			{
				$dbRes = TradingPlatformTable::getList([
					'select' => ['CODE'],
					'filter' => [
						'=ID' => $this->getField('TRADING_PLATFORM_ID')
					]
				]);

				if ($item = $dbRes->fetch())
				{
					$this->tradePlatform = TradingPlatform\Landing\Landing::getInstanceByCode($item['CODE']);
				}
			}
		}

		return $this->tradePlatform;
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		return TradingPlatform\OrderTable::update($primary, $data);
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		return TradingPlatform\OrderTable::add($data);
	}

	/**
	 * @return null|string
	 * @internal
	 *
	 */
	public static function getEntityEventName()
	{
		return 'SaleTradeBindingEntity';
	}

	/**
	 * @param $name
	 * @param $value
	 * @return void
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setFieldNoDemand($name, $value)
	{
		parent::setFieldNoDemand($name, $value);

		if ($name === 'TRADING_PLATFORM_ID')
		{
			$this->tradePlatform = null;
		}
	}

	protected function onFieldModify($name, $oldValue, $value)
	{
		$result = parent::onFieldModify($name, $oldValue, $value);
		if (!$result->isSuccess())
		{
			return $result;
		}

		if ($name === 'TRADING_PLATFORM_ID')
		{
			$this->tradePlatform = null;
		}

		return $result;
	}
}