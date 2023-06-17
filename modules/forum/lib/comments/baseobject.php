<?php

namespace Bitrix\Forum\Comments;

use \Bitrix\Forum\Internals\Error\ErrorCollection;
use \Bitrix\Forum\Internals\Error\Error;
use \Bitrix\Forum;
use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ArgumentTypeException;
use \Bitrix\Main\ArgumentException;
use \Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;

Loc::loadMessages(__FILE__);

abstract class BaseObject
{
	const ERROR_PARAMS_FORUM_ID = 'params0001';
	const ERROR_PARAMS_TOPIC_ID = 'params0002';
	const ERROR_PARAMS_ENTITY_ID = 'params0003';
	private static $topics = array();
	private static $users = array();
	/* @var \Bitrix\Forum\Comments\User */
	protected $user;
	/* @var \Bitrix\Forum\Comments\Entity */
	protected $entity;
	/** @var array */
	protected $forum;
	/** @var array */
	protected $topic;
	/** @var  ErrorCollection */
	protected $errorCollection;

	public function __construct($forumId, $entity, $userId = null)
	{
		global $USER;
		$this->errorCollection = new ErrorCollection();
		if (is_null($userId))
		{
			$userId = ($USER instanceof \CUser ? $USER->getId() : 0);
		}
		else
		{
			$userId = intval($userId);
		}
		$this->setUser($userId);

		$this->setForum($forumId);
		$this->setEntity($entity);
		$this->setTopic();
	}

	protected function setEntity(array $id)
	{
		if (($id = array_change_key_case($id, CASE_LOWER)) && $id["id"] > 0)
			$this->entity = $id;
		else
			throw new ArgumentException(Loc::getMessage("FORUM_CM_WRONG_ENTITY"), self::ERROR_PARAMS_ENTITY_ID);
	}

	/**
	 * Returns entity which manage all rights. For example forum topic or task
	 * @return Entity
	 */
	public function getEntity()
	{
		if ($this->entity instanceof Entity)
			return $this->entity;

		if (!is_array($this->entity))
			throw new ArgumentTypeException("entity");

		$id = $this->entity;
		$protoEntity = Entity::getEntityByType($id["type"]);
		if (is_null($protoEntity))
		{
			$protoEntity = Entity::getEntityByType("default");
			if (!array_key_exists('xml_id', $id) || empty($id["xml_id"]))
				$id['xml_id'] = mb_strtoupper($id["type"]."_".$id['id']);
		}
		elseif (!array_key_exists('xml_id', $id) || empty($id["xml_id"]))
			$id['xml_id'] = $protoEntity["xmlIdPrefix"]."_".$id['id'];
		if (!Loader::includeModule($protoEntity["moduleId"]))
			throw new SystemException("Module {$protoEntity["moduleId"]} is not included.");

		$this->entity = new $protoEntity["className"]($id, $this->getForum());
		if (! $this->entity instanceof Entity)
			throw new SystemException("Entity Class does not descended from \\Bitrix\\Forum\\Comments\\Entity.");

		return $this->entity;
	}

	protected function setTopic()
	{
		if (!array_key_exists($this->getEntity()->getXmlId(), self::$topics))
		{
			$dbRes = \CForumTopic::getList(null, array(
				"FORUM_ID" => $this->forum["ID"],
				"XML_ID" => $this->getEntity()->getXmlId()
			));
			self::$topics[$this->getEntity()->getXmlId()] = (($res = $dbRes->fetch()) && $res ? $res : null);
		}
		$this->topic = self::$topics[$this->getEntity()->getXmlId()];
		return $this;
	}

	protected function createTopic()
	{
		$topic = array(
			'TITLE' => $this->getEntity()->getXmlId(),
			'TAGS' => '',
			'MESSAGE' => $this->getEntity()->getXmlId(),
			'AUTHOR_ID' => 0
		);
		/** @var $request \Bitrix\Main\HttpRequest */
		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$post = array_merge($request->getQueryList()->toArray(), $request->getPostList()->toArray());

		$event = new Event("forum", "OnCommentTopicAdd", array($this->getEntity()->getType(), $this->getEntity()->getId(), $post, &$topic));
		$event->send();

		if (!isset($topic["AUTHOR_NAME"]) || strlen($topic["AUTHOR_NAME"]) <= 0)
			$topic["AUTHOR_NAME"] = ($topic["AUTHOR_ID"] <= 0 ? Loc::getMessage("FORUM_USER_SYSTEM") : self::getUserName($topic["AUTHOR_ID"]));

		$topic = array_merge($topic, array(
			"FORUM_ID" => $this->forum["ID"],
			'TITLE' => $topic["TITLE"],
			'TAGS' => $topic["TAGS"],
			'MESSAGE' => $topic["MESSAGE"],
			"USER_START_ID" => $topic["AUTHOR_ID"],
			"USER_START_NAME" => $topic["AUTHOR_NAME"],
			"LAST_POSTER_NAME" => $topic["AUTHOR_NAME"],
			"XML_ID" => $this->getEntity()->getXmlId(),
			"APPROVED" => "Y"
		));
		if (($tid = \CForumTopic::add($topic)) > 0)
		{
			if ($this->forum["ALLOW_HTML"] != "Y")
				$topic['MESSAGE'] = strip_tags($topic['MESSAGE']);

			$fields = Array(
				"POST_MESSAGE" => $topic['MESSAGE'],
				"AUTHOR_ID" => $topic["AUTHOR_ID"],
				"AUTHOR_NAME" => $topic["AUTHOR_NAME"],
				"FORUM_ID" => $topic["FORUM_ID"],
				"TOPIC_ID" => $tid,
				"APPROVED" => $topic["APPROVED"],
				"NEW_TOPIC" => "Y",
				"XML_ID" => $this->getEntity()->getXmlId(),
				"PARAM1" => $this->getEntity()->getType(),
				"PARAM2" => $this->getEntity()->getId()
			);
			if ((\CForumMessage::add($fields)) > 0)
			{
				$event = new Event("forum", "OnAfterCommentTopicAdd", array($this->getEntity()->getType(), $this->getEntity()->getId(), $tid));
				$event->send();

				self::$topics[$this->getEntity()->getXmlId()] = $topic + array("ID" => $tid);
				return self::$topics[$this->getEntity()->getXmlId()];
			}
			\CForumTopic::delete($tid);
		}
		$this->errorCollection->add(array(new Error(Loc::getMessage("FORUM_CM_TOPIC_IS_NOT_CREATED"), self::ERROR_PARAMS_TOPIC_ID)));
		return null;
	}

	/**
	 * Returns forum topic
	 * @return array
	 */
	public function getTopic()
	{
		return $this->topic;
	}

	/**
	 * @return bool
	 */
	public function hasErrors()
	{
		return $this->errorCollection->hasErrors();
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	protected static function checkForumId(&$forumId)
	{
		$res = (is_integer($forumId) || is_string($forumId) ? intval($forumId) : 0);
		if ($res > 0)
		{
			$forumId = $res;
			// TODO Complete forum verifying
			return true;
		}
		return false;
	}

	protected function setForum($id)
	{
		if (!$this->checkForumId($id))
			throw new ArgumentTypeException(Loc::getMessage("FORUM_CM_FORUM_IS_WRONG"), self::ERROR_PARAMS_FORUM_ID);

		$this->forum = Forum\ForumTable::getMainData($id, SITE_ID);

		if (!$this->forum)
			throw new ArgumentException(Loc::getMessage("FORUM_CM_FORUM_IS_LOST"), self::ERROR_PARAMS_FORUM_ID);

		return $this;
	}

	/**
	 * Returns forum
	 * @return array
	 */
	public function getForum()
	{
		return $this->forum;
	}

	/**
	 * Redefines forum params
	 *
	 * @param array $params Array(key=>value, key2=>value2) of fields to redefine forum fields.
	 * @return $this
	 */
	public function setForumFields(array $params)
	{
		foreach ($params as $key => $val)
		{
			if (array_key_exists($key, $this->forum))
				$this->forum[$key] = $val;
		}
		return $this;
	}

	/**
	 * @param $userId
	 * @return User
	 */
	protected function setUser($userId)
	{
		$this->user = new \Bitrix\Forum\Comments\User($userId);
		return $this->user;
	}
	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}

	public function getUserUnreadMessageId()
	{
		return $this->user->getUnreadMessageId($this->getTopic() ? $this->getTopic()["ID"] : 0);
	}

	public function setUserAsRead()
	{
		$this->user->readTopic($this->getTopic() ? $this->getTopic()["ID"] : 0);
	}

	public function setUserLocation()
	{
		$this->user->setLocation($this->forum["ID"], $this->getTopic() ? $this->getTopic()["ID"] : 0);
	}

	/**
	 * @return \CMain
	 */
	public function getApplication()
	{
		global $APPLICATION;
		return $APPLICATION;
	}

	private static function getUserFromForum($userId)
	{
		if ($userId > 0 && !array_key_exists($userId, self::$users))
		{
			self::$users[$userId] = \CForumUser::getListEx(array(), array("USER_ID" => $userId))->fetch();
			if(!self::$users[$userId])
			{
				self::$users[$userId] = \CUser::getById($userId)->fetch();
				self::$users[$userId]["SHOW_NAME"] = \COption::getOptionString("forum", "USER_SHOW_NAME", "Y");
			}
		}
		return self::$users[$userId];
	}

	protected function getUserName($userId)
	{
		$user = self::getUserFromForum($userId);
		$name = "";
		if (is_array($user))
		{
			$name = ($user["SHOW_NAME"] == "Y" ? trim($user["NAME"]." ".$user["LAST_NAME"]) : "");
			$name = (empty($name) ? $user["LOGIN"] : $name);
		}
		return $name;
	}
}

