<?php

namespace Bitrix\Vote\Base;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class BaseObject
{
	/** @var int */
	protected $id;
	/** @var  ErrorCollection */
	protected $errorCollection;

	protected static $userGroups = array();

	public function __construct($id)
	{
		if (!($id > 0))
			throw new \Bitrix\Main\ArgumentNullException("id");
		$this->id = $id;
		$this->errorCollection = new ErrorCollection;
		$this->init();
	}
	/**
	 * exists only for child class
	 * @return void
	 */
	public function init()
	{ }
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * @inheritdoc
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 * @return Error|null
	 */
	public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
	/**
	 * @param int $userId User ID.
	 * @return bool
	 */
	public function canRead($userId)
	{
		$groups = self::loadUserGroups($userId);
		$right = \CMain::getUserRight("vote", $groups);
		return ($right >= "R");
	}

	/**
	 * @param int $userId User ID.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		$groups = self::loadUserGroups($userId);
		$right = \CMain::getUserRight("vote", $groups);
		return ($right >= "W");
	}

	/**
	 * @return \CUser
	 */
	public function getUser()
	{
		global $USER;
		return $USER;
	}

	/**
	 * @return \CMain
	 */
	public function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	/**
	 * @param integer $userId User ID.
	 * @return array
	 */
	public static function loadUserGroups($userId)
	{
		/* @global \CUser $USER */
		global $USER;
		if (!array_key_exists($userId, self::$userGroups))
		{
			if ($USER->getId() == $userId)
			{
				$groups = $USER->getUserGroupArray();
			}
			else
			{
				$groups = \Bitrix\Main\UserTable::getUserGroupIds($userId);
				if (empty($groups))
					$groups = array(2);
			}
			self::$userGroups[$userId] = $groups;
		}
		return self::$userGroups[$userId];
	}
	/**
	 * @param integer $id Entity ID.
	 * @return mixed
	 */
	public static function loadFromId($id)
	{
		$c = get_called_class();
		return new $c(intval($id));
	}
}