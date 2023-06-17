<?php

namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\ArgumentException;

Loc::loadMessages(__FILE__);

class CreateEntity extends Base
{
	public const TYPE = 'CREATEENTITY';
	public const POST_TEXT = 'commentAuxCreateEntity';

	public const SOURCE_TYPE_BLOG_POST = 'BLOG_POST';
	public const SOURCE_TYPE_TASK = 'TASK';
	public const SOURCE_TYPE_FORUM_TOPIC = 'FORUM_TOPIC';
	public const SOURCE_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';
	public const SOURCE_TYPE_TIMEMAN_ENTRY = 'TIMEMAN_ENTRY';
	public const SOURCE_TYPE_TIMEMAN_REPORT = 'TIMEMAN_REPORT';
	public const SOURCE_TYPE_LOG_ENTRY = 'LOG_ENTRY';
	public const SOURCE_TYPE_PHOTO_ALBUM = 'PHOTO_ALBUM';
	public const SOURCE_TYPE_PHOTO_PHOTO = 'PHOTO_PHOTO';
	public const SOURCE_TYPE_WIKI = 'WIKI';
	public const SOURCE_TYPE_LISTS_NEW_ELEMENT = 'LISTS_NEW_ELEMENT';
	public const SOURCE_TYPE_INTRANET_NEW_USER = 'INTRANET_NEW_USER';
	public const SOURCE_TYPE_BITRIX24_NEW_USER = 'BITRIX24_NEW_USER';

	public const SOURCE_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';
	public const SOURCE_TYPE_FORUM_POST = 'FORUM_POST';
	public const SOURCE_TYPE_LOG_COMMENT = 'LOG_COMMENT';

	public const ENTITY_TYPE_TASK = 'TASK';
	public const ENTITY_TYPE_BLOG_POST = 'BLOG_POST';
	public const ENTITY_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';

	protected $postTypeList = [
		self::SOURCE_TYPE_BLOG_POST,
		self::SOURCE_TYPE_TASK,
		self::SOURCE_TYPE_FORUM_TOPIC,
		self::SOURCE_TYPE_CALENDAR_EVENT,
		self::SOURCE_TYPE_TIMEMAN_ENTRY,
		self::SOURCE_TYPE_TIMEMAN_REPORT,
		self::SOURCE_TYPE_LOG_ENTRY,
		self::SOURCE_TYPE_PHOTO_ALBUM,
		self::SOURCE_TYPE_PHOTO_PHOTO,
		self::SOURCE_TYPE_WIKI,
		self::SOURCE_TYPE_LISTS_NEW_ELEMENT,
		self::SOURCE_TYPE_INTRANET_NEW_USER,
		self::SOURCE_TYPE_BITRIX24_NEW_USER,
	];
	protected $commentTypeList = [
		self::SOURCE_TYPE_BLOG_COMMENT,
		self::SOURCE_TYPE_FORUM_POST,
		self::SOURCE_TYPE_LOG_COMMENT,
	];

	protected $entityTypeList = [
		self::ENTITY_TYPE_BLOG_POST,
		self::ENTITY_TYPE_TASK,
		self::ENTITY_TYPE_CALENDAR_EVENT,
	];

	protected $postTypeListInited = false;
	protected $commentTypeListInited = false;

	protected static $blogPostClass = \CBlogPost::class;
	protected static $blogCommentClass = \CBlogComment::class;

	public function getPostTypeList(): array
	{
		if ($this->postTypeListInited === false)
		{
			$moduleEvent = new \Bitrix\Main\Event(
				'socialnetwork',
				'onCommentAuxGetPostTypeList',
				[]
			);
			$moduleEvent->send();

			foreach ($moduleEvent->getResults() as $moduleEventResult)
			{
				if ($moduleEventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
				{
					$moduleEventParams = $moduleEventResult->getParameters();

					if (
						is_array($moduleEventParams)
						&& !empty($moduleEventParams['typeList'])
						&& is_array($moduleEventParams['typeList'])
					)
					{
						foreach ($moduleEventParams['typeList'] as $type)
						{
							$this->addPostTypeList($type);
						}
					}
				}
			}

			$this->postTypeListInited = true;
		}

		return $this->postTypeList;
	}

	public function getCommentTypeList(): array
	{
		if ($this->commentTypeListInited === false)
		{
			$moduleEvent = new \Bitrix\Main\Event(
				'socialnetwork',
				'onCommentAuxGetCommentTypeList',
				[]
			);
			$moduleEvent->send();

			foreach ($moduleEvent->getResults() as $moduleEventResult)
			{
				if ($moduleEventResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
				{
					$moduleEventParams = $moduleEventResult->getParameters();

					if (
						is_array($moduleEventParams)
						&& !empty($moduleEventParams['typeList'])
						&& is_array($moduleEventParams['typeList'])
					)
					{
						foreach($moduleEventParams['typeList'] as $type)
						{
							$this->addCommentTypeList($type);
						}
					}
				}
			}

			$this->commentTypeListInited = true;
		}

		return $this->commentTypeList;
	}

	public function addPostTypeList($type): void
	{
		$this->postTypeList[] = $type;
	}

	public function addCommentTypeList($type): void
	{
		$this->commentTypeList[] = $type;
	}

	public function getSourceTypeList(): array
	{
		return array_merge($this->getPostTypeList(), $this->getCommentTypeList());
	}

	public function getEntityTypeList(): array
	{
		return $this->entityTypeList;
	}

	public function getParamsFromFields($fields = []): array
	{
		$params = [];

		if (!empty($fields['SHARE_DEST']))
		{
			$params = $this->getSocNetData($fields['SHARE_DEST']);
		}
		elseif (
			isset($fields['RATING_TYPE_ID'], $fields['SOURCE_ID'])
			&& (int)$fields['SOURCE_ID'] > 0
			&& in_array($fields['RATING_TYPE_ID'], ['FORUM_POST', 'CRM_ENTITY_COMMENT' ])
			&& Loader::includeModule('forum')
		)
		{
			$messageId = (int)$fields['SOURCE_ID'];

			$forumPostLivefeedProvider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
			$commentData = $forumPostLivefeedProvider->getAuxCommentCachedData($messageId);

			$serviceData = $this->getForumServiceData($commentData);

			if (
				!empty($commentData)
				&& !empty($serviceData)
				&& isset($commentData['SERVICE_TYPE'])
				&& $commentData['SERVICE_TYPE'] === $this->getForumType()
			)
			{
				try
				{
					$messageParams = Json::decode($serviceData);
				}
				catch (ArgumentException $e)
				{
					$messageParams = [];
				}

				$params = $messageParams;
			}
			else
			{
				$res = MessageTable::getList([
					'filter' => [
						'=ID' => (int)$fields['SOURCE_ID']
					],
					'select' => $this->getForumMessageFields(),
				]);

				if ($forumMessageFields = $res->fetch())
				{
					$serviceData = $this->getForumServiceData($forumMessageFields);
					if (!empty($serviceData))
					{
						try
						{
							$messageParams = Json::decode($serviceData);
						}
						catch (ArgumentException $e)
						{
							$messageParams = [];
						}

						$params = $messageParams;
					}
				}
			}
		}

		return $params;
	}

	public function getText(): string
	{
		static $userPage = null;
		static $parser = null;

		$result = '';
		$params = $this->params;
		$options = $this->options;

		$siteId = (!empty($options['siteId']) ? $options['siteId'] : SITE_ID);

		if (
			!isset($params['sourceType'], $params['sourceId'], $params['entityId'], $params['entityType'])
			|| (int)$params['sourceId'] <= 0
			|| (int)$params['entityId'] <= 0
			|| !in_array($params['sourceType'], $this->getSourceTypeList(), true)
			|| !in_array($params['entityType'], $this->getEntityTypeList(), true)
		)
		{
			return $result;
		}

		if ($provider = $this->getLivefeedProvider())
		{
			$options['suffix'] = $provider->getSuffix($options['suffix'] ?? null);
			$this->setOptions($options);
		}

		if ($userPage === null)
		{
			$userPage = Option::get(
					'socialnetwork',
					'user_page',
					SITE_DIR.'company/personal/',
					$siteId
				).'user/#user_id#/';
		}

		if (in_array($params['sourceType'], $this->getCommentTypeList(), true))
		{
			$sourceData = $this->getSourceCommentData([
				'userPage' => $userPage,
			]);

			$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_COMMENT_' . $params['sourceType'] . (!empty($sourceData['suffix']) ? '_' . $sourceData['suffix'] : ''), [
				'#ENTITY_CREATED#' => $this->getEntityCreatedMessage(),
				'#ENTITY_NAME#' => $this->getEntityName(),
				'#A_BEGIN#' => (!empty($sourceData['path']) ? '[URL=' . $sourceData['path'] . ']' : ''),
				'#A_END#' => (!empty($sourceData['path']) ? '[/URL]' : '')
			]);
		}
		elseif (in_array($params['sourceType'], $this->getPostTypeList(), true))
		{
			$suffix = ($options['suffix'] ?? ($params['sourceType'] === static::SOURCE_TYPE_BLOG_POST ? '2' : ''));

			$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_POST_' . $params['sourceType'] . (!empty($suffix) ? '_' . $suffix : ''), [
				'#ENTITY_CREATED#' => $this->getEntityCreatedMessage(),
				'#ENTITY_NAME#' => $this->getEntityName(),
			]);
		}

		if (!empty($result))
		{
			if ($parser === null)
			{
				$parser = new \CTextParser();
				$parser->allow = [ 'HTML' => 'N', 'ANCHOR' => 'Y' ];
			}
			$result = $parser->convertText($result);
		}

		return (string)$result;
	}

	protected function getNotFoundMessage(): string
	{
		$result = '';

		$params = $this->params;
		if (
			!isset($params['entityType'])
			|| !in_array($params['entityType'], $this->getEntityTypeList(), true)
		)
		{
			return $result;
		}

		$entityType = $params['entityType'];

		switch ($entityType)
		{
			case static::ENTITY_TYPE_TASK:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_TASK_NOT_FOUND');
				break;
			case static::ENTITY_TYPE_BLOG_POST:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_BLOG_POST_NOT_FOUND');
				break;
			case static::ENTITY_TYPE_CALENDAR_EVENT:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_CALENDAR_EVENT_NOT_FOUND');
				break;
			default:
		}

		return (string)$result;
	}

	protected function getEntityCreatedMessage(): string
	{
		$result = '';

		$params = $this->params;

		if (
			!isset($params['entityType'])
			|| !in_array($params['entityType'], $this->getEntityTypeList(), true)
		)
		{
			return $result;
		}

		switch ($params['entityType'])
		{
			case static::ENTITY_TYPE_TASK:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_ENTITY_CREATED_TASK');
				break;
			case static::ENTITY_TYPE_BLOG_POST:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_ENTITY_CREATED_BLOG_POST');
				break;
			case static::ENTITY_TYPE_CALENDAR_EVENT:
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATEENTITY_ENTITY_CREATED_CALENDAR_EVENT');
				break;
			default:
				$result = '';
		}

		return (string)$result;
	}

	protected function getEntityName(): string
	{
		if ($entity = $this->getEntity(false))
		{
			$entityPath = $entity['url'];
			$entityTitle = $entity['title'];
		}
		else
		{
			$entityPath = '';
			$entityTitle = $this->getNotFoundMessage();
		}

		if (mb_strlen($entityTitle) <= 0)
		{
			return '';
		}

		return (!empty($entityPath) ? '[URL=' . $entityPath . ']' . $entityTitle . '[/URL]' : $entityTitle);
	}

	public function checkRecalcNeeded($fields, $params): bool
	{
		$result = false;

		if (
			!empty($params['bPublicPage'])
			&& $params['bPublicPage']
		)
		{
			$result = true;
		}
		else
		{
			$handlerParams = $this->getParamsFromFields($fields);

			if (
				!empty($handlerParams)
				&& !empty($handlerParams['entityType'])
				&& !empty($handlerParams['entityId'])
				&& (int)$handlerParams['entityId'] > 0
				&& ($this->getEntity())
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	protected function getEntity($checkPermissions = true)
	{
		static $cache = [
			'Y' => [],
			'N' => [],
		];

		$params = $this->params;
		$entityType = $params['entityType'] ?? null;
		$entityId = (int) ($params['entityId'] ?? null);

		$result = false;
		$permissionCacheKey = ($checkPermissions ? 'Y' : 'N');
		$entityKey = $entityType . '_' . $entityId;

		if (isset($cache[$permissionCacheKey][$entityKey]))
		{
			$result = $cache[$permissionCacheKey][$entityKey];
		}
		else
		{
			$entity = false;

			switch ($entityType)
			{
				case static::ENTITY_TYPE_TASK:
					$provider = new \Bitrix\Socialnetwork\Livefeed\TasksTask();
					break;
				case static::ENTITY_TYPE_BLOG_POST:
					$provider = new \Bitrix\Socialnetwork\Livefeed\BlogPost();
					break;
				case static::ENTITY_TYPE_CALENDAR_EVENT:
					$provider = new \Bitrix\Socialnetwork\Livefeed\CalendarEvent();
					break;
				default:
					$provider = false;
			}

			if ($provider)
			{
				$provider->setEntityId($entityId);
				$provider->setOption('checkAccess', $checkPermissions);

				$entity = [
					'title' => $provider->getSourceTitle(),
					'url' => $provider->getLiveFeedUrl(),
				];
			}

			if ($entity)
			{
				$result = $cache[$permissionCacheKey][$entityKey] = $entity;
			}
			elseif(!$checkPermissions)
			{
				$result = $cache[$permissionCacheKey][$entityKey] = false;
			}
		}

		return $result;
	}

	protected function getRatingNotificationNotigyTag(array $ratingVoteParams = [], array $fields = []): string
	{
		return 'RATING|' . ($ratingVoteParams['VALUE'] >= 0 ? '' : 'DL|') . 'BLOG_COMMENT|' . $fields['ID'];
	}

	protected function getForumType(): string
	{
		return \Bitrix\Forum\Comments\Service\Manager::TYPE_ENTITY_CREATED;
	}

	protected function getForumServiceData(array $commentData = [])
	{
		return $commentData['SERVICE_DATA'];
	}

	protected function getForumMessageFields(): array
	{
		return [ 'SERVICE_DATA' ];
	}

	protected function getSocNetData($data = ''): array
	{
		try
		{
			$result = Json::decode($data);
		}
		catch (ArgumentException $e)
		{
			$result = [];
		}

		return $result;
	}

	public function getLivefeedProvider()
	{
		$params = $this->params;
		$options = $this->options;

		return \Bitrix\Socialnetwork\Livefeed\Provider::init([
			'ENTITY_TYPE' => ($params['sourceType'] ?? $params['sourcetype']),
			'ENTITY_ID' => (int)($params['sourceId'] ?? $params['sourceid']),
			'LOG_ID' => (int)($options['logId'] ?? 0)
		]);
	}

	protected function getSourceCommentData(array $additionalParams = []): array
	{
		$result = [
			'path' => '',
			'suffix' => '',
		];

		$params = $this->params;
		$options = $this->options;

		$userPage = ($additionalParams['userPage'] ?? '');
		$params['sourceType'] = ($params['sourceType'] ?? $params['sourcetype']);
		$params['sourceId'] = (int)($params['sourceId'] ?? $params['sourceid']);

		if (
			$params['sourceType'] === static::SOURCE_TYPE_BLOG_COMMENT
			&& Loader::includeModule('blog')
			&& ($comment = static::$blogCommentClass::getById($params['sourceId']))
			&& ($post = static::$blogPostClass::getById($comment['POST_ID']))
		)
		{
			$result['path'] = (
				(!isset($options['im']) || !$options['im'])
				&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
				&& (!isset($options['mail']) || !$options['mail'])
					? str_replace([ '#user_id#', '#USER_ID#' ], $post['AUTHOR_ID'], $userPage) . 'blog/' . $post['ID'] . '/?commentId=' . $params['sourceId'] . '#com' . $params['sourceId']
					: ''
			);
		}
		else
		{
			$commentProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($params['sourceType']);

			if (
				$commentProvider
				&& (!isset($options['im']) || !$options['im'])
				&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
				&& (!isset($options['mail']) || !$options['mail'])
				&& isset($options['logId'])
				&& (int)$options['logId'] > 0
			)
			{
				$commentProvider->setEntityId((int)$params['sourceId']);
				$commentProvider->setLogId($options['logId']);
				$commentProvider->initSourceFields();

				$result['path'] = $commentProvider->getLiveFeedUrl();
			}
		}

		$result['suffix'] = ($options['suffix'] ?? ($params['sourceType'] === static::SOURCE_TYPE_BLOG_COMMENT ? '2' : ''));

		return $result;
	}
}
