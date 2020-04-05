<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Livefeed\ForumPost;
use Bitrix\Socialnetwork\LogTable;

Loc::loadMessages(__FILE__);

final class CreateTask extends Base
{
	const TYPE = 'CREATETASK';
	const POST_TEXT = 'commentAuxCreateTask';
	const SOURCE_TYPE_BLOG_POST = 'BLOG_POST';
	const SOURCE_TYPE_TASK = 'TASK';
	const SOURCE_TYPE_FORUM_TOPIC = 'FORUM_TOPIC';
	const SOURCE_TYPE_CALENDAR_EVENT = 'CALENDAR_EVENT';
	const SOURCE_TYPE_TIMEMAN_ENTRY = 'TIMEMAN_ENTRY';
	const SOURCE_TYPE_TIMEMAN_REPORT = 'TIMEMAN_REPORT';
	const SOURCE_TYPE_LOG_ENTRY = 'LOG_ENTRY';
	const SOURCE_TYPE_PHOTO_ALBUM = 'PHOTO_ALBUM';
	const SOURCE_TYPE_PHOTO_PHOTO = 'PHOTO_PHOTO';
	const SOURCE_TYPE_WIKI = 'WIKI';
	const SOURCE_TYPE_LISTS_NEW_ELEMENT = 'LISTS_NEW_ELEMENT';
	const SOURCE_TYPE_INTRANET_NEW_USER = 'INTRANET_NEW_USER';
	const SOURCE_TYPE_BITRIX24_NEW_USER = 'BITRIX24_NEW_USER';

	const SOURCE_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';
	const SOURCE_TYPE_FORUM_POST = 'FORUM_POST';
	const SOURCE_TYPE_LOG_COMMENT = 'LOG_COMMENT';

	private $postTypeList = array(
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
		self::SOURCE_TYPE_BITRIX24_NEW_USER
	);
	private $commentTypeList = array(
		self::SOURCE_TYPE_BLOG_COMMENT,
		self::SOURCE_TYPE_FORUM_POST,
		self::SOURCE_TYPE_LOG_COMMENT
	);
	private $postTypeListInited = false;
	private $commentTypeListInited = false;

	public function addPostTypeList($type)
	{
		$this->postTypeList[] = $type;
	}

	public function addCommentTypeList($type)
	{
		$this->commentTypeList[] = $type;
	}

	public function getPostTypeList()
	{
		if ($this->postTypeListInited === false)
		{
			$moduleEvent = new \Bitrix\Main\Event(
				'socialnetwork',
				'onCommentAuxGetPostTypeList',
				array()
			);
			$moduleEvent->send();

			foreach ($moduleEvent->getResults() as $moduleEventResult)
			{
				if ($moduleEventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
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
							$this->addPostTypeList($type);
						}
					}
				}
			}

			$this->postTypeListInited = true;
		}

		return $this->postTypeList;
	}

	public function getCommentTypeList()
	{
		if ($this->commentTypeListInited === false)
		{
			$moduleEvent = new \Bitrix\Main\Event(
				'socialnetwork',
				'onCommentAuxGetCommentTypeList',
				array()
			);
			$moduleEvent->send();

			foreach ($moduleEvent->getResults() as $moduleEventResult)
			{
				if ($moduleEventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
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

	public function getSourceTypeList()
	{
		return array_merge($this->getPostTypeList(), $this->getCommentTypeList());
	}

	public function getParamsFromFields($fields = array())
	{
		$params = array();

		if (!empty($fields['SHARE_DEST']))
		{
			$paramsList = explode('|', $fields['SHARE_DEST']);
			if (!empty($paramsList))
			{
				foreach($paramsList as $pair)
				{
					list($key, $value) = explode('=', $pair);
					if (isset($key) && isset($value))
					{
						$params[$key] = $value;
					}
				}
			}
		}

		return $params;
	}

	public function getText()
	{
		static $userPage = null;
		static $parser = null;

		$result = '';
		$params = $this->params;
		$options = $this->options;

		$siteId = (!empty($options['siteId']) ? $options['siteId'] : SITE_ID);

		if (
			isset($params['sourcetype'])
			&& in_array($params['sourcetype'], $this->getSourceTypeList())
			&& isset($params['sourceid'])
			&& intval($params['sourceid']) > 0
			&& isset($params['taskid'])
			&& intval($params['taskid']) > 0
		)
		{
			if ($task = $this->getTask($params['taskid'], false))
			{
				if ($userPage === null)
				{
					$userPage = Option::get(
						'socialnetwork',
						'user_page',
						SITE_DIR.'company/personal/',
						$siteId
					).'user/#user_id#/';
				}

				$taskPath = (
					(!isset($options['cache']) || !$options['cache'])
					&& (!isset($options['im']) || !$options['im'])
					&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
						? str_replace(array("#user_id#", "#USER_ID#"), $task['RESPONSIBLE_ID'], $userPage).'tasks/task/view/'.$task['ID'].'/'
						: ''
				);

				$taskTitle = $task['TITLE'];
			}
			else
			{
				$taskPath = '';
				$taskTitle = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_NOT_FOUND');
			}

			if (in_array($params['sourcetype'], $this->getCommentTypeList()))
			{
				$commentPath = '';

				if (
					$params['sourcetype'] == self::SOURCE_TYPE_BLOG_COMMENT
					&& Loader::includeModule('blog')
					&& ($comment = \CBlogComment::getByID($params['sourceid']))
					&& ($post = \CBlogPost::getByID($comment['POST_ID']))
				)
				{
					$commentPath = (
						(!isset($options['im']) || !$options['im'])
						&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
						&& (!isset($options['mail']) || !$options['mail'])
							? str_replace(array("#user_id#", "#USER_ID#"), $post['AUTHOR_ID'], $userPage).'blog/'.$post['ID'].'/?commentId='.$params['sourceid'].'#com'.$params['sourceid']
							: ''
					);
				}
				else
				{
					$commentProvider = \Bitrix\Socialnetwork\Livefeed\Provider::getProvider($params['sourcetype']);

					if (
						$commentProvider
						&& (!isset($options['im']) || !$options['im'])
						&& (!isset($options['bPublicPage']) || !$options['bPublicPage'])
						&& (!isset($options['mail']) || !$options['mail'])
						&& isset($options['logId'])
						&& intval($options['logId']) > 0
					)
					{
						$commentProvider->setEntityId(intval($params['sourceid']));
						$commentProvider->setLogId($options['logId']);
						$commentProvider->initSourceFields();

						$commentPath = $commentProvider->getLiveFeedUrl();
					}
				}

				$suffix = (isset($options['suffix']) ? $options['suffix'] : '');
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_COMMENT_'.$params['sourcetype'].(!empty($suffix) ? '_'.$suffix : ''), array(
					'#TASK_NAME#' => (!empty($taskPath) ? '[URL='.$taskPath.']'.$taskTitle.'[/URL]' : $taskTitle),
					'#A_BEGIN#' => (!empty($commentPath) ? '[URL='.$commentPath.']' : ''),
					'#A_END#' => (!empty($commentPath) ? '[/URL]' : '')
				));
			}
			elseif (in_array($params['sourcetype'], $this->getPostTypeList()))
			{
				$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_POST_'.$params['sourcetype'], array(
					'#TASK_NAME#' => (!empty($taskPath) ? '[URL='.$taskPath.']'.$taskTitle.'[/URL]' : $taskTitle),
				));
			}

			if (!empty($result))
			{
				if ($parser === null)
				{
					$parser = new \CTextParser();
					$parser->allow = array("HTML" => "N", "ANCHOR" => "Y");
				}
				$result = $parser->convertText($result);
			}
		}

		return $result;
	}

	public function checkRecalcNeeded($fields, $params)
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
				&& !empty($handlerParams['taskid'])
				&& intval($handlerParams['taskid']) > 0
				&& ($task = $this->getTask(intval($handlerParams['taskid']), true))
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	private function getTask($taskId, $checkPermissions = true)
	{
		static $cache = array(
			'Y' => array(),
			'N' => array()
		);

		$result = false;
		$permissionCacheKey = ($checkPermissions ? 'Y' : 'N');

		if (
			isset($cache[$permissionCacheKey])
			&& isset($cache[$permissionCacheKey][$taskId])
		)
		{
			$result = $cache[$permissionCacheKey][$taskId];
		}
		elseif (Loader::includeModule('tasks'))
		{
			$res = \CTasks::getByID(intval($taskId), $checkPermissions);
			if ($task = $res->fetch())
			{
				$result = $cache[$permissionCacheKey][$taskId] = $task;
			}
			elseif(!$checkPermissions)
			{
				$result = $cache[$permissionCacheKey][$taskId] = false;
			}
		}

		return $result;
	}

	public function sendRatingNotification($fields = array(), $ratingVoteParams = array())
	{
		$userId = (
			is_array($ratingVoteParams)
			&& isset($ratingVoteParams['OWNER_ID'])
				? intval($ratingVoteParams['OWNER_ID'])
				: 0
		);

		if (
			$userId > 0
			&& is_array($fields)
			&& isset($fields["SHARE_DEST"])
			&& Loader::includeModule('im')
		)
		{
			$params = $this->getParamsFromFields($fields);
			if (!empty($params))
			{
				$this->setParams($params);

				$followValue = \CSocNetLogFollow::getExactValueByRating(
					$userId,
					$ratingVoteParams['ENTITY_TYPE_ID'],
					$ratingVoteParams['ENTITY_ID']
				);

				if ($followValue != "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID'],
						'ratingEntityTypeId' => $ratingVoteParams['ENTITY_TYPE_ID'],
						'ratingEntityId' => $ratingVoteParams['ENTITY_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';
					$ratingVoteParams["ENTITY_TITLE"] = $ratingVoteParams["ENTITY_MESSAGE"] = $this->getText();

					$messageFields = array(
						"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
						"TO_USER_ID" => $userId,
						"FROM_USER_ID" => intval($ratingVoteParams['USER_ID']),
						"NOTIFY_TYPE" => IM_NOTIFY_FROM,
						"NOTIFY_MODULE" => "main",
						"NOTIFY_EVENT" => "rating_vote",
						"NOTIFY_TAG" => "RATING|".($ratingVoteParams['VALUE'] >= 0 ? "" : "DL|")."BLOG_COMMENT|".$fields['ID'],
						"NOTIFY_MESSAGE" => \CIMEvent::getMessageRatingVote($ratingVoteParams),
						"NOTIFY_MESSAGE_OUT" => \CIMEvent::getMessageRatingVote($ratingVoteParams, true)
					);

					\CIMNotify::add($messageFields);
				}
			}
		}
	}
}