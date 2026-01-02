<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity\ProcessingParam;

use Bitrix\Main\Messenger\Entity\MessageBox;

interface ProcessingParamInterface
{
	public function applyTo(MessageBox $messageBox): MessageBox;
}
