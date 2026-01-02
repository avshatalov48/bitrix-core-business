<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception;

use Bitrix\Main\Messenger\Entity\MessageBox;

trait MessageBoxAwareExceptionTrait
{
	private MessageBox $messageBox;

	public function getMessageBox(): MessageBox
	{
		return $this->messageBox;
	}
}
