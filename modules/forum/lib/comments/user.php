<?php

namespace Bitrix\Forum\Comments;

class User
{
	protected $id = 0;
	protected $groups = array(2);

	public function __construct($id)
	{
		global $USER;
		if (is_object($USER) && $id == $USER->getId())
		{
			$this->id = $USER->getId();
			$this->groups = $USER->GetUserGroupArray();
		}
		else if ($id > 0)
		{
			$this->id = $id;
			$this->groups = \Bitrix\Main\UserTable::getUserGroupIds($id);
		}
	}

	public function getId()
	{
		return $this->id;
	}

	public function getGroups()
	{
		return implode(",", $this->groups);
	}

	public function getUserGroupArray()
	{
		return $this->groups;
	}

	public function isAuthorized()
	{
		return true;
	}

	public function getParam()
	{
		return '';
	}
	public function isAdmin()
	{
		return false;
	}
	public function getUserGroup()
	{
		return $this->groups;
	}
	public function getFirstName()
	{
		return '';
	}
	public function getLastName()
	{
		return '';
	}
	public function getSecondName()
	{
		return '';
	}
	public function getLogin()
	{
		return '';
	}
	public function getFullName()
	{
		return '';
	}
}