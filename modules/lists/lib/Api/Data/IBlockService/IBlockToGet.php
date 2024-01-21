<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

class IBlockToGet
{
	private IBlockListFilter $filter;
	private array $order = ['SORT' => 'ASC'];
	private bool $needCheckPermissions = true;

	public function __construct(
		IBlockListFilter $filter,
		array $order = null,
	)
	{
		$this->filter = $filter;
		if ($order)
		{
			$this->setOrder($order);
		}
	}

	public function getFilter(): IBlockListFilter
	{
		return $this->filter;
	}

	public function getOrmFilter(): array
	{
		return $this->filter->getOrmFilter();
	}

	public function setOrder(array $order): static
	{
		$allowedFields = ['SORT', 'NAME'];
		$newOrder = [];

		foreach ($order as $field => $sort)
		{
			if (in_array($field, $allowedFields, true))
			{
				$newOrder[$field] = $sort;
			}
		}

		$this->order = $newOrder;

		return $this;
	}

	public function getOrder(): array
	{
		return $this->order;
	}

	public function enableCheckPermissions(): static
	{
		$this->needCheckPermissions = true;

		return $this;
	}

	public function disableCheckPermissions(): static
	{
		$this->needCheckPermissions = false;

		return $this;
	}

	public function needCheckPermissions(): bool
	{
		return $this->needCheckPermissions;
	}
}
