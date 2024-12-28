<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Main\Validation\Rule\NotEmpty;
use Bitrix\Main\Validation\Rule\PositiveNumber;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Socialnetwork\Permission\GroupAccessController;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessController;
use Bitrix\Socialnetwork\Control\Command\ValueObject\Features;
use Bitrix\Socialnetwork\Control\Command\ValueObject\FeaturesPermissions;
use Bitrix\Socialnetwork\Control\Command\ValueObject\SiteIds;
use Bitrix\Socialnetwork\Control\Command\ValueObject\SubjectId;
use Bitrix\Socialnetwork\Control\Command\Attribute\AccessCode;
use Bitrix\Socialnetwork\Control\Enum\ViewMode;
use Bitrix\Socialnetwork\Control\Mapper\Field\AvatarMapper;
use Bitrix\Socialnetwork\Control\Mapper\Field\ViewModeMapper;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Map;
use Bitrix\Socialnetwork\Control\Mapper\Field\DepartmentMapper;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

/**
 * @method self setOwnerId(int $ownerId)
 * @method int getOwnerId()
 * @method self setName(string $name)
 * @method string getName()
 * @method self setDescription(string $description)
 * @method string getDescription()
 * @method self setViewMode(ViewMode $viewMode)
 * @method ViewMode getViewMode()
 * @method self setAvatarId(int $avatarId)
 * @method null|int getAvatarId()
 * @method self setAvatarColor(string $avatarColor)
 * @method null|string getAvatarColor()
 * @method self setFeatures(Features $features)
 * @method Features getFeatures()
 * @method self setPermissions(array $permissions)
 * @method FeaturesPermissions getPermissions()
 * @method self setSiteIds(SiteIds $siteIds)
 * @method SiteIds getSiteIds()
 * @method self setInitiatePermissions(string $initiatePermissions)
 * @method string getInitiatePermissions()
 * @method self setSpamPermissions(string $spamPermissions)
 * @method string getSpamPermissions()
 * @method self setType(Type $type)
 * @method Type getType()
 * @method self setSubjectId(SubjectId $subjectId)
 * @method SubjectId getSubjectId()
 * @method self setMembers(array $members)
 * @method null|array getMembers()
 * @method self setInvitedMembers(?array $members)
 * @method null|array getInvitedMembers()
 * @method self setModeratorMembers(?array $members)
 * @method null|array getModeratorMembers()
 */

#[AccessController(GroupAccessController::class)]
class AddCommand extends InitiatedCommand implements DefaultValueCommandInterface
{
	#[PositiveNumber]
	protected int $ownerId;

	#[NotEmpty]
	#[Map('NAME')]
	protected string $name;

	#[Map('DESCRIPTION')]
	protected ?string $description;

	#[Map('VISIBLE', ViewModeMapper::class)]
	#[Map('OPENED', ViewModeMapper::class)]
	protected ViewMode $viewMode = ViewMode::OPEN;

	#[Map('IMAGE_ID', AvatarMapper::class)]
	protected ?string $avatarId;

	#[NotEmpty]
	protected ?string $avatarColor;

	#[Validatable]
	protected Features $features;

	protected FeaturesPermissions $permissions;

	#[Validatable]
	#[Map('SITE_ID')]
	protected SiteIds $siteIds;

	#[NotEmpty]
	#[Map('INITIATE_PERMS')]
	protected string $initiatePermissions = SONET_ROLES_USER;

	#[NotEmpty]
	#[Map('SPAM_PERMS')]
	protected string $spamPermissions = SONET_ROLES_USER;

	#[Map('TYPE')]
	protected Type $type = Type::Group;

	#[Validatable]
	#[Map('SUBJECT_ID')]
	protected SubjectId $subjectId;

	#[AccessCode]
	// #[Map('MEMBERS', MemberMapper::class)]
	#[Map('UF_SG_DEPT', DepartmentMapper::class)]
	protected ?array $members;

	#[AccessCode]
	protected ?array $invitedMembers;

	#[AccessCode]
	protected ?array $moderatorMembers;
}
