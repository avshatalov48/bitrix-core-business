<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class GetIBlockListResponse extends Response
{
	public function getIBlocks(): array
	{
		return $this->data['blocks'] ?? [];
	}

	public function setIBlocks(array $iBlocks): static
	{
		$this->data['blocks'] = $iBlocks;

		return $this;
	}

	public function hasIBlocks(): bool
	{
		return count($this->getIBlocks()) > 0;
	}
}
