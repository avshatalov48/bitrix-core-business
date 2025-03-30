<?php

namespace Bitrix\Forum\Comments;

use \Bitrix\Forum;
use Bitrix\Main\Engine\CurrentUser;
use CUser;

class User
{
	protected int $id = 0;
	private static $groups;
	protected $forumUser = null;

	public function __construct(int $id)
	{
		$user = CurrentUser::get();
		if ($id === (int)$user->getId())
		{
			$this->id = $user->getId();
			if (empty(self::$groups[$id]))
			{
				self::$groups[$id] = $user->getUserGroups();
			}
		}
		else if ($id > 0)
		{
			$this->id = $id;
			if (empty(self::$groups[$id]))
			{
				self::$groups[$id] = \Bitrix\Main\UserTable::getUserGroupIds($id);
			}
		}

		if (empty(self::$groups[$id]))
		{
			self::$groups[$id] = [2];
		}

		$this->forumUser = Forum\User::getById($this->id);
	}

	public function getId()
	{
		return $this->id;
	}

	public function getGroups()
	{
		return implode(",", self::$groups[$this->id]);
	}

	public function getUserGroupArray()
	{
		return self::$groups[$this->id];
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
		return self::$groups[$this->id];
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
