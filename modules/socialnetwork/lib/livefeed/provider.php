<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Disk\AttachedObject;
use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialnetwork\Internals\LiveFeed\Counter\CounterService;
use Bitrix\Socialnetwork\Item\Subscription;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\UserContentViewTable;
use Bitrix\Socialnetwork\Item\UserContentView;
use Bitrix\Socialnetwork\Item\Log;

Loc::loadMessages(__FILE__);

abstract class Provider
{
	public const DATA_RESULT_TYPE_SOURCE = 'SOURCE';

	public const TYPE_POST = 'POST';
	public const TYPE_COMMENT = 'COMMENT';

	public const DATA_ENTITY_TYPE_BLOG_POST = 'BLOG_POST';
	public const DATA_ENTITY_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';
	public const DATA_ENTITY_TYPE_TASKS_TASK = 'TASK';
	public const DATA_ENTITY_TYPE_FORUM_TOPIC = 'FORUM_TOPIC';
	public const DATA_ENTITY_TYPE_FORUM_POST = 'FORUM_POST';
	public const DATA_ENTITY_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';
	public const DATA_ENTITY_TYPE_LOG_ENTRY = 'LOG_ENTRY';
	public const DATA_ENTITY_TYPE_LOG_COMMENT = 'LOG_COMMENT';
	public const DATA_ENTITY_TYPE_RATING_LIST = 'RATING_LIST';
	public const DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM = 'PHOTO_ALBUM';
	public const DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO = 'PHOTO_PHOTO';
	public const DATA_ENTITY_TYPE_LISTS_ITEM = 'LISTS_NEW_ELEMENT';
	public const DATA_ENTITY_TYPE_WIKI = 'WIKI';
	public const DATA_ENTITY_TYPE_TIMEMAN_ENTRY = 'TIMEMAN_ENTRY';
	public const DATA_ENTITY_TYPE_TIMEMAN_REPORT = 'TIMEMAN_REPORT';
	public const DATA_ENTITY_TYPE_INTRANET_NEW_USER = 'INTRANET_NEW_USER';
	public const DATA_ENTITY_TYPE_BITRIX24_NEW_USER = 'BITRIX24_NEW_USER';
	public const DATA_ENTITY_TYPE_LIVE_FEED_VIEW = 'LIVE_FEED_VIEW';

	public const PERMISSION_DENY = 'D';
	public const PERMISSION_READ = 'I';
	public const PERMISSION_FULL = 'W';

	public const CONTENT_TYPE_ID = '';

	protected $entityId = 0;
	protected $additionalParams = [];
	protected $logId = 0;
	protected $sourceFields = [];
	protected $siteId = false;
	protected $options = [];
	protected string $ratingTypeId = '';
	protected int|null $ratingEntityId = null;
	protected $parentProvider = false;

	protected $cloneDiskObjects = false;
	protected $sourceDescription = '';
	protected $sourceTitle = '';
	protected $pinnedTitle = '';
	protected $sourceOriginalText = '';
	protected $sourceAuxData = [];
	protected $sourceAttachedDiskObjects = [];
	protected $sourceDiskObjects = [];
	protected $diskObjectsCloned = [];
	protected $attachedDiskObjectsCloned = [];
	protected $sourceDateTime = null;
	protected $sourceAuthorId = 0;

	protected $logEventId = null;
	protected $logEntityType = null;
	protected $logEntityId = null;

	protected static $logTable = LogTable::class;

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className(): string
	{
		return static::class;
	}

	public function setSiteId($siteId): void
	{
		$this->siteId = $siteId;
	}

	public function getSiteId()
	{
		return $this->siteId;
	}

	/**
	 * Option value setter
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setOption(string $key, $value): void
	{
		$this->options[$key] = $value;
	}

	/**
	 * Option value getter
	 * @param string $key
	 * @return mixed
	 */
	public function getOption(string $key)
	{
		return ($this->options[$key] ?? null);
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
		return '';
	}

	public function getRatingTypeId(): string
	{
		return $this->ratingTypeId;
	}

	public function setRatingTypeId(string $value): void
	{
		$this->ratingTypeId = $value;
	}

	public function getRatingEntityId(): int|null
	{
		return $this->ratingEntityId;
	}

	public function setRatingEntityId(int $value): void
	{
		$this->ratingEntityId = $value;
	}

	public function getUserTypeEntityId(): string
	{
		return '';
	}

	public function getCommentProvider()
	{
		return false;
	}

	public function setParentProvider($value): void
	{
		$this->parentProvider = $value;
	}

	public function getParentProvider()
	{
		return $this->parentProvider;
	}

	private static function getTypes(): array
	{
		return [
			self::TYPE_POST,
			self::TYPE_COMMENT,
		];
	}

	final public static function getProvider($entityType)
	{
		$provider = false;

		$moduleEvent = new Main\Event(
			'socialnetwork',
			'onLogProviderGetProvider',
			[
				'entityType' => $entityType
			]
		);
		$moduleEvent->send();

		foreach ($moduleEvent->getResults() as $moduleEventResult)
		{
			if ($moduleEventResult->getType() === EventResult::SUCCESS)
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
			switch($entityType)
			{
				case self::DATA_ENTITY_TYPE_BLOG_POST:
					$provider = new BlogPost();
					break;
				case self::DATA_ENTITY_TYPE_BLOG_COMMENT:
					$provider = new BlogComment();
					break;
				case self::DATA_ENTITY_TYPE_TASKS_TASK:
					$provider = new TasksTask();
					break;
				case self::DATA_ENTITY_TYPE_FORUM_TOPIC:
					$provider = new ForumTopic();
					break;
				case self::DATA_ENTITY_TYPE_FORUM_POST:
					$provider = new ForumPost();
					break;
				case self::DATA_ENTITY_TYPE_CALENDAR_EVENT:
					$provider = new CalendarEvent();
					break;
				case self::DATA_ENTITY_TYPE_LOG_ENTRY:
					$provider = new LogEvent();
					break;
				case self::DATA_ENTITY_TYPE_LOG_COMMENT:
					$provider = new LogComment();
					break;
				case self::DATA_ENTITY_TYPE_RATING_LIST:
					$provider = new RatingVoteList();
					break;
				case self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM:
					$provider = new PhotogalleryAlbum();
					break;
				case self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO:
					$provider = new PhotogalleryPhoto();
					break;
				case self::DATA_ENTITY_TYPE_LISTS_ITEM:
					$provider = new ListsItem();
					break;
				case self::DATA_ENTITY_TYPE_WIKI:
					$provider = new Wiki();
					break;
				case self::DATA_ENTITY_TYPE_TIMEMAN_ENTRY:
					$provider = new TimemanEntry();
					break;
				case self::DATA_ENTITY_TYPE_TIMEMAN_REPORT:
					$provider = new TimemanReport();
					break;
				case self::DATA_ENTITY_TYPE_INTRANET_NEW_USER:
					$provider = new IntranetNewUser();
					break;
				case self::DATA_ENTITY_TYPE_BITRIX24_NEW_USER:
					$provider = new Bitrix24NewUser();
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
			$provider->setSiteId($params['SITE_ID'] ?? SITE_ID);

			if (
				isset($params['CLONE_DISK_OBJECTS'])
				&& $params['CLONE_DISK_OBJECTS'] === true
			)
			{
				$provider->cloneDiskObjects = true;
			}

			if (
				isset($params['LOG_ID'])
				&& (int)$params['LOG_ID'] > 0
			)
			{
				$provider->setLogId((int)$params['LOG_ID']);
			}

			if (isset($params['RATING_TYPE_ID']))
			{
				$provider->setRatingTypeId($params['RATING_TYPE_ID']);
			}

			if (isset($params['RATING_ENTITY_ID']))
			{
				$provider->setRatingEntityId($params['RATING_ENTITY_ID']);
			}

			if (
				isset($params['ADDITIONAL_PARAMS'])
				&& is_array($params['ADDITIONAL_PARAMS'])
			)
			{
				$provider->setAdditionalParams($params['ADDITIONAL_PARAMS']);
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

	public function getLogId($params = [])
	{
		$result = false;

		if ($this->logId > 0)
		{
			$result = $this->logId;
		}
		else
		{
			$eventId = $this->getEventId();

			if (
				empty($eventId)
				|| $this->entityId <= 0
			)
			{
				return $result;
			}

			if ($this->getType() === Provider::TYPE_POST)
			{
				$filter = [
					'EVENT_ID' => $eventId
				];

				if (static::getId() === LogEvent::PROVIDER_ID)
				{
					$filter['=ID'] = $this->entityId;
				}
				else
				{
					$filter['=SOURCE_ID'] = $this->entityId;
				}

				if (
					is_array($params)
					&& isset($params['inactive'])
					&& $params['inactive']
				)
				{
					$filter['=INACTIVE'] = 'Y';
				}

				$res = \CSocNetLog::getList(
					[],
					$filter,
					false,
					[ 'nTopCount' => 1 ],
					[ 'ID' ]
				);

				$logEntry = $res->fetch();
				if (
					!$logEntry
					&& static::getId() === TasksTask::PROVIDER_ID
					&& Loader::includeModule('crm')
				)
				{
					$res = \CCrmActivity::getList(
						[],
						[
							'ASSOCIATED_ENTITY_ID' => $this->entityId,
							'TYPE_ID' => \CCrmActivityType::Task,
							'CHECK_PERMISSIONS' => 'N'
						],
						false,
						false,
						[ 'ID' ]
					);
					if ($activityFields = $res->fetch())
					{
						$res = \CSocNetLog::getList(
							[],
							[
								'EVENT_ID' => $eventId,
								'=ENTITY_TYPE' => 'CRMACTIVITY',
								'=ENTITY_ID' => $activityFields['ID'],
							],
							false,
							[ 'nTopCount' => 1 ],
							[ 'ID' ]
						);
						$logEntry = $res->fetch();
					}
				}

				if (
					$logEntry
					&& ((int)$logEntry['ID'] > 0)
				)
				{
					$result = $this->logId = (int)$logEntry['ID'];
				}
			}
			elseif ($this->getType() === Provider::TYPE_COMMENT)
			{
				$filter = [
					'EVENT_ID' => $eventId
				];

				if (static::getId() === LogComment::PROVIDER_ID)
				{
					$filter['ID'] = $this->entityId;
				}
				else
				{
					$filter['SOURCE_ID'] = $this->entityId;
				}

				$res = \CSocNetLogComments::getList(
					[],
					$filter,
					false,
					[ 'nTopCount' => 1 ],
					[ 'ID', 'LOG_ID' ]
				);

				if (
					($logCommentEntry = $res->fetch())
					&& ((int)$logCommentEntry['LOG_ID'] > 0)
				)
				{
					$result = $this->logId = (int)$logCommentEntry['LOG_ID'];
				}
			}
		}

		return $result;
	}

	public function getLogCommentId()
	{
		$result = false;

		$eventId = $this->getEventId();
		if (
			empty($eventId)
			|| $this->getType() !== self::TYPE_COMMENT
		)
		{
			return $result;
		}

		$filter = [
			'EVENT_ID' => $eventId
		];

		if (static::getId() === LogComment::PROVIDER_ID)
		{
			$filter['ID'] = $this->entityId;
		}
		else
		{
			$filter['SOURCE_ID'] = $this->entityId;
		}

		$res = \CSocNetLogComments::getList(
			[],
			$filter,
			false,
			[ 'nTopCount' => 1 ],
			[ 'ID', 'LOG_ID' ]
		);

		if ($logCommentEntry = $res->fetch())
		{
			$result = (int)$logCommentEntry['ID'];
			if ((int)$logCommentEntry['LOG_ID'] > 0)
			{
				$this->logId = (int)$logCommentEntry['LOG_ID'];
			}
		}

		return $result;
	}

	public function getSonetGroupsAvailable($feature = false, $operation = false): array
	{
		global $USER;

		$result = [];

		$logRights = $this->getLogRights();
		if (
			!empty($logRights)
			&& is_array($logRights)
		)
		{
			foreach ($logRights as $groupCode)
			{
				if (preg_match('/^SG(\d+)/', $groupCode, $matches))
				{
					$result[] = (int)$matches[1];
				}
			}
		}

		if (
			!empty($result)
			&& !!$feature
			&& !!$operation
		)
		{
			$activity = \CSocNetFeatures::isActiveFeature(
				SONET_ENTITY_GROUP,
				$result,
				$feature
			);
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
					!isset($activity[$groupId])
					|| !$activity[$groupId]
					|| !isset($availability[$groupId])
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

	public function getLogRights(): array
	{
		$result = [];
		$logId = $this->getLogId();

		if ($logId > 0)
		{
			$result = $this->getLogRightsEntry();
		}

		return $result;
	}

	protected function getLogRightsEntry(): array
	{
		$result = [];

		if ($this->logId > 0)
		{
			$res = \CSocNetLogRights::getList(
				[],
				[
					'LOG_ID' => $this->logId
				]
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

	final public function getEntityId()
	{
		return $this->entityId;
	}

	final public function setLogId($logId): void
	{
		$this->logId = $logId;
	}

	final public function setAdditionalParams(array $additionalParams): void
	{
		$this->additionalParams = $additionalParams;
	}

	final public function getAdditionalParams(): array
	{
		return $this->additionalParams;
	}

	final protected function setSourceFields(array $fields): void
	{
		$this->sourceFields = $fields;
	}

	public function initSourceFields()
	{
		return $this->sourceFields;
	}

	final public function getSourceFields(): array
	{
		return $this->sourceFields;
	}

	final protected function setSourceDescription($description): void
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

	final protected function setSourceTitle($title): void
	{
		$this->sourceTitle = $title;
	}

	public function getSourceTitle(): string
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceTitle;
	}

	public function getPinnedTitle()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$result = $this->pinnedTitle;
		if ($result === null)
		{
			$result = $this->getSourceTitle();
		}

		return $result;
	}

	public function getPinnedDescription()
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$result = $this->getSourceDescription();
		$result = truncateText(\CTextParser::clearAllTags($result), 100);

		return $result;
	}

	final protected function setSourceOriginalText($text): void
	{
		$this->sourceOriginalText = $text;
	}

	public function getSourceOriginalText(): string
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceOriginalText;
	}

	final protected function setSourceAuxData($auxData): void
	{
		$this->sourceAuxData = $auxData;
	}

	public function getSourceAuxData(): array
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceAuxData;
	}

	final protected function setSourceAttachedDiskObjects(array $diskAttachedObjects): void
	{
		$this->sourceAttachedDiskObjects = $diskAttachedObjects;
	}

	final protected function setSourceDiskObjects(array $files): void
	{
		$this->sourceDiskObjects = $files;
	}

	final public function setDiskObjectsCloned(array $values): void
	{
		$this->diskObjectsCloned = $values;
	}

	final public function getDiskObjectsCloned(): array
	{
		return $this->diskObjectsCloned;
	}

	final public function getAttachedDiskObjectsCloned(): array
	{
		return $this->attachedDiskObjectsCloned;
	}

	public function getSourceAttachedDiskObjects(): array
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceAttachedDiskObjects;
	}

	public function getSourceDiskObjects(): array
	{
		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		return $this->sourceDiskObjects;
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return [];
	}

	final protected function setSourceDateTime(DateTime $datetime): void
	{
		$this->sourceDateTime = $datetime;
	}

	final public function getSourceDateTime(): ?DateTime
	{
		return $this->sourceDateTime;
	}

	final protected function setSourceAuthorId($authorId = 0): void
	{
		$this->sourceAuthorId = (int)$authorId;
	}

	final public function getSourceAuthorId(): int
	{
		return $this->sourceAuthorId;
	}

	protected static function cloneUfValues(array $values)
	{
		global $USER;

		$result = [];
		if (Loader::includeModule('disk'))
		{
			$result = \Bitrix\Disk\Driver::getInstance()->getUserFieldManager()->cloneUfValuesFromAttachedObject($values, $USER->getId());
		}

		return $result;
	}

	public function getDiskObjects($entityId, $clone = false): array
	{
		$result = [];

		if ($clone)
		{
			$result = $this->getAttachedDiskObjects(true);

			if (
				empty($this->diskObjectsCloned)
				&& Loader::includeModule('disk')
			)
			{
				foreach ($result as $clonedDiskObjectId)
				{
					if (
						in_array($clonedDiskObjectId, $this->attachedDiskObjectsCloned)
						&& ($attachedDiskObjectId = array_search($clonedDiskObjectId, $this->attachedDiskObjectsCloned))
					)
					{
						$attachedObject = AttachedObject::loadById($attachedDiskObjectId);
						if ($attachedObject)
						{
							$this->diskObjectsCloned[\Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX.$attachedObject->getObjectId()] = $this->attachedDiskObjectsCloned[$attachedDiskObjectId];
						}
					}
				}
			}

			return $result;
		}

		$diskObjects = $this->getAttachedDiskObjects(false);

		if (
			!empty($diskObjects)
			&& Loader::includeModule('disk')
		)
		{
			foreach ($diskObjects as $attachedObjectId)
			{
				$attachedObject = AttachedObject::loadById($attachedObjectId);
				if ($attachedObject)
				{
					$result[] = \Bitrix\Disk\Uf\FileUserType::NEW_FILE_PREFIX . $attachedObject->getObjectId();
				}
			}
		}

		return $result;
	}

	private function processDescription($text)
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
				"#\\[disk file id=(n\\d+)\\]#isu",
				[ $this, 'parseDiskObjectsCloned' ],
				$result
			);
		}

		if (
			!empty($attachedDiskObjectsCloned)
			&& is_array($attachedDiskObjectsCloned)
		)
		{
			$result = preg_replace_callback(
				"#\\[disk file id=(\\d+)\\]#isu",
				[ $this, 'parseAttachedDiskObjectsCloned' ],
				$result
			);
		}

		return $result;
	}

	private function parseDiskObjectsCloned($matches)
	{
		$text = $matches[0];

		$diskObjectsCloned = $this->getDiskObjectsCloned();

		if (array_key_exists($matches[1], $diskObjectsCloned))
		{
			$text = str_replace($matches[1], $diskObjectsCloned[$matches[1]], $text);
		}

		return $text;
	}

	private function parseAttachedDiskObjectsCloned($matches)
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

	final public function getContentTypeId(): string
	{
		return static::CONTENT_TYPE_ID;
	}

	public static function getContentId($event = [])
	{
		$result = false;

		if (!is_array($event))
		{
			return $result;
		}

		$contentEntityType = false;
		$contentEntityId = false;

		$moduleEvent = new Main\Event(
			'socialnetwork',
			'onLogProviderGetContentId',
			[
				'eventFields' => $event,
			]
		);
		$moduleEvent->send();

		foreach ($moduleEvent->getResults() as $moduleEventResult)
		{
			if ($moduleEventResult->getType() === EventResult::SUCCESS)
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
			return [
				'ENTITY_TYPE' => $contentEntityType,
				'ENTITY_ID' => $contentEntityId
			];
		}

		// getContent

		if (
			!empty($event['EVENT_ID'])
			&& $event['EVENT_ID'] === 'photo'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM;
			$contentEntityId = (int)$event['SOURCE_ID'];
		}
		elseif (
			!empty($event['EVENT_ID'])
			&& $event['EVENT_ID'] === 'photo_photo'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO;
			$contentEntityId = (int)$event['SOURCE_ID'];
		}
		elseif (
			!empty($event['EVENT_ID'])
			&& $event['EVENT_ID'] === 'data'
		)
		{
			$contentEntityType = self::DATA_ENTITY_TYPE_LOG_ENTRY;
			$contentEntityId = (int)$event['ID'];
		}
		elseif (
			!empty($event['RATING_TYPE_ID'])
			&& !empty($event['RATING_ENTITY_ID'])
			&& (int)$event['RATING_ENTITY_ID'] > 0
		)
		{
			$contentEntityType = $event['RATING_TYPE_ID'];
			$contentEntityId = (int)$event['RATING_ENTITY_ID'];

			if (in_array($event['RATING_TYPE_ID'], [ 'IBLOCK_ELEMENT', 'IBLOCK_SECTION' ]))
			{
				$res = self::$logTable::getList([
					'filter' => [
						'=RATING_TYPE_ID' => $event['RATING_TYPE_ID'],
						'=RATING_ENTITY_ID' => $event['RATING_ENTITY_ID'],
					],
					'select' => [ 'EVENT_ID' ]
				]);
				if ($logEntryFields = $res->fetch())
				{
					if ($event['RATING_TYPE_ID'] === 'IBLOCK_ELEMENT')
					{
						$found = false;
						$photogalleryPhotoProvider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryPhoto;
						if (in_array($logEntryFields['EVENT_ID'], $photogalleryPhotoProvider->getEventId(), true))
						{
							$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_PHOTO;
							$contentEntityId = (int)$event['RATING_ENTITY_ID'];
							$found = true;
						}

						if (!$found)
						{
							$wikiProvider = new \Bitrix\Socialnetwork\Livefeed\Wiki;
							if (in_array($logEntryFields['EVENT_ID'], $wikiProvider->getEventId()))
							{
								$contentEntityType = self::DATA_ENTITY_TYPE_WIKI;
								$contentEntityId = (int)$event['RATING_ENTITY_ID'];
								$found = true;
							}
						}
					}
					elseif ($event['RATING_TYPE_ID'] === 'IBLOCK_SECTION')
					{
						$photogalleryalbumProvider = new \Bitrix\Socialnetwork\Livefeed\PhotogalleryAlbum;
						if (in_array($logEntryFields['EVENT_ID'], $photogalleryalbumProvider->getEventId(), true))
						{
							$contentEntityType = self::DATA_ENTITY_TYPE_PHOTOGALLERY_ALBUM;
							$contentEntityId = (int)$event['RATING_ENTITY_ID'];
						}
					}
				}
			}
			elseif (preg_match('/^wiki_[\d]+_page$/i', $event['RATING_TYPE_ID'], $matches))
			{
				$contentEntityType = self::DATA_ENTITY_TYPE_WIKI;
				$contentEntityId = (int)$event['SOURCE_ID'];
				$found = true;
			}
		}
		elseif (
			!empty($event['EVENT_ID'])
			&& !empty($event['SOURCE_ID'])
			&& (int)$event['SOURCE_ID'] > 0
		)
		{
			switch ($event['EVENT_ID'])
			{
				case 'tasks':
					$contentEntityType = self::DATA_ENTITY_TYPE_TASKS_TASK;
					$contentEntityId = (int)$event['SOURCE_ID'];
					break;
				case 'calendar':
					$contentEntityType = self::DATA_ENTITY_TYPE_CALENDAR_EVENT;
					$contentEntityId = (int)$event['SOURCE_ID'];
					break;
				case 'timeman_entry':
					$contentEntityType = self::DATA_ENTITY_TYPE_TIMEMAN_ENTRY;
					$contentEntityId = (int)$event['SOURCE_ID'];
					break;
				case 'report':
					$contentEntityType = self::DATA_ENTITY_TYPE_TIMEMAN_REPORT;
					$contentEntityId = (int)$event['SOURCE_ID'];
					break;
				case 'lists_new_element':
					$contentEntityType = self::DATA_ENTITY_TYPE_LISTS_ITEM;
					$contentEntityId = (int)$event['SOURCE_ID'];
					break;
				default:
			}
		}

		if (
			$contentEntityType
			&& $contentEntityId > 0
		)
		{
			$result = [
				'ENTITY_TYPE' => $contentEntityType,
				'ENTITY_ID' => $contentEntityId
			];
		}

		return $result;
	}

	public function setContentView($params = [])
	{
		global $USER;

		if (!is_array($params))
		{
			$params = [];
		}

		if (
			!isset($params['user_id'])
			&& is_object($USER)
			&& \CSocNetUser::isCurrentUserModuleAdmin()
		) // don't track users on God Mode
		{
			return false;
		}

		$userId = (
			isset($params['user_id'])
			&& (int)$params['user_id'] > 0
				? (int)$params['user_id']
				: 0
		);
		if ($userId <= 0 && is_object($USER))
		{
			$userId = $USER->getId();
		}

		$contentTypeId = $this->getContentTypeId();
		$contentEntityId = $this->getEntityId();
		$logId = $this->getLogId();
		$save = (!isset($params['save']) || (bool)$params['save']);

		if (
			(int)$userId <= 0
			|| !$contentTypeId
			|| !$contentEntityId
		)
		{
			return false;
		}

		$viewParams = [
			'userId' => $userId,
			'typeId' => $contentTypeId,
			'entityId' => $contentEntityId,
			'logId' => $logId,
			'save' => $save
		];

		$pool = Application::getInstance()->getConnectionPool();
		$pool->useMasterOnly(true);

		$result = UserContentViewTable::set($viewParams);

		// we need to update the last DATE_VIEW for the parent post if it is a comment
		if ($this->isComment($this->getContentTypeId()))
		{
			$logItem = Log::getById($logId);
			if ($logItem)
			{
				$fields = $logItem->getFields();
				$contentTypeId = $fields['RATING_TYPE_ID'] ?? null;
				$contentEntityId = $fields['RATING_ENTITY_ID'] ?? null;
				if ($contentTypeId && $contentEntityId)
				{
					$result = UserContentViewTable::set([
						'userId' => $userId,
						'typeId' => $contentTypeId,
						'entityId' => $contentEntityId,
						'logId' => $logId,
						'save' => true
					]);
				}
			}
		}

		$pool->useMasterOnly(false);

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
					if (Loader::includeModule('pull') && !$this->isComment($this->getContentTypeId()))
					{
						$contentId = $viewParams['typeId'] . '-' . $viewParams['entityId'];
						$views = \Bitrix\Socialnetwork\Item\UserContentView::getViewData([
							'contentId' => [$contentId]
						]);

						\CPullWatch::addToStack('CONTENTVIEW' . $viewParams['typeId'] . '-' . $viewParams['entityId'],
							[
								'module_id' => 'contentview',
								'command' => 'add',
								'expiry' => 0,
								'params' => [
									'USER_ID' => $userId,
									'TYPE_ID' => $viewParams['typeId'],
									'ENTITY_ID' => $viewParams['entityId'],
									'CONTENT_ID' => $contentId,
									'TOTAL_VIEWS' => (int)($views[$contentId]['CNT'] ?? 0),
								]
							]
						);
					}
				}

				if ($logId > 0)
				{
					Subscription::onContentViewed([
						'userId' => $userId,
						'logId' => $logId
					]);

					\Bitrix\Socialnetwork\Internals\EventService\Service::addEvent(
						\Bitrix\Socialnetwork\Internals\EventService\EventDictionary::EVENT_SPACE_LIVEFEED_POST_VIEW,
						[
							'SONET_LOG_ID' => (int)$logId,
							'USER_ID' => (int)$userId,
							'ENTITY_TYPE_ID' => $contentTypeId,
							'ENTITY_ID' => $contentEntityId,
						]
					);
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
				$type === self::TYPE_POST
				&& in_array($params['EVENT_ID'], $blogPostLivefeedProvider->getEventId(), true)
			)
			{
				$entityType = self::DATA_ENTITY_TYPE_BLOG_POST;
				$entityId = (isset($params['SOURCE_ID']) ? (int)$params['SOURCE_ID'] : false);
			}
		}

		return (
			$entityType
			&& $entityId
				? [
					'ENTITY_TYPE' => $entityType,
					'ENTITY_ID' => $entityId
				]
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

	final public function setLogEventId($eventId = ''): bool
	{
		if ($eventId == '')
		{
			return false;
		}

		$this->logEventId = $eventId;

		return true;
	}

	private function setLogEntityType($entityType = ''): bool
	{
		if ($entityType == '')
		{
			return false;
		}

		$this->logEntityType = $entityType;

		return true;
	}

	private function setLogEntityId($entityId = 0): bool
	{
		if ((int)$entityId <= 0)
		{
			return false;
		}

		$this->logEntityId = $entityId;

		return true;
	}

	final protected function getLogFields(): array
	{
		$return = [];

		$logId = $this->getLogId();
		if ((int)$logId <= 0)
		{
			return $return;
		}

		$res = self::$logTable::getList([
			'filter' => [
				'ID' => $logId,
			],
			'select' => [ 'EVENT_ID', 'ENTITY_TYPE', 'ENTITY_ID' ]
		]);
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

	public function getAdditionalData($params = [])
	{
		return [];
	}

	protected function checkAdditionalDataParams(&$params): bool
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
			$params['id'] = [ $params['id'] ];
		}

		return true;
	}

	public function warmUpAuxCommentsStaticCache(array $params = []): void
	{

	}

	protected function getUnavailableTitle()
	{
		return Loc::getMessage('SONET_LIVEFEED_BASE_TITLE_UNAVAILABLE');
	}

	protected function getEntityAttachedDiskObjects(array $params = [])
	{
		global $USER_FIELD_MANAGER;

		$result = [];

		$userFieldEntity = (string)($params['userFieldEntity'] ?? '');
		$userFieldEntityId = $this->entityId;
		$userFieldCode = (string)($params['userFieldCode'] ?? '');
		$clone = (boolean)($params['clone'] ?? false);

		if (
			$userFieldEntity === ''
			|| $userFieldCode === ''
			|| $userFieldEntityId <= 0
		)
		{
			return $result;
		}

		static $cache = [];

		$cacheKey = $userFieldEntity . $userFieldEntityId . $clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$entityUF = $USER_FIELD_MANAGER->getUserFields($userFieldEntity, $userFieldEntityId, LANGUAGE_ID);
			if (
				!empty($entityUF[$userFieldCode])
				&& !empty($entityUF[$userFieldCode]['VALUE'])
				&& is_array($entityUF[$userFieldCode]['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($entityUF[$userFieldCode]['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $entityUF[$userFieldCode]['VALUE'];
				}
			}
		}

		if (!is_array($result))
		{
			$result = [];
		}

		return $result;
	}

	public function getParentEntityId(): int
	{
		return 0;
	}

	private function isComment(string $contentTypeId): bool
	{
		return $contentTypeId === LogComment::CONTENT_TYPE_ID
			|| $contentTypeId === BlogComment::CONTENT_TYPE_ID
			|| $contentTypeId === ForumPost::CONTENT_TYPE_ID;
	}
}
