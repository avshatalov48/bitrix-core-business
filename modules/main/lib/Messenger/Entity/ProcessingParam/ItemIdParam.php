<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity\ProcessingParam;

use Bitrix\Main\Messenger\Entity\MessageBox;

class ItemIdParam implements ProcessingParamInterface
{
	public function __construct(public readonly int|string $itemId)
	{
	}

	public function applyTo(MessageBox $messageBox): MessageBox
	{
		$messageBox->setItemId($this->itemId);

		return $messageBox;
	}
}
