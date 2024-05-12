<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Main\Result;

class IBlockElementToUpdateValues extends Result
{
	public function setElementData(array $elementData): static
	{
		$this->data['element'] = $elementData;

		return $this;
	}

	public function getElementData(): ?array
	{
		$element = $this->data['element'] ?? null;

		return is_array($element) ? $element : null;
	}

	public function setHasChangedFields(bool $has): static
	{
		$this->data['hasChangedFields'] = $has;

		return $this;
	}

	public function getHasChangedFields(): bool
	{
		$has = $this->data['hasChangedFields'] ?? false;

		return is_bool($has) ? $has : false;
	}

	public function setHasChangedProps(bool $has): static
	{
		$this->data['hasChangedProps'] = $has;

		return $this;
	}

	public function getHasChangedProps(): bool
	{
		$has = $this->data['hasChangedProps'] ?? false;

		return is_bool($has) ? $has : false;
	}
}
