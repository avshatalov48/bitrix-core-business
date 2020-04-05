<?php

namespace Bitrix\Main\Engine;

use CUser;

final class CurrentUser
{
	/** @var CUser */
	private $cuser;

	/**
	 * CurrentUser constructor.
	 * In future when we create User in D7 we can make refactoring and make public the constructor.
	 */
	protected function __construct()
	{
	}

	/**
	 * Returns the fully qualified name of this class.
	 * @return string
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * Returns current user by global $USER.
	 * @return static
	 */
	public static function get()
	{
		global $USER;
		$self = new static();
		$self->cuser = $USER;

		return $self;
	}

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->cuser->getId();
	}

	/**
	 * @return mixed
	 */
	public function getLogin()
	{
		return $this->cuser->getLogin();
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->cuser->getEmail();
	}

	/**
	 * @return mixed
	 */
	public function getFullName()
	{
		return $this->cuser->getFullName();
	}

	/**
	 * @return mixed
	 */
	public function getFirstName()
	{
		return $this->cuser->getFirstName();
	}

	/**
	 * @return mixed
	 */
	public function getLastName()
	{
		return $this->cuser->getLastName();
	}

	/**
	 * @return mixed
	 */
	public function getSecondName()
	{
		return $this->cuser->getSecondName();
	}

	/**
	 * @return array
	 */
	public function getUserGroups()
	{
		return $this->cuser->getUserGroupArray();
	}

	/**
	 * @return string
	 */
	public function getFormattedName()
	{
		return $this->cuser->getFormattedName(false, false);
	}

	/**
	 * @param string $operationName
	 * @return boolean
	 */
	public function canDoOperation($operationName)
	{
		return $this->cuser->canDoOperation($operationName);
	}

	/**
	 * @return boolean
	 */
	public function isAdmin()
	{
		return $this->cuser->isAdmin();
	}
}