<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Forum\ForumTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Web\Json;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

final class ForumPost extends Provider
{
	const PROVIDER_ID = 'FORUM_POST';
	const CONTENT_TYPE_ID = 'FORUM_POST';

	public static $auxCommentsCache = [];

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array(
			'forum',
			'tasks_comment',
			'calendar_comment',
			'timeman_entry_comment',
			'report_comment',
			'photo_comment',
			'wiki_comment',
			'lists_new_element_comment',
			'crm_activity_add_comment'
		);
	}

	public function getType()
	{
		return Provider::TYPE_COMMENT;
	}

	public function getRatingTypeId(): string
	{
		return 'FORUM_POST';
	}

	public function getUserTypeEntityId(): string
	{
		return 'FORUM_MESSAGE';
	}

	public function initSourceFields(): void
	{
		$messageId = (int)$this->entityId;

		if (
			$messageId <= 0
			|| !Loader::includeModule('forum')
		)
		{
			return;
		}

		$res = MessageTable::getList([
			'filter' => [
				'=ID' => $messageId
			],
			'select' => [ 'ID', 'POST_MESSAGE', 'SERVICE_TYPE', 'SERVICE_DATA', 'POST_DATE', 'AUTHOR_ID' ]
		]);
		$message = $res->fetch();

		if (!$message)
		{
			return;
		}

		$auxData = [
			'SHARE_DEST' => $message['SERVICE_DATA'],
			'SOURCE_ID' => $messageId,
		];

		$logId = false;

		$res = LogCommentTable::getList([
			'filter' => [
				'SOURCE_ID' => $messageId,
				'@EVENT_ID' => $this->getEventId(),
			],
			'select' => ['ID', 'LOG_ID', 'SHARE_DEST', 'MESSAGE', 'EVENT_ID', 'RATING_TYPE_ID']
		]);
		if ($logComentFields = $res->fetch())
		{
			$logId = (int)$logComentFields['LOG_ID'];

			$auxData['ID'] = (int)$logComentFields['ID'];
			$auxData['LOG_ID'] = $logId;
		}

		$this->setSourceDescription($message['POST_MESSAGE']);

		$title = htmlspecialcharsback($message['POST_MESSAGE']);
		$title = \Bitrix\Socialnetwork\Helper\Mention::clear($title);

		$CBXSanitizer = new \CBXSanitizer;
		$CBXSanitizer->delAllTags();
		$title = preg_replace(
			[
				"/\n+/is".BX_UTF_PCRE_MODIFIER,
				"/\s+/is".BX_UTF_PCRE_MODIFIER
			],
			' ',
			\CTextParser::clearAllTags($title)
		);
		$this->setSourceTitle(truncateText($title, 100));
		$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($messageId));
		$this->setSourceDiskObjects($this->getDiskObjects($messageId, $this->cloneDiskObjects));
		$this->setSourceDateTime($message['POST_DATE']);
		$this->setSourceAuthorId((int)$message['AUTHOR_ID']);

		if ($logId)
		{
			$res = \CSocNetLog::getList(
				[],
				[
					'=ID' => $logId
				],
				false,
				false,
				[ 'ID', 'EVENT_ID' ],
				[
					'CHECK_RIGHTS' => 'Y',
					'USE_FOLLOW' => 'N',
					'USE_SUBSCRIBE' => 'N',
				]
			);
			if ($logFields = $res->fetch())
			{
				$this->setLogId($logFields['ID']);
				$this->setSourceFields(array_merge($message, [ 'LOG_EVENT_ID' => $logFields['EVENT_ID'] ]));

				if(
					!empty($logComentFields)
					&& in_array((int)$message['SERVICE_TYPE'], \Bitrix\Forum\Comments\Service\Manager::getTypesList(), true)
				)
				{
					$this->setSourceOriginalText($logComentFields['MESSAGE']);
					$auxData['SHARE_DEST'] = '';
					$auxData['EVENT_ID'] = $logComentFields['EVENT_ID'];
					$auxData['SOURCE_ID'] = $messageId;
					$auxData['RATING_TYPE_ID'] = $logComentFields['RATING_TYPE_ID'];
				}
				else
				{
					$this->setSourceOriginalText($message['POST_MESSAGE']);
				}

				$this->setSourceAuxData($auxData);
			}
		}
		else
		{
			$this->setSourceFields($message);
			$this->setSourceDescription($message['POST_MESSAGE']);
			$this->setSourceOriginalText($message['POST_MESSAGE']);
			$this->setSourceAuxData($auxData);
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = [];

		$messageId = $this->entityId;

		$result = [];
		$cacheKey = $messageId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$messageUF = $USER_FIELD_MANAGER->getUserFields('FORUM_MESSAGE', $messageId, LANGUAGE_ID);
			if (
				!empty($messageUF['UF_FORUM_MESSAGE_DOC'])
				&& !empty($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'])
				&& is_array($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'];
				}
			}
		}

		if (!is_array($result))
		{
			$result = [];
		}

		return $result;
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
	{
		static $urlCache = array();
		$result = '';

		$entityUrl = false;

		$logId = $this->getLogId();

		if ($logId)
		{
			if (isset($urlCache[$logId]))
			{
				$entityUrl = $urlCache[$logId];
			}
			else
			{
				$res = self::$logTable::getList(array(
					'filter' => array(
						'ID' => $logId
					),
					'select' => [ 'ENTITY_ID', 'EVENT_ID', 'SOURCE_ID', 'RATING_TYPE_ID', 'RATING_ENTITY_ID', 'PARAMS' ]
				));
				if ($logEntryFields = $res->fetch())
				{
					$provider = false;

					$providerTasksTask = new TasksTask();
					if (in_array((string)$logEntryFields['EVENT_ID'], $providerTasksTask->getEventId(), true))
					{
						$provider = $providerTasksTask;
						$provider->setOption('checkAccess', false);
						$entityId = (int)$logEntryFields['SOURCE_ID'];
						if ($logEntryFields['EVENT_ID'] === 'crm_activity_add')
						{
							if ($logEntryFields['RATING_TYPE_ID'] === 'TASK')
							{
								$entityId = (int)$logEntryFields['RATING_ENTITY_ID'];
							}
							elseif (
								$logEntryFields['RATING_TYPE_ID'] === 'LOG_ENTRY'
								&& Loader::includeModule('crm')
								&& ($activity = \CCrmActivity::getById($logEntryFields['ENTITY_ID'], false))
								&& (int)$activity['TYPE_ID'] === (int)\CCrmActivityType::Task
							)
							{
								$entityId = (int)$activity['ASSOCIATED_ENTITY_ID'];
							}
						}
						$provider->setEntityId($entityId);
						$provider->setLogId($logId);
						$provider->initSourceFields();

						$postUrl = $provider->getLiveFeedUrl();
						$entityUrl = $postUrl.(mb_strpos($postUrl, '?') === false ? '?' : '&').'commentId='.$this->getEntityId().'#com'.$this->getEntityId();
					}

					if (!$provider)
					{
						$providerCalendarEvent = new CalendarEvent();
						if (in_array($logEntryFields['EVENT_ID'], $providerCalendarEvent->getEventId()))
						{
							$provider = $providerCalendarEvent;
							$provider->setEntityId((int)$logEntryFields['SOURCE_ID']);
							$provider->setLogId($logId);
							$provider->initSourceFields();

							$postUrl = $provider->getLiveFeedUrl();
							$entityUrl = $postUrl.(mb_strpos($postUrl, '?') === false ? '?' : '&').'commentId='.$this->getEntityId().'#com'.$this->getEntityId();
						}
					}

					if (!$provider)
					{
						$providerTimemanEntry = new TimemanEntry();
						if (in_array($logEntryFields['EVENT_ID'], $providerTimemanEntry->getEventId()))
						{
							$provider = $providerTimemanEntry;
							$provider->setEntityId((int)$logEntryFields['SOURCE_ID']);
							$provider->setLogId($logId);
							$provider->initSourceFields();
							$entityUrl = $provider->getLiveFeedUrl();
						}
					}

					if (!$provider)
					{
						$providerTimemanReport = new TimemanReport();
						if (in_array($logEntryFields['EVENT_ID'], $providerTimemanReport->getEventId()))
						{
							$provider = $providerTimemanReport;
							$provider->setEntityId((int)$logEntryFields['SOURCE_ID']);
							$provider->setLogId($logId);
							$provider->initSourceFields();
							$entityUrl = $provider->getLiveFeedUrl();
						}
					}

					if (!$provider)
					{
						$providerPhotogalleryPhoto = new PhotogalleryPhoto();
						if (in_array($logEntryFields['EVENT_ID'], $providerPhotogalleryPhoto->getEventId()))
						{
							$provider = $providerPhotogalleryPhoto;
							$provider->setEntityId((int)$logEntryFields['SOURCE_ID']);
							$provider->setLogId($logId);
							$provider->initSourceFields();
							$entityUrl = $provider->getLiveFeedUrl();
						}
					}

					if (!$provider)
					{
						$providerWiki = new Wiki();
						if (in_array($logEntryFields['EVENT_ID'], $providerWiki->getEventId()))
						{
							$provider = $providerWiki;
							$provider->setEntityId((int)($logEntryFields['SOURCE_ID']));
							$provider->setLogId($logId);
							$provider->initSourceFields();
							$entityUrl = $provider->getLiveFeedUrl();
						}
					}

					if (!$provider)
					{
						$providerListsItem = new ListsItem();
						if (in_array($logEntryFields['EVENT_ID'], $providerListsItem->getEventId()))
						{
							$provider = $providerListsItem;
							$provider->setEntityId((int)($logEntryFields['SOURCE_ID']));
							$provider->setLogId($logId);
							$provider->initSourceFields();
							$entityUrl = $provider->getLiveFeedUrl().'?commentId='.$this->getEntityId().'#com'.$this->getEntityId();
						}
					}

					if (!$provider)
					{
						$providerForumTopic = new ForumTopic();
						if (in_array($logEntryFields['EVENT_ID'], $providerForumTopic->getEventId()))
						{
							if (
								!empty($logEntryFields["PARAMS"])
								&& unserialize($logEntryFields["PARAMS"], ['allowed_classes' => false])
							)
							{
								$paramsList = unserialize($logEntryFields["PARAMS"], ['allowed_classes' => false]);
								if (!empty($paramsList["PATH_TO_MESSAGE"]))
								{
									$entityUrl = \CComponentEngine::makePathFromTemplate($paramsList["PATH_TO_MESSAGE"], array("MID" => $this->getEntityId()));
								}
							}
						}
					}
				}
			}
		}

		if (!empty($entityUrl))
		{
			$result = $entityUrl;
		}

		return $result;
	}

	public function getSuffix($defaultValue = '')
	{
		$logEventId = $this->getLogEventId();

		if (!empty($logEventId))
		{
			$providerTasksTask = new TasksTask();
			if (in_array($logEventId, $providerTasksTask->getEventId()))
			{
				return 'TASK';
			}

			$providerCalendarEvent = new CalendarEvent();
			if (in_array($logEventId, $providerCalendarEvent->getEventId()))
			{
				return 'CALENDAR';
			}

			$providerForumTopic = new ForumTopic();
			if (in_array($logEventId, $providerForumTopic->getEventId()))
			{
				return 'FORUM_TOPIC';
			}

			$providerTimemanEntry = new TimemanEntry();
			if (in_array($logEventId, $providerTimemanEntry->getEventId()))
			{
				return 'TIMEMAN_ENTRY';
			}

			$providerTimemanReport = new TimemanReport();
			if (in_array($logEventId, $providerTimemanReport->getEventId()))
			{
				return 'TIMEMAN_REPORT';
			}

			$providerPhotogalleryPhoto = new PhotogalleryPhoto();
			if (in_array($logEventId, $providerPhotogalleryPhoto->getEventId()))
			{
				return 'PHOTO_PHOTO';
			}

			$providerWiki = new Wiki();
			if (in_array($logEventId, $providerWiki->getEventId()))
			{
				return 'WIKI';
			}

			$providerListsItem = new ListsItem();
			if (in_array($logEventId, $providerListsItem->getEventId()))
			{
				return 'LISTS_NEW_ELEMENT';
			}
		}
		elseif (!empty ($defaultValue))
		{
			return $defaultValue;
		}

		return '2';
	}

	public function add($params = [])
	{
		global $USER;

		static $parser = null;

		$siteId = (
			isset($params['SITE_ID'])
			&& $params['SITE_ID'] <> ''
				? $params['SITE_ID']
				: SITE_ID
		);

		$authorId = (
			isset($params['AUTHOR_ID'])
			&& (int)$params['AUTHOR_ID'] > 0
				? (int)$params['AUTHOR_ID']
				: $USER->getId()
		);

		$message = (
			isset($params['MESSAGE'])
			&& $params['MESSAGE'] <> ''
			? $params['MESSAGE']
			: ''
		);

		if (
			$message == ''
			|| !Loader::includeModule('forum')
		)
		{
			return false;
		}

		$logId = $this->getLogId();

		$this->setLogId($logId);
		$feedParams = $this->getFeedParams();

		if (empty($feedParams))
		{
			return false;
		}

		$forumId = self::getForumId(array_merge($feedParams, [
			'SITE_ID' => $siteId,
		]));

		if (!$forumId)
		{
			return false;
		}

		$feed = new \Bitrix\Forum\Comments\Feed(
			$forumId,
			$feedParams,
			$authorId
		);

		$forumMessageFields = [
			'POST_MESSAGE' => $message,
			'AUTHOR_ID' => $authorId,
			'USE_SMILES' => 'Y',
			'AUX' => (isset($params['AUX']) && $params['AUX'] === 'Y' ? $params['AUX'] : 'N')
		];

		if ($message === \Bitrix\Socialnetwork\CommentAux\CreateEntity::getPostText())
		{
			$forumMessageFields['SERVICE_TYPE'] = \Bitrix\Forum\Comments\Service\Manager::TYPE_ENTITY_CREATED;
			$forumMessageFields['SERVICE_DATA'] = Json::encode(isset($params['AUX_DATA']) && is_array($params['AUX_DATA']) ? $params['AUX_DATA'] : []);
			$forumMessageFields['POST_MESSAGE'] = \Bitrix\Forum\Comments\Service\Manager::find([
				'SERVICE_TYPE' => \Bitrix\Forum\Comments\Service\Manager::TYPE_ENTITY_CREATED
			])->getText($forumMessageFields['SERVICE_DATA']);
			$params['SHARE_DEST'] = '';

			if (
				is_array($params['AUX_DATA'])
				&& !empty($params['AUX_DATA']['entityType'])
				&& (int)$params['AUX_DATA']['entityId'] > 0
			)
			{
				$entityLivefeedPovider = Provider::getProvider($params['AUX_DATA']['entityType']);
				$entityLivefeedPovider->setEntityId((int)$params['AUX_DATA']['entityId']);
				$entityLivefeedPovider->initSourceFields();

				$url = $entityLivefeedPovider->getLiveFeedUrl();
				if (!empty($url))
				{
					$metaData = \Bitrix\Main\UrlPreview\UrlPreview::getMetadataAndHtmlByUrl($url, true, false);

					if (
						!empty($metaData)
						&& !empty($metaData['ID'])
						&& (int)$metaData['ID'] > 0
					)
					{
						$signer = new \Bitrix\Main\Security\Sign\Signer();
						$forumMessageFields['UF_FORUM_MES_URL_PRV'] = $signer->sign($metaData['ID'] . '', \Bitrix\Main\UrlPreview\UrlPreview::SIGN_SALT);
					}
				}
			}
		}
		elseif ($message === \Bitrix\Socialnetwork\CommentAux\CreateTask::getPostText())
		{
			$forumMessageFields['SERVICE_TYPE'] = \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_CREATED;
			$forumMessageFields['SERVICE_DATA'] = Json::encode(isset($params['AUX_DATA']) && is_array($params['AUX_DATA']) ? $params['AUX_DATA'] : []);
			$forumMessageFields['POST_MESSAGE'] = \Bitrix\Forum\Comments\Service\Manager::find([
				'SERVICE_TYPE' => \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_CREATED
			])->getText($forumMessageFields['SERVICE_DATA']);
			$params['SHARE_DEST'] = '';
		}

		$forumComment = $feed->add($forumMessageFields);

		if (!$forumComment)
		{
			return false;
		}

		$sonetCommentId = false;

		if ($logId > 0)
		{
			if ($params['AUX'] === 'Y')
			{
				if ($parser === null)
				{
					$parser = new \CTextParser();
				}

				$sonetCommentFields = [
					"ENTITY_TYPE" => $this->getLogEntityType(),
					"ENTITY_ID" => $this->getLogEntityId(),
					"EVENT_ID" => $this->getCommentEventId(),
					"MESSAGE" => $message,
					"TEXT_MESSAGE" => $parser->convert4mail($message),
					"MODULE_ID" => $this->getModuleId(),
					"SOURCE_ID" => $forumComment['ID'],
					"LOG_ID" => $logId,
					"RATING_TYPE_ID" => "FORUM_POST",
					"RATING_ENTITY_ID" => $forumComment['ID'],
					"USER_ID" => $authorId,
					"=LOG_DATE" => \CDatabase::currentTimeFunction(),
				];

				if (!empty($params['SHARE_DEST']))
				{
					$sonetCommentFields['SHARE_DEST'] = $params['SHARE_DEST'];
				}

				if (!empty($forumMessageFields['UF_FORUM_MES_URL_PRV']))
				{
					$sonetCommentFields['UF_SONET_COM_URL_PRV'] = $forumMessageFields['UF_FORUM_MES_URL_PRV'];
				}

				$sonetCommentId = \CSocNetLogComments::add($sonetCommentFields, false, false);
			}
			else // comment is added on event
			{
				$res = LogCommentTable::getList(array(
					'filter' => array(
						'EVENT_ID' => $this->getCommentEventId(),
						'SOURCE_ID' => $forumComment['ID']
					),
					'select' => array('ID')
				));
				if ($sonetCommentFields = $res->fetch())
				{
					$sonetCommentId = $sonetCommentFields['ID'];
				}
			}
		}

		return [
			'sonetCommentId' => $sonetCommentId,
			'sourceCommentId' => $forumComment['ID']
		];
	}

	private static function getForumId($params = array())
	{
		$result = 0;

		$siteId = (
			isset($params['SITE_ID'])
			&& $params['SITE_ID'] <> ''
				? $params['SITE_ID']
				: SITE_ID
		);

		if (isset($params['type']))
		{
			if ($params['type'] === 'TK')
			{
				$result = Option::get('tasks', 'task_forum_id', 0, $siteId);

				if (
					(int)$result <= 0
					&& Loader::includeModule('forum')
				)
				{
					$res = ForumTable::getList(array(
						'filter' => array(
							'=XML_ID' => 'intranet_tasks'
						),
						'select' => array('ID')
					));
					if ($forumFields = $res->fetch())
					{
						$result = (int)$forumFields['ID'];
					}
				}
			}
			elseif ($params['type'] === 'WF')
			{
				$result = Option::get('bizproc', 'forum_id', 0, $siteId);

				if ((int)$result <= 0)
				{
					$res = ForumTable::getList(array(
						'filter' => array(
							'=XML_ID' => 'bizproc_workflow'
						),
						'select' => array('ID')
					));
					if ($forumFields = $res->fetch())
					{
						$result = (int)$forumFields['ID'];
					}
				}
			}
			elseif (in_array($params['type'], array('TM', 'TR')))
			{
				$result = Option::get('timeman', 'report_forum_id', 0, $siteId);
			}
			elseif (
				$params['type'] === 'EV'
				&& Loader::includeModule('calendar')
			)
			{
				$calendarSettings = \CCalendar::getSettings();
				$result = $calendarSettings["forum_id"];
			}
			elseif (
				$params['type'] === 'PH'
				&& Loader::includeModule('forum')
			)
			{
				$res = ForumTable::getList(array(
					'filter' => array(
						'=XML_ID' => 'PHOTOGALLERY_COMMENTS'
					),
					'select' => array('ID')
				));
				if ($forumFields = $res->fetch())
				{
					$result = (int)$forumFields['ID'];
				}
			}
			elseif ($params['type'] === 'IBLOCK')
			{
				$result = Option::get('wiki', 'socnet_forum_id', 0, $siteId);
			}
			else
			{
				$res = ForumTable::getList(array(
					'filter' => array(
						'=XML_ID' => 'USERS_AND_GROUPS'
					),
					'select' => array('ID')
				));
				if ($forumFields = $res->fetch())
				{
					$result = (int)$forumFields['ID'];
				}
			}
		}

		return $result;
	}

	private function getCommentEventId()
	{

		$result = false;

		$logEventId = $this->getLogEventId();
		if (!$logEventId)
		{
			return $result;
		}

		switch($logEventId)
		{
			case 'tasks':
				$result = 'tasks_comment';
				break;
			case 'crm_activity_add':
				$result = 'crm_activity_add_comment';
				break;
			case 'calendar':
				$result = 'calendar_comment';
				break;
			case 'forum':
				$result = 'forum';
				break;
			case 'timeman_entry':
				$result = 'timeman_entry_comment';
				break;
			case 'report':
				$result = 'report_comment';
				break;
			case 'photo_photo':
				$result = 'photo_comment';
				break;
			case 'wiki':
				$result = 'wiki_comment';
				break;
			case 'lists_new_element':
				$result = 'lists_new_element_comment';
				break;
			default:
				$result = false;
		}

		return $result;
	}

	private function getModuleId()
	{
		$result = false;

		$logEventId = $this->getLogEventId();
		if (!$logEventId)
		{
			return $result;
		}

		switch($logEventId)
		{
			case 'tasks':
				$result = 'tasks';
				break;
			case 'calendar':
				$result = 'calendar';
				break;
			case 'forum':
				$result = 'forum';
				break;
			case 'timeman_entry':
				$result = 'timeman';
				break;
			case 'photo_photo':
				$result = 'photogallery';
				break;
			case 'wiki':
				$result = 'wiki';
				break;
			default:
				$result = false;
		}

		return $result;
	}

	public function getFeedParams()
	{
		global $USER;

		$result = [];

		$entityType = false;
		$entityId = 0;
		$entityData = [];

		$parentProvider = $this->getParentProvider();
		if ($parentProvider)
		{
			$entityType = $parentProvider->getContentTypeId();
			$entityId = $parentProvider->getEntityId();
			$entityData = $parentProvider->getAdditionalParams();
		}
		else
		{
			$logId = $this->getLogId();

			if (!$logId)
			{
				return $result;
			}

			$res = self::$logTable::getList(array(
				'filter' => array(
					'ID' => $logId
				),
				'select' => array('EVENT_ID', 'SOURCE_ID')
			));

			if (
				($logFields = $res->fetch())
				&& (!empty($logFields['EVENT_ID']))
				&& ((int)$logFields['SOURCE_ID'] > 0)
			)
			{
				$this->setLogEventId($logFields['EVENT_ID']);

				$providerTasksTask = new TasksTask();
				if (in_array($logFields['EVENT_ID'], $providerTasksTask->getEventId()))
				{
					$entityType = $providerTasksTask->getContentTypeId();
					$entityId = (int)$logFields['SOURCE_ID'];
				}

				if ($entityId <= 0)
				{
					$providerCalendarEvent = new CalendarEvent();
					if (in_array($logFields['EVENT_ID'], $providerCalendarEvent->getEventId()))
					{
						$entityType = $providerCalendarEvent->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerForumTopic = new ForumTopic();
					if (in_array($logFields['EVENT_ID'], $providerForumTopic->getEventId()))
					{
						$entityType = $providerForumTopic->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerTimemanEntry = new TimemanEntry();
					if (in_array($logFields['EVENT_ID'], $providerTimemanEntry->getEventId()))
					{
						$entityType = $providerTimemanEntry->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerTimemanReport = new TimemanReport();
					if (in_array($logFields['EVENT_ID'], $providerTimemanReport->getEventId()))
					{
						$entityType = $providerTimemanReport->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerPhotogalleryPhoto = new PhotogalleryPhoto();
					if (in_array($logFields['EVENT_ID'], $providerPhotogalleryPhoto->getEventId()))
					{
						$entityType = $providerPhotogalleryPhoto->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerWiki = new Wiki();
					if (in_array($logFields['EVENT_ID'], $providerWiki->getEventId()))
					{
						$entityType = $providerWiki->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}

				if ($entityId <= 0)
				{
					$providerListsItem = new ListsItem();
					if (in_array($logFields['EVENT_ID'], $providerListsItem->getEventId()))
					{
						$entityType = $providerListsItem->getContentTypeId();
						$entityId = (int)$logFields['SOURCE_ID'];
					}
				}
			}
		}

		if (
			$entityType
			&& $entityId > 0
		)
		{
			$xmlId = $entityId;
			$type = false;

			switch ($entityType)
			{
				case TasksTask::CONTENT_TYPE_ID:
					$type = 'TK';
					$xmlId = 'TASK_'.$entityId;
					break;
				case CalendarEvent::CONTENT_TYPE_ID:
					$type = 'EV';
					$xmlId = 'EVENT_' . $entityId;
					if (
						is_array($entityData)
						&& !empty($entityData['parentId'])
						&& !empty($entityData['dateFrom'])
						&& Loader::includeModule('calendar')
					)
					{
						$calendarEntry = \CCalendarEvent::getEventForViewInterface($entityData['parentId'], [
							'eventDate' => $entityData['dateFrom'],
							'userId' => $USER->getId(),
						]);

						if ($calendarEntry)
						{
							$xmlId = \CCalendarEvent::getEventCommentXmlId($calendarEntry);
						}
					}
					break;
				case ForumTopic::CONTENT_TYPE_ID:
					$type = 'DEFAULT';
					$xmlId = 'TOPIC_'.$entityId;
					break;
				case TimemanEntry::CONTENT_TYPE_ID:
					$type = 'TM';
					$xmlId = 'TIMEMAN_ENTRY_'.$entityId;
					break;
				case TimemanReport::CONTENT_TYPE_ID:
					$type = 'TR';
					$xmlId = 'TIMEMAN_REPORT_'.$entityId;
					break;
				case PhotogalleryPhoto::CONTENT_TYPE_ID:
					$type = 'PH';
					$xmlId = 'PHOTO_'.$entityId;
					break;
				case Wiki::CONTENT_TYPE_ID:
					$type = 'IBLOCK';
					$xmlId = 'IBLOCK_'.$entityId;
					break;
				case ListsItem::CONTENT_TYPE_ID:
					$type = 'WF';
					if (
						Loader::includeModule('bizproc')
						&& ($workflowId = \CBPStateService::getWorkflowByIntegerId($entityId))
					)
					{
						$xmlId = 'WF_' . $workflowId;
					}
					break;
				default:
			}

			if ($type)
			{
				$result = [
					'type' => $type,
					'id' => $entityId,
					'xml_id' => $xmlId,
				];
			}
		}

		return $result;
	}

	public function getAdditionalData($params = array())
	{
		$result = array();

		if (
			!$this->checkAdditionalDataParams($params)
			|| !Loader::includeModule('forum')
		)
		{
			return $result;
		}

		$res = MessageTable::getList(array(
			'filter' => array(
				'@ID' => $params['id']
			),
			'select' => array('ID', 'USE_SMILES')
		));

		while ($message = $res->fetch())
		{
			$data = $message;
			unset($data['ID']);
			$result[$message['ID']] = $data;
		}

		return $result;
	}

	public function warmUpAuxCommentsStaticCache(array $params = [])
	{
		if (!Loader::includeModule('forum'))
		{
			return;
		}

		$logEventsData = (isset($params['logEventsData']) && is_array($params['logEventsData']) ? $params['logEventsData'] : []);

		$forumCommentEventIdList = $this->getEventId();

		$logIdList = [];
		foreach($logEventsData as $logId => $logEventId)
		{
			$commentEvent = \CSocNetLogTools::findLogCommentEventByLogEventID($logEventId);
			if (empty($commentEvent['EVENT_ID']))
			{
				continue;
			}

			if (in_array($commentEvent['EVENT_ID'], $forumCommentEventIdList))
			{
				$logIdList[] = $logId;
			}
		}

		if (!empty($logIdList))
		{
			$query = MessageTable::query();
			$query->setSelect([ 'ID', 'POST_MESSAGE', 'SERVICE_DATA', 'SERVICE_TYPE' ]);
			$query->whereIn('SERVICE_TYPE', \Bitrix\Forum\Comments\Service\Manager::getTypesList());
			$query->registerRuntimeField(
				new Reference(
					'LOG_COMMENT', LogCommentTable::class, Join::on('this.ID', 'ref.SOURCE_ID'), [ 'join_type' => 'INNER' ]
				)
			);
			$query->whereIn('LOG_COMMENT.LOG_ID', $logIdList);
			$query->setLimit(1000);

			$messages = $query->exec()->fetchCollection();
			while ($message = $messages->current())
			{
				$messageFields = $message->collectValues();
				self::$auxCommentsCache[$messageFields['ID']] = $messageFields;
				$messages->next();
			}
		}
	}

	public function getAuxCommentCachedData(int $messageId = 0): array
	{
		$result = [];

		if ($messageId <= 0)
		{
			return $result;
		}

		$result = (isset(self::$auxCommentsCache[$messageId]) ? self::$auxCommentsCache[$messageId] : []);
		return $result;
	}
}