<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto\Member;

use Bitrix\Socialnetwork\Collab\Controller\Dto\AbstractBaseDto;

class LeaveMemberDto extends AbstractBaseDto
{
	public function __construct(
		public readonly ?int   $groupId = null,
	)
	{

	}
}