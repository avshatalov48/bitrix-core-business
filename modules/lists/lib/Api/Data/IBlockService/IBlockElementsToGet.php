<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

class IBlockElementsToGet
{
	private const LIMIT = 5;

	private IBlockElementFilter $filter;
	private array $order = [];
	private array $navigation = [];
	private array $selectFields = ['ID', 'NAME'];
	private bool $isCheckPermissionsEnabled = true;
	private bool $isNeedLoadWorkflowState = false;

	public function __construct(
		IBlockElementFilter $filter,
		array $order = [],
		int $offset = 0,
		int $limit = self::LIMIT,
		array $additionalSelectFields = [],
	)
	{
		$this->filter = $filter;
		$this->setOrder($order);
		$this->setNavigation($offset, $limit);
		$this->setAdditionalSelectFields($additionalSelectFields);
	}

	private function setOrder(array $order): void
	{
		$allowedFields = ['ID'];

		$newOrder = [];
		foreach ($order as $field => $value)
		{
			if (
				in_array($field, $allowedFields, true)
				&& in_array(mb_strtolower((string)$value), ['asc', 'desc'], true)
			)
			{
				$newOrder[$field] = $value;
			}
		}

		$this->order = $newOrder;
	}

	private function setNavigation(int $offset, int $limit): void
	{
		$this->navigation = [
			'nPageSize' => $limit >= 0 ? $limit : self::LIMIT,
			'iNumPage' =>
				($limit > 0 && $offset > 0)
					? (int)($offset / $limit) + 1
					: 1
			,
		];
	}

	private function setAdditionalSelectFields(array $additionalSelectFields): void
	{
		$additionalAllowedFields = ['IBLOCK_ID', 'IBLOCK_SECTION_ID', 'IBLOCK_TYPE_ID', 'FIELDS', 'PROPS'];

		$fields = [];
		foreach ($additionalSelectFields as $fieldId)
		{
			if (in_array($fieldId, $additionalAllowedFields, true))
			{
				$fields[] = $fieldId;
			}
		}

		$this->selectFields = array_merge($this->selectFields, $fields);
	}

	public function getFilter(): IBlockElementFilter
	{
		return $this->filter;
	}

	public function getOrmFilter(): array
	{
		return $this->filter->getOrmFilter();
	}

	public function getOrder(): array
	{
		return $this->order;
	}

	public function getNavigation(): array
	{
		return $this->navigation;
	}

	public function getSelect(): array
	{
		$shouldLoadFields = $this->isNeedLoadFields();
		$shouldLoadProps = $this->isNeedLoadProps();

		if (!$shouldLoadFields && !$shouldLoadProps)
		{
			return $this->selectFields;
		}

		$select = array_filter(
			$this->selectFields,
			static fn($fieldId) => !in_array($fieldId, ['FIELDS', 'PROPS'], true)
		);

		if ($shouldLoadFields && $this->getFilter()->hasField('IBLOCK_ID'))
		{
			$list = new \CList($this->filter->getFieldValue('IBLOCK_ID'));
			foreach ($list->GetFields() as $fieldId => $property)
			{
				if ($list->is_field($fieldId))
				{
					$select[] = $fieldId;
				}
			}

			$select = array_unique($select);
		}

		return array_values($select);
	}

	public function isNeedLoadProps(): bool
	{
		return in_array('PROPS', $this->selectFields, true);
	}

	public function isNeedLoadFields(): bool
	{
		return in_array('FIELDS', $this->selectFields, true);
	}

	public function enableCheckPermissions(): static
	{
		$this->isCheckPermissionsEnabled = true;

		return $this;
	}

	public function disableCheckPermissions(): static
	{
		$this->isCheckPermissionsEnabled = false;

		return $this;
	}

	public function isCheckPermissionsEnabled(): bool
	{
		return $this->isCheckPermissionsEnabled;
	}

	public function setIsNeedLoadWorkflowStateInfo(bool $flag = true): static
	{
		$this->isNeedLoadWorkflowState = $flag;

		return $this;
	}

	public function isNeedLoadWorkflowState(): bool
	{
		return $this->isNeedLoadWorkflowState;
	}
}
