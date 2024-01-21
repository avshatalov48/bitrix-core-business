<?php

namespace Bitrix\Lists\Api\Response;

use Bitrix\Lists\Security\ElementRight;
use Bitrix\Lists\Security\IblockRight;
use Bitrix\Lists\Security\RightParam;

class CheckPermissionsResponse extends Response
{
	public function getRightParam(): ?RightParam
	{
		return $this->data['rightParam'] ?? null;
	}

	public function setRightParam(RightParam $rightParam): static
	{
		$this->data['rightParam'] = $rightParam;

		return $this;
	}

	public function getElementRight(): ?ElementRight
	{
		return $this->data['elementRight'] ?? null;
	}

	public function setElementRight(ElementRight $elementRight): static
	{
		$this->data['elementRight'] = $elementRight;

		return $this;
	}

	public function getIBlockRight(): ?IblockRight
	{
		return $this->data['iBlockRight'] ?? null;
	}

	public function setIBlockRight(IblockRight $iBlockRight): static
	{
		$this->data['iBlockRight'] = $iBlockRight;

		return $this;
	}
}
