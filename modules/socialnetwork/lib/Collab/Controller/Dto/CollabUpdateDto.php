<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Controller\Dto;

use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\MetaType;
use Bitrix\Socialnetwork\Collab\Controller\Meta\Attribute\PropertyMetaType;

class CollabUpdateDto extends AbstractBaseDto
{
	public function __construct(
		#[PositiveNumber]
		public readonly ?int    $id = null,
		public readonly ?int    $ownerId = null,
		public readonly ?string $name = null,
		public readonly ?string $description = null,
		public readonly ?string $viewMode = null,
		public readonly ?string $avatarId = null,
		public readonly ?string $avatarColor = null,
		public readonly ?array  $features = null,
		public readonly ?array  $permissions = null,
		public readonly ?string $siteId = null,
		public readonly ?string $initiatePermissions = null,
		public readonly ?string $spamPermissions = null,
		public readonly ?int    $subjectId = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $addMembers = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $deleteMembers = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $addInvitedMembers = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $deleteInvitedMembers = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $addModeratorMembers = null,
		#[MetaType(PropertyMetaType::MemberSelectorCodes)]
		public readonly ?array  $deleteModeratorMembers = null,
		public readonly ?array  $options = null,
	)
	{

	}
}