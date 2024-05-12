<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class UpdateIBlockElementResponse extends Response
{
	public function setIsSuccessUpdate(bool $isSuccessUpdate): static
	{
		$this->data['isSuccessUpdate'] = $isSuccessUpdate;

		return $this;
	}

	public function getIsSuccessUpdate(): bool
	{
		$isSuccessUpdate = $this->data['isSuccessUpdate'] ?? false;

		return is_bool($isSuccessUpdate) ? $isSuccessUpdate : false;
	}
}
