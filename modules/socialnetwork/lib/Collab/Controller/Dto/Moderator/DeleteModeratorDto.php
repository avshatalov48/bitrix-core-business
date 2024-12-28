<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto\Moderator;

use Bitrix\Socialnetwork\Collab\Controller\Dto\AbstractBaseDto;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\MetaType;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\PropertyMetaType;

class DeleteModeratorDto extends AbstractBaseDto
{
	public function __construct(
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array $moderatorMembers = null,
		public readonly ?int   $groupId = null,
	)
	{

	}
}