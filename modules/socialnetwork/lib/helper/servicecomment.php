<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2020 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\UrlPreview\UrlPreview;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\CommentAux;
use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\ComponentHelper;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Socialnetwork\LogTable;

class ServiceComment
{
	public static function processBlogCreateEntity($params): bool
	{
		$entityType = ($params['ENTITY_TYPE'] ?? false);
		$entityId = (int)($params['ENTITY_ID'] ?? 0);
		$sourceEntityType = (isset($params['SOURCE_ENTITY_TYPE']) && in_array($params['SOURCE_ENTITY_TYPE'], [ 'BLOG_POST', 'BLOG_COMMENT' ]) ? $params['SOURCE_ENTITY_TYPE'] : false);
		$sourceEntityId = (int)($params['SOURCE_ENTITY_ID'] ?? 0);
		$logId = 0;

		if (
			empty($sourceEntityType)
			|| $sourceEntityId <= 0
			|| empty($entityType)
			|| $entityId <= 0
			|| !Loader::includeModule('blog')
		)
		{
			return false;
		}

		$entity = static::getEntityData([
			'entityType' => $entityType,
			'entityId' => $entityId,
		]);

		if (empty($entity))
		{
			return false;
		}

		$source = static::getSourceData([
			'sourceEntityType' => $sourceEntityType,
			'sourceEntityId' => $sourceEntityId,
		]);

		if (empty($source))
		{
			return false;
		}

		$authorId = static::getEntityAuthorId([
			'entityType' => $entityType,
			'entityData' => $entity,
		]);
		$userIP = \CBlogUser::getUserIP();
		$auxText = CommentAux\CreateEntity::getPostText();

		$logCommentFields = [
			'POST_ID' => $source['post']['ID'],
			'BLOG_ID' => $source['post']['BLOG_ID'],
			'POST_TEXT' => $auxText,
			'DATE_CREATE' => convertTimeStamp(time() + \CTimeZone::getOffset(), 'FULL'),
			'AUTHOR_IP' => $userIP[0],
			'AUTHOR_IP1' => $userIP[1],
			'PARENT_ID' => false,
			'AUTHOR_ID' => $authorId,
			'SHARE_DEST' => Json::encode([
				'sourceType' => $sourceEntityType,
				'sourceId' => $sourceEntityId,
				'entityType' => $entityType,
				'entityId' => $entityId,
			]),
		];

		$entityLivefeedPovider = Provider::getProvider($sourceEntityType);
		$entityLivefeedPovider->setEntityId($sourceEntityId);
		$entityLivefeedPovider->initSourceFields();

		$url = $entityLivefeedPovider->getLiveFeedUrl();
		if (!empty($url))
		{
			$metaData = UrlPreview::getMetadataAndHtmlByUrl($url, true, false);

			if (
				!empty($metaData)
				&& !empty($metaData['ID'])
				&& (int)$metaData['ID'] > 0
			)
			{
				$signer = new Signer();
				$logCommentFields['UF_BLOG_COMM_URL_PRV'] = $signer->sign($metaData['ID'] . '', UrlPreview::SIGN_SALT);
			}
		}

		$newCommentId = \CBlogComment::add($logCommentFields, false);
		if (!$newCommentId)
		{
			return false;
		}

		BXClearCache(true, '/blog/comment/' . (int)($source['post']['ID'] / 100) . '/' . $source['post']['ID'] . '/');

		$blogPostLivefeedProvider = new Livefeed\BlogPost;

		$res = \CSocNetLog::getList(
			[],
			[
				'EVENT_ID' => $blogPostLivefeedProvider->getEventId(),
				'SOURCE_ID' => $source['post']['ID']
			],
			false,
			[ 'nTopCount' => 1 ],
			[ 'ID' ]
		);
		if ($log = $res->fetch())
		{
			$logId = (int)$log['ID'];
		}

		if ($logId > 0)
		{
			$connection = Application::getConnection();
			$helper = $connection->getSqlHelper();

			$logCommentFields = [
				'ENTITY_TYPE' => SONET_ENTITY_USER,
				'ENTITY_ID' => $source['post']['AUTHOR_ID'],
				'EVENT_ID' => 'blog_comment',
				'=LOG_DATE' => $helper->getCurrentDateTimeFunction(),
				'LOG_ID' => $logId,
				'USER_ID' => $authorId,
				'MESSAGE' => $auxText,
				'TEXT_MESSAGE' => $auxText,
				'MODULE_ID' => false,
				'SOURCE_ID' => $newCommentId,
				'RATING_TYPE_ID' => 'BLOG_COMMENT',
				'RATING_ENTITY_ID' => $newCommentId,
			];

			\CSocNetLogComments::add($logCommentFields, false, false);

			if (
				isset($params['LIVE'])
				&& $params['LIVE'] === 'Y'
			)
			{
				$userPage = Option::get('socialnetwork', 'user_page', SITE_DIR . 'company/personal/');
				$userPath = $userPage . 'user/' . $source['post']['AUTHOR_ID'] . '/';

				$auxLiveParamList = static::getAuxLiveParams([
					'sourceEntityType' => $sourceEntityType,
					'sourceEntityId' => $sourceEntityId,
					'sourceData' => $source,
					'entityType' => $entityType,
					'entityId' => $entityId,
					'entityData' => $entity,
					'userPath' => $userPath,
					'logId' => $logId,
				]);

				$provider = CommentAux\Base::init(CommentAux\CreateEntity::getType(), [
					'liveParamList' => $auxLiveParamList
				]);

				\CBlogComment::addLiveComment($newCommentId, [
					'PATH_TO_USER' => $userPath,
					'LOG_ID' => $logId,
					'MODE' => 'PULL_MESSAGE',
					'AUX' => 'createentity',
					'AUX_LIVE_PARAMS' => $provider->getLiveParams(),
				]);
			}
		}

		return true;
	}

	public static function processLogEntryCreateEntity(array $params = []): bool
	{
		$entityType = ($params['ENTITY_TYPE'] ?? false);
		$entityId = (int)($params['ENTITY_ID'] ?? 0);
		$postEntityType = ($params['POST_ENTITY_TYPE'] ?? false);
		$sourceEntityType = ($params['SOURCE_ENTITY_TYPE'] ?? false);
		$sourceEntityId = (int)($params['SOURCE_ENTITY_ID'] ?? 0);
		$sourceEntityData = (array)($params['SOURCE_ENTITY_DATA'] ?? []);
		$logId = (int)($params['LOG_ID'] ?? 0);
		$siteId = ($params['SITE_ID'] ?? SITE_ID);

		if (in_array($sourceEntityType, [ CommentAux\CreateEntity::SOURCE_TYPE_BLOG_POST, CommentAux\CreateEntity::SOURCE_TYPE_BLOG_COMMENT], true))
		{
			return self::processBlogCreateEntity([
				'ENTITY_TYPE' => $entityType,
				'ENTITY_ID' => $entityId,
				'SOURCE_ENTITY_TYPE' => $sourceEntityType,
				'SOURCE_ENTITY_ID' => $sourceEntityId,
				'LIVE' => 'Y',
			]);
		}

		if (
			empty($postEntityType)
			|| empty($sourceEntityType)
			|| $sourceEntityId <= 0
			|| empty($entityType)
			|| $entityId <= 0
		)
		{
			return false;
		}

		$entity = static::getEntityData([
			'entityType' => $entityType,
			'entityId' => $entityId,
		]);

		if (empty($entity))
		{
			return false;
		}

		$res = \CSite::getById($siteId);
		$site = $res->fetch();

		$provider = Livefeed\Provider::init([
			'ENTITY_TYPE' => $sourceEntityType,
			'ENTITY_ID' => $sourceEntityId,
			'ADDITIONAL_PARAMS' => $sourceEntityData,
			'LOG_ID' => $logId
		]);

		if (!$provider)
		{
			return false;
		}

		$commentProvider = false;
		if ($postProvider = Livefeed\Provider::getProvider($postEntityType))
		{
			$commentProvider = $postProvider->getCommentProvider();
		}

		if (!$commentProvider)
		{
			return false;
		}

		if ($postProvider::className() === $provider::className())
		{
			$commentProvider->setParentProvider($provider);
		}
		else
		{
			$postEntityId = $provider->getParentEntityId();
			$postProvider->setEntityId($postEntityId);
			$commentProvider->setParentProvider($postProvider);
		}

		$logId = $provider->getLogId();

		if ($logId <= 0)
		{
			$provider->initSourceFields();
			$logId = $provider->getLogId();
		}

		if ($logId > 0)
		{
			$commentProvider->setLogId($provider->getLogId());
		}

		$auxData = [
			'sourceType' => $sourceEntityType,
			'sourceId' => $sourceEntityId,
			'entityType' => $entityType,
			'entityId' => $entityId,
		];

		$authorId = static::getEntityAuthorId([
			'entityType' => $entityType,
			'entityData' => $entity,
		]);

		$sonetCommentData = $commentProvider->add([
			'SITE_ID' => $siteId,
			'AUTHOR_ID' => $authorId,
			'MESSAGE' => CommentAux\CreateEntity::getPostText(),
			'SHARE_DEST' => Json::encode($auxData),
			'AUX' => 'Y',
			'AUX_DATA' => $auxData,
			'MODULE' => false,
		]);

		$sonetCommentId = (int)($sonetCommentData['sonetCommentId'] ?? 0);
		$sourceCommentId = (int)($sonetCommentData['sourceCommentId'] ?? 0);

		if (
			$sonetCommentId <= 0
			&& $sourceCommentId <= 0
		)
		{
			return false;
		}

		if (
			isset($params['LIVE'])
			&& $params['LIVE'] === 'Y'
		)
		{
			$userPage = Option::get('socialnetwork', 'user_page', $site['DIR'] . 'company/personal/');
			$provider->initSourceFields();

			$auxLiveParamList = static::getAuxLiveParams([
				'sourceEntityType' => $sourceEntityType,
				'sourceEntityId' => $sourceEntityId,
				'entityType' => $entityType,
				'entityId' => $entityId,
				'entityData' => $entity,
				'sourceEntityLink' => $provider->getLiveFeedUrl(),
			]);

			$liveCommentParams = [
				'ACTION' => 'ADD',
				'TIME_FORMAT' => \CSite::getTimeFormat(),
				'NAME_TEMPLATE' => \CSite::getNameFormat(null, $siteId),
				'SHOW_LOGIN' => 'N',
				'AVATAR_SIZE' => 100,
				'LANGUAGE_ID' => $site['LANGUAGE_ID'],
				'SITE_ID' => $siteId,
				'PULL' => 'Y',
				'AUX' => 'createentity',
			];

			if ($sonetCommentId > 0)
			{
				$logCommentFields = \Bitrix\Socialnetwork\Item\LogComment::getById($sonetCommentId)->getFields();

				$res = LogTable::getList([
					'filter' => [
						'=ID' => $logCommentFields['LOG_ID']
					],
					'select' => [ 'ID', 'ENTITY_TYPE', 'ENTITY_ID', 'USER_ID', 'EVENT_ID', 'SOURCE_ID' ],
				]);
				if (!($logEntry = $res->fetch()))
				{
					return false;
				}

				$userPath = $userPage . 'user/' . $logEntry['USER_ID'] . '/';

				$auxLiveParamList['userPath'] = $userPath;

				$serviceCommentProvider = CommentAux\Base::init(
					CommentAux\CreateEntity::getType(),
					[
						'liveParamList' => $auxLiveParamList,
					]
				);

				$commentEvent = \CSocNetLogTools::findLogCommentEventByLogEventID($logEntry['EVENT_ID']);

				$liveCommentParams['SOURCE_ID'] = $logCommentFields['SOURCE_ID'];
				$liveCommentParams['PATH_TO_USER'] = $userPath;
				$liveCommentParams['AUX_LIVE_PARAMS'] = $serviceCommentProvider->getLiveParams();

				if ($commentProvider->getContentTypeId() === Livefeed\ForumPost::CONTENT_TYPE_ID)
				{
					$feedParams = $commentProvider->getFeedParams();
					if (!empty($feedParams['xml_id']))
					{
						$liveCommentParams['ENTITY_XML_ID'] = $feedParams['xml_id'];
					}
				}

				ComponentHelper::addLiveComment(
					$logCommentFields,
					$logEntry,
					$commentEvent,
					$liveCommentParams
				);
			}
			elseif ($sourceCommentId > 0)
			{
				$commentProvider->setEntityId($sourceCommentId);
				$commentProvider->initSourceFields();

				$serviceCommentProvider = CommentAux\Base::init(
					CommentAux\CreateEntity::getType(),
					[
						'liveParamList' => $auxLiveParamList,
					]
				);

				ComponentHelper::addLiveSourceComment([
					'postProvider' => $postProvider,
					'commentProvider' => $commentProvider,
					'siteId' => $siteId,
					'languageId' => $site['LANGUAGE_ID'],
					'nameTemplate' => \CSite::getNameFormat(null, $siteId),
					'showLogin' => 'N',
					'avatarSize' => 100,
					'aux' => 'createentity',
					'auxLiveParams' => $serviceCommentProvider->getLiveParams()
				]);
			}
		}

		return true;
	}

	public static function getEntityData(array $params = [])
	{
		global $USER;

		static $cache = [];

		$result = false;

		$entityType = ($params['entityType'] ?? false);
		$entityId = (int)($params['entityId'] ?? 0);

		if (
			!$entityType
			|| $entityId <= 0
		)
		{
			return $result;
		}

		$cacheKey = $entityType . '_' . $entityId;

		if (isset($cache[$cacheKey]))
		{
			return $cache[$cacheKey];
		}

		switch ($entityType)
		{
			case CommentAux\CreateEntity::ENTITY_TYPE_TASK:
				if (
					Loader::includeModule('tasks')
					&& ($task = \Bitrix\Tasks\Manager\Task::get($USER->getId(), $entityId))
				)
				{
					$result = $task['DATA'];
				}
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_BLOG_POST:

				$provider = new Livefeed\BlogPost();
				$provider->setOption('checkAccess', true);
				$provider->setEntityId($entityId);
				$provider->initSourceFields();

				$post = $provider->getSourceFields();
				if (!empty($post))
				{
					$post['URL'] = $provider->getLiveFeedUrl();
					$result = $post;
				}
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_CALENDAR_EVENT:
				if (Loader::includeModule('calendar'))
				{
					$res = \CCalendarEvent::getList(
						[
							'arFilter' => [
								'ID' => $entityId,
							],
							'parseRecursion' => false,
							'fetchAttendees' => false,
							'checkPermissions' => true,
							'setDefaultLimit' => false
						]
					);

					if (is_array($res) && is_array($res[0]))
					{
						$result = $res[0];
					}
				}
				break;
			default:
		}

		$cache[$cacheKey] = $result;

		return $result;
	}

	public static function getEntityAuthorId(array $params = []): int
	{
		$result = 0;

		$entityType = ($params['entityType'] ?? false);
		$entityData = ($params['entityData'] ?? []);

		if (
			!$entityType
			|| empty($entityData)
			|| !is_array($entityData)
		)
		{
			return $result;
		}

		switch ($entityType)
		{
			case CommentAux\CreateEntity::ENTITY_TYPE_TASK:
				$result = (isset($entityData['CREATED_BY']) ? (int)$entityData['CREATED_BY'] : 0);
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_BLOG_POST:
				$result = (isset($entityData['AUTHOR_ID']) ? (int)$entityData['AUTHOR_ID'] : 0);
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_CALENDAR_EVENT:
				$result = (isset($entityData['OWNER_ID']) ? (int)$entityData['OWNER_ID'] : 0);
				break;
			default:
		}

		return $result;
	}

	protected static function getAuxLiveParams(array $params = []): array
	{
		$result = [];

		$sourceEntityType = ($params['sourceEntityType'] ?? false);
		$sourceEntityId = (int)($params['sourceEntityId'] ?? 0);
		$sourceData = ($params['sourceData'] ?? []);
		$entityType = ($params['entityType'] ?? false);
		$entityId = (int)($params['entityId'] ?? 0);
		$entityData = ($params['entityData'] ?? []);
		$userPath = ($params['userPath'] ?? '');
		$logId = (int)($params['logId'] ?? 0);

		if (
			!$sourceEntityType
			|| $sourceEntityId <= 0
			|| !$entityType
			|| $entityId <= 0
			|| empty($entityData)
			|| !is_array($entityData)
		)
		{
			return $result;
		}

		$entityProvider = Livefeed\Provider::init([
			'ENTITY_TYPE' => $sourceEntityType,
			'ENTITY_ID' => $sourceEntityId,
			'LOG_ID' => $logId
		]);

		$sourceEntityLink = (
			$params['sourceEntityLink'] ?? self::getSourceEntityUrl([
				'sourceEntityType' => $sourceEntityType,
				'sourceEntityId' => $sourceEntityId,
				'sourceData' => $sourceData,
				'entityType' => $entityType,
				'entityData' => $entityData,
				'userPath' => $userPath,
			])
		);

		$result = [
			'sourceEntityType' => $sourceEntityType,
			'sourceEntityId' => $sourceEntityId,
			'entityType' => $entityType,
			'entityId' => $entityId,
			'entityUrl' => static::getEntityUrl([
				'entityType' => $entityType,
				'entityData' => $entityData,
			]),
			'entityName' => static::getEntityName([
				'entityType' => $entityType,
				'entityData' => $entityData,
			]),
			'sourceEntityLink' => $sourceEntityLink,
			'suffix' => $entityProvider->getSuffix(),
		];

		if ($entityType === CommentAux\CreateEntity::ENTITY_TYPE_TASK)
		{
			$result['taskResponsibleId'] = static::getEntityAuthorId([
				'entityType' => $entityType,
				'entityData' => $entityData,
			]);
		}
		elseif (
			$entityType === CommentAux\CreateEntity::ENTITY_TYPE_BLOG_POST
			&& Loader::includeModule('blog')
		)
		{
			$result['socNetPermissions'] = \CBlogPost::getSocNetPermsCode($entityId);
		}
		elseif ($entityType === CommentAux\CreateEntity::ENTITY_TYPE_CALENDAR_EVENT)
		{
			$attendees = [];
			if (!empty($entityData['USER_IDS']) && is_array($entityData['USER_IDS']))
			{
				$attendees = $entityData['USER_IDS'];
			}
			elseif (!empty($entityData['attendeesEntityList']))
			{
				$attendees = array_map(static function($item) {
					return (int)(isset($item['entityId'], $item['id']) && $item['entityId'] === 'user' ? $item['id'] : 0);
				}, $entityData['attendeesEntityList']);
				$attendees = array_filter($attendees, static function($item) {
					return $item > 0;
				});
			}

			if (
				!empty($entityData['MEETING_HOST'])
				&& (int)$entityData['MEETING_HOST'] > 0
			)
			{
				$attendees[] = (int)($entityData['MEETING_HOST']);
			}
			elseif (
				!empty($entityData['CREATED_BY'])
				&& (int)$entityData['CREATED_BY'] > 0
			)
			{
				$attendees[] = (int)($entityData['CREATED_BY']);
			}

			$result['attendees'] = array_unique($attendees);
		}

		return $result;
	}

	protected static function getEntityUrl(array $params = []): string
	{
		$result = '';

		$entityType = ($params['entityType'] ?? false);
		$entityData = ($params['entityData'] ?? []);

		if (
			!$entityType
			|| empty($entityData)
			|| !is_array($entityData)
		)
		{
			return $result;
		}

		switch ($entityType)
		{
			case CommentAux\CreateEntity::ENTITY_TYPE_TASK:
				if (Loader::includeModule('tasks'))
				{
					$result = \CTaskNotifications::getNotificationPath(['ID' => $entityData['CREATED_BY']], $entityData['ID'], false);
				}
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_BLOG_POST:
				$result = $entityData['URL'];
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_CALENDAR_EVENT:
				$calendarEventProvider = new Livefeed\CalendarEvent();
				$calendarEventProvider->setEntityId((int)$entityData['ID']);
				$calendarEventProvider->initSourceFields();
				$result = $calendarEventProvider->getLiveFeedUrl();
				break;
			default:
		}

		return $result;
	}

	protected static function getSourceEntityUrl(array $params = []): string
	{
		$sourceEntityType = ($params['sourceEntityType'] ?? false);
		$sourceEntityId = (int)($params['sourceEntityId'] ?? 0);
		$sourceData = ($params['sourceData'] ?? []);
		$userPath = ($params['userPath'] ?? '');

		return (
			$sourceEntityType === CommentAux\CreateEntity::SOURCE_TYPE_BLOG_COMMENT
				? $userPath . 'blog/' . $sourceData['post']['ID']. '/?commentId=' . $sourceEntityId . '#com' . $sourceEntityId
				: ''
		);
	}

	protected static function getEntityName(array $params = []): string
	{
		$result = '';

		$entityType = ($params['entityType'] ?? false);
		$entityData = ($params['entityData'] ?? []);

		if (
			!$entityType
			|| empty($entityData)
			|| !is_array($entityData)
		)
		{
			return $result;
		}

		switch ($entityType)
		{
			case CommentAux\CreateEntity::ENTITY_TYPE_TASK:
				$result = htmlspecialcharsback($entityData['TITLE']);
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_BLOG_POST:
				$result = $entityData['TITLE'];
				break;
			case CommentAux\CreateEntity::ENTITY_TYPE_CALENDAR_EVENT:
				$result = $entityData['NAME'];
				break;
			default:
		}

		return $result;
	}

	protected static function getSourceData(array $params = []): array
	{
		$result = [];

		$sourceEntityType = ($params['sourceEntityType'] ?? false);
		$sourceEntityId = (int)($params['sourceEntityId'] ?? 0);

		if (
			in_array($sourceEntityType, [ CommentAux\CreateEntity::SOURCE_TYPE_BLOG_POST, CommentAux\CreateEntity::SOURCE_TYPE_BLOG_COMMENT ], true)
			&& Loader::includeModule('blog'))
		{
			$postId = 0;

			if ($sourceEntityType === CommentAux\CreateEntity::SOURCE_TYPE_BLOG_COMMENT)
			{
				if ($comment = \CBlogComment::getById($sourceEntityId))
				{
					$postId = $comment['POST_ID'];
				}
			}
			elseif ($sourceEntityType === CommentAux\CreateEntity::SOURCE_TYPE_BLOG_POST)
			{
				$postId = $sourceEntityId;
			}

			if (
				$postId <= 0
				|| !($post = \CBlogPost::getById($postId))
				|| !Livefeed\BlogPost::canRead([
					'POST' => $post
				])
			)
			{
				return $result;
			}

			$blogId = (int)$post['BLOG_ID'];
			if ($blogId <= 0)
			{
				return $result;
			}

			$result = [
				'post' => $post
			];
		}

		return $result;
	}
}
