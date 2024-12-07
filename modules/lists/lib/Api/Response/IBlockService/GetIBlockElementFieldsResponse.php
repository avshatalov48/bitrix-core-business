<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class GetIBlockElementFieldsResponse extends Response
{
	public function getFields(): array
	{
		return $this->data['fields'] ?? [];
	}

	public function setFields(array $fields): static
	{
		$this->data['fields'] = $fields;

		return $this;
	}

	public function getProps(): array
	{
		return $this->data['props'] ?? [];
	}

	public function setProps(array $props): static
	{
		$this->data['props'] = $props;

		return $this;
	}

	public function setAll(array $all): static
	{
		$this->data['all'] = $all;

		return $this;
	}

	public function getAll()
	{
		return $this->data['all'] ?? [];
	}
}
