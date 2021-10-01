<?php

namespace Bitrix\Socialnetwork\CommentAux;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class CreateTask extends CreateEntity
{
	public const TYPE = 'CREATETASK';
	public const POST_TEXT = 'commentAuxCreateTask';

	public function getText(): string
	{
		static $userPage = null;
		static $parser = null;

		$result = '';
		$params = $this->params;
		$options = $this->options;

		$siteId = (!empty($options['siteId']) ? $options['siteId'] : SITE_ID);

		if (
			!isset($params['sourcetype'], $params['sourceid'], $params['taskid'])
			|| (int)$params['sourceid'] <= 0
			|| (int)$params['taskid'] <= 0
			|| !in_array($params['sourcetype'], $this->getSourceTypeList(), true)
		)
		{
			return $result;
		}

		if ($provider = $this->getLivefeedProvider())
		{
			$options['suffix'] = $provider->getSuffix($options['suffix']);
		}

		if ($userPage === null)
		{
			$userPage = Option::get(
					'socialnetwork',
					'user_page',
					SITE_DIR . 'company/personal/',
					$siteId
				) . 'user/#user_id#/';
		}

		if ($task = $this->getTask($params['taskid'], false))
		{
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

		if (in_array($params['sourcetype'], $this->getCommentTypeList(), true))
		{
			$sourceData = $this->getSourceCommentData([
				'userPage' => $userPage,
			]);

			$suffix = (
				$options['suffix']
					? '_' . $options['suffix']
					: (!empty($sourceData['suffix']) ? '_' . $sourceData['suffix'] : '')
			);

			$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_COMMENT_' . $params['sourcetype'] . $suffix, [
				'#TASK_NAME#' => (!empty($taskPath) ? '[URL=' . $taskPath . ']' . $taskTitle . '[/URL]' : $taskTitle),
				'#A_BEGIN#' => (!empty($sourceData['path']) ? '[URL=' . $sourceData['path'] . ']' : ''),
				'#A_END#' => (!empty($sourceData['path']) ? '[/URL]' : '')
			]);
		}
		elseif (in_array($params['sourcetype'], $this->getPostTypeList(), true))
		{
			$suffix = ($options['suffix'] ?? ($params['sourcetype'] === static::SOURCE_TYPE_BLOG_POST ? '2' : ''));

			$result = Loc::getMessage('SONET_COMMENTAUX_CREATETASK_POST_' . $params['sourcetype'].(!empty($suffix) ? '_' . $suffix : ''), [
				'#TASK_NAME#' => (!empty($taskPath) ? '[URL='.$taskPath.']'.$taskTitle.'[/URL]' : $taskTitle),
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

		return $result;
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
				&& !empty($handlerParams['taskid'])
				&& (int)$handlerParams['taskid'] > 0
				&& ($this->getTask((int)$handlerParams['taskid']))
			)
			{
				$result = true;
			}
		}

		return $result;
	}

	public function getTask($taskId, $checkPermissions = true)
	{
		static $cache = array(
			'Y' => [],
			'N' => [],
		);

		$result = false;
		$permissionCacheKey = ($checkPermissions ? 'Y' : 'N');

		if (isset($cache[$permissionCacheKey][$taskId]))
		{
			$result = $cache[$permissionCacheKey][$taskId];
		}
		elseif (Loader::includeModule('tasks'))
		{
			$res = \CTasks::getByID((int)$taskId, $checkPermissions);
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

	protected function getForumType(): string
	{
		return \Bitrix\Forum\Comments\Service\Manager::TYPE_TASK_CREATED;
	}

	protected function getForumServiceData(array $commentData = [])
	{
		return (!empty($commentData['SERVICE_DATA']) ? $commentData['SERVICE_DATA'] : $commentData['POST_MESSAGE']);
	}

	protected function getForumMessageFields(): array
	{
		return [ 'SERVICE_DATA', 'POST_MESSAGE' ];
	}

	protected function getSocNetData($data = ''): array
	{
		$result = [];

		$paramsList = explode('|', $data);

		if (!empty($paramsList))
		{
			foreach ($paramsList as $pair)
			{
				[ $key, $value ] = explode('=', $pair);
				if (isset($key, $value))
				{
					$result[$key] = $value;
				}
			}
		}

		return $result;
	}
}
