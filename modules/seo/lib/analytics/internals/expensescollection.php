<?php

namespace Bitrix\Seo\Analytics\Internals;

use Bitrix\Main\Type\Dictionary;

/**
 * Dictionary for work with Expenses objects
 */
final class ExpensesCollection extends Dictionary
{
	/**
	 * @var Expenses[]
	 */
	protected $values = [];

	/**
	 * @param $name
	 * @param Expenses | null $value
	 *
	 * @return void
	 */
	public function set($name, $value = null)
	{
		if ($value instanceof Expenses)
		{
			parent::set($name, $value);
		}
	}

	/**
	 * @param Expenses $value
	 *
	 * @return self
	 */
	public function addItem(Expenses $value): self
	{
		$this->values[] = $value;

		return $this;
	}

	/**
	 * Returns the values as an array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$data = [];

		foreach ($this->values as $expenses)
		{
			$data[] = $expenses->toArray();
		}

		return $data;
	}
}
