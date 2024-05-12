<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class UpdateElementResponse extends Response
{
	public function setIsSuccessElementUpdate(bool $isSuccessElementUpdate): static
	{
		$this->data['isSuccessElementUpdate'] = $isSuccessElementUpdate;

		return $this;
	}

	public function getIsSuccessElementUpdate(): bool
	{
		$isSuccessElementUpdate = $this->data['isSuccessElementUpdate'] ?? false;

		return is_bool($isSuccessElementUpdate) ? $isSuccessElementUpdate : false;
	}
}
