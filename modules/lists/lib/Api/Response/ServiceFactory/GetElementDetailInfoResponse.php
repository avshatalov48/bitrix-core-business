<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class GetElementDetailInfoResponse extends Response
{
	public function setInfo(array $info): static
	{
		$this->data['info'] = $info;

		return $this;
	}

	public function getInfo(): array
	{
		return $this->data['info'] ?? [];
	}

	public function hasInfo(): bool
	{
		return array_key_exists('info', $this->data);
	}
}
