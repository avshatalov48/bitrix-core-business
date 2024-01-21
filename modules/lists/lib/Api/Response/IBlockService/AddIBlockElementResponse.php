<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

use Bitrix\Lists\Api\Response\Response;

class AddIBlockElementResponse extends Response
{
	public function setId(int $id): static
	{
		$this->data['id'] = $id;

		return $this;
	}

	public function getId(): ?int
	{
		$id = $this->data['id'] ?? null;

		return is_int($id) && $id !== 0 ? $id : null;
	}
}
