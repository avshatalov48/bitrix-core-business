<?php

namespace Bitrix\Im\V2\Pull\Event;

use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Pull\Dto\Diff;

trait DialogIdFiller
{
	abstract public function getTarget(): ?Chat;

	protected function getDiffByUser(int $userId): Diff
	{
		return new Diff($userId, ['dialogId' => $this->getTarget()?->getDialogId($userId)]);
	}
}