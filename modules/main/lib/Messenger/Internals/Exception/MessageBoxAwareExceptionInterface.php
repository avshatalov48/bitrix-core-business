<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Internals\Exception;

use Bitrix\Main\Messenger\Entity\MessageBox;

interface MessageBoxAwareExceptionInterface
{
	public function getMessageBox(): MessageBox;
}
