<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Socialnetwork\Control\Command\ValueObject\FeaturesPermissions;
use Bitrix\Socialnetwork\Control\Mapper\Field\AvatarMapper;
use Bitrix\Socialnetwork\Control\Mapper\Field\DepartmentMapper;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessCode;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

/**
 * @method self setOwnerId(int $ownerId)
 * @method int getOwnerId()
 * @method self setId(int $id)
 * @method int getId()
 * @method self setInitiatorId(int $initiatorId)
 * @method int getInitiatorId()
 * @method null|string getName()
 * @method self setName(string $name)
 * @method self setAvatarId(int $avatarId)
 * @method null|int getAvatarId()
 * @method self setPermissions(FeaturesPermissions $permissions)
 * @method null|FeaturesPermissions getPermissions()
 * @method self setAddMembers(?array $members)
 * @method null|array getAddMembers()
 * @method self setAddInvitedMembers(?array $members)
 * @method null|array getAddInvitedMembers()
 * @method self setAddModeratorMembers(?array $members)
 * @method null|array getAddModeratorMembers()
 * @method self setDeleteMembers(?array $members)
 * @method null|array getDeleteMembers()
 * @method self setDeleteInvitedMembers(?array $members)
 * @method null|array getDeleteInvitedMembers()
 * @method self setDeleteModeratorMembers(?array $members)
 * @method null|array getDeleteModeratorMembers()
 * @method self setDescription(string $description)
 * @method null|string getDescription()
 */

#[AccessController(GroupAccessController::class)]
class UpdateCommand extends InitiatedCommand
{
	#[PositiveNumber]
	protected int $id;

	#[PositiveNumber]
	protected ?int $ownerId;

	#[Map('NAME')]
	#[NotEmpty]
	protected ?string $name;

	#[Map('DESCRIPTION')]
	protected ?string $description;

	#[Map('IMAGE_ID', AvatarMapper::class)]
	protected ?string $avatarId;

	protected ?FeaturesPermissions $permissions;

	#[Map('TYPE')]
	protected ?Type $type;

	#[AccessCode]
	#[Map('UF_SG_DEPT', DepartmentMapper::class)]
	protected ?array $addMembers;

	#[AccessCode]
	protected ?array $addInvitedMembers;

	#[AccessCode]
	protected ?array $addModeratorMembers;

	#[AccessCode]
	protected ?array $deleteMembers;

	#[AccessCode]
	protected ?array $deleteInvitedMembers;

	#[AccessCode]
	protected ?array $deleteModeratorMembers;
}
