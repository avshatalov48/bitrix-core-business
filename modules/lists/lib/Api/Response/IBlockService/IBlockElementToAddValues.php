<?php

namespace Bitrix\Lists\Api\Response\IBlockService;

class IBlockElementToAddValues extends IBlockElementToUpdateValues
{
	public function getHasChangedFields(): bool
	{
		$has = $this->data['hasChangedFields'] ?? true;

		return is_bool($has) ? $has : true;
	}

	public function getHasChangedProps(): bool
	{
		$has = $this->data['hasChangedProps'] ?? true;

		return is_bool($has) ? $has : true;
	}
}
