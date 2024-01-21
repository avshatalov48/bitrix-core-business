<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class GetIBlockByIdResponse extends Response
{
	public function getIBlock(): array
	{
		return $this->data['iBlock'] ?? [];
	}

	public function setIBlock(array $iBlock): static
	{
		$this->data['iBlock'] = $iBlock;

		return $this;
	}

	public function getFieldById(string $fieldId)
	{
		$iBlock = $this->data['iBlock'] ?? [];

		return $iBlock[$fieldId] ?? null;
	}
}
