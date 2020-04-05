<?php
namespace Bitrix\Forum;

use Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class ForumTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FORUM_GROUP_ID int
 * <li> NAME string(255) mandatory
 * <li> DESCRIPTION text optional
 * <li> SORT int mandatory default '150'
 * <li> ACTIVE bool mandatory default 'Y'

 * <li> ALLOW_HTML bool mandatory default 'N'
 * <li> ALLOW_ANCHOR bool mandatory default 'Y'
 * <li> ALLOW_BIU bool mandatory default 'Y'
 * <li> ALLOW_IMG bool mandatory default 'Y'
 * <li> ALLOW_VIDEO bool mandatory default 'Y'
 * <li> ALLOW_LIST bool mandatory default 'Y'
 * <li> ALLOW_QUOTE bool mandatory default 'Y'
 * <li> ALLOW_CODE bool mandatory default 'Y'
 * <li> ALLOW_FONT bool mandatory default 'Y'
 * <li> ALLOW_SMILES bool mandatory default 'Y'
 * <li> ALLOW_UPLOAD bool mandatory default 'N'
 * <li> ALLOW_TABLE bool mandatory default 'N'
 * <li> ALLOW_ALIGN bool mandatory default 'Y'
 * <li> ALLOW_UPLOAD_EXT string(255) null
 * <li> ALLOW_MOVE_TOPIC bool mandatory default 'Y'
 * <li> ALLOW_TOPIC_TITLED bool mandatory default 'N'
 * <li> ALLOW_NL2BR bool mandatory default 'N'
 * <li> ALLOW_SIGNATURE bool mandatory default 'Y'
 * <li> ASK_GUEST_EMAIL bool mandatory default 'N'
 * <li> USE_CAPTCHA bool mandatory default 'N'
 * <li> INDEXATION bool mandatory default 'Y'
 * <li> DEDUPLICATION bool mandatory default 'Y'
 * <li> MODERATION bool mandatory default 'N'
 * <li> ORDER_BY enum('P', 'T', 'N', 'V', 'D', 'A', '') mandatory default 'P'
 * <li> ORDER_DIRECTION enum('DESC', 'ASC') mandatory default 'DESC'

 * <li> TOPICS int
 * <li> POSTS int
 * <li> LAST_POSTER_ID int
 * <li> LAST_POSTER_NAME string(255)
 * <li> LAST_POST_DATE datetime
 * <li> LAST_MESSAGE_ID int
 * <li> POSTS_UNAPPROVED int
 * <li> ABS_LAST_POSTER_ID int
 * <li> ABS_LAST_POSTER_NAME string(255)
 * <li> ABS_LAST_POST_DATE datetime
 * <li> ABS_LAST_MESSAGE_ID int

 * <li> EVENT1 string(255) default 'forum'
 * <li> EVENT2 string(255) default 'message'
 * <li> EVENT3 string(255)

 * <li> XML_ID varchar(255)
 * </ul>
 *
 * @package Bitrix\Forum
 */
class ForumTable extends \Bitrix\Main\Entity\DataManager
{
	private static $topicSort = array(
		"P" => "LAST_POST_DATE",
		"T" => "TITLE",
		"N" => "POSTS",
		"V" => "VIEWS",
		"D" => "START_DATE",
		"A" => "USER_START_NAME"
	);
	private static $cache = [];

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_forum';
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
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ID'),
			),
			'FORUM_GROUP_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_FORUM_GROUP_ID'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_NAME'),
				'size' => 255
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_DESCRIPTION'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_SORT'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N','Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ACTIVE'),
			),
			'ALLOW_HTML' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_HTML')
			),
			'ALLOW_ANCHOR' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_ANCHOR')
			),
			'ALLOW_BIU' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_BIU')
			),
			'ALLOW_IMG' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_IMG')
			),
			'ALLOW_VIDEO' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_VIDEO')
			),
			'ALLOW_LIST' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_LIST')
			),
			'ALLOW_QUOTE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_QUOTE')
			),
			'ALLOW_CODE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_CODE')
			),
			'ALLOW_FONT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_FONT')
			),
			'ALLOW_SMILES' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_SMILES')
			),
			'ALLOW_TABLE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_TABLE')
			),
			'ALLOW_ALIGN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_ALIGN')
			),
			'ALLOW_UPLOAD' => array(
				'data_type' => 'boolean',
				'values' => array('Y', 'F', 'A'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_UPLOAD')
			),
			'ALLOW_UPLOAD_EXT' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_UPLOAD'),
				'size' => 255
			),
			'ALLOW_MOVE_TOPIC' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_MOVE_TOPIC')
			),
			'ALLOW_TOPIC_TITLED' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_TOPIC_TITLED')
			),
			'ALLOW_NL2BR' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_NL')
			),
			'ALLOW_SIGNATURE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ALLOW_SIGNATURE')
			),
			'ASK_GUEST_EMAIL' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ASK_GUEST_EMAIL')
			),
			'USE_CAPTCHA' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_USE_CAPTCHA')
			),
			'INDEXATION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_INDEXATION')
			),
			'DEDUPLICATION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_DEDUPLICATION')
			),
			'MODERATION' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_MODERATION')
			),
			'ORDER_BY' =>  array(
				'data_type' => 'enum',
				'values' => self::$topicSort,
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ORDER_BY')
			),
			'ORDER_DIRECTION' =>  array(
				'data_type' => 'enum',
				'values' => array('ASC', 'DESC'),
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ORDER_BY')
			),
			'TOPICS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_TOPICS'),
			),
			'POSTS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_POSTS'),
			),
			'LAST_POSTER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_'),
			),
			'LAST_POSTER_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_LAST_POSTER_NAME'),
			),
			'LAST_POST_DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_LAST_POST_DATE'),
			),
			'LAST_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_'),
			),
			'POSTS_UNAPPROVED' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_'),
			),
			'ABS_LAST_POSTER_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_'),
			),
			'ABS_LAST_POSTER_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ABS_LAST_POSTER_NAME'),
			),
			'ABS_LAST_POST_DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_ABS_LAST_POST_DATE'),
			),
			'ABS_LAST_MESSAGE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_'),
			),
			'EVENT1' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_EVENT1'),
			),
			'EVENT2' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_EVENT2'),
			),
			'EVENT3' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_EVENT3'),
			),
			'XML_ID' => array(
				'data_type' => 'string',
				'title' => Loc::getMessage('FORUM_TABLE_FIELD_EVENT3'),
				'size' => 255
			),
			'PERMISSION' => array(
				'data_type' => 'Bitrix\Forum\Permission',
				'reference' => array('=this.ID' => 'ref.FORUM_ID')
			),
			'SITE' => array(
				'data_type' => 'Bitrix\Forum\ForumSite',
				'reference' => array('=this.ID' => 'ref.FORUM_ID')
			),
		);
	}

	/*
	 * Returns main data
	 * @return array|null
	 */
	public static function getMainData(int $forumId)
	{
		if (!array_key_exists($forumId, self::$cache))
		{
			self::$cache[$forumId] = ForumTable::getList([
				"select" => [
					"ID", "FORUM_GROUP_ID", "NAME", "DESCRIPTION", "SORT", "ACTIVE",
					"ALLOW_HTML", "ALLOW_ANCHOR", "ALLOW_BIU", "ALLOW_IMG", "ALLOW_VIDEO",
					"ALLOW_LIST", "ALLOW_QUOTE", "ALLOW_CODE", "ALLOW_FONT", "ALLOW_SMILES",
					"ALLOW_TABLE", "ALLOW_ALIGN", "ALLOW_UPLOAD", "ALLOW_UPLOAD_EXT",
					"ALLOW_MOVE_TOPIC", "ALLOW_TOPIC_TITLED", "ALLOW_NL2BR", "ALLOW_SIGNATURE",
					"ASK_GUEST_EMAIL", "USE_CAPTCHA" ,"INDEXATION", "DEDUPLICATION",
					"MODERATION", "ORDER_BY", "ORDER_DIRECTION",
					"EVENT1", "EVENT2", "EVENT3", "XML_ID"
				],
				"filter" => [
					"ID" => $forumId
				],
				"cache" => [
					"ttl" => 84600
				]
			])->fetch();
		}
		self::bindOldKernelEvents();
		return self::$cache[$forumId];
	}

	public static function onAfterAdd(\Bitrix\Main\ORM\Event $event)
	{
		self::$cache = [];
		return new Entity\EventResult();
	}

	public static function onAfterUpdate(\Bitrix\Main\ORM\Event $event)
	{
		self::$cache = [];
		return new Entity\EventResult();
	}

	public static function onAfterDelete(\Bitrix\Main\ORM\Event $event)
	{
		self::$cache = [];
		return new Entity\EventResult();
	}

	public static function clearCache() // TODO redesign old forum new to D7
	{
		self::$cache = [];
		self::getEntity()->cleanCache();
	}

	private static function bindOldKernelEvents()  // TODO redesign old forum new to D7 and delete this function
	{
		static $bound = false;
		if ($bound === true)
		{
			return;
		}
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler("forum", "onAfterForumAdd", [__CLASS__, "clearCache"]);
		$eventManager->addEventHandler("forum", "onAfterForumUpdate", [__CLASS__, "clearCache"]);
		$eventManager->addEventHandler("forum", "OnAfterForumDelete", [__CLASS__, "clearCache"]);
		$bound = true;
	}
}
class Forum implements \ArrayAccess {
	use \Bitrix\Forum\Internals\EntityFabric;
	use \Bitrix\Forum\Internals\EntityBaseMethods;

	/** @var int */
	protected $id = 0;
	/** @var array */
	protected $data = [];
	/** @var array */
	protected $strore = [];

	public function __construct($id)
	{
		$this->id = $id;
		if ($id <= 0)
		{
			throw new \Bitrix\Main\ArgumentNullException("Forum id");
		}
		$this->data = ForumTable::getMainData($this->id);
		if (empty($this->data))
		{
			throw new \Bitrix\Main\ObjectNotFoundException("Forum is not found.");
		}
		$this->bindEvents();
		$this->errorCollection = new \Bitrix\Main\ErrorCollection();
	}

	private function bindEvents()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->addEventHandler("forum", "onAfterPermissionSet", [$this, "clearCache"]);
		$eventManager->addEventHandler("forum", "onAfterUserUpdate", [$this, "clearCache"]);
	}

	public function clearCache()
	{
		$this->strore = [];
	}

	public function getPermissions()
	{
		if (!array_key_exists("permission_for_all", $this->strore))
		{
			$dbRes = PermissionTable::getList([
				"select" => ["GROUP_ID", "PERMISSION"],
				"filter" => ["FORUM_ID" => $this->id],
				"cache" => ["ttl" => 84600]
			]);
			$this->strore["permission_for_all"] = [];
			while ($res = $dbRes->fetch())
			{
				$this->strore["permission_for_all"][$res["GROUP_ID"]] = $res["PERMISSION"];
			}
		}
		return $this->strore["permission_for_all"];
	}

	private function getPermissionFromUserGroups(array $groups)
	{
		sort($groups);
		$key = "permission_".implode("_", $groups);
		if (!array_key_exists($key, $this->strore))
		{
			$this->strore[$key] =
			$dbRes = PermissionTable::getList([
				"select" => ["MAX_PERMISSION"],
				"runtime" => [
					new \Bitrix\Main\Entity\ExpressionField("MAX_PERMISSION", "MAX(%s)", ["PERMISSION"])
				],
				"filter" => [
					"FORUM_ID" => $this->id,
					"GROUP_ID" => $groups + [2]
				],
				"group" => "FORUM_ID",
				"cache" => ["ttl" => "3600"]
			]);
			$this->strore[$key] = ($res = $dbRes->fetch()) ? $res["MAX_PERMISSION"] : Permission::ACCESS_DENIED;
		}
		return $this->strore[$key];
	}

	public function getPermissionForUser(\Bitrix\Forum\User $user)
	{
		if ($user->isAdmin())
		{
			$result = Permission::FULL_ACCESS;
		}
		elseif ($this->data["ACTIVE"] != "Y")
		{
			$result = Permission::ACCESS_DENIED;
		}
		else
		{
			$result = $this->getPermissionFromUserGroups($user->getGroups());
		}
		return $result;
	}

	public function getPermissionForUserGroups(array $groups)
	{
		if (\Bitrix\Forum\User::isUserAdmin($groups))
		{
			$result = Permission::FULL_ACCESS;
		}
		elseif ($this->data["ACTIVE"] != "Y")
		{
			$result = Permission::ACCESS_DENIED;
		}
		else
		{
			$result = $this->getPermissionFromUserGroups($groups);
		}
		return $result;
	}

	public function setPermission(array $groups)
	{
		$dbRes = PermissionTable::getList([
			"select" => ["ID"],
			"filter" => [
				"FORUM_ID" => $this->id
			]
		]);
		while ($res = $dbRes->fetch())
		{
			PermissionTable::delete($res["ID"]);
		}
		foreach ($groups as $key => $val)
		{
			PermissionTable::add([
				"FORUM_ID" => $this->id,
				"GROUP_ID" => $key,
				"PERMISSION" => strtoupper($val)
			]);
		}
		foreach (GetModuleEvents("forum", "onAfterPermissionSet", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array($this->id, $groups));
		}
		return true;
	}

	public function getSites()
	{
		if (!array_key_exists("sites", $this->strore))
		{
			$dbRes = ForumSiteTable::getList([
				"select" => ["*"],
				"filter" => ["FORUM_ID" => $this->id],
				"cache" => ["ttl" => 84600]
			]);
			$this->strore["sites"] = [];
			while ($res = $dbRes->fetch())
			{
				$this->strore["sites"][$res["SITE_ID"]] = $res["PATH2FORUM_MESSAGE"];
			}
		}
		return $this->strore["sites"];
	}

	public function calcStat()
	{
		$fields = [
			"TOPICS" => 0,
			"POSTS" => 0,
			"LAST_POSTER_ID" => 0,
			"LAST_POSTER_NAME" => 0,
			"LAST_POST_DATE" => 0,
			"LAST_MESSAGE_ID" => 0,
			"POSTS_UNAPPROVED" => 0,
			"ABS_LAST_POSTER_ID" => 0,
			"ABS_LAST_POSTER_NAME" => 0,
			"ABS_LAST_POST_DATE" => 0,
			"ABS_LAST_MESSAGE_ID" => 0
		];
		if ($res = TopicTable::getList([
			"select" => ["CNT_APPROVED"],
			"filter" => [
				"FORUM_ID" => $this->id,
				"APPROVED" => Topic::APPROVED_APPROVED
			],
			"runtime" => [
				new \Bitrix\Main\Entity\ExpressionField("CNT_APPROVED", "COUNT(*)")
			]
		])->fetch())
		{
			$fields["TOPICS"] = $res["CNT_APPROVED"];
			if ($res = MessageTable::getList([
				"select" => ["CNT_APPROVED", "MAX_APPROVED"],
				"filter" => [
					"FORUM_ID" => $this->id,
					"APPROVED" => Message::APPROVED_APPROVED
				],
				"runtime" => [
					new \Bitrix\Main\Entity\ExpressionField("CNT_APPROVED", "COUNT(*)"),
					new \Bitrix\Main\Entity\ExpressionField("MAX_APPROVED", "MAX(%s)", ["ID"])
				]
			])->fetch())
			{
				$fields["POSTS"] = $res["CNT_APPROVED"];
				$fields["LAST_MESSAGE_ID"] = $res["MAX_APPROVED"];
			}
		}
		if ($res = MessageTable::getList([
			"select" => ["CNT_ALL", "MAX_OF_ALL"],
			"filter" => [
				"FORUM_ID" => $this->id
			],
			"runtime" => [
				new \Bitrix\Main\Entity\ExpressionField("CNT_ALL", "COUNT(*)"),
				new \Bitrix\Main\Entity\ExpressionField("MAX_OF_ALL", "MAX(%s)", ["ID"])
			]
		])->fetch())
		{
			$fields["POSTS_UNAPPROVED"] = $res["CNT_ALL"] - $fields["POSTS"];
			$fields["ABS_LAST_MESSAGE_ID"] = $res["MAX_OF_ALL"];
		}
		if ($fields["LAST_MESSAGE_ID"] > 0 || $fields["ABS_LAST_MESSAGE_ID"] > 0)
		{
			$dbRes = MessageTable::getList([
				"select" => ["ID", "AUTHOR_ID", "AUTHOR_NAME", "POST_DATE"],
				"filter" => [
					"ID" => [
						$fields["LAST_MESSAGE_ID"], $fields["ABS_LAST_MESSAGE_ID"]
					]
				]
			]);
			while ($res = $dbRes->fetch())
			{
				if ($res["ID"] == $fields["LAST_MESSAGE_ID"])
				{
					$fields["LAST_POSTER_ID"] = $res["AUTHOR_ID"];
					$fields["LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
					$fields["LAST_POST_DATE"] = $res["POST_DATE"];
				}
				if ($res["ID"] == $fields["ABS_LAST_MESSAGE_ID"])
				{
					$fields["ABS_LAST_POSTER_ID"] = $res["AUTHOR_ID"];
					$fields["ABS_LAST_POSTER_NAME"] = $res["AUTHOR_NAME"];
					$fields["ABS_LAST_POST_DATE"] = $res["POST_DATE"];
				}
			}
		}
		ForumTable::update($this->id, $fields);
	}

	public function incrementStatistic(array $message)
	{
		$fields = [
			"ABS_LAST_POSTER_ID" => $message["AUTHOR_ID"],
			"ABS_LAST_POSTER_NAME" => $message["AUTHOR_NAME"],
			"ABS_LAST_POST_DATE" => $message["POST_DATE"],
			"ABS_LAST_MESSAGE_ID" => $message["ID"]
		];
		if ($message["APPROVED"] == "Y")
		{
			$fields += [
				"LAST_POSTER_ID" => $message["AUTHOR_ID"],
				"LAST_POSTER_NAME" => $message["AUTHOR_NAME"],
				"LAST_POST_DATE" => $message["POST_DATE"],
				"LAST_MESSAGE_ID" => $message["ID"]
			];
			$fields["POSTS"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', "POSTS");
			if ($message["NEW_TOPIC"] == "Y")
			{
				$fields["TOPICS"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', "TOPICS");
			}
		}
		else
		{
			$fields["POSTS_UNAPPROVED"] = new \Bitrix\Main\DB\SqlExpression('?# + 1', "POSTS_UNAPPROVED");
		}
		ForumTable::update($this->getId(), $fields);

		if (\CModule::IncludeModule("statistic"))
		{
			$F_EVENT1 = $this->data["EVENT1"];
			$F_EVENT2 = $this->data["EVENT2"];
			$F_EVENT3 = $this->data["EVENT3"];
			if (empty($F_EVENT3))
			{
				$F_EVENT3 = $_SERVER["HTTP_REFERER"];
			}
			\CStatistics::Set_Event($F_EVENT1, $F_EVENT2, $F_EVENT3);
		}
	}
}