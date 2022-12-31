<?php

namespace Bitrix\Sale;

use Bitrix\Main;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Sale\Reservation\BasketReservationService;

/**
 * @method ReserveQuantityCollection getCollection()
 */
class ReserveQuantity extends Internals\CollectableEntity
{
	/**
	 * @return string
	 */
	public static function getRegistryType()
	{
		return Registry::REGISTRY_TYPE_ORDER;
	}

	public static function getRegistryEntity()
	{
		return Registry::ENTITY_BASKET_RESERVE_COLLECTION_ITEM;
	}

	/**
	 * @return array
	 */
	public static function getAvailableFields()
	{
		return [
			'QUANTITY', 'BASKET_ID', 'STORE_ID',
			'DATE_RESERVE', 'DATE_RESERVE_END', 'RESERVED_BY',
		];
	}

	/**
	 * @return array
	 */
	protected static function getMeaningfulFields()
	{
		return [];
	}

	/**
	 * @return array
	 */
	protected static function getFieldsMap()
	{
		return Reservation\Internals\BasketReservationTable::getMap();
	}

	/**
	 * @param ReserveQuantityCollection $collection
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	public static function create(ReserveQuantityCollection $collection): ReserveQuantity
	{
		$basketItem = $collection->getBasketItem();
		if (!$basketItem->isReservableItem())
		{
			throw new Main\SystemException('Basket item is not available for reservation');
		}

		$fields = [
			'STORE_ID' => Configuration::getDefaultStoreId()
		];

		if ($basketItem->getId() > 0)
		{
			$fields['BASKET_ID'] = $basketItem->getId();
		}

		$reservedItem = static::createEntityObject($fields);
		$reservedItem->setCollection($collection);

		return $reservedItem;
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
		$entityClassName = $registry->get(static::getRegistryEntity());

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
	public static function loadForBasketItem(int $id)
	{
		if ($id <= 0)
		{
			throw new Main\ArgumentNullException('id');
		}

		$entityList = [];

		$registry = Registry::getInstance(static::getRegistryType());

		/** @var ReserveQuantityCollection $reserveCollection */
		$reserveCollection = $registry->get(Registry::ENTITY_BASKET_RESERVE_COLLECTION);
		$dbRes = $reserveCollection::getList([
			'filter' => [
				'=BASKET_ID' => $id,
			]
		]);

		while ($data = $dbRes->fetch())
		{
			$entityList[] = static::createEntityObject($data);
		}

		return $entityList;
	}

	public function getQuantity() : float
	{
		return (float)$this->getField('QUANTITY');
	}

	public function setQuantity($quantity) : Result
	{
		return $this->setField('QUANTITY', $quantity);
	}

	public function getStoreId() : int
	{
		return (int)$this->getField('STORE_ID');
	}

	public function setStoreId(int $storeId) : Result
	{
		return $this->setField('STORE_ID', $storeId);
	}

	protected function onFieldModify($name, $oldValue, $value)
	{
		if ($name === 'QUANTITY')
		{
			$collection = $this->getCollection();

			if ($collection->getQuantity() > $collection->getBasketItem()->getQuantity())
			{
				$result = new Result();

				return $result->addError(
					new Main\Error(
						Main\Localization\Loc::getMessage('SALE_RESERVE_QUANTITY_EXCEEDING_ERROR')
					)
				);
			}

			$result = Internals\Catalog\Provider::tryReserve($this);
			if (!$result->isSuccess())
			{
				return $result;
			}
		}
		else if (
			$name === 'STORE_ID'
			&& $this->getQuantity() > 0
		)
		{
			throw new Main\SystemException(
				Main\Localization\Loc::getMessage('SALE_RESERVE_QUANTITY_CHANGE_STORE_ERROR')
			);
		}

		return parent::onFieldModify($name, $oldValue, $value);
	}

	/**
	 * @param array $values
	 * @return array
	 */
	protected function onBeforeSetFields(array $values)
	{
		if (isset($values['QUANTITY']))
		{
			$quantity = $values['QUANTITY'];

			// move to the end of array
			unset($values['QUANTITY']);
			$values['QUANTITY'] = $quantity;
		}

		return $values;
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
		}
		else
		{
			if (!$this->getField('BASKET_ID'))
			{
				$basketItem = $this->getCollection()->getBasketItem();

				$fields['BASKET_ID'] = $basketItem->getId();
				$this->setFieldNoDemand('BASKET_ID', $fields['BASKET_ID']);
			}

			if (!$this->getField('DATE_RESERVE_END'))
			{
				$reserveClearPeriod = Configuration::getProductReserveClearPeriod();
				$defaultDateReserveEnd = (new \Bitrix\Main\Type\Date())->add($reserveClearPeriod . 'D');
				$this->setFieldNoDemand('DATE_RESERVE_END', $defaultDateReserveEnd);
			}

			$this->setFieldNoDemand('DATE_RESERVE', new Main\Type\DateTime());

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
		}

		$result->setId($id);

		return $result;
	}

	public function delete()
	{
		$result = $this->setQuantity(0);
		if (!$result->isSuccess())
		{
			return $result;
		}

		return parent::delete();
	}

	public function deleteNoDemand()
	{
		return parent::delete();
	}

	/**
	 * @param $primary
	 * @param array $data
	 * @return Main\Entity\UpdateResult
	 * @throws \Exception
	 */
	protected function updateInternal($primary, array $data)
	{
		/** @var BasketReservationService */
		$service = ServiceLocator::getInstance()->get('sale.basketReservation');
		return $service->update($primary, $data);
	}

	/**
	 * @param array $data
	 * @return Main\Entity\AddResult
	 * @throws \Exception
	 */
	protected function addInternal(array $data)
	{
		/** @var BasketReservationService */
		$service = ServiceLocator::getInstance()->get('sale.basketReservation');
		return $service->add($data);
	}

	public static function getEntityEventName()
	{
		return 'SaleReservedQuantity';
	}
}
