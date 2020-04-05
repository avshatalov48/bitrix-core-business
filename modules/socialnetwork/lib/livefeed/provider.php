<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Item\Subscription;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Socialnetwork\Item\UserContentView;

Loc::loadMessages(__FILE__);

abstract class Provider
{
	const DATA_RESULT_TYPE_SOURCE = 'SOURCE';

	const TYPE_POST = 'POST';
	const TYPE_COMMENT = 'COMMENT';

	const DATA_ENTITY_TYPE_BLOG_POST = 'BLOG_POST';
	const DATA_ENTITY_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';
	const DATA_ENTITY_TYPE_TASKS_TASK = 'TASK';
	const DATA_ENTITY_TYPE_FORUM_POST = 'FORUM_POST';
	const DATA_ENTITY_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';
	const DATA_ENTITY_TYPE_LOG_ENTRY = 'LOG_ENTRY';
	const DATA_ENTITY_TYPE_LOG_COMMENT = 'LOG_COMMENT';
	const DATA_ENTITY_TYPE_RATING_LIST = 'RATING_LIST';
	const DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM = 'PHOTO_ALBUM';
	const DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO = 'PHOTO_PHOTO';
	const DATA_ENTITY_TYPE_LISTS_ITEM = 'LISTS_NEW_ELEMENT';

	const PERMISSION_DENY = 'D';
	const PERMISSION_READ = 'I';
	const PERMISSION_FULL = 'W';

	const CONTENT_TYPE_ID = false;

	protected $entityId = 0;
	protected $logId = 0;
	protected $sourceFields = array();
	protected $siteId = false;

	protected $cloneDiskObjects = false;
	protected $sourceDescription = '';
	protected $sourceTitle = '';
	protected $sourceAttachedDiskObjects = array();
	protected $sourceDiskObjects = array();
	protected $diskObjectsCloned = array();
	protected $attachedDiskObjectsCloned = array();

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	public function setSiteId($siteId)
	{
		$this->siteId = $siteId;
	}

	public function getSiteId()
	{
		return $this->siteId;
	}

	public static function getId()
	{
		return 'BASE';
	}

	public function getEventId()
	{
		return false;
	}

	public function getType()
	{
		return false;
	}

	final private static function getTypes()
	{
		return array(
			self::TYPE_POST,
			self::TYPE_COMMENT,
		);
	}

	final private static function getProvider($entityType)
	{
		switch ($entityType)
		{
			case self::DATA_ENTITY_TYPE_BLOG_POST:
				$provider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();
				break;
			case self::DATA_ENTITY_TYPE_BLOG_COMMENT:
				$provider = new \Bitrix\Socialnetwork\Livefeed\BlogComment();
				break;
			case self::DATA_ENTITY_TYPE_TASKS_TASK:
				$provider = new \Bitrix\Socialnetwork\Livefeed\TasksTask();
				break;
			case self::DATA_ENTITY_TYPE_FORUM_POST:
				$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
				break;
			case self::DATA_ENTITY_TYPE_CALENDAR_EVENT:
				$provider = new \Bitrix\Socialnetwork\Livefeed\CalendarEvent();
				break;
			case self::DATA_ENTITY_TYPE_LOG_ENTRY:
				$provider = new \Bitrix\Socialnetwork\Livefeed\LogEvent();
				break;
			case self::DATA_ENTITY_TYPE_LOG_COMMENT:
				$provider = new \Bitrix\Socialnetwork\Livefeed\LogComment();
				break;
			case self::DATA_ENTITY_TYPE_RATING_LIST:
				$provider = new \Bitrix\Socialnetwork\Livefeed\RatingVoteList();
				break;
			case self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM:
				$provider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryAlbum();
				break;
			case self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO:
				$provider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryPhoto();
				break;
			case self::DATA_ENTITY_TYPE_LISTS_ITEM:
				$provider = new \Bitrix\Socialnetwork\Livefeed\ListsItem();
				break;
			default:
				$provider = false;
		}

		return $provider;
	}

	public static function init(array $params)
	{
		$provider = self::getProvider($params['ENTITY_TYPE']);
		if ($provider)
		{
			$provider->setEntityId($params['ENTITY_ID']);
			$provider->setSiteId(isset($params['SITE_ID']) ? $params['SITE_ID'] : SITE_ID);
			if (
				isset($params['CLONE_DISK_OBJECTS'])
				&& $params['CLONE_DISK_OBJECTS'] === true
			)
			{
				$provider->cloneDiskObjects = true;
			}
			if (
				isset($params['LOG_ID'])
				&& intval($params['LOG_ID']) > 0
			)
			{
				$provider->setLogId(intval($params['LOG_ID']));
			}
		}

		return $provider;
	}

	public function initSourceFields()
	{
	}

	public static function canRead($params)
	{
		return false;
	}

	protected function getPermissions(array $entity)
	{
		return self::PERMISSION_DENY;
	}

	public function getLogId()
	{
		$result = false;

		if (intval($this->logId) > 0)
		{
			$result = intval($this->logId);
		}
		else
		{
			$eventId = $this->getEventId();

			if (!empty($eventId))
			{
				if ($this->getType() == 'entry')
				{
					$res = \CSocNetLog::getList(
						array(),
						array(
							'SOURCE_ID' => $this->entityId,
							'EVENT_ID' => $eventId
						),
						false,
						array('nTopCount' => 1),
						array('ID')
					);

					if (
						($logEntry = $res->fetch())
						&& (intval($logEntry['ID']) > 0)
					)
					{
						$result = $this->logId = intval($logEntry['ID']);
					}
				}
				elseif ($this->getType() == 'comment')
				{
					$res = \CSocNetLogComments::getList(
						array(),
						array(
							'SOURCE_ID' => $this->entityId,
							'EVENT_ID' => $eventId
						),
						false,
						array('nTopCount' => 1),
						array('ID', 'LOG_ID')
					);

					if (
						($logEntry = $res->fetch())
						&& (intval($logEntry['LOG_ID']) > 0)
					)
					{
						$result = $this->logId = intval($logEntry['LOG_ID']);
					}
				}
			}
		}

		return $result;
	}

	public function getSonetGroupsAvailable($feature = false, $operation = false)
	{
		global $USER;

		$result = array();

		$logRights = $this->getLogRights();
		if (
			!empty($logRights)
			&& is_array($logRights)
		)
		{
			foreach($logRights as $groupCode)
			{
				if (preg_match('/^SG(\d+)/', $groupCode, $matches))
				{
					$result[] = $matches[1];
				}
			}
		}

		if (
			!empty($result)
			&& !!$feature
			&& !!$operation
		)
		{
			$availability = \CSocNetFeaturesPerms::canPerformOperation(
				$USER->getId(),
				SONET_ENTITY_GROUP,
				$result,
				$feature,
				$operation
			);
			foreach ($result as $key => $groupId)
			{
				if (
					!isset($availability[$groupId])
					|| !$availability[$groupId]
				)
				{
					unset($result[$key]);
				}
			}
		}
		$result = array_unique($result);

		return $result;
	}

	public function getLogRights()
	{
		$result = array();
		$logId = $this->getLogId();

		if ($logId  > 0)
		{
			$result = $this->getLogRightsEntry();
		}

		return $result;
	}

	protected function getLogRightsEntry()
	{
		$result = array();

		if ($this->logId > 0)
		{
			$res = \CSocNetLogRights::getList(
				array(),
				array(
					'LOG_ID' => $this->logId
				)
			);

			while ($right = $res->fetch())
			{
				$result[] = $right['GROUP_CODE'];
			}
		}

		return $result;
	}

	final protected function setEntityId($entityId)
	{
		$this->entityId = $entityId;
	}

	final protected function getEntityId()
	{
		return $this->entityId;
	}

	final protected function setLogId($logId)
	{
		$this->logId = $logId;
	}

	final protected function setSourceFields(array $fields)
	{
		$this->sourceFields = $fields;
	}

	final protected function getSourceFields()
	{
		return $this->sourceFields;
	}

	final protected function setSourceDescription($description)
	{
		$this->sourceDescription = $description;
	}

	public function getSourceDescription()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$result = $this->sourceDescription;

		if ($this->cloneDiskObjects === true)
		{
			$this->getAttachedDiskObjects(true);
			$result = $this->processDescription($result);
		}

		return $result;
	}

	final protected  function setSourceTitle($title)
	{
		$this->sourceTitle = $title;
	}

	public  function getSourceTitle()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceTitle;
	}

	final protected function setSourceAttachedDiskObjects(array $diskAttachedObjects)
	{
		$this->sourceAttachedDiskObjects = $diskAttachedObjects;
	}

	final protected function setSourceDiskObjects(array $files)
	{
		$this->sourceDiskObjects = $files;
	}

	final public function setDiskObjectsCloned(array $values)
	{
		$this->diskObjectsCloned = $values;
	}

	final public function getDiskObjectsCloned()
	{
		return $this->diskObjectsCloned;
	}

	final public function getAttachedDiskObjectsCloned()
	{
		return $this->attachedDiskObjectsCloned;
	}

	public function getSourceAttachedDiskObjects()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceAttachedDiskObjects;
	}

	public function getSourceDiskObjects()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceDiskObjects;
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return array();
	}

	protected static function cloneUfValues(array $values)
	{
		global $USER;

		$result = array();
		if (Loader::includeModule('disk'))
		{
			$result = \Bitrix\Disk\Driver::getInstance()->getUserFieldManager()->cloneUfValuesFromAttachedObject($values, $USER->getId());
		}

		return $result;
	}

	public function getDiskObjects($entityId, $clone = false)
	{
		$result = array();

		if ($clone)
		{
			$result = $this->getAttachedDiskObjects(true);

			if (
				empty($this->diskObjectsCloned)
				&& Loader::includeModule('disk')
			)
			{
				foreach($result as $clonedDiskObjectId)
				{
					if (
						in_array($clonedDiskObjectId, $this->attachedDiskObjectsCloned)
						&& ($attachedDiskObjectId = array_search($clonedDiskObjectId, $this->attachedDiskObjectsCloned))
					)
					{
						$attachedObject = \Bitrix\Disk\AttachedObject::loadById($attachedDiskObjectId);
						if ($attachedObject)
						{
							$this->diskObjectsCloned[\Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$attachedObject->getObjectId()] = $this->attachedDiskObjectsCloned[$attachedDiskObjectId];
						}
					}
				}
			}

			return $result;
		}
		else
		{
			$diskObjects = $this->getAttachedDiskObjects(false);

			if (
				!empty($diskObjects)
				&& Loader::includeModule('disk')
			)
			{
				foreach ($diskObjects as $attachedObjectId)
				{
					$attachedObject = \Bitrix\Disk\AttachedObject::loadById($attachedObjectId);
					if ($attachedObject)
					{
						$result[] = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$attachedObject->getObjectId();
					}
				}
			}
		}

		return $result;
	}

	final private function processDescription($text)
	{
		$result = $text;

		$diskObjectsCloned = $this->getDiskObjectsCloned();
		$attachedDiskObjectsCloned = $this->getAttachedDiskObjectsCloned();

		if (
			!empty($diskObjectsCloned)
			&& is_array($diskObjectsCloned)
		)
		{
			$result = preg_replace_callback(
				"#\\[disk file id=(n\\d+)\\]#is".BX_UTF_PCRE_MODIFIER,
				array($this, "parseDiskObjectsCloned"),
				$result
			);
		}

		if (
			!empty($attachedDiskObjectsCloned)
			&& is_array($attachedDiskObjectsCloned)
		)
		{
			$result = preg_replace_callback(
				"#\\[disk file id=(\\d+)\\]#is".BX_UTF_PCRE_MODIFIER,
				array($this, "parseAttachedDiskObjectsCloned"),
				$result
			);
		}

		return $result;
	}

	final private function parseDiskObjectsCloned($matches)
	{
		$text = $matches[0];

		$diskObjectsCloned = $this->getDiskObjectsCloned();

		if (array_key_exists($matches[1], $diskObjectsCloned))
		{
			$text = str_replace($matches[1], $diskObjectsCloned[$matches[1]], $text);
		}

		return $text;
	}

	final private function parseAttachedDiskObjectsCloned($matches)
	{
		$text = $matches[0];

		$attachedDiskObjectsCloned = $this->getAttachedDiskObjectsCloned();

		if (array_key_exists($matches[1], $attachedDiskObjectsCloned))
		{
			$text = str_replace($matches[1], $attachedDiskObjectsCloned[$matches[1]], $text);
		}

		return $text;
	}

	public function getLiveFeedUrl()
	{
		return '';
	}

	final function getContentTypeId()
	{
		return static::CONTENT_TYPE_ID;
	}

	public static function getContentId($event = array())
	{
		$result = false;

		if (!is_array($event))
		{
			return $result;
		}

		$contentEntityType = $contentEntityId = false;

		if (
			!empty($event["EVENT_ID"])
			&& $event["EVENT_ID"] == 'photo'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM;
			$contentEntityId = intval($event["SOURCE_ID"]);
		}
		elseif (
			!empty($event["EVENT_ID"])
			&& $event["EVENT_ID"] == 'photo_photo'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO;
			$contentEntityId = intval($event["SOURCE_ID"]);
		}
		elseif (
			!empty($event["RATING_TYPE_ID"])
			&& !empty($event["RATING_ENTITY_ID"])
			&& intval($event["RATING_ENTITY_ID"]) > 0
		)
		{
			$contentEntityType = $event["RATING_TYPE_ID"];
			$contentEntityId = intval($event["RATING_ENTITY_ID"]);
		}
		elseif (
			!empty($event["EVENT_ID"])
			&& !empty($event["SOURCE_ID"])
			&& intval($event["SOURCE_ID"]) > 0
		)
		{
			switch ($event["EVENT_ID"])
			{
				case "tasks":
					$contentEntityType = self::DATA_ENTITY_TYPE_TASKS_TASK;
					$contentEntityId = intval($event["SOURCE_ID"]);
					break;
				case "calendar":
					$contentEntityType = self::DATA_ENTITY_TYPE_CALENDAR_EVENT;
					$contentEntityId = intval($event["SOURCE_ID"]);
					break;
				default:
			}
		}

		if (
			$contentEntityType
			&& $contentEntityId > 0
		)
		{
			$result = array(
				'ENTITY_TYPE' => $contentEntityType,
				'ENTITY_ID' => $contentEntityId
			);
		}

		return $result;
	}

	public function setContentView($params = array())
	{
		global $USER;

		if (!is_array($params))
		{
			$params = array();
		}

		if (
			!isset($params["user_id"])
			&& is_object($USER)
			&& isset($_SESSION["SONET_ADMIN"])
		) // don't track users on God Mode
		{
			return false;
		}

		$userId = (
			isset($params["user_id"])
			&& intval($params["user_id"]) > 0
				? intval($params["user_id"])
				: (
					is_object($USER)
						? $USER->getId()
						: 0
				)
		);

		$contentTypeId = $this->getContentTypeId();
		$contentEntityId = $this->getEntityId();
		$logId = $this->getLogId();
		$save = (!isset($params["save"]) || !!$params["save"]);

		if (
			intval($userId) <= 0
			|| !$contentTypeId
		)
		{
			return false;
		}

		$viewParams = array(
			'userId' => $userId,
			'typeId' => $contentTypeId,
			'entityId' => $contentEntityId,
			'logId' => $logId,
			'save' => $save
		);

		$result = UserContentViewTable::set($viewParams);

		if (
			$result
			&& isset($result['success'])
			&& $result['success']
		)
		{
/*
			TODO: markAsRead sonet module notifications
			ContentViewHandler::onContentViewed($viewParams);
*/
			if (UserContentView::getAvailability())
			{
				if (
					isset($result['savedInDB'])
					&& $result['savedInDB']
				)
				{
					if (Loader::includeModule('pull'))
					{
						\CPullWatch::addToStack('CONTENTVIEW'.$contentTypeId."-".$contentEntityId,
							array(
								'module_id' => 'contentview',
								'command' => 'add',
								'expiry' => 60,
								'params' => array(
									"USER_ID" => $userId,
									"TYPE_ID" => $contentTypeId,
									"ENTITY_ID" => $contentEntityId,
									"CONTENT_ID" => $contentTypeId."-".$contentEntityId
								)
							)
						);
					}
				}

				if ($logId > 0)
				{
					Subscription::onContentViewed(array(
						'userId' => $userId,
						'logId' => $logId
					));
				}

				$event = new Main\Event(
					'socialnetwork', 'onContentViewed',
					$viewParams
				);
				$event->send();
			}
		}

		return $result;
	}

	final public static function getEntityData(array $params)
	{
		$entityType = false;
		$entityId = false;

		$type = (
			isset($params['TYPE'])
			&& in_array($params['TYPE'], self::getTypes())
				? $params['TYPE']
				: self::TYPE_POST
		);

		if (!empty($params['EVENT_ID']))
		{
			$blogPostLivefeedProvider = new BlogPost;
			if (
				$type == self::TYPE_POST
				&& in_array($params['EVENT_ID'], $blogPostLivefeedProvider->getEventId())
			)
			{
				$entityType = self::DATA_ENTITY_TYPE_BLOG_POST;
				$entityId = (isset($params['SOURCE_ID']) ? intval($params['SOURCE_ID']) : false);
			}
		}

		return (
			$entityType
			&& $entityId
				? array(
					'ENTITY_TYPE' => $entityType,
					'ENTITY_ID' => $entityId
				)
				: false
		);
	}
}