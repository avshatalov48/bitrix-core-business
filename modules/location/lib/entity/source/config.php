<?php

namespace Bitrix\Location\Entity\Source;

/**
 * Class Config
 * @package Bitrix\Location\Entity\Source
 * @internal
 */
final class Config implements \IteratorAggregate
{
	/** @var ConfigItem[] */
	private $items = [];

	/**
	 * @param ConfigItem $item
	 * @return $this
	 */
	public function addItem(ConfigItem $item)
	{
		$this->items[] = $item;

		return $this;
	}

	/**
	 * @param string $code
	 * @return mixed|null
	 */
	public function getValue(string $code)
	{
		foreach ($this->items as $item)
		{
			if ($item->getCode() !== $code)
			{
				continue;
			}

			return $item->getValue();
		}

		return null;
	}

	/**
	 * @param string $code
	 * @param mixed $value
	 * @return bool
	 */
	public function setValue(string $code, $value): bool
	{
		foreach ($this->items as $item)
		{
			if ($item->getCode() !== $code)
			{
				continue;
			}

			$item->setValue($value);

			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getIterator()
	{
		usort(
			$this->items,
			function (ConfigItem $item1, ConfigItem $item2)
			{
				if ($item1->getSort() == $item2->getSort())
				{
					return 0;
				}

				return ($item1->getSort() < $item2->getSort()) ? -1 : 1;
			}
		);

		return new \ArrayIterator($this->items);
	}
}
