<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Security;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

use Bitrix\Sender\Integration;

Loc::loadMessages(__FILE__);

/**
 * Class User
 * @package Bitrix\Sender\Security
 */
class User
{
	/** @var integer|null $id User ID. */
	protected $id = null;

	/** @var \CUser $object User object. */
	protected $object;

	/** @var Access $access Access. */
	protected $access;

	/** @var static $instanceCurrentUser Instance. */
	protected static $instanceCurrentUser;

	/** @var static $instance Instance. */
	protected static $instance;

	protected static array $cache = [];
	/**
	 * Get current user.
	 *
	 * @return static
	 */
	public static function current()
	{
		if (!static::$instanceCurrentUser)
		{
			static::$instanceCurrentUser = new static();
		}

		return static::$instanceCurrentUser;
	}

	/**
	 * Get user by ID.
	 *
	 * @param integer $id ID.
	 * @return static
	 */
	public static function get($id)
	{
		if (!static::$instance || static::$instance->getId() != $id)
		{
			static::$instance = new static($id);
		}

		return static::$instance;
	}

	/**
	 * User constructor.
	 *
	 * @param integer|null $id ID.
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

	/**
	 * Get current user ID.
	 *
	 * @return integer|null
	 */
	public function getId()
	{
		if ($this->isCurrent())
		{
			return $this->getObject()->getID();
		}

		return $this->id;
	}

	/**
	 * Return true if current user is admin.
	 *
	 * @return bool
	 */
	public function isAdmin()
	{
		if ($this->isCurrent())
		{
			return $this->getObject()->isAdmin();
		}

		return in_array(1, UserTable::getUserGroupIds($this->id));
	}

	/**
	 * Return true if current user has access to one or more module pages.
	 *
	 * @return bool
	 */
	public function hasAccess()
	{
		return $this->getAccess()->canViewAnything();
	}

	/**
	 * Return access instance.
	 *
	 * @return Access
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getAccess()
	{
		if (!$this->access)
		{
			$this->access = Access::getInstance($this);
		}

		return $this->access;
	}

	/**
	 * Return true if current user can view module pages.
	 *
	 * @return bool
	 */
	public function canView()
	{
		if (!$this->isModuleAccessibleOnPortal())
		{
			return false;
		}

		if ($this->isBroadAccess())
		{
			return true;
		}

		if ($this->isPortalAdmin())
		{
			return true;
		}

		if (is_object($GLOBALS['APPLICATION']) && $GLOBALS['APPLICATION']->getGroupRight('sender') !== "D")
		{
			return true;
		}

		return false;
	}

	private function isBroadAccess()
	{
		if ($this->isExtranet())
		{
			return false;
		}

		if (!Integration\Bitrix24\Service::isPortal())
		{
			return false;
		}

		return !Role\Manager::canUse();
	}

	private function isModuleAccessibleOnPortal()
	{
		if (!Integration\Bitrix24\Service::isCloud())
		{
			return true;
		}

		return Option::get('sender', '~is_accessible_on_portal', 'Y') === 'Y';
	}

	/**
	 * Return true if current user can edit on module pages.
	 *
	 * @return bool
	 */
	public function canEdit()
	{
		if (!$this->isModuleAccessibleOnPortal())
		{
			return false;
		}

		if ($this->isBroadAccess())
		{
			return true;
		}

		if ($this->isPortalAdmin())
		{
			return true;
		}

		if (is_object($GLOBALS['APPLICATION']) && $GLOBALS['APPLICATION']->getGroupRight('sender') === "W")
		{
			return true;
		}

		return false;
	}

	/**
	 * Return true if current user is portal admin.
	 *
	 * @return bool
	 */
	public function isPortalAdmin()
	{
		if (!Integration\Bitrix24\Service::isPortal() && !Integration\Bitrix24\Service::isCloud())
		{
			return $this->isAdmin();
		}

		return $this->getObject()->canDoOperation('bitrix24_config', $this->id);
	}

	public function isExtranet()
	{
		if(!$this->isConfigured())
		{
			return false;
		}

		if(array_key_exists($this->getId(), static::$cache))
		{
			return static::$cache[$this->getId()];
		}

		$result = !\CExtranet::IsIntranetUser(SITE_ID, $this->getId());

		static::$cache[$this->getId()] = $result;

		return $result;
	}

	private function isConfigured()
	{
		return Loader::includeModule('extranet') && $this->getExtranetSiteID();
	}

	private function getExtranetSiteID()
	{
		$extranet_site_id = \COption::GetOptionString("extranet", "extranet_site");
		if (
			($extranet_site_id !== '')
			&& \CSite::GetArrayByID($extranet_site_id)
		)
		{
			return $extranet_site_id;
		}

		return false;
	}

	/**
	 * Return true if user accepted agreement.
	 *
	 * @return bool
	 */
	public function isAgreementAccepted()
	{
		if (!Integration\Bitrix24\Service::isCloud())
		{
			return true;
		}

		return Agreement::isAcceptedByUser($this->getId());
	}

	/**
	 * Return true if current user can edit php.
	 *
	 * @return bool
	 */
	public function canEditPhp()
	{
		if (Integration\Bitrix24\Service::isCloud())
		{
			return false;
		}

		return $this->getObject()->canDoOperation('edit_php', $this->id);
	}

	/**
	 * Return true if current user can use LPA.
	 *
	 * @return bool
	 */
	public function canUseLpa()
	{
		if (Integration\Bitrix24\Service::isCloud())
		{
			return false;
		}

		return $this->getObject()->canDoOperation('lpa_template_edit', $this->id);
	}

	/**
	 * Get USER object.
	 *
	 * @return \CUser|null
	 */
	public function getObject()
	{
		if ($this->object)
		{
			return $this->object;
		}

		if ($this->isCurrent())
		{
			$this->object = (is_object($GLOBALS['USER']) && ($GLOBALS['USER'] instanceof \CUser)) ? $GLOBALS['USER'] : null;
		}

		if (!$this->object)
		{
			$this->object = new \CUser();
		}

		return $this->object;
	}

	private function isCurrent()
	{
		return !$this->id;
	}
}