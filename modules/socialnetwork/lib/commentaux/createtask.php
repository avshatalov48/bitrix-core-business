<?php
namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class CreateTask extends Base
{
	const TYPE = 'CREATETASK';
	const POST_TEXT = 'commentAuxCreateTask';
	const SOURCE_TYPE_BLOG_POST = 'BLOG_POST';
	const SOURCE_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';

	private $sourceTypeList = array(self::SOURCE_TYPE_BLOG_POST, self::SOURCE_TYPE_BLOG_COMMENT);

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

		if (
			isset($params['sourcetype'])
			&& in_array($params['sourcetype'], $this->sourceTypeList)
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
						(!empty($options['siteId']) ? $options['siteId'] : SITE_ID)
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

			if ($params['sourcetype'] == self::SOURCE_TYPE_BLOG_COMMENT)
			{
				if (
					Loader::includeModule('blog')
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
					$commentPath = '';
				}

				$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_BLOG_COMMENT', array(
					'#TASK_NAME#' => (!empty($taskPath) ? '[URL='.$taskPath.']'.$taskTitle.'[/URL]' : $taskTitle),
					'#COMMENT_LINK#' => (!empty($commentPath) ? '[URL='.$commentPath.']'.Loc::getMessage('SONET_COMMENTAUX_CREATETASK_BLOG_COMMENT_LINK').'[/URL]' : Loc::getMessage('SONET_COMMENTAUX_CREATETASK_BLOG_COMMENT_LINK'))
				));
			}
			elseif ($params['sourcetype'] == self::SOURCE_TYPE_BLOG_POST)
			{
				if (
					Loader::includeModule('blog')
					&& ($post = \CBlogPost::getByID($params['sourceid']))
				)
				{
					$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_BLOG_POST', array(
						'#TASK_NAME#' => (!empty($taskPath) ? '[URL='.$taskPath.']'.$taskTitle.'[/URL]' : $taskTitle)
					));
				}
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
					'BLOG_COMMENT',
					$fields['ID']
				);

				if ($followValue != "N")
				{
					$ratingVoteParams['ENTITY_LINK'] = $this->getRatingCommentLink(array(
						'commentId' => $fields['ID'],
						'commentAuthorId' => $ratingVoteParams['OWNER_ID']
					));

					$ratingVoteParams["ENTITY_PARAM"] = 'COMMENT';
					$ratingVoteParams["ENTITY_MESSAGE"] = $this->getText();

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