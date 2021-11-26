<?php
namespace Bitrix\Forum;

use Bitrix\Forum;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\EntityError;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\Fields\FieldError;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;


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
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_User_Query query()
 * @method static EO_User_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_User_Result getById($id)
 * @method static EO_User_Result getList(array $parameters = array())
 * @method static EO_User_Entity getEntity()
 * @method static \Bitrix\Forum\EO_User createObject($setDefaultValues = true)
 * @method static \Bitrix\Forum\EO_User_Collection createCollection()
 * @method static \Bitrix\Forum\EO_User wakeUpObject($row)
 * @method static \Bitrix\Forum\EO_User_Collection wakeUpCollection($rows)
 */
class UserTable extends Main\Entity\DataManager
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
		$result = new Main\ORM\EventResult();
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

		return self::modifyData($event, $result);
	}

	public static function onBeforeUpdate(Main\ORM\Event $event)
	{
		$result = new Main\ORM\EventResult();

		return self::modifyData($event, $result);
	}

	private static function modifyData(Main\ORM\Event $event, Main\ORM\EventResult $result)
	{
		$data = array_merge($event->getParameter("fields"), $result->getModified());
		$fields = [];

		//region check image
		if (array_key_exists("AVATAR", $data))
		{
			\CFile::ResizeImage($data["AVATAR"], array(
				"width" => Main\Config\Option::get("forum", "avatar_max_width", 100),
				"height" => Main\Config\Option::get("forum", "avatar_max_height", 100)));
			$maxSize = Main\Config\Option::get("forum", "file_max_size", 5242880);
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
				if (!\CFile::SaveForDB($fields, "AVATAR", "forum/avatar"))
				{
					$result->unsetField("AVATAR");
				}
			}
		}
		//endregion
		if (!empty($fields))
		{
			$result->modifyFields(array_merge($result->getModified(), $fields));
		}
		return $result;
	}

	public static function onBeforeDelete(Main\ORM\Event $event)
	{
		$result = new Main\ORM\EventResult();
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

	public static function onAfterDelete(Main\ORM\Event $event)
	{
		$id = $event->getParameter("id");
		$id = $id["ID"];
		foreach(GetModuleEvents("forum", "onAfterUserDelete", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id]);
		}
	}
}

class User implements \ArrayAccess {
	use Internals\EntityFabric;
	use Internals\EntityBaseMethods;
	/** @var int */
	protected $id = 0;
	/** @var array */
	protected $data = [];
	/** @var int */
	protected $forumUserId = null;
	/** @var bool */
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
			elseif ($user = Main\UserTable::getList(array(
				'select' => array('*'),
				'filter' => array('ID' => (int)$id),
				'limit' => 1,
			))->fetch())
			{
				$this->id = $user["ID"];
				$this->locked = ($user["ACTIVE"] !== "Y");

				$this->data["ALLOW_POST"] = "Y";
				$this->data["SHOW_NAME"] = (\COption::GetOptionString("forum", "USER_SHOW_NAME", "Y") == "Y" ? "Y" : "N");
			}
			else
			{
				throw new Main\ObjectNotFoundException("User was not found.");
			}
			$this->data = $user;
			$this->data["NAME"] = $user["NAME"];
			$this->data["SECOND_NAME"] = $user["SECOND_NAME"];
			$this->data["LAST_NAME"] = $user["LAST_NAME"];
			$this->data["LOGIN"] = $user["LOGIN"];
			$this->data["ALLOW_POST"] = ($this->data["ALLOW_POST"] === "N" ? "N" : "Y");
			if ($this->data["SHOW_NAME"] !== "Y" && $this->data["SHOW_NAME"] !== "N")
				$this->data["SHOW_NAME"] = (\COption::GetOptionString("forum", "USER_SHOW_NAME", "Y") == "Y" ? "Y" : "N");
			$this->data["VISIBLE_NAME"] = ($this->data["SHOW_NAME"] === "Y" ?  \CUser::FormatName(\CSite::getNameFormat(false), $user, true, false) : $this->data["LOGIN"]);
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

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$tableName = UserTable::getTableName();
		$update = $helper->prepareUpdate($tableName, ['LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())]);
		$where = $helper->prepareAssignment($tableName, 'USER_ID', $this->getId());
		$sql = 'UPDATE '.$helper->quote($tableName).' SET '.$update[0].' WHERE '.$where;
		$connection->queryExecute($sql, $update[1]);
		if ($connection->getAffectedRowsCount() <= 0)
		{
			$merge = $helper->prepareMerge(
				'b_forum_user',
				array('USER_ID'),
				array(
					'SHOW_NAME' => ($this->data['SHOW_NAME'] === 'N' ? 'N' : 'Y'),
					'ALLOW_POST' => ($this->data['ALLOW_POST'] === 'N' ? 'N' : 'Y'),
					'USER_ID' => $this->getId(),
					'DATE_REG' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
					'LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
				),
				array(
					'LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
				)
			);
			if ($merge[0] != '')
			{
				$connection->query($merge[0]);
			}
		}

		unset($GLOBALS['FORUM_CACHE']['USER']);
		unset($GLOBALS['FORUM_CACHE']['USER_ID']);
	}

	public function setLocation(int $forumId = 0, int $topicId = 0)
	{
		global $USER;
		if (!($USER instanceof \CUser && $this->getId() === $USER->GetID()))
		{
			return;
		}

		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$primaryFields = [
			'USER_ID' => $this->getId(),
			'PHPSESSID' => $this->getSessId()
		];
		$fields = [
			'SHOW_NAME' => $this->getName(),
			'IP_ADDRESS' => Main\Service\GeoIp\Manager::getRealIp(),
			'LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction()),
			'SITE_ID' => SITE_ID,
			'FORUM_ID' => $forumId,
			'TOPIC_ID' => $topicId,
		];

		if ($this->getId() > 0)
		{
			$fields['PHPSESSID'] = $primaryFields['PHPSESSID'];
			unset($primaryFields['PHPSESSID']);
		}

		$merge = $helper->prepareMerge(
			'b_forum_stat',
			array_keys($primaryFields),
			$primaryFields + $fields,
			$fields
		);
		if ($merge[0] != '')
		{
			$connection->query($merge[0]);
		}
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

	public function isAuthorized()
	{
		return ($this->getId() > 0);
	}

	public function edit(array $fields)
	{
		$result = new Result();

		if (!$this->isAuthorized())
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

	public function calculateStatistic()
	{
		$result = new Result();

		if (!$this->isAuthorized())
		{
			return $result;
		}

		$fields = [
			"LAST_POST" => 0,
			"NUM_POSTS" => 0,
			"POINTS" => 0
		];
		if ($res = MessageTable::getList([
			"select" => ["CNT", "LAST_MESSAGE_ID"],
			"filter" => ["AUTHOR_ID" => $this->getId(), "APPROVED" => "Y"],
			"runtime" => [
				new Main\Entity\ExpressionField("CNT", "COUNT(*)"),
				new Main\Entity\ExpressionField("LAST_MESSAGE_ID", "MAX(%s)", ["ID"])
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
		if (!$this->isAuthorized() || $message["APPROVED"] != "Y")
		{
			return;
		}

		$this->data["NUM_POSTS"]++;
		$this->data["POINTS"] = \CForumUser::GetUserPoints($this->getId(), array("INCREMENT" => $this->data["NUM_POSTS"]));
		$this->data["LAST_POST"] = $message["ID"];
		$this->save([
			"NUM_POSTS" => new Main\DB\SqlExpression('?# + 1', "NUM_POSTS"),
			"POINTS" => $this->data["POINTS"],
			"LAST_POST" => $message["ID"]
		]);
	}

	public function decrementStatistic($message = null)
	{
		if (!$this->isAuthorized() || $message['APPROVED'] != 'Y')
		{
			return;
		}

		$this->data['NUM_POSTS']--;
		$this->data['POINTS'] = \CForumUser::GetUserPoints($this->getId(), array('INCREMENT' => $this->data['NUM_POSTS']));
		$fields = [
			'NUM_POSTS' => new Main\DB\SqlExpression('?# - 1', 'NUM_POSTS'),
			'POINTS' => $this->data['POINTS'],
		];
		if ($message === null ||
			$message['ID'] === $this->data['LAST_POST']
		)
		{
			$message = MessageTable::getList([
				'select' => ['ID'],
				'filter' => ['AUTHOR_ID' => $this->getId(), 'APPROVED' => 'Y'],
				'limit' => 1,
				'order' => ['ID' => 'DESC']
			])->fetch();
			$this->data['LAST_POST'] = $message['ID'];
			$fields['LAST_POST'] =  $message['ID'];
		}
		$this->save($fields);
	}

	/**
	 * insteadOf ForumGetFirstUnreadMessage($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"]);
	 */
	public function getUnreadMessageId($topicId = 0): ?int
	{
		if ($topicId <= 0)
		{
			return null;
		}

		try
		{
			$topic = Topic::getById($topicId);
		}
		catch (Main\ObjectNotFoundException $e)
		{
			return null;
		}

		$query = MessageTable::query()
			->setSelect(['ID'])
			->where('TOPIC_ID', $topic->getId())
			->registerRuntimeField('FORCED_INT_ID', new Main\Entity\ExpressionField('FORCED_ID', '%s + ""', ['ID']))
			->setOrder(['FORCED_INT_ID' => 'ASC'])
			->setLimit(1);
		if ($this->isAuthorized())
		{
			$query
				->registerRuntimeField(
					'USER_TOPIC',
					new Main\ORM\Fields\Relations\Reference(
						'USER_TOPIC',
						UserTopicTable::getEntity(),
						[
							'=this.TOPIC_ID' => 'ref.TOPIC_ID',
							'=ref.USER_ID' => ['?i', $this->getId()],
						],
						['join_type' => 'LEFT']
					)
				)
				->registerRuntimeField(
					'USER_FORUM',
					new Main\ORM\Fields\Relations\Reference(
						'USER_FORUM',
						UserForumTable::getEntity(),
						[
							'=this.FORUM_ID' => 'ref.FORUM_ID',
							'=ref.USER_ID' => ['?i', $this->getId()]
						],
						['join_type' => 'LEFT']
					)
				)
				->registerRuntimeField(
					'USER_FORUM_0',
					new Main\ORM\Fields\Relations\Reference(
						'FUF0',
						UserForumTable::getEntity(),
						[
							'=this.FORUM_ID' => ['?i', 0],
							'=ref.USER_ID' => ['?i', $this->getId()]
						],
						['join_type' => 'LEFT']
					)
				)
				->where(
					Main\ORM\Query\Query::filter()
						->logic('or')
						->where(
							Main\ORM\Query\Query::filter()
								->whereNotNull('USER_TOPIC.LAST_VISIT')
								->whereColumn('POST_DATE', '>', 'USER_TOPIC.LAST_VISIT')
						)
						->where(
							Main\ORM\Query\Query::filter()
								->whereNull('USER_TOPIC.LAST_VISIT')
								->whereColumn('POST_DATE', '>', 'USER_FORUM.LAST_VISIT')
						)
						->where(
							Main\ORM\Query\Query::filter()
								->whereNull('USER_TOPIC.LAST_VISIT')
								->whereNull('USER_FORUM.LAST_VISIT')
								->whereColumn('POST_DATE', '>', 'USER_FORUM_0.LAST_VISIT')
						)
						->where(
							Main\ORM\Query\Query::filter()
								->whereNull('USER_TOPIC.LAST_VISIT')
								->whereNull('USER_FORUM.LAST_VISIT')
								->whereNull('USER_FORUM_0.LAST_VISIT')
								->whereNotNull('ID')
						)
				);
		}
		else
		{
			$timestamps = $this->getFromSession('GUEST_TID');
			$lastVisit = max(
				$this->getFromSession('LAST_VISIT_FORUM_0'),
				$this->getFromSession('LAST_VISIT_FORUM_'.$topic->getForumId()),
				is_array($timestamps) && array_key_exists($topic->getId(), $timestamps) ? $timestamps[$topic->getId()] : 0
			);
			if ($lastVisit > 0)
			{
				$query->where('POST_DATE', '>', DateTime::createFromTimestamp($lastVisit));
			}
			else
			{
				return null;
			}
		}
		if ($res = $query->fetch())
		{
			return $res['ID'];
		}
		return null;
	}

	/**
	 * Instead of ForumSetReadTopic($arParams["FORUM_ID"], $arResult["FORUM_TOPIC_ID"]);
	 * @param int $topicId
	 */
	public function readTopic($topicId = 0): void
	{
		if ($topicId <= 0)
		{
			return;
		}

		try
		{
			$topic = Topic::getById($topicId);
		}
		catch (Main\ObjectNotFoundException $e)
		{
			return;
		}

		$topic->incrementViews();

		if ($this->isAuthorized())
		{
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$primaryFields = [
				'USER_ID' => $this->getId(),
				'TOPIC_ID' => $topic->getId()
			];

			$fields = [
				'FORUM_ID' => $topic->getForumId(),
				'LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
			];

			$result = UserTopicTable::update($primaryFields, $fields);
			if ($result->getAffectedRowsCount() <= 0)
			{
				$merge = $helper->prepareMerge(
					'b_forum_user_topic',
					array_keys($primaryFields),
					$primaryFields + $fields,
					$fields
				);
				if ($merge[0] != '')
				{
					$connection->query($merge[0]);
				}
			}
		}
		else
		{
			$timestamp = new DateTime();
			$this->saveInSession('GUEST_TID', null);

			if (Main\Config\Option::set('forum', 'USE_COOKIE', 'N') == 'Y')
			{
				$GLOBALS['APPLICATION']->set_cookie('FORUM_GUEST_TID', '', false, '/', false, false, 'Y', false);
			}
		}
	}

	public function readTopicsOnForum(int $forumId = 0)
	{
		if ($this->isAuthorized())
		{
			$connection = Main\Application::getConnection();
			$helper = $connection->getSqlHelper();

			$primaryFields = [
				'USER_ID' => $this->getId(),
				'FORUM_ID' => $forumId
			];

			$fields = [
				'LAST_VISIT' => new Main\DB\SqlExpression($helper->getCurrentDateTimeFunction())
			];

			$result = Forum\UserForumTable::update($primaryFields, $fields);
			if ($result->getAffectedRowsCount() <= 0)
			{
				$merge = $helper->prepareMerge(
					Forum\UserForumTable::getTableName(),
					array_keys($primaryFields),
					$primaryFields + $fields,
					$fields
				);
				if ($merge[0] != '')
				{
					$connection->query($merge[0]);
				}
			}

			if ($forumId > 0)
			{
				Forum\UserTopicTable::deleteBatch(['USER_ID' => $this->getId(), 'FORUM_ID' => $forumId]);
			}
			else
			{
				Forum\UserTopicTable::deleteBatch(['USER_ID' => $this->getId()]);
				Forum\UserForumTable::deleteBatch(['USER_ID' => $this->getId(), '>FORUM_ID' => 0]);
			}
		}
		else
		{
			$timestamp = new DateTime();
			$topics = $this->saveInSession('GUEST_TID', [$topic->getId() => $timestamp->getTimestamp()]);

			if (Main\Config\Option::set('forum', 'USE_COOKIE', 'N') == 'Y')
			{
				foreach ($topics as $topicId => $timestamp)
				{
					$topics[$topicId] = implode('-', [$topicId, $timestamp]);
				}
				$GLOBALS['APPLICATION']->set_cookie('FORUM_GUEST_TID', implode('/', $topics), false, '/', false, false, 'Y', false);
			}
		}
	}

	private function save(array $fields)
	{
		$result = new Result();

		if (!$this->isAuthorized())
		{
			return $result;
		}

		if ($this->forumUserId > 0)
		{
			$result = User::update($this->forumUserId, $fields);
		}
		else
		{
			$fields = ['USER_ID' => $this->getId()] + $fields + $this->data;
			unset($fields['ID']);
			$result = User::add($fields);
			if ($result->isSuccess())
			{
				$res = $result->getPrimary();
				if (is_array($res))
				{
					$res = reset($res);
				}
				$this->forumUserId = $res;
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
				if (Main\Config\Option::get("main", "new_user_registration", "") == "Y")
				{
					$def_group = Main\Config\Option::get("main", "new_user_registration_def_group", "");
					if($def_group != "" && ($res = explode(",", $def_group)))
					{
						$this->groups = array_merge($this->groups, $res);
					}
				}
			}
			elseif ($USER instanceof \CUser && $this->getId() === $USER->GetID())
			{
				$this->groups = $USER->GetUserGroupArray();
			}
			else
			{
				$dbRes = Main\UserGroupTable::getList(array(
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
		$forum = Forum\Forum::getInstance($forum);
		$this->permissions[$forum->getId()] = $permission;
		return $this;
	}

	public function getPermissionOnForum($forum)
	{
		$forum = Forum\Forum::getInstance($forum);
		if (!array_key_exists($forum->getId(), $this->permissions))
		{
			$this->permissions[$forum->getId()] = $forum->getPermissionForUser($this);
		}
		return $this->permissions[$forum->getId()];
	}

	public function canModerate(Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::CAN_MODERATE;
	}

	public function canAddTopic(Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::CAN_ADD_TOPIC;
	}

	public function canAddMessage(Topic $topic)
	{
		if ($topic["STATE"] === Topic::STATE_OPENED && $topic["APPROVED"] === Topic::APPROVED_APPROVED)
		{
			return $this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_ADD_MESSAGE;
		}
		return $this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_EDIT;
	}

	public function canEditTopic(Topic $topic)
	{
		if ($this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_EDIT)
		{
			return true;
		}
		if (!$this->isAuthorized())
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

	public function canEditMessage(Message $message)
	{
		if ($this->getPermissionOnForum($message->getForumId()) >= Permission::CAN_EDIT)
		{
			return true;
		}
		if (!$this->isAuthorized())
		{
			return false;
		}
		if ($this->getId() == $message->getAuthorId())
		{
			if ($this->editOwn)
			{
				return true;
			}
			$topic = Topic::getById($message["TOPIC_ID"]);
			if ($topic["ABS_LAST_MESSAGE_ID"] == $message->getId())
			{
				return true;
			}
		}
		return false;
	}

	public function canDeleteMessage(Message $message)
	{
		return $this->canEditMessage($message);
	}

	public function canEditForum(Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::FULL_ACCESS;
	}

	public function canDeleteForum(Forum\Forum $forum)
	{
		return $this->canEditForum($forum);
	}

	public function canReadForum(Forum\Forum $forum)
	{
		return $this->getPermissionOnForum($forum->getId()) >= Permission::CAN_READ;
	}

	public function canReadTopic(Topic $topic)
	{
		return $this->getPermissionOnForum($topic->getForumId()) >= Permission::CAN_READ;
	}

	public static function isUserAdmin(array $groups)
	{
		global $APPLICATION;
		return (in_array(1, $groups) || $APPLICATION->GetGroupRight("forum", $groups) >= "W");
	}

	private function saveInSession(string $name, $value)
	{
		if (method_exists(Main\Application::getInstance(), 'getKernelSession'))
		{
			$forumSession = Main\Application::getInstance()->getKernelSession()->get('FORUM');
		}
		else
		{
			$forumSession = $_SESSION['FORUM'];
		}
		$forumSession = is_array($forumSession) ? $forumSession : [];
		if (is_array($value) && array_key_exists($name, $forumSession) && is_array($forumSession[$name]))
		{
			$forumSession[$name] = $value + $forumSession[$name];
		}
		else
		{
			$forumSession[$name] = $value;
		}
		if (method_exists(Main\Application::getInstance(), 'getKernelSession'))
		{
			Main\Application::getInstance()->getKernelSession()->set('FORUM', $forumSession);
		}
		else
		{
			$_SESSION['FORUM'] = $forumSession;
		}
		return $forumSession[$name];
	}

	private function getFromSession(string $name)
	{
		if (method_exists(Main\Application::getInstance(), 'getKernelSession'))
		{
			$forumSession = Main\Application::getInstance()->getKernelSession()->get('FORUM');
		}
		else
		{
			$forumSession = $_SESSION['FORUM'];
		}
		if (is_array($forumSession) && array_key_exists($name, $forumSession))
		{
			return $forumSession[$name];
		}
		return null;
	}

	private function getSessId()
	{
		if (method_exists(Main\Application::getInstance(), 'getKernelSession'))
		{
			return Main\Application::getInstance()->getKernelSession()->getId();
		}
		return bitrix_sessid();
	}

	public static function add(array &$data)
	{
		$result = new \Bitrix\Main\ORM\Data\AddResult();
		if (($events = GetModuleEvents("forum", "onBeforeUserAdd", true)) && !empty($events))
		{
			global $APPLICATION;
			foreach ($events as $ev)
			{
				$APPLICATION->ResetException();

				if (ExecuteModuleEventEx($ev, array(&$data)) === false)
				{
					$errorMessage = Loc::getMessage("FORUM_EVENT_BEFORE_USER_ADD");
					if (($ex = $APPLICATION->GetException()) && ($ex instanceof \CApplicationException))
					{
						$errorMessage = $ex->getString();
					}

					$result->addError(new \Bitrix\Main\Error($errorMessage, "onBeforeUserAdd"));
					return $result;
				}
			}
		}

		$dbResult = UserTable::add($data);

		if (!$dbResult->isSuccess())
		{
			$result->addErrors($dbResult->getErrors());
		}
		else
		{
			$id = $dbResult->getId();
			$result->setId($id);
			foreach (GetModuleEvents("forum", "onAfterUserAdd", true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $data]);
			}
		}

		return $result;
	}

	public static function update(int $id, array &$data)
	{
		unset($data["USER_ID"]);

		$result = new Main\ORM\Data\UpdateResult();
		$result->setPrimary(["ID" => $id]);

		if (($events = GetModuleEvents("forum", "onBeforeUserUpdate", true)) && !empty($events))
		{
			global $APPLICATION;
			foreach ($events as $ev)
			{
				$APPLICATION->ResetException();

				if (ExecuteModuleEventEx($ev, array($id, &$data)) === false)
				{
					$errorMessage = Loc::getMessage("FORUM_EVENT_BEFORE_USER_UPDATE_ERROR");
					if (($ex = $APPLICATION->GetException()) && ($ex instanceof \CApplicationException))
					{
						$errorMessage = $ex->getString();
					}
					$result->addError(new Main\Error($errorMessage, "onBeforeUserUpdate"));
					return $result;
				}
			}
		}

		$dbResult = UserTable::update($id, $data);

		if (!$dbResult->isSuccess())
		{
			$result->addErrors($dbResult->getErrors());
		}
		else
		{
			foreach (GetModuleEvents("forum", "onAfterUserUpdate", true) as $event)
			{
				ExecuteModuleEventEx($event, [$id, $data]);
			}
		}
		return $result;
	}
}