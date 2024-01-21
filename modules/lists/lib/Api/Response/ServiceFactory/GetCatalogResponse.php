<?php

namespace Bitrix\Lists\Api\Response\ServiceFactory;

use Bitrix\Lists\Api\Response\Response;

class GetCatalogResponse extends Response
{
	public function getCatalog()
	{
		return $this->data['catalog'] ?? [];
	}

	public function setCatalog(array $catalog): static
	{
		$this->data['catalog'] = $catalog;

		return $this;
	}
}
