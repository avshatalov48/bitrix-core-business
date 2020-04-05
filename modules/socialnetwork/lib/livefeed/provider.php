<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Item\Subscription;
use Bitrix\Socialnetwork\LogTable;
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
	const DATA_ENTITY_TYPE_FORUM_TOPIC = 'FORUM_TOPIC';
	const DATA_ENTITY_TYPE_FORUM_POST = 'FORUM_POST';
	const DATA_ENTITY_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';
	const DATA_ENTITY_TYPE_LOG_ENTRY = 'LOG_ENTRY';
	const DATA_ENTITY_TYPE_LOG_COMMENT = 'LOG_COMMENT';
	const DATA_ENTITY_TYPE_RATING_LIST = 'RATING_LIST';
	const DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM = 'PHOTO_ALBUM';
	const DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO = 'PHOTO_PHOTO';
	const DATA_ENTITY_TYPE_LISTS_ITEM = 'LISTS_NEW_ELEMENT';
	const DATA_ENTITY_TYPE_WIKI = 'WIKI';
	const DATA_ENTITY_TYPE_TIMEMAN_ENTRY = 'TIMEMAN_ENTRY';
	const DATA_ENTITY_TYPE_TIMEMAN_REPORT = 'TIMEMAN_REPORT';
	const DATA_ENTITY_TYPE_INTRANET_NEW_USER = 'INTRANET_NEW_USER';
	const DATA_ENTITY_TYPE_BITRIX24_NEW_USER = 'BITRIX24_NEW_USER';

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
	protected $sourceOriginalText = '';
	protected $sourceAuxData = array();
	protected $sourceAttachedDiskObjects = array();
	protected $sourceDiskObjects = array();
	protected $diskObjectsCloned = array();
	protected $attachedDiskObjectsCloned = array();

	protected $logEventId = null;
	protected $logEntityType = null;
	protected $logEntityId = null;

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

	public function getCommentProvider()
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

	final public static function getProvider($entityType)
	{
		$provider = false;

		$moduleEvent = new Main\Event(
			'socialnetwork',
			'onLogProviderGetProvider',
			array(
				'entityType' => $entityType
			)
		);
		$moduleEvent->send();

		foreach ($moduleEvent->getResults() as $moduleEventResult)
		{
			if ($moduleEventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$moduleEventParams = $moduleEventResult->getParameters();

				if (
					is_array($moduleEventParams)
					&& !empty($moduleEventParams['provider'])
				)
				{
					$provider = $moduleEventParams['provider'];
				}
				break;
			}
		}

		if (!$provider)
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
				case self::DATA_ENTITY_TYPE_FORUM_TOPIC:
					$provider = new \Bitrix\Socialnetwork\Livefeed\ForumTopic();
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
				case self::DATA_ENTITY_TYPE_WIKI:
					$provider = new \Bitrix\Socialnetwork\Livefeed\Wiki();
					break;
				case self::DATA_ENTITY_TYPE_TIMEMAN_ENTRY:
					$provider = new \Bitrix\Socialnetwork\Livefeed\TimemanEntry();
					break;
				case self::DATA_ENTITY_TYPE_TIMEMAN_REPORT:
					$provider = new \Bitrix\Socialnetwork\Livefeed\TimemanReport();
					break;
			case self::DATA_ENTITY_TYPE_INTRANET_NEW_USER:
				$provider = new \Bitrix\Socialnetwork\Livefeed\IntranetNewUser();
				break;
			case self::DATA_ENTITY_TYPE_BITRIX24_NEW_USER:
				$provider = new \Bitrix\Socialnetwork\Livefeed\Bitrix24NewUser();
				break;
				default:
					$provider = false;
			}
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
				if ($this->getType() == Provider::TYPE_POST)
				{
					$filter = array(
						'EVENT_ID' => $eventId
					);

					if ($this->getId() == LogEvent::PROVIDER_ID)
					{
						$filter['=ID'] = $this->entityId;
					}
					else
					{
						$filter['=SOURCE_ID'] = $this->entityId;
					}

					$res = \CSocNetLog::getList(
						array(),
						$filter,
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
				elseif ($this->getType() == Provider::TYPE_COMMENT)
				{
					$filter = array(
						'EVENT_ID' => $eventId
					);

					if ($this->getId() == LogComment::PROVIDER_ID)
					{
						$filter['ID'] = $this->entityId;
					}
					else
					{
						$filter['SOURCE_ID'] = $this->entityId;
					}

					$res = \CSocNetLogComments::getList(
						array(),
						$filter,
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

	public function setEntityId($entityId)
	{
		$this->entityId = $entityId;
	}

	final protected function getEntityId()
	{
		return $this->entityId;
	}

	final public function setLogId($logId)
	{
		$this->logId = $logId;
	}

	final protected function setSourceFields(array $fields)
	{
		$this->sourceFields = $fields;
	}

	public function initSourceFields()
	{
		return $this->sourceFields;
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

	final protected function setSourceTitle($title)
	{
		$this->sourceTitle = $title;
	}

	public function getSourceTitle()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceTitle;
	}

	final protected function setSourceOriginalText($text)
	{
		$this->sourceOriginalText = $text;
	}

	public function getSourceOriginalText()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceOriginalText;
	}

	final protected function setSourceAuxData($auxData)
	{
		$this->sourceAuxData = $auxData;
	}

	public function getSourceAuxData()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceAuxData;
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

		$moduleEvent = new Main\Event(
			'socialnetwork',
			'onLogProviderGetContentId',
			array(
				'eventFields' => $event
			)
		);
		$moduleEvent->send();

		foreach ($moduleEvent->getResults() as $moduleEventResult)
		{
			if ($moduleEventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$moduleEventParams = $moduleEventResult->getParameters();

				if (
					is_array($moduleEventParams)
					&& !empty($moduleEventParams['contentEntityType'])
					&& !empty($moduleEventParams['contentEntityId'])
				)
				{
					$contentEntityType = $moduleEventParams['contentEntityType'];
					$contentEntityId = $moduleEventParams['contentEntityId'];
				}
				break;
			}
		}

		if (
			$contentEntityType
			&& $contentEntityId > 0
		)
		{
			return array(
				'ENTITY_TYPE' => $contentEntityType,
				'ENTITY_ID' => $contentEntityId
			);
		}

		// getContent

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
			!empty($event["EVENT_ID"])
			&& $event["EVENT_ID"] == 'data'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_LOG_ENTRY;
			$contentEntityId = intval($event["ID"]);
		}
		elseif (
			!empty($event["RATING_TYPE_ID"])
			&& !empty($event["RATING_ENTITY_ID"])
			&& intval($event["RATING_ENTITY_ID"]) > 0
		)
		{
			$contentEntityType = $event["RATING_TYPE_ID"];
			$contentEntityId = intval($event["RATING_ENTITY_ID"]);

			if (in_array($event["RATING_TYPE_ID"], array('IBLOCK_ELEMENT', 'IBLOCK_SECTION')))
			{
				$res = LogTable::getList(array(
					'filter' => array(
						'=RATING_TYPE_ID' => $event["RATING_TYPE_ID"],
						'=RATING_ENTITY_ID' => $event["RATING_ENTITY_ID"],
					),
					'select' => array('EVENT_ID')
				));
				if ($logEntryFields = $res->fetch())
				{
					if ($event["RATING_TYPE_ID"] == 'IBLOCK_ELEMENT')
					{
						$found = false;
						$photogalleryPhotoProvider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryPhoto;
						if (in_array($logEntryFields['EVENT_ID'], $photogalleryPhotoProvider->getEventId()))
						{
							$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO;
							$contentEntityId = intval($event["RATING_ENTITY_ID"]);
							$found = true;
						}

						if (!$found)
						{
							$wikiProvider = new \Bitrix\Socialnetwork\Livefeed\Wiki;
							if (in_array($logEntryFields['EVENT_ID'], $wikiProvider->getEventId()))
							{
								$contentEntityType = self::DATA_ENTITY_TYPE_WIKI;
								$contentEntityId = intval($event["RATING_ENTITY_ID"]);
								$found = true;
							}
						}
					}
					elseif ($event["RATING_TYPE_ID"] == 'IBLOCK_SECTION')
					{
						$photogalleryalbumProvider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryAlbum;
						if (in_array($logEntryFields['EVENT_ID'], $photogalleryalbumProvider->getEventId()))
						{
							$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM;
							$contentEntityId = intval($event["RATING_ENTITY_ID"]);
						}
					}
				}
			}
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
				case "timeman_entry":
					$contentEntityType = self::DATA_ENTITY_TYPE_TIMEMAN_ENTRY;
					$contentEntityId = intval($event["SOURCE_ID"]);
					break;
				case "report":
					$contentEntityType = self::DATA_ENTITY_TYPE_TIMEMAN_REPORT;
					$contentEntityId = intval($event["SOURCE_ID"]);
					break;
				case "lists_new_element":
					$contentEntityType = self::DATA_ENTITY_TYPE_LISTS_ITEM;
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
								'expiry' => 0,
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

	public function getSuffix()
	{
		return '';
	}

	public function add()
	{
		return false;
	}

	final public function setLogEventId($eventId = '')
	{
		if (strlen($eventId) <= 0)
		{
			return false;
		}

		$this->logEventId = $eventId;

		return true;
	}

	final private function setLogEntityType($entityType = '')
	{
		if (strlen($entityType) <= 0)
		{
			return false;
		}

		$this->logEntityType = $entityType;

		return true;
	}

	final private function setLogEntityId($entityId = 0)
	{
		if (intval($entityId) <= 0)
		{
			return false;
		}

		$this->logEntityId = $entityId;

		return true;
	}

	final protected function getLogFields()
	{
		$return = array();

		$logId = $this->getLogId();
		if (intval($logId) <= 0)
		{
			return $return;
		}

		$res = LogTable::getList(array(
			'filter' => array(
				'ID' => $logId
			),
			'select' => array('EVENT_ID', 'ENTITY_TYPE', 'ENTITY_ID')
		));
		if ($logFields = $res->fetch())
		{
			$return = $logFields;

			$this->setLogEventId($logFields['EVENT_ID']);
			$this->setLogEntityType($logFields['ENTITY_TYPE']);
			$this->setLogEntityId($logFields['ENTITY_ID']);
		}

		return $return;
	}

	protected function getLogEventId()
	{
		$result = false;

		if ($this->logEventId !== null)
		{
			$result = $this->logEventId;
		}
		else
		{
			$logFields = $this->getLogFields();
			if (!empty($logFields['EVENT_ID']))
			{
				$result = $logFields['EVENT_ID'];
			}
		}

		return $result;
	}

	protected function getLogEntityType()
	{
		$result = false;

		if ($this->logEntityType !== null)
		{
			$result = $this->logEntityType;
		}
		else
		{
			$logFields = $this->getLogFields();
			if (!empty($logFields['ENTITY_TYPE']))
			{
				$result = $logFields['ENTITY_TYPE'];
			}
		}

		return $result;
	}

	protected function getLogEntityId()
	{
		$result = false;

		if ($this->logEntityId !== null)
		{
			$result = $this->logEntityId;
		}
		else
		{
			$logFields = $this->getLogFields();
			if (!empty($logFields['ENTITY_ID']))
			{
				$result = $logFields['ENTITY_ID'];
			}
		}

		return $result;
	}

	public function getAdditionalData($params = array())
	{
		return array();
	}

	protected function checkAdditionalDataParams(&$params)
	{
		if (
			empty($params)
			|| !is_array($params)
			|| empty($params['id'])
		)
		{
			return false;
		}

		if (!is_array($params['id']))
		{
			$params['id'] = array($params['id']);
		}

		return true;
	}

}