<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Command;

use Bitrix\Socialnetwork\Control\Enum\ViewMode;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\AvatarField;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\EnumField;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\Field;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\YesNoField;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\Field\SubjectIdField;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapMany;
use Bitrix\Socialnetwork\Control\Mapper\Attribute\MapOne;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

/**
 * @method self setInitiatorId(int $initiatorId)
 * @method self setOwnerId(int $ownerId)
 * @method self setName(string $name)
 * @method self setViewMode(ViewMode $viewMode)
 * @method self setAvatarId(int $avatarId)
 * @method self setAvatarColor(string $avatarColor)
 * @method self setFeatures(array $features)
 * @method self setSiteId(string $siteId)
 * @method self setInitiatePermissions(string $initiatePermissions)
 * @method self setSpamPermissions(string $spamPermissions)
 * @method self setType(Type $type)
 * @method self setSubjectId(int $subjectId)
 * @method self setMembers(array $members)
 */
class AddCommand extends AbstractCommand
{
	public int $initiatorId;

	public int $ownerId;

	#[MapOne(new Field('NAME'))]
	public string $name;

	#[MapMany(
		new YesNoField('VISIBLE', ViewMode::SECRET),
		new YesNoField('OPENED', ViewMode::OPEN),
	)]
	public ViewMode $viewMode = ViewMode::OPEN;

	#[MapOne(new AvatarField('IMAGE_ID'))]
	public ?int $avatarId = null;

	public ?string $avatarColor = null;

	public ?array $features = null;

	#[MapOne(new Field('SITE_ID'))]
	public string $siteId = SITE_ID;

	#[MapOne(new Field('INITIATE_PERMS'))]
	public string $initiatePermissions = SONET_ROLES_USER;

	#[MapOne(new Field('SPAM_PERMS'))]
	public string $spamPermissions = SONET_ROLES_USER;

	#[MapOne(new EnumField('TYPE'))]
	public Type $type = Type::GROUP;

	#[MapOne(new SubjectIdField('SUBJECT_ID'))]
	public ?int $subjectId = null;

	public ?array $members = null;
}