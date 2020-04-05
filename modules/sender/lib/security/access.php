<?php

namespace Bitrix\Sender\Security;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Security\Role\Permission;

Loc::loadMessages(__FILE__);

/**
 * Class Access
 *
 * @package Bitrix\Sender\Security
 */
class Access
{
	/** @var  User $user User. */
	protected $user;

	/** @var array $permissions Permissions. */
	protected $permissions;

	/**
	 * Get Access instance for current user.
	 *
	 * @return static
	 */
	public static function current()
	{
		return new static(User::current());
	}

	/**
	 * PageAccess constructor.
	 *
	 * @param User $user User instance.
	 */
	public function __construct(User $user)
	{
		$this->user = $user;
		$this->permissions = Permission::getByUserId($this->user->getId());
	}

	/**
	 * Return true if can view anything.
	 *
	 * @return bool
	 */
	public function canViewAnything()
	{
		return (
			$this->canViewLetters()
			||
			$this->canViewAds()
			||
			$this->canViewRc()
			||
			$this->canViewSegments()
		);
	}

	/**
	 * Return true if can modify settings.
	 *
	 * @return bool
	 */
	public function canModifySettings()
	{
		return $this->canPerform(Permission::ENTITY_SETTINGS, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 */
	public function canViewLetters()
	{
		return $this->canPerform(Permission::ENTITY_LETTER, Permission::ACTION_VIEW);
	}

	/**
	 * Return true if can modify letters.
	 *
	 * @return bool
	 */
	public function canModifyLetters()
	{
		return $this->canPerform(Permission::ENTITY_LETTER, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view letters.
	 *
	 * @return bool
	 */
	public function canViewAds()
	{
		return $this->canPerform(Permission::ENTITY_AD, Permission::ACTION_VIEW);
	}

	/**
	 * Return true if can modify letters.
	 *
	 * @return bool
	 */
	public function canModifyAds()
	{
		return $this->canPerform(Permission::ENTITY_AD, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view return customer tools.
	 *
	 * @return bool
	 */
	public function canViewRc()
	{
		return $this->canPerform(Permission::ENTITY_RC, Permission::ACTION_VIEW);
	}

	/**
	 * Return true if can modify return customer tools.
	 *
	 * @return bool
	 */
	public function canModifyRc()
	{
		return $this->canPerform(Permission::ENTITY_RC, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view segments.
	 *
	 * @return bool
	 */
	public function canViewSegments()
	{
		return $this->canPerform(Permission::ENTITY_SEGMENT, Permission::ACTION_VIEW);
	}

	/**
	 * Return true if can modify segments.
	 *
	 * @return bool
	 */
	public function canModifySegments()
	{
		return $this->canPerform(Permission::ENTITY_SEGMENT, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view blacklist.
	 *
	 * @return bool
	 */
	public function canViewStart()
	{
		return $this->canModifyRc() || $this->canModifyLetters() || $this->canModifyAds();
	}

	/**
	 * Return true if can view blacklist.
	 *
	 * @return bool
	 */
	public function canViewBlacklist()
	{
		return $this->canPerform(Permission::ENTITY_BLACKLIST, Permission::ACTION_VIEW);
	}

	/**
	 * Return true if can modify blacklist.
	 *
	 * @return bool
	 */
	public function canModifyBlacklist()
	{
		return $this->canPerform(Permission::ENTITY_BLACKLIST, Permission::ACTION_MODIFY);
	}

	/**
	 * Return true if can view abuses.
	 *
	 * @return bool
	 */
	public function canViewAbuses()
	{
		return $this->canViewLetters();
	}

	/**
	 * Return true if can modify abuses.
	 *
	 * @return bool
	 */
	public function canModifyAbuses()
	{
		return !Integration\Bitrix24\Service::isCloud() && $this->canModifySegments();
	}

	/**
	 * Returns true if user can perform specified action on the entity.
	 * @param string $entityCode Code of the entity.
	 * @param string $actionCode Code of the action.
	 * @param string $minPerm Code of minimal permission.
	 * @return bool
	 * @throws ArgumentException
	 */
	public function canPerform($entityCode, $actionCode, $minPerm = null)
	{
		if ($this->user->canEdit())
		{
			return true;
		}

		return Role\Permission::check(
			$this->permissions,
			$entityCode,
			$actionCode,
			$minPerm
		);
	}
}