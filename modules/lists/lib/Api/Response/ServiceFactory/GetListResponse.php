<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class GetListResponse extends Response
{
	public function getElements(): ?array
	{
		return $this->data['elements'] ?? null;
	}

	public function setElements(array $elements): static
	{
		$this->data['elements'] = $elements;

		return $this;
	}
}
