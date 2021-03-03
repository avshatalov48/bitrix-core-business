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

	public function initSourceFields()
	{
		$messageId = $this->entityId;

		if (
			$messageId > 0
			&& Loader::includeModule('forum')
		)
		{
			$res = MessageTable::getList(array(
				'filter' => [
					'=ID' => $messageId
				],
				'select' => [ 'ID', 'POST_MESSAGE', 'SERVICE_TYPE', 'SERVICE_DATA' ]
			));
			if ($message = $res->fetch())
			{
				$logId = false;

				$res = LogCommentTable::getList(array(
					'filter' => [
						'SOURCE_ID' => $messageId,
						'@EVENT_ID' => $this->getEventId(),
					],
					'select' => [ 'ID', 'LOG_ID', 'SHARE_DEST', 'MESSAGE', 'EVENT_ID', 'RATING_TYPE_ID' ]
				));
				$auxData = [];
				if ($logComentFields = $res->fetch())
				{
					$auxData = [
						'ID' => (int)$logComentFields['ID'],
						'LOG_ID' => (int)$logComentFields['LOG_ID'],
						'SHARE_DEST' => $logComentFields['SHARE_DEST'],
					];
					$logId = (int)($logComentFields['LOG_ID']);
				}

				if ($logId)
				{
					$res = \CSocNetLog::getList(
						array(),
						array(
							'=ID' => $logId
						),
						false,
						false,
						array('ID', 'EVENT_ID'),
						array(
							"CHECK_RIGHTS" => "Y",
							"USE_FOLLOW" => "N",
							"USE_SUBSCRIBE" => "N"
						)
					);
					if ($logFields = $res->fetch())
					{
						$this->setLogId($logFields['ID']);
						$this->setSourceFields(array_merge($message, array('LOG_EVENT_ID' => $logFields['EVENT_ID'])));
						$this->setSourceDescription($message['POST_MESSAGE']);

						$title = htmlspecialcharsback($message['POST_MESSAGE']);
						$title = preg_replace(
							"/\[USER\s*=\s*([^\]]*)\](.+?)\[\/USER\]/is".BX_UTF_PCRE_MODIFIER,
							"\\2",
							$title
						);
						$CBXSanitizer = new \CBXSanitizer;
						$CBXSanitizer->delAllTags();
						$title = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", $CBXSanitizer->sanitizeHtml($title));
						$this->setSourceTitle(truncateText($title, 100));
						$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($messageId));
						$this->setSourceDiskObjects($this->getDiskObjects($messageId, $this->cloneDiskObjects));

						if (
							in_array($message['SERVICE_TYPE'], \Bitrix\Forum\Comments\Service\Manager::getTypesList())
							&& !empty($logComentFields)
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
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$messageId = $this->entityId;

		$result = array();
		$cacheKey = $messageId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$messageUF = $USER_FIELD_MANAGER->getUserFields("FORUM_MESSAGE", $messageId, LANGUAGE_ID);
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
					if (empty($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE']))
					{
						$cache[$cacheKey] = array();
					}
					elseif (!is_array($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE']))
					{
						$cache[$cacheKey] = array($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE']);
					}
					else
					{
						$cache[$cacheKey] = $messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'];
					}
					$result = $cache[$cacheKey];
				}
			}
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
					'select' => array('EVENT_ID', 'SOURCE_ID', 'RATING_ENTITY_ID', 'PARAMS')
				));
				if ($logEntryFields = $res->fetch())
				{
					$provider = false;

					$providerTasksTask = new TasksTask();
					if (in_array($logEntryFields['EVENT_ID'], $providerTasksTask->getEventId()))
					{
						$provider = $providerTasksTask;
						$provider->setEntityId((int)$logEntryFields[($logEntryFields['EVENT_ID'] === 'crm_activity_add' ? 'RATING_ENTITY_ID' : 'SOURCE_ID')]);
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

	public function add($params = array())
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

		if (!$logId)
		{
			return false;
		}

		$this->setLogId($logId);
		$feedParams = $this->getFeedParams();

		if (empty($feedParams))
		{
			return false;
		}

		$forumId = self::getForumId(array_merge($feedParams, array(
			'SITE_ID' => $siteId
		)));

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

		if ($message === \Bitrix\Socialnetwork\CommentAux\CreateTask::getPostText())
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

		if ($params['AUX'] === 'Y')
		{
			if ($parser === null)
			{
				$parser = new \CTextParser();
			}

			$sonetCommentFields = array(
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
			);

			if (!empty($params['SHARE_DEST']))
			{
				$sonetCommentFields['SHARE_DEST'] = $params['SHARE_DEST'];
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

		return $sonetCommentId;
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

	private function getFeedParams()
	{
		$result = array();

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
				$result = array(
					"type" => "TK",
					"id" => (int)$logFields['SOURCE_ID'],
					"xml_id" => "TASK_".(int)$logFields['SOURCE_ID']
				);
			}

			if (empty($result))
			{
				$providerCalendarEvent = new CalendarEvent();
				if (in_array($logFields['EVENT_ID'], $providerCalendarEvent->getEventId()))
				{
					$result = array(
						"type" => "EV",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "EVENT_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerForumTopic = new ForumTopic();
				if (in_array($logFields['EVENT_ID'], $providerForumTopic->getEventId()))
				{
					$result = array(
						"type" => "DEFAULT",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "TOPIC_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerTimemanEntry = new TimemanEntry();
				if (in_array($logFields['EVENT_ID'], $providerTimemanEntry->getEventId()))
				{
					$result = array(
						"type" => "TM",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "TIMEMAN_ENTRY_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerTimemanReport = new TimemanReport();
				if (in_array($logFields['EVENT_ID'], $providerTimemanReport->getEventId()))
				{
					$result = array(
						"type" => "TR",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "TIMEMAN_REPORT_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerPhotogalleryPhoto = new PhotogalleryPhoto();
				if (in_array($logFields['EVENT_ID'], $providerPhotogalleryPhoto->getEventId()))
				{
					$result = array(
						"type" => "PH",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "PHOTO_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerWiki = new Wiki();
				if (in_array($logFields['EVENT_ID'], $providerWiki->getEventId()))
				{
					$result = array(
						"type" => "IBLOCK",
						"id" => (int)$logFields['SOURCE_ID'],
						"xml_id" => "IBLOCK_".(int)$logFields['SOURCE_ID']
					);
				}
			}

			if (empty($result))
			{
				$providerListsItem = new ListsItem();
				if (
					in_array($logFields['EVENT_ID'], $providerListsItem->getEventId())
					&& Loader::includeModule('bizproc')
				)
				{
					$workflowId = \CBPStateService::getWorkflowByIntegerId((int)$logFields['SOURCE_ID']);
					if ($workflowId)
					{
						$result = array(
							"type" => "WF",
							"id" => (int)$logFields['SOURCE_ID'],
							"xml_id" => "WF_".$workflowId
						);
					}
				}
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