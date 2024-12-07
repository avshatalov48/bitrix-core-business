<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class GetElementUrlResponse extends Response
{
	public function setUrl(string $url): static
	{
		$this->data['url'] = $url;

		return $this;
	}

	public function getUrl(): ?string
	{
		$url = $this->data['url'] ?? null;

		return is_string($url) ? $url : null;
	}
}
