<?php

namespace Bitrix\Vote\Controller\Filter;

use Bitrix\Vote\Model\Dto\AttachedVotePayload;

class CheckAttachReadAccessWithSign extends CheckAttachReadAccess
{
	protected function hasAccess(AttachedVotePayload $payload): bool
	{
		return $payload->readAccessThroughSignedParam || parent::hasAccess($payload);
	}
}