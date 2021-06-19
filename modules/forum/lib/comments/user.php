<?php

namespace Bitrix\Forum\Comments;

use \Bitrix\Forum;

class User
{
	protected $id = 0;
	protected $groups = [2];
	protected $forumUser = null;

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
		$this->forumUser = Forum\User::getById($this->id);
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

	public function getParam(string $key)
	{
		if ($this->forumUser instanceof Forum\User)
		{
			return $this->forumUser[$key];
		}
		return null;
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

	public function getUnreadMessageId($topicId = 0)
	{
		if ($this->forumUser instanceof Forum\User)
		{
			return $this->forumUser->getUnreadMessageId($topicId);
		}
		return null;
	}

	public function readTopic($topicId = 0)
	{
		if ($this->forumUser instanceof Forum\User)
		{
			$this->forumUser->readTopic($topicId);
			$this->forumUser->setLastVisit();
		}
	}

	public function setLocation(int $forumId = 0, int $topicId = 0)
	{
		if ($this->forumUser instanceof Forum\User)
		{
			$this->forumUser->setLocation($forumId, $topicId);
		}
	}
}
