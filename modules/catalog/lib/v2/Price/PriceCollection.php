<?php

namespace Bitrix\Catalog\v2\Price;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Main\InvalidOperationException;

/**
 * Class PriceCollection
 *
 * @package Bitrix\Catalog\v2\Price
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class PriceCollection extends BaseCollection
{
	/** @var \Bitrix\Catalog\v2\Price\PriceFactory */
	protected $factory;

	public function __construct(PriceFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @param \Bitrix\Catalog\v2\BaseEntity|\Bitrix\Catalog\v2\Price\HasPriceCollection|null $parent
	 * @return \Bitrix\Catalog\v2\BaseCollection
	 */
	public function setParent(?BaseEntity $parent): BaseCollection
	{
		parent::setParent($parent);

		if ($parent)
		{
			if (!($parent instanceof HasPriceCollection))
			{
				throw new InvalidOperationException(sprintf(
					'Parent entity must implement {%s} interface',
					HasPriceCollection::class
				));
			}

			$parent->setPriceCollection($this);
		}

		return $this;
	}

	public function findByGroupId(int $groupId): ?BasePrice
	{
		/** @var \Bitrix\Catalog\v2\Price\BasePrice $price */
		foreach ($this->getIterator() as $price)
		{
			if ($price->getGroupId() === $groupId)
			{
				return $price;
			}
		}

		return null;
	}

	public function create(): BasePrice
	{
		$price = $this->factory->createEntity();

		$this->add($price);

		return $price;
	}

	/**
	 * @param array $values
	 * @return $this
	 */
	public function setValues(array $values): self
	{
		$preparedValues = $this->prepareValues($values);

		foreach ($preparedValues as $id => $fields)
		{
			$price = $this->findByGroupId($id);

			if ($price === null)
			{
				// ToDo make all collections with factory methods?
				$price = $this->create()->setGroupId($id);
			}

			if ($price)
			{
				if (isset($fields['PRICE']))
				{
					$price->setPrice($fields['PRICE']);
				}

				if (isset($fields['CURRENCY']))
				{
					$price->setCurrency($fields['CURRENCY']);
				}
			}
		}

		return $this;
	}

	public function getValues(): array
	{
		$values = [];

		/** @var \Bitrix\Catalog\v2\Price\BasePrice $item */
		foreach ($this->getIterator() as $item)
		{
			$values[$item->getGroupId()] = [
				'PRICE' => $item->getPrice(),
				'CURRENCY' => $item->getCurrency(),
			];
		}

		return $values;
	}

	private function prepareValues(array $values): array
	{
		// ToDo check required properties + QUANTITY_FROM etc
		$prepared = [];

		foreach ($values as $id => $fields)
		{
			if (!is_array($fields))
			{
				if (is_numeric($fields))
				{
					$fields = ['PRICE' => $fields];
				}
				else
				{
					continue;
				}
			}

			$fields = array_intersect_key($fields, ['PRICE' => true, 'CURRENCY' => true]);
			if (!empty($fields))
			{
				if (isset($fields['PRICE']))
				{
					if (empty($fields['PRICE']))
					{
						$fields['PRICE'] = 0;
					}
					elseif (is_numeric($fields['PRICE']))
					{
						$fields['PRICE'] = (float)$fields['PRICE'];
					}
					else
					{
						unset($fields['PRICE']);
					}
				}

				if (isset($fields['CURRENCY']) && !is_string($fields['CURRENCY']))
				{
					unset($fields['CURRENCY']);
				}

			}

			if (!empty($fields))
			{
				if (is_numeric($id))
				{
					$id = (int)$id;
					$prepared[$id] = $fields;
				}
				elseif ($id === 'BASE')
				{
					$basePrice = \CCatalogGroup::GetBaseGroup();

					if (!empty($basePrice['ID']))
					{
						$id = (int)$basePrice['ID'];
						$prepared[$id] = $fields;
					}
				}
			}
		}

		return $prepared;
	}
}