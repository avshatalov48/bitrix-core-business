<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class GetIBlockDefaultFieldsResponse extends Response
{
	public function setDefaultFields(array $fields): static
	{
		$this->data['fields'] = $fields;

		return $this;
	}

	public function getDefaultFields(): array
	{
		$fields = $this->data['fields'] ?? [];

		return is_array($fields) ? $fields : [];
	}
}
