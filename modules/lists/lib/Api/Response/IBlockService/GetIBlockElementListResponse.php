<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class GetIBlockElementListResponse extends Response
{
	public function getElements(): array
	{
		return $this->data['elements'] ?? [];
	}

	public function hasElements(): bool
	{
		return count($this->getElements()) > 0;
	}

	public function setElements(array $elements): static
	{
		$this->data['elements'] = $elements;

		return $this;
	}

	public function getFirstElement(): ?array
	{
		if ($this->hasElements())
		{
			return $this->getElements()[0];
		}

		return null;
	}
}
