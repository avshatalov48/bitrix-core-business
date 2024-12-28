<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto\Member;

use Bitrix\Socialnetwork\Collab\Controller\Dto\AbstractBaseDto;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\MetaType;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\PropertyMetaType;

class DeleteMemberDto extends AbstractBaseDto
{
	public function __construct(
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array $members = null,
		public readonly ?int   $groupId = null,
	)
	{

	}
}