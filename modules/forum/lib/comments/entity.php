<?php

namespace Bitrix\Forum\Comments;

use Bitrix\Forum\ForumTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Event;
use Bitrix\Main\SystemException;

class Entity
{
	const ENTITY_TYPE = 'default';
	const MODULE_ID = 'forum';
	const XML_ID_PREFIX = 'TOPIC_';

	/** @var array */
	protected $entity;
	/** @var array */
	protected $forum;
	/** @var array  */
	protected static $permissions = array();
	/** @var bool */
	private $editOwn = false;
	protected static $pathToUser  = '/company/personal/user/#user_id#/';
	protected static $pathToGroup = '/workgroups/group/#group_id#/';

	/** @var array */
	protected static $entities;

	/**
	 * @param array $entity
	 * @param array $storage
	 */
	public function __construct(array $entity, array $storage)
	{
		$this->entity = array(
			"type" => $entity["type"],
			"id" => $entity["id"],
			"xml_id" => $entity["xml_id"]
		);
		$this->forum = $storage;
		$this->editOwn = (\COption::GetOptionString("forum", "USER_EDIT_OWN_POST", "Y") == "Y");
	}

	public function getId()
	{
		return $this->entity["id"];
	}

	public function getType()
	{
		return $this->entity["type"];
	}

	public function getXmlId()
	{
		if (!empty($this->entity["xml_id"]))
			return $this->entity["xml_id"];
		return strtoupper($this->entity["type"]."_".$this->entity["id"]);
	}

	/**
	 * @return array
	 */
	public function getFullId()
	{
		return $this->entity;
	}

	public static function className()
	{
		return get_called_class();
	}

	public static function getModule()
	{
		return static::MODULE_ID;
	}

	public static function getEntityType()
	{
		return static::ENTITY_TYPE;
	}

	public static function getXmlIdPrefix()
	{
		return static::XML_ID_PREFIX;
	}

	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	public function canRead($userId)
	{
		return $this->getPermission($userId) >= "E";
	}
	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	public function canAdd($userId)
	{
		return $this->getPermission($userId) >= "I";
	}

	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	public function canEdit($userId)
	{
		return $this->getPermission($userId) >= "U";
	}
	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	public function canEditOwn($userId)
	{
		return $this->canEdit($userId) || $this->getPermission($userId) >= "I" && $this->editOwn;
	}
	/**
	 * @param integer $userId User id.
	 * @return bool
	 */
	public function canModerate($userId)
	{
		return $this->getPermission($userId) >= "Q";
	}

	/**
	 * @param integer $userId User id.
	 * @param string $permission A < E < I < M < Q < U < Y
	// A - NO ACCESS		E - READ			I - ANSWER
	// M - NEW TOPIC		Q - MODERATE	U - EDIT			Y - FULL_ACCESS.
	 * @return $this
	 */
	public function setPermission($userId, $permission)
	{
		if (is_string($permission))
		{
			if (!is_array(self::$permissions[$userId]))
				self::$permissions[$userId] = array();
			self::$permissions[$userId][$this->forum["ID"]] = $permission;
		}
		return $this;
	}

	/**
	 * @param bool $permission
	 * @return $this
	 */
	public function setEditOwn($permission)
	{
		$this->editOwn = $permission;
		return $this;
	}

	/**
	 * @param integer $userId User id.
	 * @return $this
	 */
	public function getPermission($userId)
	{
		if (!array_key_exists($userId, self::$permissions))
		{
			self::$permissions[$userId] = array();
			if (!array_key_exists($this->forum["ID"], self::$permissions[$userId]))
			{
				if (\CForumUser::IsAdmin($userId))
					$result = "Y";
				else if ($this->forum["ACTIVE"] != "Y")
					$result = "A";
				else if (\CForumUser::IsLocked($userId))
					$result = \CForumNew::GetPermissionUserDefault($this->forum["ID"]);
				else
				{
					if (in_array($this->getType(), array('PH', 'TR', 'TE', 'IBLOCK')))
					{
						$result = 'Y';
					}
					else
					{
						$res = ForumTable::getList(array(
							'filter' => array(
								'=ID' => $this->forum["ID"],
								'@XML_ID' => array(
									'USERS_AND_GROUPS'
								)
							),
							'select' => array('ID')
						));
						if ($forumFields = $res->fetch())
						{
							$result = 'Y';
						}
						else
						{
							$result = \CForumNew::GetUserPermission($this->forum["ID"], $userId);
						}
					}
				}

				self::$permissions[$userId][$this->forum["ID"]] = $result;
			}
		}
		return self::$permissions[$userId][$this->forum["ID"]];
	}
	/**
	 * @param string $type Type entity.
	 * @return array|null
	 */
	public static function getEntityByType($type = "")
	{
		$type = strtolower($type);
		$entities = self::getEntities();
		return (array_key_exists($type, $entities) ? $entities[$type] : null);
	}

	/**
	 * @param string $xmlId Type entity.
	 * @return array|null
	 */
	public static function getEntityByXmlId($xmlId = "")
	{
		$xmlId = strtoupper($xmlId);
		$entities = self::getEntities();
		$result = null;
		foreach ($entities as $entity)
		{
			if (preg_match("/^".$entity["xmlIdPrefix"]."(\\d+)/", $xmlId))
			{
				$result = $entity;
				break;
			}
		}
		return $result;
	}

	private static function getEntities()
	{
		if (!is_array(self::$entities))
		{
			self::$entities = array(
				"tk" => array(
					"entityType" => "tk",
					"className" => TaskEntity::className(),
					"moduleId" => "tasks",
					"xmlIdPrefix" => TaskEntity::getXmlIdPrefix()),
				"wf" => array(
					"entityType" => "wf",
					"className" => WorkflowEntity::className(),
					"moduleId" => "lists",
					"xmlIdPrefix" => WorkflowEntity::getXmlIdPrefix()),
				"ev" => array(
					"entityType" => "ev",
					"className" => CalendarEntity::className(),
					"moduleId" => "calendar",
					"xmlIdPrefix" => CalendarEntity::getXmlIdPrefix()),
				"te" => array(
					"entityType" => "te",
					"className" => Entity::className(),
					"moduleId" => "timeman",
					"xmlIdPrefix" => 'TIMEMAN_ENTRY_'
				),
				"tr" => array(
					"entityType" => "tr",
					"className" => Entity::className(),
					"moduleId" => "timeman",
					"xmlIdPrefix" => 'TIMEMAN_REPORT_'
				),
				"default" => array(
					"entityType" => "default",
					"className" => Entity::className(),
					"moduleId" => "forum",
					"xmlIdPrefix" => Entity::getXmlIdPrefix()
				)
			);

			$event = new Event("forum", "onBuildAdditionalEntitiesList");
			$event->send();

			foreach ($event->getResults() as $evenResult)
			{
				$result = $evenResult->getParameters();
				if (!is_array($result))
				{
					throw new SystemException('Event onBuildAdditionalEntitiesList: result must be an array.');
				}

				foreach ($result as $connector)
				{
					if (empty($connector['ENTITY_TYPE']))
					{
						throw new SystemException('Event onBuildAdditionalEntitiesList: key ENTITY_TYPE is not found.');
					}

					if (empty($connector['MODULE_ID']))
					{
						throw new SystemException('Event onBuildAdditionalEntitiesList: key MODULE_ID is not found.');
					}

					if (empty($connector['CLASS']))
					{
						throw new SystemException('Event onBuildAdditionalEntitiesList: key CLASS is not found.');
					}

					if (is_string($connector['CLASS']) && class_exists($connector['CLASS']))
					{
						self::$entities[strtolower($connector['ENTITY_TYPE'])] = array(
							"id" => strtolower($connector['ENTITY_TYPE']),
							"className" => str_replace('\\\\', '\\', $connector['CLASS']),
							"moduleId" => $connector['MODULE_ID'],
							"xmlIdPrefix" => strtoupper($connector['ENTITY_TYPE'])."_"
						);
					}
				}
			}
		}
		return self::$entities;
	}
	/**
	 * Event before indexing message.
	 * @param integer $id Message ID.
	 * @param array $message Message data.
	 * @param array &$index Search index array.
	 * @return boolean
	 */
	public static function onMessageIsIndexed($id, array $message, array &$index)
	{
		return (empty($message["PARAM1"]) && empty($message["PARAM2"]));
	}
}