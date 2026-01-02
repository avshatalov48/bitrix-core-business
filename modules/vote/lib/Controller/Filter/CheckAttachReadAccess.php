<?php

namespace Bitrix\Vote\Controller\Filter;

use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Vote\Model\Dto\AttachedVotePayload;

class CheckAttachReadAccess extends Base
{
	public function onBeforeAction(Event $event): ?EventResult
	{
		$payload = $this->getPayload();
		if ($payload === null)
		{
			return null;
		}

		if ($payload->attach === null)
		{
			$this->addError(new Error('Attach not found', 'ATTACH_NOT_FOUND'));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		if (!$this->hasAccess($payload))
		{
			$this->addError(new Error('Attach read access denied'));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}

	private function getPayload(): ?AttachedVotePayload
	{
		foreach ($this->getAction()->getArguments() as $argument)
		{
			if ($argument instanceof AttachedVotePayload)
			{
				return $argument;
			}
		}

		return null;
	}

	protected function hasAccess(AttachedVotePayload $payload): bool
	{
		return $payload->attach->canRead((int)CurrentUser::get()->getId());
	}
}