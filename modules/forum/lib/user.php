<?php
namespace Bitrix\Forum;

use \Bitrix\Main\Entity;
use Bitrix\Main\Error;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\NotImplementedException;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\FieldError;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Vote\VoteTable;


Loc::loadMessages(__FILE__);

/**
 * Class UserTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int
 * <li> DESCRIPTION string(255) null,
 * <li> AVATAR int(10),
 * <li> POINTS int not null default 0,
 * <li> RANK_ID int null,
 * <li> NUM_POSTS int(10) default '0',
 * <li> INTERESTS text,
 * <li> LAST_POST int(10),
 * <li> SIGNATURE varchar(255) null,

 * <li> IP_ADDRESS string(128) null
 * <li> REAL_IP_ADDRESS varchar(128) null,
 * <li> DATE_REG date not null,
 * <li> LAST_VISIT datetime not null,

 * <li> ALLOW_POST char(1) not null default 'Y',
 * <li> SHOW_NAME char(1) not null default 'Y',
 * <li> HIDE_FROM_ONLINE char(1) not null default 'N',
 * <li> SUBSC_GROUP_MESSAGE char(1) NOT NULL default 'N',
 * <li> SUBSC_GET_MY_MESSAGE char(1) NOT NULL default 'Y',
 * </ul>
 *
 * @package Bitrix\Forum
 */
class UserTable extends \Bitrix\Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum_user';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'required' => true
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\UserTable',
				'reference' => array(
					'=this.USER_ID' => 'ref.ID'
				)
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
			),
			'AVATAR' => array(
				'data_type' => 'integer'
			),
			'POINTS' => array(
				'data_type' => 'integer'
			),
			'RANK_ID' => array(
				'data_type' => 'integer'
			),
			'NUM_POSTS' => array(
				'data_type' => 'integer'
			),
			'INTERESTS' => array(
				'data_type' => 'text'
			),
			'LAST_POST' => array(
				'data_type' => 'integer'
			),
			'SIGNATURE' => array(
				'data_type' => 'string'
			),
			'IP_ADDRESS' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'REAL_IP_ADDRESS' => array(
				'data_type' => 'string',
				'size' => 255
			),
			'DATE_REG' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => function(){return new DateTime();}
			),
			'LAST_VISIT' => array(
				'data_type' => 'datetime',
				'required' => true,
				'default_value' => function(){return new DateTime();}
			),
			'ALLOW_POST' => array(
				'data_type' => "boolean",
				'values' => array("N", "Y"),
				'default_value' => "Y"
			),
			'SHOW_NAME' => array(
				'data_type' => "boolean",
				'values' => array("N", "Y"),
				'default_value' => "Y"
			),
			'HIDE_FROM_ONLINE' => array(
				'data_type' => "boolean",
				'values' => array("N", "Y"),
				'default_value' => "N"
			),
			'SUBSC_GROUP_MESSAGE' => array(
				'data_type' => "boolean",
				'values' => array("N", "Y"),
				'default_value' => "N"
			),
			'SUBSC_GET_MY_MESSAGE' => array(
				'data_type' => "boolean",
				'values' => array("N", "Y"),
				'default_value' => "Y"
			)
		);
	}
	public static function onBeforeAdd(Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		if (($events = GetModuleEvents("forum", "onBeforeUserAdd", true)) && !empty($events))
		{
			/** @var array $data */
			$data = $event->getParameter("fields");
			if ($res = UserTable::getList([
				"select" => ["ID"],
				"filter" => ["USER_ID" => $data["USER_ID"]]
			])->fetch())
			{
				$result->addError(new EntityError("Error: User is already exists.", "event"));
				return $result;
			}
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array(&$data)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "event"));
					return $result;
				}
			}
			if ($data != $event->getParameter("fields"))
			{
				$result->modifyFields($data);
			}
		}
		return self::modifyData($event, $result);
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterAdd(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$fields = $event->getParameter("fields");
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("forum", "onAfterUserAdd", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields]);
		/***************** /Event ******************************************/
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return \Bitrix\Main\ORM\EventResult|void
	 * @throws \Bitrix\Main\ObjectException
	 */
	public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		if (($events = GetModuleEvents("forum", "onBeforeUserUpdate", true)) && !empty($events))
		{
			/** @var array $data */
			$data = $event->getParameter("fields");
			$id = $event->getParameter("id");
			$id = $id["ID"];
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array($id, &$data)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "event"));
					return $result;
				}
			}
			if ($data != $event->getParameter("fields"))
			{
				$result->modifyFields($data);
			}
		}
		return self::modifyData($event, $result);
	}

	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @return void
	 */
	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		$fields = $event->getParameter("fields");
		/***************** Event onAfterVoteAdd ****************************/
		foreach (GetModuleEvents("forum", "onAfterUserUpdate", true) as $event)
			ExecuteModuleEventEx($event, [$id, $fields]);
		/***************** /Event ******************************************/
	}
	/**
	 * @param \Bitrix\Main\ORM\Event $event
	 * @param \Bitrix\Main\ORM\EventResult $result
	 * @return \Bitrix\Main\ORM\EventResult
	 * @throws \Bitrix\Main\ObjectException
	 */
	private static function modifyData(\Bitrix\Main\ORM\Event $event, \Bitrix\Main\ORM\EventResult $result)
	{
		$data = array_merge($event->getParameter("fields"), $result->getModified());
		$fields = [];

		//region check image
		if (array_key_exists("AVATAR", $data))
		{
			\CFile::ResizeImage($data["AVATAR"], array(
				"width" => \Bitrix\Main\Config\Option::get("forum", "avatar_max_width", 100),
				"height" => \Bitrix\Main\Config\Option::get("forum", "avatar_max_height", 100)));
			$maxSize = \Bitrix\Main\Config\Option::get("forum", "file_max_size", 5242880);
			if ($str = \CFile::CheckImageFile($data["AVATAR"], $maxSize))
			{
				$result->addError(new FieldError(static::getEntity()->getField("AVATAR"), $str));
			}
			else
			{
				$fields["AVATAR"] = $data["AVATAR"];
				$fields["AVATAR"]["MODULE_ID"] = "forum";
				if ($id = $event->getParameter("id"))
				{
					$id = is_integer($id) ? $id : $id["ID"];
					if ($id > 0 && ($res = UserTable::getById($id)->fetch()) && ($res["AVATAR"] > 0))
					{
						$fields["AVATAR"]["old_file"] = $res["AVATAR"];
					}
				}
				\CFile::SaveForDB($fields, "AVATAR", "forum/avatar");
			}
		}
		//endregion
		if (!empty($fields))
		{
			$result->modifyFields(array_merge($result->getModified(), $fields));
		}
		return $result;
	}

	public static function onBeforeDelete(\Bitrix\Main\ORM\Event $event)
	{
		$result = new \Bitrix\Main\ORM\EventResult();
		$id = $event->getParameter("id");
		$id = $id["ID"];
		if (($events = GetModuleEvents("forum", "onBeforeUserDelete", true)) && !empty($events))
		{
			foreach ($events as $ev)
			{
				if (ExecuteModuleEventEx($ev, array($id)) === false)
				{
					$result->addError(new EntityError("Error: ".serialize($ev), "event"));
					return $result;
				}
			}
		}
		if (($user = UserTable::getById($id)->fetch()) && $user["AVATAR"] > 0)
		{
			\CFile::Delete($user["AVATAR"]);
		}
		return $result;
	}

	public static function onAfterDelete(\Bitrix\Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		foreach(GetModuleEvents("forum", "onAfterUserDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id]);
		}
	}
}

class User {
	use \Bitrix\Forum\Internals\EntityFabric;
	use \Bitrix\Forum\Internals\EntityBaseMethods;
	/** @var int */
	protected $forumUserId = null;
	/** @var boolean */
	protected $locked = false;
	/** @var array */
	protected $groups;

	/** @var array  */
	protected $permissions = [];
	/** @var bool */
	private $editOwn = false;

	protected function __construct($id)
	{
		$this->data = [
			"VISIBLE_NAME"=> "Guest"
		];
		if ($id > 0)
		{
			$user = UserTable::getList(array(
				"select" => array(
					"*",
					"ACTIVE" => "USER.ACTIVE",
					"NAME" => "USER.NAME",
					"SECOND_NAME" => "USER.SECOND_NAME",
					"LAST_NAME" => "USER.LAST_NAME",
					"LOGIN" => "USER.LOGIN"
				),
				"filter" => array("USER_ID" => (int)$id),
				"limit" => 1,
			))->fetch();
			if ($user)
			{
				$this->forumUserId = $user["ID"];
				$this->id = $user["USER_ID"];
				$this->locked = ($user["ACTIVE"] !== "Y" || $user["ALLOW_POST"] !== "Y");
			}
			else if ($user = \Bitrix\Main\UserTable::getList(array(
				'select' => array('*'),
				'filter' => array('ID' => (int)$id),
				'limit' => 1,
			))->fetch())
			{
				$this->id = $user["ID"];
				$this->locked = ($user["ACTIVE"] !== "Y");
			}
			else
			{
				throw new \Bitrix\Main\ArgumentException("User was not found.");
			}
			$this->data = $user;
			$this->data["NAME"] = $user["NAME"];
			$this->data["SECOND_NAME"] = $user["SECOND_NAME"];
			$this->data["LAST_NAME"] = $user["LAST_NAME"];
			$this->data["LOGIN"] = $user["LOGIN"];
			$this->data["VISIBLE_NAME"] = ($user["SHOW_NAME"] === "Y" ?  \CUser::FormatName(\CSite::getNameFormat(false), $user, true, false) : $this->data["LOGIN"]);
			$this->editOwn = (\COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y") == "Y");
		}

	}
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->data["VISIBLE_NAME"];
	}

	public function setLastVisit()
	{
		if ($this->getId() <= 0)
		{
			return;
		}

		static $connection = false;
		static $helper = false;
		if (!$connection)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$helper = $connection->getSqlHelper();
		}

		$merge = $helper->prepareMerge(
			"b_forum_user",
			array("USER_ID"),
			array(
				"SHOW_NAME" => (\COption::GetOptionString("forum", "USER_SHOW_NAME", "Y") == "Y" ? "Y" : "N"),
				"ALLOW_POST" => "Y",
				"USER_ID" => $this->getId(),
				"DATE_REG" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
				"LAST_VISIT" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
			),
			array(
				"LAST_VISIT" => new \Bitrix\Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
			)
		);
		if ($merge[0] != "")
		{
			$connection->query($merge[0]);
		}

		unset($GLOBALS["FORUM_CACHE"]["USER"]);
		unset($GLOBALS["FORUM_CACHE"]["USER_ID"]);
	}

	public function isLocked()
	{
		return $this->locked;
	}

	public function isAdmin()
	{
		if ($this->isLocked())
			return false;
		return self::isUserAdmin($this->getGroups());
	}

	public function isGuest()
	{
		return ($this->getId() <= 0);
	}

	public function edit(array $fields)
	{
		$result = new Result();

		if ($this->isGuest())
		{
			return $result;
		}

		foreach (GetModuleEvents("forum", "onBeforeUserEdit", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, [$this->forumUserId, $this->getId(), &$fields]) === false)
			{
				$result->addError(new Error("Event error"));
				return $result;
			}
		}

		$result = $this->save($fields);

		if ($result->isSuccess())
		{
			foreach(GetModuleEvents("forum", "onAfterUserEdit", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($this->forumUserId, $this->getId(), $fields));
			}
		}

		return $result;
	}

	public function calcStatistic()
	{
		$result = new Result();

		if ($this->isGuest())
		{
			return $result;
		}

		$fields = [
			"LAST_POST" => 0,
			"NUM_POSTS" => 0,
			"POINTS" => 0
		];
		if ($res = \Bitrix\Forum\MessageTable::getList([
			"select" => ["CNT", "LAST_MESSAGE_ID"],
			"filter" => ["AUTHOR_ID" => $this->getId(), "APPROVED" => "Y"],
			"runtime" => [
				new \Bitrix\Main\Entity\ExpressionField("CNT", "COUNT(*)"),
				new \Bitrix\Main\Entity\ExpressionField("LAST_MESSAGE_ID", "MAX(%s)", ["ID"])
			],
		])->fetch())
		{
			$fields = [
				"LAST_POST" => $res["LAST_MESSAGE_ID"],
				"NUM_POSTS" => $res["CNT"],
				"POINTS" => \CForumUser::GetUserPoints($this->getId(), ["NUM_POSTS" => $res["CNT"]])
			];
		}
		return $this->save($fields);
	}

	public function incrementStatistic(array $message)
	{
		if ($this->isGuest() || $message["APPROVED"] != "Y")
		{
			return;
		}

		$this->data["NUM_POSTS"]++;
		$this->data["POINTS"] = \CForumUser::GetUserPoints($this->getId(), array("INCREMENT" => $this->data["NUM_POSTS"]));
		$this->data["LAST_POST"] = $message["ID"];
		$this->save(["NUM_POSTS" => $this->data["NUM_POSTS"], "POINTS" => $this->data["POINTS"], "LAST_POST" => $message["ID"]]);
	}

	public function decrementStatistic($message = null)
	{

	}

	private function save(array $fields)
	{
		$result = new Result();

		if ($this->isGuest())
		{
			return $result;
		}

		if ($this->forumUserId > 0)
		{
			$result = UserTable::update($this->forumUserId, $fields);
		}
		else
		{
			$result = UserTable::add($fields);
			if ($result->isSuccess())
			{
				$res = $result->getPrimary();
				$this->forumUserId = $result->getPrimary();
			}
		}
		return $result;
	}

	public function getGroups()
	{
		if (!$this->groups)
		{
			global $USER;
			$this->groups = [2];
			if ($this->getId() <= 0 || $this->isLocked())
			{
				if (\Bitrix\Main\Config\Option::get("main", "new_user_registration", "") == "Y")
				{
					$def_group = \Bitrix\Main\Config\Option::get("main", "new_user_registration_def_group", "");
					if($def_group != "" && ($res = explode(",", $def_group)))
					{
						$this->groups = array_merge($this->groups, $res);
					}
				}
			}
			else if ($USER instanceof \CUser && $this->getId() === $USER->GetID())
			{
				$this->groups = $USER->GetUserGroupArray();
			}
			else
			{
				$dbRes = \Bitrix\Main\UserGroupTable::getList(array(
					"select" => ["GROUP_ID"],
					"filter" => ["USER_ID" => $this->getId()],
					"order" => ["GROUP_ID" => "ASC"]
				));
				while ($res = $dbRes->fetch())
				{
					$this->groups[] = $res["GROUP_ID"];
				}
			}
		}
		return $this->groups;
	}

	public function setPermissionOnForum($forum, $permission)
	{
		$forum = \Bitrix\Forum\Forum::getInstance($forum);
		$this->permissions[$forum->getId()] = $permission;
		return $this;
	}

	public function getPermissionOnForum($forum)
	{
		$forum = \Bitrix\Forum\Forum::getInstance($forum);
		if (!array_key_exists($forum->getId(), $this->permissions))
		{
			$this->permissions[$forum->getId()] = $forum->getPermissionForUser($this);
		}
		return $this->permissions[$forum->getId()];
	}
	public function canModerate(\Bitrix\Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::CAN_MODERATE;
	}

	public function canAddTopic(\Bitrix\Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::CAN_ADD_TOPIC;
	}

	public function canAddMessage(\Bitrix\Forum\Topic $topic)
	{
		if ($topic["STATE"] === Topic::STATE_OPENED && $topic["APPROVED"] === Topic::APPROVED_APPROVED)
		{
			return $this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_ADD_MESSAGE;
		}
		return $this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_EDIT;
	}

	public function canEditTopic(\Bitrix\Forum\Topic $topic)
	{
		if ($this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_EDIT)
		{
			return true;
		}
		if ($this->isGuest())
		{
			return false;
		}
		if (
			($this->getId() == $topic->getAuthorId())
			&&
			($this->editOwn || ($topic["POSTS"] <= 0 && $topic["POSTS_UNAPPROVED"] <= 0))
		)
		{
			return true;
		}
		return false;
	}

	public function canEditMessage(\Bitrix\Forum\Message $message)
	{
		if ($this->getPermissionOnForum($message->getForumId()) >= Permission::CAN_EDIT)
		{
			return true;
		}
		if ($this->isGuest())
		{
			return false;
		}
		if ($this->getId() == $message->getAuthorId())
		{
			if ($this->editOwn)
			{
				return true;
			}
			$topic = \Bitrix\Forum\Topic::getById($message["TOPIC_ID"]);
			if ($topic["ABS_LAST_MESSAGE_ID"] == $message->getId())
			{
				return true;
			}
		}
		return false;
	}

	public function canDeleteMessage(\Bitrix\Forum\Message $message)
	{
		return $this->canEditMessage($message);
	}

	public function canEditForum(\Bitrix\Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::FULL_ACCESS;
	}

	public function canDeleteForum(\Bitrix\Forum\Forum $forum)
	{
		return $this->canEditForum($forum);
	}

	public static function isUserAdmin(array $groups)
	{
		global $APPLICATION;
		return (in_array(1, $groups) || $APPLICATION->GetGroupRight("forum", $groups) >= "W");
	}
}