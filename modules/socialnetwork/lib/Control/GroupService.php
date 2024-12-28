<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control;

use Bitrix\Socialnetwork\Control\Handler\Add;
use Bitrix\Socialnetwork\Control\Handler\Update;

class GroupService extends AbstractGroupService
{
	protected function getAddHandlers(): array
	{
		return [new Add\AddInvitationHandler(), new Add\AddFeatureHandler()];
	}

	protected function getUpdateHandlers(): array
	{
		return [new Update\ExcludeMemberHandler(), new Update\AddInvitationHandler()];
	}

	protected function getDeleteHandlers(): array
	{
		return [];
	}
}